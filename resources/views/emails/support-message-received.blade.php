<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Support Request</title>
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
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .message-box {
            background-color: #ffffff;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Support Request</h1>
        </div>

        <p>A new support request has been submitted:</p>

        <div class="info-box">
            <strong>From:</strong> {{ $user->name }} ({{ $supportMessage->email }})<br>
            <strong>User ID:</strong> {{ $user->id }}<br>
            <strong>Subject:</strong> {{ $supportMessage->subject }}
        </div>

        <div class="message-box">
            <strong>Message:</strong><br>
            {{ $supportMessage->message }}
        </div>

        <div class="info-box">
            <strong>Submitted:</strong> {{ $supportMessage->created_at->format('Y-m-d H:i:s') }}<br>
            <strong>Message ID:</strong> {{ $supportMessage->id }}
        </div>

        @if(isset($adminUrl))
        <div style="text-align: center;">
            <a href="{{ $adminUrl }}" class="button">View in Admin Panel</a>
        </div>
        @endif

        <div class="footer">
            <p>Best regards,<br><strong>{{ config('app.name') }}</strong></p>
        </div>
    </div>
</body>
</html>

