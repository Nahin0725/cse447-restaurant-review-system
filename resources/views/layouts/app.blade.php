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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: min(500px, 90%);
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin: 20px auto;
        }

        /* Dashboard container - override for dashboard pages */
        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            background: transparent;
            padding: 0;
            box-shadow: none;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
            font-weight: 600;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .button.secondary {
            background: #6b7280;
        }

        .button.secondary:hover {
            background: #4b5563;
        }

        .field {
            margin-bottom: 20px;
        }

        .field label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #374151;
        }

        .field input, .field select, .field textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .field input:focus, .field select:focus, .field textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .flash {
            padding: 16px;
            background: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #065f46;
            font-weight: 500;
        }

        .error {
            padding: 16px;
            background: #fee2e2;
            border: 1px solid #ef4444;
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
            color: #1f2937;
            margin-bottom: 30px;
            font-size: 28px;
        }

        h2 {
            color: #1f2937;
            margin-bottom: 15px;
        }

        nav {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        nav a {
            margin-right: 12px;
        }

        p {
            color: #6b7280;
            line-height: 1.6;
        }

        a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.2s;
        }

        a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            .dashboard-container {
                padding: 10px;
            }

            h1 {
                font-size: 24px;
            }

            .nav-buttons {
                flex-direction: column;
                width: 100%;
            }

            .nav-button {
                width: 100%;
                text-align: center;
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
                <strong>There were errors:</strong>
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
