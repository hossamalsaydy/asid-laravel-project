<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DentalLab;
use App\Models\DentalTreatment;
use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * SyncController
 * ===============
 * نقطة النهاية الموحدة لمزامنة كل الجداول بين النسخة المحلية (Offline)
 * والسيرفر السحابي، دون الحاجة لكتابة كنترولر مزامنة منفصل لكل جدول.
 *
 * آلية العمل:
 *  - Push:  ترفع النسخة المحلية سجلاتها غير المتزامنة (is_synced = false)
 *           إلى هذا الـ API، ويتم حفظها/تحديثها في السيرفر بالاعتماد على local_id
 *           (لأن id التسلسلي يختلف بين كل جهاز والسيرفر).
 *  - Pull:  تطلب النسخة المحلية كل السجلات التي تغيّرت في السيرفر بعد وقت معين
 *           (updated_at > after) لتحميلها وتحديث قاعدتها المحلية.
 *
 * كل الجداول المزامنة تشترك في نفس الحقول الثلاثة: local_id, is_synced, synced_at
 * لذلك تم بناء هذا الكنترولر بشكل عام (Generic) يعمل على أي جدول من القائمة البيضاء أدناه.
 */
class SyncController extends Controller
{
    /**
     * القائمة البيضاء لأسماء الجداول القابلة للمزامنة وربطها بالموديل المقابل.
     * أي جدول غير موجود هنا يُرفض تلقائياً حمايةً من العبث بجداول غير مصرح بها.
     */
    private array $syncableModels = [
        'patients'              => Patient::class,
        'patient_documents'     => PatientDocument::class,
        'appointments'          => Appointment::class,
        'dental_treatments'     => DentalTreatment::class,
        'dental_labs'           => DentalLab::class,
        'invoices'              => Invoice::class,
        'invoice_items'         => InvoiceItem::class,
        'installments'          => Installment::class,
        'installment_payments'  => InstallmentPayment::class,
        'vouchers'              => Voucher::class,
        'inventory_items'       => InventoryItem::class,
        'inventory_transactions' => InventoryTransaction::class,
    ];

    /**
     * استقبال سجلات من الجهاز المحلي وحفظها/تحديثها في قاعدة بيانات السيرفر.
     *
     * الطلب المتوقع:
     * POST /api/sync/{table}/push
     * { "records": [ { "local_id": "...", "name": "...", ... }, ... ] }
     *
     * الرد: قائمة local_id التي تمت مزامنتها بنجاح، ليقوم الجهاز المحلي
     * بتعليمها is_synced = true محلياً.
     */
    public function push(Request $request, string $table): JsonResponse
    {
        $modelClass = $this->resolveModel($table);

        $request->validate([
            'records'             => ['required', 'array', 'min:1'],
            'records.*.local_id'  => ['required', 'string'],
        ]);

        $syncedLocalIds = [];

        DB::transaction(function () use ($request, $modelClass, &$syncedLocalIds) {
            foreach ($request->input('records') as $record) {
                // استبعاد الحقول التي يجب ألا تُفرض من الجهاز المرسل (id التسلسلي الخاص بالسيرفر)
                unset($record['id']);

                /** @var \Illuminate\Database\Eloquent\Model $modelClass */
                $modelClass::updateOrCreate(
                    ['local_id' => $record['local_id']],
                    array_merge($record, [
                        'is_synced' => true,
                        'synced_at' => now(),
                    ])
                );

                $syncedLocalIds[] = $record['local_id'];
            }
        });

        return response()->json([
            'status'          => 'success',
            'table'           => $table,
            'synced_count'    => count($syncedLocalIds),
            'synced_local_ids' => $syncedLocalIds,
        ]);
    }

    /**
     * تحميل السجلات التي تغيّرت في السيرفر بعد وقت معين، لمزامنتها إلى الجهاز المحلي.
     *
     * الطلب المتوقع:
     * GET /api/sync/{table}/pull?after=2026-07-01T00:00:00Z
     */
    public function pull(Request $request, string $table): JsonResponse
    {
        $modelClass = $this->resolveModel($table);

        $request->validate(['after' => ['nullable', 'date']]);

        $query = $modelClass::query();

        if ($request->filled('after')) {
            $query->where('updated_at', '>', $request->date('after'));
        }

        $records = $query->orderBy('updated_at')->limit(500)->get();

        return response()->json([
            'status'  => 'success',
            'table'   => $table,
            'count'   => $records->count(),
            'records' => $records,
            // وقت الخادم الحالي، يُستخدم كنقطة بداية (after) في طلب المزامنة القادم
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * التحقق من أن اسم الجدول المطلوب مسموح بمزامنته، وإرجاع الموديل المرتبط به.
     */
    private function resolveModel(string $table): string
    {
        abort_if(
            !array_key_exists($table, $this->syncableModels),
            Response::HTTP_NOT_FOUND,
            'الجدول المطلوب غير متاح للمزامنة.'
        );

        return $this->syncableModels[$table];
    }
}
