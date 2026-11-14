<?php
require_once __DIR__ . '/../includes/db.php';

$_SESSION = [];
session_destroy();

session_start();
set_flash('success', 'تم تسجيل الخروج بنجاح');

header('Location: login.php');
exit;