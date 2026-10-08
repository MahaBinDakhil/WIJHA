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


    /* ===== الجزء 1: الرئيسية + الجولات + الدخول =====
       المطلوب هنا:
       - الوضع الليلي: زر #themeToggle يضيف/يشيل class "dark" على body
         (التنسيق جاهز في style.css تحت body.dark)
       - سلايدر صور الرئيسية
       - فلترة الجولات حسب المدينة والنوع
       - التحقق من نموذج الدخول
    */


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
