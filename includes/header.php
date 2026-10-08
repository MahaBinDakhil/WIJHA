<?php
/* =====================================================
   header.php — الجزء العلوي المشترك لكل الصفحات
   طريقة الاستخدام في أول أي صفحة:

   <?php
     $pageTitle = 'الجولات';   // عنوان التبويب
     $activePage = 'tours';    // home | tours | login  (يلوّن الرابط الحالي)
     $base = '';               // صفحات مجلد admin تكتب: $base = '../';
     require_once 'includes/header.php';
   ?>
   ===================================================== */
$pageTitle  = $pageTitle  ?? 'وِجهة';
$activePage = $activePage ?? '';
$base       = $base       ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | وِجهة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?= $base ?>index.php" class="logo">
            <span class="logo-mark">◈</span> وِجهة
        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="فتح القائمة" aria-expanded="false">☰</button>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="<?= $base ?>index.php" class="<?= $activePage === 'home'  ? 'active' : '' ?>">الرئيسية</a></li>
                <li><a href="<?= $base ?>tours.php" class="<?= $activePage === 'tours' ? 'active' : '' ?>">الجولات</a></li>
                <?php if (is_admin()): ?>
                    <li><a href="<?= $base ?>admin/dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">لوحة التحكم</a></li>
                    <li><a href="<?= $base ?>admin/logout.php">تسجيل الخروج</a></li>
                <?php else: ?>
                    <li><a href="<?= $base ?>admin/login.php" class="<?= $activePage === 'login' ? 'active' : '' ?>">دخول المشرفات</a></li>
                <?php endif; ?>
            </ul>
            <button class="theme-toggle" id="themeToggle" aria-label="الوضع الليلي">🌙</button>
        </nav>
    </div>
</header>

<main>
