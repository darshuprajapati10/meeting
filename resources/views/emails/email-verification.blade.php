<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
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
        .message {
            margin: 20px 0;
            color: #555;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 40px;
            background-color: #3498db;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background-color: #2980b9;
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
        .link-fallback {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            word-break: break-all;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verify Your Email Address</h1>
        </div>

        <p>Hello {{ $user->name ?? 'User' }},</p>

        <p class="message">
            Thank you for registering with <strong>{{ config('app.name', 'Ongoing Forge') }}</strong>! 
            To complete your registration and start using your account, please verify your email address.
        </p>

        <div class="button-container">
            <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        </div>

        <div class="link-fallback">
            <p style="margin: 0 0 5px 0; font-weight: bold;">If the button doesn't work, copy and paste this link into your browser:</p>
            <p style="margin: 0; word-break: break-all;">{{ $verificationUrl }}</p>
        </div>

        <div class="warning">
            <strong>⚠️ Important:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>This verification link will expire in 24 hours</li>
                <li>You must verify your email before you can login to your account</li>
                <li>If you did not create an account, please ignore this email</li>
            </ul>
        </div>

        <p class="message">
            If you're having trouble clicking the button, copy and paste the URL above into your web browser.
        </p>

        <p class="message">
            If you did not create an account, no further action is required.
        </p>

        <div class="footer">
            <p>Thank you,<br><strong>{{ config('app.name', 'Ongoing Forge') }}</strong></p>
            <p style="margin-top: 10px; color: #999;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>
