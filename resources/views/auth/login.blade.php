<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Brother Outdoor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background-color: #2C1A00;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            width: 400px;
            text-align: center;
        }
        .login-box img {
            width: 150px;
            margin-bottom: 10px;
        }
        h2 {
            color: #2C1A00;
            font-size: 20px;
            margin-bottom: 5px;
        }
        p.subtitle {
            font-size: 12px;
            color: #888;
            margin-bottom: 25px;
        }
        label {
            display: block;
            text-align: left;
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #F5A623;
        }
        button {
            width: 100%;
            padding: 11px;
            background-color: #F5A623;
            color: #2C1A00;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover { background-color: #e09400; }
        .error {
            background: #ffe0e0;
            color: #c00;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="{{ asset('images/logo_brother.PNG') }}" alt="Brother Outdoor">
        <h2>Sistem Inventori Rental Alat Gunung</h2>
        <p class="subtitle">Brother Outdoor Wonosobo</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>

            <button type="submit">LOGIN</button>
        </form>
    </div>
</body>
</html>