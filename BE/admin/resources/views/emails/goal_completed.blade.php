<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Hoàn thành mục tiêu</title>
</head>

<body>
    <h2>🎉 Xin chúc mừng!</h2>
    <p>Bạn đã hoàn thành mục tiêu tiết kiệm: <strong>{{ $goal->name }}</strong></p>
    <p>Số tiền cần đạt: <strong>{{ number_format($goal->target, 0, ',', '.') }} ₫</strong></p>
    <p>Số tiền đã tiết kiệm: <strong>{{ number_format($goal->save_money, 0, ',', '.') }} ₫</strong></p>
    <p>Tiếp tục phát huy nhé! 💪</p>
</body>

</html>
