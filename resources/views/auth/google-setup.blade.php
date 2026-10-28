<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Authenticator Setup</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 450px;
            margin: auto;
            text-align: center;
        }

        .qr-code {
            margin: 20px auto;
        }

        .qr-code svg {
            max-width: 100%;
            height: auto;
        }

        input {
            padding: 12px;
            font-size: 20px;
            text-align: center;
            letter-spacing: 5px;
            width: 90%;
        }

        button {
            padding: 12px 20px;
            margin-top: 15px;
            cursor: pointer;
        }

        .secret {
            overflow-wrap: anywhere;
            background: #f1f1f1;
            padding: 12px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Google Authenticator</h2>

    <p>1. เปิด Google Authenticator บนมือถือ</p>
    <p>2. กด + แล้วเลือกสแกน QR Code</p>

    <div class="qr-code">
        {!! $qrCode !!}
    </div>

    <p>หากสแกนไม่ได้ ให้ใช้ Setup Key</p>

    <div class="secret">
        <strong>{{ $secretKey }}</strong>
    </div>

    <p>3. กรอกรหัส 6 หลักที่แอปแสดง</p>

    @if ($errors->any())
        <p style="color:red">
            {{ $errors->first() }}
        </p>
    @endif

    <form method="POST" action="{{ route('google.confirm') }}">
        @csrf

        <input
            type="text"
            name="otp"
            maxlength="6"
            pattern="[0-9]{6}"
            inputmode="numeric"
            autocomplete="one-time-code"
            placeholder="000000"
            required
        >

        <button type="submit">
            ยืนยันและเปิดใช้งาน
        </button>

    </form>

</div>

</body>
</html>