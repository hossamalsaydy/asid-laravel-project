{{--
    مكون AlertModal الموحد
    ====================
    نافذة منبثقة عامة للرسائل التأكيدية (حذف، إلغاء، تحذير، نجاح...).
    يتم تضمينه مرة واحدة فقط داخل admin.blade.php، ثم يُستدعى من أي مكان
    في النظام عبر دالة JavaScript عامة: AlertModal.confirm({...})

    مثال استخدام في أي صفحة أو سكربت:

    AlertModal.confirm({
        title: 'تأكيد الحذف',
        message: 'هل أنت متأكد من حذف بيانات هذا المريض؟ لا يمكن التراجع عن هذا الإجراء.',
        confirmText: 'حذف',
        cancelText: 'إلغاء',
        type: 'danger', // danger | warning | success | info
        onConfirm: function () {
            document.getElementById('delete-form-1').submit();
        }
    });
--}}

<div id="alert-modal-overlay" class="alert-modal-overlay" aria-hidden="true">
    <div class="alert-modal-box" role="alertdialog" aria-modal="true" aria-labelledby="alert-modal-title">
        <div class="alert-modal-icon" id="alert-modal-icon">!</div>
        <h3 class="alert-modal-title" id="alert-modal-title">عنوان التنبيه</h3>
        <p class="alert-modal-message" id="alert-modal-message">نص الرسالة هنا.</p>
        <div class="alert-modal-actions">
            <button type="button" id="alert-modal-cancel-btn" class="alert-modal-btn alert-modal-btn-cancel">إلغاء</button>
            <button type="button" id="alert-modal-confirm-btn" class="alert-modal-btn alert-modal-btn-confirm">تأكيد</button>
        </div>
    </div>
</div>

<style>
    /* ============ تنسيق AlertModal ============ */
    .alert-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 20, 0.55);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease;
        padding: 16px;
    }

    .alert-modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
    }

    .alert-modal-box {
        background: #ffffff;
        border-radius: 16px;
        max-width: 420px;
        width: 100%;
        padding: 28px 24px;
        text-align: center;
        transform: translateY(12px) scale(0.96);
        transition: transform 0.2s ease;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
        font-family: 'Tajawal', sans-serif;
    }

    .alert-modal-overlay.is-open .alert-modal-box {
        transform: translateY(0) scale(1);
    }

    .alert-modal-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 26px;
        font-weight: 700;
        color: #fff;
        background: var(--primary-color, #0D7C3D);
    }

    .alert-modal-icon.type-danger  { background: #DC2626; }
    .alert-modal-icon.type-warning { background: var(--accent-color, #F59E0B); }
    .alert-modal-icon.type-success { background: var(--primary-color, #0D7C3D); }
    .alert-modal-icon.type-info    { background: #2563EB; }

    .alert-modal-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 8px;
        color: #1F2937;
    }

    .alert-modal-message {
        font-size: 14.5px;
        color: #4B5563;
        line-height: 1.7;
        margin: 0 0 22px;
    }

    .alert-modal-actions {
        display: flex;
        gap: 10px;
    }

    .alert-modal-btn {
        flex: 1;
        border: none;
        border-radius: 10px;
        padding: 11px 14px;
        font-family: 'Tajawal', sans-serif;
        font-size: 14.5px;
        font-weight: 700;
        cursor: pointer;
        transition: filter 0.15s ease;
    }

    .alert-modal-btn:hover { filter: brightness(0.92); }

    .alert-modal-btn-cancel {
        background: #F3F4F6;
        color: #374151;
    }

    .alert-modal-btn-confirm {
        background: var(--primary-color, #0D7C3D);
        color: #fff;
    }

    .alert-modal-btn-confirm.type-danger { background: #DC2626; }
</style>

<script>
    /**
     * كائن AlertModal العام
     * يوفر واجهة برمجية بسيطة لاستدعاء نافذة التأكيد من أي مكان في النظام.
     */
    const AlertModal = (function () {
        const overlay     = document.getElementById('alert-modal-overlay');
        const box         = document.getElementById('alert-modal-box');
        const iconEl      = document.getElementById('alert-modal-icon');
        const titleEl     = document.getElementById('alert-modal-title');
        const messageEl   = document.getElementById('alert-modal-message');
        const confirmBtn  = document.getElementById('alert-modal-confirm-btn');
        const cancelBtn   = document.getElementById('alert-modal-cancel-btn');

        let currentOnConfirm = null;

        // خريطة الأيقونات النصية البسيطة حسب نوع التنبيه
        const iconMap = {
            danger: '!',
            warning: '!',
            success: '✓',
            info: 'i',
        };

        function open(options) {
            const settings = Object.assign({
                title: 'تأكيد العملية',
                message: 'هل أنت متأكد من إتمام هذا الإجراء؟',
                confirmText: 'تأكيد',
                cancelText: 'إلغاء',
                type: 'danger', // danger | warning | success | info
                onConfirm: null,
                onCancel: null,
            }, options);

            titleEl.textContent = settings.title;
            messageEl.textContent = settings.message;
            confirmBtn.textContent = settings.confirmText;
            cancelBtn.textContent = settings.cancelText;

            // إعادة ضبط الكلاسات الخاصة بالنوع
            iconEl.className = 'alert-modal-icon type-' + settings.type;
            iconEl.textContent = iconMap[settings.type] || '!';

            confirmBtn.className = 'alert-modal-btn alert-modal-btn-confirm' +
                (settings.type === 'danger' ? ' type-danger' : '');

            currentOnConfirm = settings.onConfirm;
            currentOnCancel = settings.onCancel;

            document.getElementById('alert-modal-overlay').classList.add('is-open');
            document.getElementById('alert-modal-overlay').setAttribute('aria-hidden', 'false');
        }

        function close() {
            document.getElementById('alert-modal-overlay').classList.remove('is-open');
            document.getElementById('alert-modal-overlay').setAttribute('aria-hidden', 'true');
            currentOnConfirm = null;
        }

        // زر التأكيد
        confirmBtn.addEventListener('click', function () {
            if (typeof currentOnConfirm === 'function') {
                currentOnConfirm();
            }
            close();
        });

        // زر الإلغاء
        cancelBtn.addEventListener('click', function () {
            if (typeof currentOnCancel === 'function') {
                currentOnCancel();
            }
            close();
        });

        // إغلاق عند الضغط خارج الصندوق
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                close();
            }
        });

        // إغلاق عند الضغط على Esc
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                close();
            }
        });

        // الواجهة العامة المتاحة للاستخدام في أي مكان بالنظام
        return {
            confirm: open,
            close: close,
        };
    })();
</script>
