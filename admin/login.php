<?php
/* =====================================================
   login.php — تسجيل دخول المشرفات
   ===================================================== */
require_once __DIR__ . '/../includes/db.php';

// إذا هي أصلاً مسجلة دخول → نوديها للوحة التحكم مباشرة
if (is_admin()) {
    header('Location: dashboard.php');
    exit;
}

$error    = '';
$username = '';

// ---------- معالجة النموذج لما ينضغط زر الدخول ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1) تحقق في السيرفر (حتى لو الـ JS تحقق قبل — الأمان ما يعتمد على JS)
    if ($username === '' || $password === '') {
        $error = 'الرجاء تعبئة اسم المستخدم وكلمة المرور';
    } else {
        // 2) نجيب المشرفة من القاعدة بـ Prepared Statement (حماية من SQL Injection)
        $stmt = $conn->prepare('SELECT id, username, password, full_name FROM admins WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // 3) نقارن كلمة المرور بالنسخة المشفرة
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);              // حماية من سرقة الجلسة
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];

            set_flash('success', 'أهلاً ' . $_SESSION['admin_name'] . '، تم تسجيل الدخول بنجاح');
            header('Location: dashboard.php');
            exit;
        } else {
            // رسالة وحدة للحالتين — ما نقول وش الغلط بالضبط (أمان)
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}

$pageTitle  = 'دخول المشرفات';
$activePage = 'login';
$base       = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="login-wrap">

            <div class="login-visual">
                <img src="../images/tour-alula.jpg" alt="العلا">
                <div class="login-visual-text">
                    <h2>لوحة تحكم وِجهة</h2>
                    <p>إدارة الجولات والحجوزات في مكان واحد</p>
                </div>
            </div>

            <div class="login-form">
                <h1>تسجيل الدخول</h1>
                <p class="muted">خاص بمشرفات المنصة</p>

                <?php show_flash(); ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php" id="loginForm" novalidate>
                    <div class="form-group">
                        <label for="username">اسم المستخدم <span class="req">*</span></label>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?= e($username) ?>" autocomplete="username" autofocus>
                        <span class="error-msg">الرجاء إدخال اسم المستخدم</span>
                    </div>

                    <div class="form-group">
                        <label for="password">كلمة المرور <span class="req">*</span></label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-control"
                                   autocomplete="current-password">
                            <span class="error-msg">الرجاء إدخال كلمة المرور</span>
                            <button type="button" class="toggle-password" id="togglePassword" aria-label="إظهار كلمة المرور">👁</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-block">دخول</button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
