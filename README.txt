وِجهة — منصة حجز الجولات السياحية (CSC457)
================================================

طريقة التشغيل على جهازك:
1) انسخي مجلد wijha كامل إلى:  C:\xampp\htdocs\
2) انسخي صور الجولات والمدن إلى مجلد:  wijha\images\
3) انسخي includes\config.example.php وسمّي النسخة config.php، وعبّي فيها بيانات الاتصال
   (config.php ما ينرفع على GitHub — كل وحدة تسوي نسختها)
4) شغّلي Apache من XAMPP (MySQL المحلي ما نحتاجه لأن القاعدة سحابية)
5) افتحي المتصفح على:  http://localhost/wijha/test.php
   إذا طلعت البطاقات بالصور = كل شي جاهز ✅

هيكل المجلدات:
wijha/
├── index.php            ← الرئيسية                  (الجزء 1)
├── tours.php            ← الجولات + الفلترة          (الجزء 1)
├── tour.php             ← التفاصيل + نموذج الحجز     (الجزء 2)
├── book.php             ← معالجة الحجز               (الجزء 2)
├── test.php             ← صفحة تجربة — تنحذف قبل التسليم
├── includes/
│   ├── db.php           ← الاتصال + دوال مساعدة (مشترك)
│   ├── config.example.php ← نموذج بيانات الاتصال
│   ├── config.php       ← بياناتك الحقيقية (ما ينرفع على GitHub)
│   ├── header.php       ← الهيدر والقائمة (مشترك)
│   └── footer.php       ← الفوتر (مشترك)
├── admin/
│   ├── login.php        ← دخول المشرفات              (الجزء 1)
│   ├── auth.php         ← حماية الصفحات              (الجزء 1)
│   ├── logout.php       ← تسجيل الخروج               (الجزء 1)
│   ├── bookings.php     ← إدارة الحجوزات             (الجزء 2)
│   ├── dashboard.php    ← لوحة التحكم                (الجزء 3)
│   ├── add_tour.php     ← إضافة جولة                 (الجزء 3)
│   ├── edit_tour.php    ← تعديل جولة                 (الجزء 3)
│   └── delete_tour.php  ← حذف جولة                   (الجزء 3)
├── css/style.css        ← مشترك + قسم لكل جزء في آخره
├── js/script.js         ← مشترك + قسم لكل جزء
└── images/

قالب أي صفحة جديدة:
<?php
require_once 'includes/db.php';
$pageTitle  = 'عنوان الصفحة';
$activePage = 'tours';            // home | tours | login
require_once 'includes/header.php';
?>
    ... محتوى الصفحة ...
<?php require_once 'includes/footer.php'; ?>

صفحات مجلد admin: نفس القالب بس المسارات تبدأ بـ ../ وتضيفين  $base = '../';  قبل الهيدر.

الدوال المساعدة الجاهزة (في db.php):
e($text)            ← لازم قبل عرض أي نص من القاعدة (حماية XSS)
price(450)          ← "450 ريال"
arabic_date($date)  ← "10 ديسمبر 2026"

قواعد الفريق:
- كل وحدة تعدّل ملفاتها بس.
- التنسيق والـ JS الخاص بك تحت عنوان جزئك في آخر style.css و script.js.
- أي تعديل على الملفات المشتركة (includes/ وأول style.css) نتفق عليه قبل.
- استخدموا Prepared Statements في أي استعلام فيه مدخلات من المستخدم.
