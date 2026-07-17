<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'اسيد') | نظام إدارة عيادة الأسنان</title>

    {{-- خط Tajawal محلي بالكامل لضمان عمله دون إنترنت (Offline-First) --}}
    {{-- تأكد من وضع ملفات الخط داخل: public/assets/fonts/tajawal/ --}}
    <link rel="stylesheet" href="{{ asset('assets/fonts/tajawal/tajawal.css') }}">

    <style>
        /* ============================================================
           المتغيرات العامة (الهوية البصرية لنظام اسيد)
           ============================================================ */
        :root {
            --primary-color: #0D7C3D;   /* أخضر غامق طبي رسمي */
            --primary-dark: #095C2D;
            --accent-color: #F59E0B;    /* ذهبي دافئ للمواعيد والتحذيرات */
            --bg-color: #F4F6F5;
            --surface-color: #FFFFFF;
            --text-color: #1F2937;
            --text-muted: #6B7280;
            --border-color: #E5E7EB;
            --danger-color: #DC2626;
            --sidebar-width: 260px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Tajawal', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            direction: rtl;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        /* ============================================================
           تخطيط الصفحة العام: شريط جانبي + محتوى
           ============================================================ */
        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* -------------------- الشريط الجانبي -------------------- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: #fff;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            transition: transform 0.25s ease;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 22px 20px;
            font-size: 20px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-nav {
            list-style: none;
            margin: 0;
            padding: 12px 0;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            font-size: 15px;
            color: rgba(255,255,255,0.9);
            border-right: 3px solid transparent;
            transition: background 0.15s ease;
        }

        .sidebar-nav li a:hover,
        .sidebar-nav li a.active {
            background: rgba(255,255,255,0.12);
            border-right-color: var(--accent-color);
        }

        /* -------------------- المحتوى الرئيسي -------------------- */
        .main-wrapper {
            margin-right: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-right 0.25s ease, width 0.25s ease;
        }

        /* -------------------- الشريط العلوي (Navbar) -------------------- */
        .topbar {
            background: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .topbar-left  { display: flex; align-items: center; gap: 10px; }

        .sidebar-toggle-btn {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: var(--text-color);
        }

        /* -------------------- زر وضع الستر -------------------- */
        .privacy-toggle-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #FEF3E2;
            color: #92400E;
            border: 1px solid var(--accent-color);
            border-radius: 8px;
            padding: 7px 14px;
            font-family: 'Tajawal', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .privacy-toggle-btn:hover { background: #FDE7C4; }

        .privacy-toggle-btn.is-active {
            background: var(--accent-color);
            color: #fff;
        }

        /* -------------------- محتوى الصفحة -------------------- */
        .page-content {
            padding: 22px;
            flex: 1;
        }

        /* ============================================================
           وضع الستر (Privacy Mode)
           عند تفعيله على مستوى body، تُطمس كل العناصر التي تحمل
           class="sensitive-data" (أسماء المريضات، صور الأشعة الحساسة...)
           ============================================================ */
        body.privacy-mode .sensitive-data {
            filter: blur(7px);
            user-select: none;
            transition: filter 0.15s ease;
        }

        body.privacy-mode .sensitive-data:hover {
            filter: blur(2px); /* كشف مؤقت سريع عند تمرير الفأرة، اختياري */
        }

        /* ============================================================
           مؤشر التحميل (Page Loader)
           دائرة ذهبية دوّارة في منتصفها أيقونة ضرس تنط للأعلى والأسفل
           ============================================================ */
        .page-loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.15s ease;
        }

        .page-loader-overlay.is-active {
            opacity: 1;
            visibility: visible;
        }

        .page-loader-spinner {
            position: relative;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 6px solid #FDE7C4;
            border-top-color: var(--accent-color);
            animation: spin 0.9s linear infinite;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-loader-tooth {
            font-size: 30px;
            animation: bounce-tooth 0.8s ease-in-out infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        @keyframes bounce-tooth {
            0%, 100% { transform: translateY(-4px); }
            50%      { transform: translateY(4px); }
        }

        /* ============================================================
           التنبيهات المنبثقة السريعة (Toast Notifications)
           ============================================================ */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 20px; /* في الواجهة RTL تظهر التنبيهات من جهة اليسار لعدم إزعاج القراءة */
            z-index: 99998;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 340px;
        }

        .toast-item {
            background: var(--surface-color);
            border-right: 4px solid var(--primary-color);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            transform: translateX(-120%);
            transition: transform 0.25s ease;
        }

        .toast-item.is-visible { transform: translateX(0); }
        .toast-item.toast-success { border-right-color: var(--primary-color); }
        .toast-item.toast-error   { border-right-color: var(--danger-color); }
        .toast-item.toast-warning { border-right-color: var(--accent-color); }
        .toast-item.toast-info    { border-right-color: #2563EB; }

        /* ============================================================
           التجاوب مع الشاشات الصغيرة (هواتف/أجهزة لوحية)
           ============================================================ */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(100%);
                box-shadow: -4px 0 20px rgba(0,0,0,0.2);
            }
            .sidebar.is-open { transform: translateX(0); }

            .main-wrapper {
                margin-right: 0;
                width: 100%;
            }

            .sidebar-toggle-btn { display: block; }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.4);
                z-index: 35;
            }
            .sidebar-overlay.is-open { display: block; }
        }

        /* ============================================================
           مكتبة عناصر واجهة مشتركة (Cards / Buttons / Tables / Forms)
           تُستخدم في جميع صفحات موديولات النظام لضمان اتساق التصميم
           ============================================================ */
        .card {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-title { font-size: 16px; font-weight: 700; margin: 0; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-right: 4px solid var(--primary-color);
            border-radius: 12px;
            padding: 16px 18px;
        }

        .stat-box .stat-value { font-size: 22px; font-weight: 800; color: var(--primary-color); }
        .stat-box .stat-label { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
        .stat-box.accent { border-right-color: var(--accent-color); }
        .stat-box.accent .stat-value { color: var(--accent-color); }
        .stat-box.danger { border-right-color: var(--danger-color); }
        .stat-box.danger .stat-value { color: var(--danger-color); }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 9px;
            padding: 9px 16px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: filter 0.15s ease;
        }
        .btn:hover { filter: brightness(0.93); }
        .btn-primary { background: var(--primary-color); color: #fff; }
        .btn-accent  { background: var(--accent-color); color: #fff; }
        .btn-danger  { background: var(--danger-color); color: #fff; }
        .btn-outline { background: #fff; color: var(--text-color); border: 1px solid var(--border-color); }
        .btn-sm { padding: 6px 11px; font-size: 12.5px; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        table.data-table th {
            background: #F0FBF4;
            color: var(--primary-dark);
            text-align: right;
            padding: 11px 12px;
            font-weight: 700;
            border-bottom: 2px solid var(--border-color);
        }
        table.data-table td {
            padding: 11px 12px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        table.data-table tbody tr:hover { background: #FAFBFA; }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-green  { background: #DCFCE7; color: #15803D; }
        .badge-gold   { background: #FEF3C7; color: #92400E; }
        .badge-red    { background: #FEE2E2; color: #B91C1C; }
        .badge-gray   { background: #F3F4F6; color: #4B5563; }
        .badge-blue   { background: #DBEAFE; color: #1D4ED8; }

        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-color);
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 9px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            background: #fff;
            color: var(--text-color);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13,124,61,0.12);
        }
        .form-error { color: var(--danger-color); font-size: 12.5px; margin-top: 4px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
        .form-check { display: flex; align-items: center; gap: 8px; }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .pagination-wrapper { margin-top: 16px; }

        @yield('extra-styles')
    </style>
</head>
<body>

    <div class="app-shell">

        {{-- ================= الشريط الجانبي ================= --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <span>🦷</span>
                <span>اسيد | ASID</span>
            </div>
            <ul class="sidebar-nav">
                <li><a href="{{ route('dashboard') ?? '#' }}">🏠 لوحة التحكم</a></li>
                <li><a href="#">🧑‍⚕️ المرضى</a></li>
                <li><a href="#">📅 المواعيد</a></li>
                <li><a href="#">🦷 مخطط الأسنان والمعالجات</a></li>
                <li><a href="#">🏭 المعامل الخارجية</a></li>
                <li><a href="#">🧾 الفواتير والأقساط</a></li>
                <li><a href="#">💵 السندات</a></li>
                <li><a href="#">📦 المخزن الطبي</a></li>
            </ul>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- ================= المحتوى الرئيسي ================= --}}
        <div class="main-wrapper">

            {{-- الشريط العلوي --}}
            <header class="topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="فتح القائمة">☰</button>
                    <h2 style="margin:0; font-size:17px;">@yield('page-title', 'لوحة التحكم')</h2>
                </div>

                <div class="topbar-right">
                    {{-- زر وضع الستر --}}
                    <button type="button" class="privacy-toggle-btn" id="privacyToggleBtn">
                        <span id="privacyIcon">🙈</span>
                        <span id="privacyLabel">وضع الستر</span>
                    </button>

                    <span style="font-size:14px; color:var(--text-muted);">
                        {{ now()->translatedFormat('l، d F Y') }}
                    </span>
                </div>
            </header>

            {{-- محتوى الصفحة --}}
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ================= مؤشر التحميل (Page Loader) ================= --}}
    <div class="page-loader-overlay" id="pageLoader">
        <div class="page-loader-spinner">
            <span class="page-loader-tooth">🦷</span>
        </div>
    </div>

    {{-- ================= حاوية التنبيهات (Toasts) ================= --}}
    <div class="toast-container" id="toastContainer"></div>

    {{-- ================= مكون AlertModal الموحد ================= --}}
    @include('components.alert-modal')

    <script>
        /* ================================================================
           1) منطق مؤشر التحميل (Page Loader)
           يظهر عند الانتقال بين الصفحات (روابط) أو عند إرسال أي نموذج
           ================================================================ */
        const pageLoader = document.getElementById('pageLoader');

        function showPageLoader() {
            pageLoader.classList.add('is-active');
        }

        function hidePageLoader() {
            pageLoader.classList.remove('is-active');
        }

        // إظهار المؤشر عند الضغط على أي رابط داخلي (باستثناء الروابط الخارجية أو التي بها target=_blank)
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (!link) return;
            if (link.target === '_blank') return;
            if (link.hasAttribute('data-no-loader')) return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

            showPageLoader();
        });

        // إظهار المؤشر عند إرسال أي نموذج عادي في النظام
        document.addEventListener('submit', function (e) {
            if (e.target.hasAttribute('data-no-loader')) return;
            showPageLoader();
        });

        // إخفاء المؤشر تلقائياً عند اكتمال تحميل الصفحة الجديدة
        window.addEventListener('pageshow', hidePageLoader);

        /* ================================================================
           2) منطق وضع الستر (Privacy Mode)
           يُحفظ الاختيار في localStorage ليبقى مفعّلاً بين الصفحات
           ================================================================ */
        const privacyToggleBtn = document.getElementById('privacyToggleBtn');
        const privacyIcon = document.getElementById('privacyIcon');
        const privacyLabel = document.getElementById('privacyLabel');
        const PRIVACY_STORAGE_KEY = 'asid_privacy_mode';

        function applyPrivacyState(isActive) {
            document.body.classList.toggle('privacy-mode', isActive);
            privacyToggleBtn.classList.toggle('is-active', isActive);
            privacyIcon.textContent = isActive ? '🙉' : '🙈';
            privacyLabel.textContent = isActive ? 'إلغاء الستر' : 'وضع الستر';
        }

        privacyToggleBtn.addEventListener('click', function () {
            const isCurrentlyActive = document.body.classList.contains('privacy-mode');
            const newState = !isCurrentlyActive;
            applyPrivacyState(newState);
            localStorage.setItem(PRIVACY_STORAGE_KEY, newState ? '1' : '0');
        });

        // استرجاع حالة وضع الستر المحفوظة عند تحميل أي صفحة
        (function initPrivacyMode() {
            const saved = localStorage.getItem(PRIVACY_STORAGE_KEY) === '1';
            applyPrivacyState(saved);
        })();

        /* ================================================================
           3) منطق التنبيهات السريعة (Toast Notifications)
           دالة عامة showToast(message, type) متاحة من أي مكان في النظام
           ================================================================ */
        const toastContainer = document.getElementById('toastContainer');
        const toastIcons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

        function showToast(message, type = 'success', duration = 4000) {
            const toast = document.createElement('div');
            toast.className = `toast-item toast-${type}`;
            toast.innerHTML = `<span>${toastIcons[type] || 'ℹ️'}</span><span>${message}</span>`;
            toastContainer.appendChild(toast);

            // تأخير بسيط لتفعيل حركة الدخول (transition)
            requestAnimationFrame(() => toast.classList.add('is-visible'));

            setTimeout(() => {
                toast.classList.remove('is-visible');
                setTimeout(() => toast.remove(), 250);
            }, duration);
        }

        // عرض تلقائي لرسائل الجلسة (Flash Messages) القادمة من Laravel Controllers
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', () => showToast(@json(session('success')), 'success'));
        @endif
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', () => showToast(@json(session('error')), 'error'));
        @endif
        @if(session('warning'))
            document.addEventListener('DOMContentLoaded', () => showToast(@json(session('warning')), 'warning'));
        @endif
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () => showToast(@json($errors->first()), 'error'));
        @endif

        /* ================================================================
           4) منطق فتح/إغلاق القائمة الجانبية في الشاشات الصغيرة
           ================================================================ */
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

        function toggleSidebar() {
            sidebar.classList.toggle('is-open');
            sidebarOverlay.classList.toggle('is-open');
        }

        sidebarToggleBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        /* ================================================================
           إعداد إعدادات Ajax الافتراضية لإرسال CSRF Token تلقائياً
           (مفيد لاحقاً عند بناء طلبات fetch/AJAX الخاصة بالمزامنة)
           ================================================================ */
        window.ASID_CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    </script>

    @yield('extra-scripts')
</body>
</html>
