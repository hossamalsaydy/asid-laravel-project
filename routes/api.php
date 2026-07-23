<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسارات API - نظام اسيد (ASID)
|--------------------------------------------------------------------------
| مخصصة بالكامل لآلية المزامنة بين النسخة المحلية (Offline) والسيرفر السحابي.
| محمية بـ auth:sanctum لأن الجهاز المحلي يحتاج مصادقة قبل رفع/تحميل البيانات.
|
| ملاحظة (Laravel 11+): تأكد من تفعيل ملف الراوت هذا داخل bootstrap/app.php:
|   ->withRouting(
|       web: __DIR__.'/../routes/web.php',
|       api: __DIR__.'/../routes/api.php',
|       ...
|   )
*/

Route::middleware(['auth:sanctum'])->prefix('sync')->group(function () {
    Route::post('{table}/push', [SyncController::class, 'push'])->name('api.sync.push');
    Route::get('{table}/pull', [SyncController::class, 'pull'])->name('api.sync.pull');
});
