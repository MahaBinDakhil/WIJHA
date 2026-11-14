<?php

require_once __DIR__ . '/../includes/db.php';

if (!is_admin()) {
    set_flash('error', 'لازم تسجلين دخول أول عشان توصلين لهالصفحة');
    header('Location: login.php');
    exit;
}