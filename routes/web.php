<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DentalLabController;
use App\Http\Controllers\DentalTreatmentController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسارات نظام اسيد (ASID)
|--------------------------------------------------------------------------
| النظام يُدار من مستخدم واحد فقط (مدير/طبيب) بصلاحية مطلقة،
| لذلك تُطبّق حماية auth فقط دون أي طبقة أدوار أو صلاحيات إضافية.
*/

Route::middleware(['auth'])->group(function () {

    // -------------------- لوحة التحكم --------------------
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // -------------------- المرضى --------------------
    Route::resource('patients', PatientController::class);
    Route::post('patients/{patient}/documents', [PatientController::class, 'uploadDocument'])
        ->name('patients.documents.upload');

    // -------------------- المواعيد --------------------
    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    // -------------------- مخطط الأسنان والمعالجات --------------------
    Route::get('patients/{patient}/dental-chart', [DentalTreatmentController::class, 'chart'])->name('dental.chart');
    Route::post('patients/{patient}/dental-treatments', [DentalTreatmentController::class, 'store'])->name('dental.treatments.store');
    Route::delete('patients/{patient}/dental-treatments/{treatment}', [DentalTreatmentController::class, 'destroy'])->name('dental.treatments.destroy');

    // -------------------- المعامل الخارجية --------------------
    Route::resource('labs', DentalLabController::class)->except(['show']);

    // -------------------- الفواتير --------------------
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // -------------------- الأقساط --------------------
    Route::get('installments', [InstallmentController::class, 'index'])->name('installments.index');
    Route::get('installments/{installment}', [InstallmentController::class, 'show'])->name('installments.show');
    Route::post('installments/{installment}/schedule', [InstallmentController::class, 'storePaymentSchedule'])->name('installments.schedule.store');
    Route::post('installments/{installment}/payments/{payment}/pay', [InstallmentController::class, 'recordPayment'])->name('installments.payments.pay');

    // -------------------- السندات --------------------
    Route::get('vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::delete('vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // -------------------- المخزن الطبي --------------------
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('inventory/{item}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('inventory/{item}/transactions', [InventoryController::class, 'storeTransaction'])->name('inventory.transactions.store');
    Route::delete('inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
});

// -------------------- مسارات التوثيق (Breeze) --------------------
require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

use Illuminate\Support\Facades\Artisan;

Route::get('/run-seed', function () {
    try {
        // هذا الأمر يمسح الجداول القديمة ويعيد بناءها مع تشغيل السييدر الجديد
        Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        return 'تم تحديث قاعدة البيانات وتشغيل الـ Seeder بالبيانات الجديدة بنجاح!';
    } catch (\Exception $e) {
        return 'حدث خطأ أثناء التحديث: ' . $e->getMessage();
    }
});
