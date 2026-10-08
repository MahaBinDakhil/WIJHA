<?php
/* =====================================================
   config.example.php — نموذج لبيانات الاتصال
   1) انسخي هذا الملف وسمّي النسخة:  config.php
   2) عبّي البيانات الحقيقية في config.php
   ⚠️ config.php ما ينرفع على GitHub (موجود في .gitignore)
   ===================================================== */

define('DB_HOST', 'ضعي-الـHost-هنا');     // مثال: mysql-xxxx.aivencloud.com
define('DB_PORT', 3306);                  // رقم البورت بدون علامات تنصيص
define('DB_USER', 'ضعي-اسم-المستخدم');
define('DB_PASS', 'ضعي-كلمة-المرور');
define('DB_NAME', 'defaultdb');
define('DB_SSL',  true);                  // القواعد السحابية تحتاج SSL
