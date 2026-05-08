<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Review System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            position: relative;
            padding: 20px;
        }

        /* Beautiful restaurant/food background with rating plate image */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('/images/rating-plate.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            filter: brightness(0.6) blur(2px);
            z-index: 1;
        }

        /* Fallback gradient if image doesn't load */
        body {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.85) 0%, rgba(56, 142, 60, 0.85) 100%);
        }

        .container {
            width: min(500px, 100%);
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            margin: 20px auto;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
            font-weight: 600;
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(76, 175, 80, 0.5);
        }

        .button.secondary {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            width: auto;
        }

        .button.secondary:hover {
            box-shadow: 0 10px 20px rgba(33, 150, 243, 0.5);
        }

        .field {
            margin-bottom: 20px;
        }

        .field label {
            font-weight: 700;
            display: block;
            margin-bottom: 10px;
            color: #1B5E20;
            font-size: 15px;
        }

        .field input, .field select {
            width: 100%;
            padding: 14px;
            border: 2px solid #C8E6C9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: inherit;
            background: #F1F8E9;
        }

        .field input:focus, .field select:focus {
            outline: none;
            border-color: #4CAF50;
            background: white;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.15);
        }

        .flash {
            padding: 16px;
            background: linear-gradient(135deg, #d1fae5 0%, #c1f4d9 100%);
            border-left: 4px solid #10b981;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #065f46;
            font-weight: 500;
        }

        .error {
            padding: 16px;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #991b1b;
            font-weight: 500;
        }

        .error ul {
            margin-top: 10px;
            margin-left: 20px;
        }

        h1 {
            text-align: center;
            color: #1B5E20;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 700;
        }

        p {
            text-align: center;
            margin-top: 20px;
            color: #558B2F;
            font-weight: 500;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 600;
        }

        a:hover {
            color: #2E7D32;
            text-decoration: underline;
        }

        .nav-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #1B5E20;
        }

        .logo-emoji {
            margin-right: 8px;
            font-size: 28px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 28px;
            }

            .nav-top {
                flex-direction: column;
                gap: 15px;
            }

            body::before {
                filter: brightness(0.5) blur(3px);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if(session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="error">
                <strong>⚠️ There were errors:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>