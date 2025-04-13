<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Xác nhận Email</title>
</head>
<body>
    <h2>Chào <?php echo e($fullName); ?></h2>
    <p>Cảm ơn bạn đã đăng ký tài khoản.</p>
    <p>Vui lòng nhấn vào link dưới đây để kích hoạt tài khoản:</p>
    <p><a href="<?php echo e($verifyUrl); ?>">Xác nhận Email</a></p>
    <p>Nếu bạn không đăng ký tài khoản, vui lòng bỏ qua email này.</p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\khoaLuanTotNghiep\KLTN_NHOM10-HeThongQuanLyChiTieu\admin\resources\views/emails/verify-email.blade.php ENDPATH**/ ?>