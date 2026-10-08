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

    /* --- سلايدر الرئيسية --- */
    const slider = document.getElementById('heroSlider');

    if (slider) {
        const slides = slider.querySelectorAll('.slide');
        const dots   = slider.querySelectorAll('.dot');
        let current  = 0;
        let timer;

        function showSlide(index) {
            // نلف: بعد آخر شريحة نرجع للأولى، وقبل الأولى نروح للأخيرة
            current = (index + slides.length) % slides.length;
            slides.forEach(function (s, i) { s.classList.toggle('active', i === current); });
            dots.forEach(function (d, i)   { d.classList.toggle('active', i === current); });
        }

        function startAuto() {
            clearInterval(timer);
            timer = setInterval(function () { showSlide(current + 1); }, 5000);  // كل 5 ثواني
        }

        const nextBtn = document.getElementById('sliderNext');
        const prevBtn = document.getElementById('sliderPrev');
        if (nextBtn) nextBtn.addEventListener('click', function () { showSlide(current + 1); startAuto(); });
        if (prevBtn) prevBtn.addEventListener('click', function () { showSlide(current - 1); startAuto(); });

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                showSlide(parseInt(dot.dataset.index));
                startAuto();
            });
        });

        // يوقف لما الماوس فوق السلايدر، ويكمل لما يطلع
        slider.addEventListener('mouseenter', function () { clearInterval(timer); });
        slider.addEventListener('mouseleave', startAuto);

        if (slides.length > 1) startAuto();
    }

    /* --- فلترة وترتيب الجولات --- */
    const toursGrid = document.getElementById('toursGrid');

    if (toursGrid) {
        const cards        = Array.from(toursGrid.querySelectorAll('.tour-card'));
        const searchInput  = document.getElementById('searchInput');
        const cityFilter   = document.getElementById('cityFilter');
        const sortSelect   = document.getElementById('sortSelect');
        const chips        = document.querySelectorAll('.chip');
        const resultsCount = document.getElementById('resultsCount');
        const emptyState   = document.getElementById('emptyState');
        let selectedType   = 'all';

        function applyFilters() {
            const search = searchInput.value.trim().toLowerCase();
            const city   = cityFilter.value;
            let visible  = 0;

            cards.forEach(function (card) {
                // كل شرط لازم يتحقق عشان البطاقة تظهر
                const matchSearch = card.dataset.title.toLowerCase().includes(search);
                const matchCity   = city === 'all' || card.dataset.city === city;
                const matchType   = selectedType === 'all' || card.dataset.type === selectedType;

                const show = matchSearch && matchCity && matchType;
                card.classList.toggle('hide', !show);
                if (show) visible++;
            });

            resultsCount.textContent = 'عدد النتائج: ' + visible + ' من ' + cards.length;
            emptyState.hidden = visible > 0;
        }

        function applySort() {
            const sortBy = sortSelect.value;
            const sorted = cards.slice().sort(function (a, b) {
                if (sortBy === 'price-asc')  return a.dataset.price - b.dataset.price;
                if (sortBy === 'price-desc') return b.dataset.price - a.dataset.price;
                return a.dataset.date.localeCompare(b.dataset.date);   // الأقرب موعداً
            });
            // نرجّع البطاقات للصفحة بالترتيب الجديد
            sorted.forEach(function (card) { toursGrid.appendChild(card); });
        }

        // الأحداث
        searchInput.addEventListener('input', applyFilters);
        cityFilter.addEventListener('change', applyFilters);
        sortSelect.addEventListener('change', applySort);

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chips.forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                selectedType = chip.dataset.type;
                applyFilters();
            });
        });

        document.getElementById('resetFilters').addEventListener('click', function () {
            searchInput.value = '';
            cityFilter.value  = 'all';
            selectedType      = 'all';
            chips.forEach(function (c) { c.classList.toggle('active', c.dataset.type === 'all'); });
            applyFilters();
        });

        applyFilters();   // أول ما تفتح الصفحة (عشان لو جاية من الرئيسية بمدينة محددة)
    }
    

});
