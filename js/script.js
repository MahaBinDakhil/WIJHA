/* =====================================================
   وِجهة — ملف JavaScript المشترك
   -----------------------------------------------------
   كل وحدة تضيف كودها تحت عنوان جزئها في آخر الملف.
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ---------- مشترك: قائمة الجوال ---------- */
    const menuToggle = document.getElementById('menuToggle');
    const mainNav    = document.getElementById('mainNav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function () {
            const isOpen = mainNav.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', isOpen);
            menuToggle.textContent = isOpen ? '✕' : '☰';
        });
    }


    /* ===== الجزء 1: الرئيسية + الجولات + الدخول ===== */

    /* --- الوضع الليلي (يتذكر اختيارك حتى لو قفلتي الصفحة) --- */
    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(isDark) {
        document.body.classList.toggle('dark', isDark);
        if (themeToggle) themeToggle.textContent = isDark ? '☀️' : '🌙';
    }

    let savedTheme = null;
    try { savedTheme = localStorage.getItem('wijha-theme'); } catch (e) {}
    applyTheme(savedTheme === 'dark');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const isDark = !document.body.classList.contains('dark');
            applyTheme(isDark);
            try { localStorage.setItem('wijha-theme', isDark ? 'dark' : 'light'); } catch (e) {}
        });
    }

    /* --- نموذج الدخول: التحقق قبل الإرسال --- */
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        const fields = [
            document.getElementById('username'),
            document.getElementById('password')
        ];

        loginForm.addEventListener('submit', function (event) {
            let valid = true;
            fields.forEach(function (input) {
                if (input.value.trim() === '') {
                    input.classList.add('invalid');   // يظهر رسالة الخطأ تحته
                    valid = false;
                } else {
                    input.classList.remove('invalid');
                }
            });
            if (!valid) event.preventDefault();       // يوقف الإرسال
        });

        // تختفي رسالة الخطأ أول ما تبدأ تكتب
        fields.forEach(function (input) {
            input.addEventListener('input', function () {
                if (input.value.trim() !== '') input.classList.remove('invalid');
            });
        });

        // زر إظهار/إخفاء كلمة المرور
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput  = document.getElementById('password');
        togglePassword.addEventListener('click', function () {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            togglePassword.textContent = show ? '🙈' : '👁';
        });
    }

    /* (لاحقاً هنا: سلايدر الرئيسية + فلترة الجولات) */


    /* ===== الجزء 2: تفاصيل الجولة + الحجز =====
       المطلوب هنا:
       - التحقق من نموذج الحجز (الجوال 05xxxxxxxx، الإيميل، عدد الأشخاص ≤ المقاعد)
       - حساب السعر الإجمالي مباشرة (السعر × عدد الأشخاص)
       ملاحظة: لإظهار رسالة خطأ تحت حقل:
         input.classList.add('invalid')   ← يظهر .error-msg اللي بعده
         input.classList.remove('invalid')
    */


    /* ===== الجزء 3: لوحة التحكم + إدارة الجولات =====
       المطلوب هنا:
       - رسالة تأكيد قبل الحذف (confirm)
       - التحقق من نماذج الإضافة والتعديل
       - معاينة الصورة قبل الرفع
    */

});
