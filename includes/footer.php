<?php
/* =====================================================
   footer.php — الجزء السفلي المشترك
   يُستدعى في آخر كل صفحة:
   <?php require_once 'includes/footer.php'; ?>
   ===================================================== */
$base = $base ?? '';
?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <a href="<?= $base ?>index.php" class="logo logo-light"><span class="logo-mark">◈</span> وِجهة</a>
            <p>منصة لحجز الجولات السياحية داخل المملكة، ضمن مستهدفات رؤية السعودية 2030 لتنمية السياحة الوطنية.</p>
        </div>
        <div>
            <h4>روابط</h4>
            <ul>
                <li><a href="<?= $base ?>index.php">الرئيسية</a></li>
                <li><a href="<?= $base ?>tours.php">الجولات</a></li>
            </ul>
        </div>
    </div>
    <p class="copyright">© <?= date('Y') ?> وِجهة — مشروع مقرر CSC457</p>
</footer>

<script src="<?= $base ?>js/script.js"></script>
</body>
</html>
