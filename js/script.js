
/* =====================================================
   WIJHA - Shared JavaScript File
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Mobile Navigation ---------- */

    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function () {
            const isOpen = mainNav.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', isOpen);
            menuToggle.textContent = isOpen ? '✕' : '☰';
        });
    }

    /* =====================================================
       Part 1: Home, Tours, and Login
       ===================================================== */

    /* ---------- Dark Mode ---------- */

    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(isDark) {
        document.body.classList.toggle('dark', isDark);

        if (themeToggle) {
            themeToggle.textContent = isDark ? '☀️' : '🌙';
        }
    }

    let savedTheme = null;

    try {
        savedTheme = localStorage.getItem('wijha-theme');
    } catch (error) {
        // Local storage may be unavailable.
    }

    applyTheme(savedTheme === 'dark');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const isDark = !document.body.classList.contains('dark');

            applyTheme(isDark);

            try {
                localStorage.setItem(
                    'wijha-theme',
                    isDark ? 'dark' : 'light'
                );
            } catch (error) {
                // Ignore local storage errors.
            }
        });
    }

    /* ---------- Login Form Validation ---------- */

    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        const fields = [
            document.getElementById('username'),
            document.getElementById('password')
        ].filter(Boolean);

        loginForm.addEventListener('submit', function (event) {
            let valid = true;

            fields.forEach(function (input) {
                if (input.value.trim() === '') {
                    input.classList.add('invalid');
                    valid = false;
                } else {
                    input.classList.remove('invalid');
                }
            });

            if (!valid) {
                event.preventDefault();
            }
        });

        fields.forEach(function (input) {
            input.addEventListener('input', function () {
                if (input.value.trim() !== '') {
                    input.classList.remove('invalid');
                }
            });
        });

        /* ---------- Password Visibility Toggle ---------- */

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const show = passwordInput.type === 'password';

                passwordInput.type = show ? 'text' : 'password';
                togglePassword.textContent = show ? '🙈' : '👁';
            });
        }
    }

    /* ---------- Home Page Slider ---------- */

    const slider = document.getElementById('heroSlider');

    if (slider) {
        const slides = slider.querySelectorAll('.slide');
        const dots = slider.querySelectorAll('.dot');
        let current = 0;
        let timer;

        function showSlide(index) {
            if (slides.length === 0) return;

            current = (index + slides.length) % slides.length;

            slides.forEach(function (slide, i) {
                slide.classList.toggle('active', i === current);
            });

            dots.forEach(function (dot, i) {
                dot.classList.toggle('active', i === current);
            });
        }

        function startAuto() {
            clearInterval(timer);

            if (slides.length > 1) {
                timer = setInterval(function () {
                    showSlide(current + 1);
                }, 5000);
            }
        }

        const nextBtn = document.getElementById('sliderNext');
        const prevBtn = document.getElementById('sliderPrev');

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                showSlide(current + 1);
                startAuto();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                showSlide(current - 1);
                startAuto();
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                showSlide(parseInt(dot.dataset.index, 10));
                startAuto();
            });
        });

        slider.addEventListener('mouseenter', function () {
            clearInterval(timer);
        });

        slider.addEventListener('mouseleave', startAuto);

        startAuto();
    }

    /* ---------- Tour Filtering and Sorting ---------- */

    const toursGrid = document.getElementById('toursGrid');

    if (toursGrid) {
        const cards = Array.from(
            toursGrid.querySelectorAll('.tour-card')
        );

        const searchInput = document.getElementById('searchInput');
        const cityFilter = document.getElementById('cityFilter');
        const sortSelect = document.getElementById('sortSelect');
        const chips = document.querySelectorAll('.chip');
        const resultsCount = document.getElementById('resultsCount');
        const emptyState = document.getElementById('emptyState');
        const resetFilters = document.getElementById('resetFilters');

        let selectedType = 'all';

        function applyFilters() {
            const search = searchInput
                ? searchInput.value.trim().toLowerCase()
                : '';

            const city = cityFilter ? cityFilter.value : 'all';
            let visible = 0;

            cards.forEach(function (card) {
                const title = (card.dataset.title || '').toLowerCase();
                const cardCity = card.dataset.city || '';
                const cardType = card.dataset.type || '';

                const matchSearch = title.includes(search);
                const matchCity = city === 'all' || cardCity === city;
                const matchType =
                    selectedType === 'all' || cardType === selectedType;

                const show = matchSearch && matchCity && matchType;

                card.classList.toggle('hide', !show);

                if (show) {
                    visible++;
                }
            });

            if (resultsCount) {
                resultsCount.textContent =
                    'عدد النتائج: ' + visible + ' من ' + cards.length;
            }

            if (emptyState) {
                emptyState.hidden = visible > 0;
            }
        }

        function applySort() {
            if (!sortSelect) return;

            const sortBy = sortSelect.value;

            const sorted = cards.slice().sort(function (a, b) {
                if (sortBy === 'price-asc') {
                    return Number(a.dataset.price) - Number(b.dataset.price);
                }

                if (sortBy === 'price-desc') {
                    return Number(b.dataset.price) - Number(a.dataset.price);
                }

                return (a.dataset.date || '').localeCompare(
                    b.dataset.date || ''
                );
            });

            sorted.forEach(function (card) {
                toursGrid.appendChild(card);
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        if (cityFilter) {
            cityFilter.addEventListener('change', applyFilters);
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', applySort);
        }

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chips.forEach(function (item) {
                    item.classList.remove('active');
                });

                chip.classList.add('active');
                selectedType = chip.dataset.type || 'all';

                applyFilters();
            });
        });

        if (resetFilters) {
            resetFilters.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                if (cityFilter) cityFilter.value = 'all';

                selectedType = 'all';

                chips.forEach(function (chip) {
                    chip.classList.toggle(
                        'active',
                        chip.dataset.type === 'all'
                    );
                });

                applyFilters();
                applySort();
            });
        }

        applyFilters();
    }

    /* =====================================================
       Part 2: Tour Booking Form
       ===================================================== */

    const bookingForm = document.getElementById('bookingForm');

    if (bookingForm) {
        const price = parseFloat(bookingForm.dataset.price);
        const maxSeats = parseInt(bookingForm.dataset.seats, 10);
        const persons = bookingForm.querySelector('#persons');
        const totalEl = document.getElementById('bookingTotal');

        /* ---------- Update Booking Total ---------- */

        function updateTotal() {
            if (!persons || !totalEl) return;

            const count = parseInt(persons.value, 10);
            const total = count > 0 ? count * price : 0;

            totalEl.textContent =
                total.toLocaleString('en-US') + ' ريال';
        }

        if (persons) {
            persons.addEventListener('input', updateTotal);
            updateTotal();
        }

        /* ---------- Booking Validation Rules ---------- */

        const rules = {
            customer_name: v => {
            if (v.length < 3) return 'الرجاء إدخال الاسم (3 أحرف على الأقل)';
            const arabic  = /^[\u0621-\u064A\s]+$/;   // حروف عربية ومسافات
            const english = /^[A-Za-z\s]+$/;          // حروف إنجليزية ومسافات
            if (!arabic.test(v) && !english.test(v)) {
            return 'الاسم لازم يكون بالعربي كامل أو بالإنجليزي كامل، بدون أرقام أو رموز';
            }
            return '';
        },

            phone: value =>
                !/^05\d{8}$/.test(value)
                    ? 'رقم الجوال لازم يبدأ بـ 05 ويتكون من 10 أرقام'
                    : '',

            email: value =>
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
                    ? 'الرجاء إدخال بريد إلكتروني صحيح'
                    : '',

            persons: value => {
                const count = Number(value);

                if (!Number.isInteger(count) || count < 1) {
                    return 'عدد الأشخاص لازم يكون 1 على الأقل';
                }

                if (count > maxSeats) {
                    return 'المقاعد المتبقية ' + maxSeats + ' فقط';
                }

                return '';
            }
        };

        function validateField(input) {
            const rule = rules[input.name];

            if (!rule) return true;

            const message = rule(input.value.trim());
            const group = input.closest('.form-group');

            if (!group) return message === '';

            group.classList.toggle('invalid', message !== '');

            const errorMessage = group.querySelector('.error-msg');

            if (errorMessage) {
                errorMessage.textContent = message;
            }

            return message === '';
        }

        /* ---------- Validate Fields on Input ---------- */

        Object.keys(rules).forEach(function (name) {
            const input = bookingForm.elements[name];

            if (!input) return;

            input.addEventListener('blur', function () {
                validateField(input);
            });

            input.addEventListener('input', function () {
                const group = input.closest('.form-group');

                if (group && group.classList.contains('invalid')) {
                    validateField(input);
                }
            });
        });

        /* ---------- Validate Before Submission ---------- */

        bookingForm.addEventListener('submit', function (event) {
            let firstInvalid = null;

            Object.keys(rules).forEach(function (name) {
                const input = bookingForm.elements[name];

                if (!input) return;

                if (!validateField(input) && !firstInvalid) {
                    firstInvalid = input;
                }
            });

            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.focus();
                return;
            }

            // كل شي سليم: نعطل الزر عشان ما ينرسل الحجز مرتين
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'جاري الحجز...';
        });
    }

    /* =====================================================
       Part 3: Image Preview for Add/Edit Tour Forms
       ===================================================== */

    const imageInput = document.getElementById('image');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImage = document.getElementById('imagePreview');

    if (imageInput && previewContainer && previewImage) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];

            if (!file) {
                previewContainer.hidden = true;
                previewImage.removeAttribute('src');
                return;
            }

            /* ---------- Validate Image Type ---------- */

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                alert('نوع الصورة غير مسموح. استخدمي JPG أو PNG أو WEBP.');

                imageInput.value = '';
                previewContainer.hidden = true;
                previewImage.removeAttribute('src');

                return;
            }

            /* ---------- Validate Image Size ---------- */

            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الصورة يجب ألا يتجاوز 5MB.');

                imageInput.value = '';
                previewContainer.hidden = true;
                previewImage.removeAttribute('src');

                return;
            }

            /* ---------- Display Image Preview ---------- */

            const reader = new FileReader();

            reader.onload = function (event) {
                previewImage.src = event.target.result;
                previewContainer.hidden = false;
            };

            reader.onerror = function () {
                alert('تعذر عرض معاينة الصورة.');

                imageInput.value = '';
                previewContainer.hidden = true;
                previewImage.removeAttribute('src');
            };

            reader.readAsDataURL(file);
        });
    }

    /* =====================================================
       Part 4: Delete Confirmation
       Supports Tour and Booking Delete Forms
       ===================================================== */

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.dataset.confirm;

            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

});
