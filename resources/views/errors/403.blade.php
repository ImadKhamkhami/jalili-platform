<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>غير مصرح</title>
    <style>
        body {
            font-family: Tajawal, sans-serif;
            background: #0f172a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .box {
            max-width: 420px;
        }
        h1 {
            font-size: 40px;
            margin-bottom: 10px;
        }
        p {
            font-size: 18px;
            opacity: .85;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #22c55e;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>403</h1>
        <p>لا يمكنك تنفيذ هذا الإجراء، ليس لديك الصلاحية المطلوبة.</p>
        <a href="{{ url()->previous() }}">العودة</a>
    </div>
</body>
</html>
