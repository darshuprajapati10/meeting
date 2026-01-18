<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .code-container {
            background-color: #f8f9fa;
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #3498db;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .message {
            margin: 20px 0;
            color: #555;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3498db;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Code</h1>
        </div>

        <p>Hello {{ $user->name ?? 'User' }},</p>

        <p class="message">
            You are receiving this email because we received a password reset request for your account.
        </p>

        <div class="code-container">
            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Your password reset code is:</p>
            <div class="code">{{ $code }}</div>
            <p style="margin: 10px 0 0 0; color: #666; font-size: 12px;">This code will expire in {{ $expiresIn }} minutes</p>
        </div>

        <p class="message">
            Please enter this code in the password reset form to create a new password.
        </p>

        <div class="warning">
            <strong>⚠️ Security Notice:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>This code is valid for {{ $expiresIn }} minutes only</li>
                <li>Do not share this code with anyone</li>
                <li>If you did not request a password reset, please ignore this email</li>
            </ul>
        </div>

        <p class="message">
            If you did not request a password reset, no further action is required. Your account remains secure.
        </p>

        <div class="footer">
            <p>Thank you,<br><strong>{{ config('app.name') }}</strong></p>
            <p style="margin-top: 10px; color: #999;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>

