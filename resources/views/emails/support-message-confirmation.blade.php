<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Request Received</title>
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
            color: #28a745;
            margin: 0;
        }
        .success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
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
            <h1>We've Received Your Support Request</h1>
        </div>

        <p>Hi {{ $user->name }},</p>

        <div class="success">
            <strong>✓ Thank you for contacting us!</strong> We've received your support request and our team will review it shortly.
        </div>

        <div class="info-box">
            <strong>Subject:</strong> {{ $supportMessage->subject }}<br>
            <strong>Reference ID:</strong> {{ $supportMessage->id }}
        </div>

        <div class="message-box">
            <strong>Your Message:</strong><br>
            {{ $supportMessage->message }}
        </div>

        <p>Our support team will review your request and get back to you as soon as possible.</p>

        <p>If you have any additional information, please reply to this email.</p>

        <div class="footer">
            <p>Best regards,<br><strong>{{ config('app.name') }} Support Team</strong></p>
            <p style="margin-top: 10px; color: #999;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>

