<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait: HasSyncFields
 * ====================
 * يُضاف إلى كل Model يحتاج حقول المزامنة (local_id, is_synced, synced_at).
 * يقوم تلقائياً بتوليد local_id (UUID) عند إنشاء أي سجل جديد،
 * ويوفر Scope وdوال مساعدة للتعامل مع السجلات غير المتزامنة.
 */
trait HasSyncFields
{
    /**
     * تفعيل الحدث الخاص بتوليد local_id تلقائياً عند الإنشاء.
     */
    protected static function bootHasSyncFields(): void
    {
        static::creating(function ($model) {
            if (empty($model->local_id)) {
                $model->local_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope: جلب السجلات التي لم تتم مزامنتها بعد مع السيرفر السحابي.
     */
    public function scopeUnsynced($query)
    {
        return $query->where('is_synced', false);
    }

    /**
     * تعليم السجل كمُزامَن مع تسجيل وقت المزامنة.
     */
    public function markAsSynced(): void
    {
        $this->update([
            'is_synced' => true,
            'synced_at' => now(),
        ]);
    }
}
