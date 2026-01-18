<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deletion Confirmation</title>
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
        .info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Deletion Confirmation</h1>
        </div>

        <p>Hello {{ $userName ?? 'User' }},</p>

        <div class="info">
            <strong>ℹ️ Account Deleted:</strong> Your account has been successfully deleted on {{ $deletedAt ?? now()->format('Y-m-d H:i:s') }}.
        </div>

        <p class="message">
            All your data has been permanently removed from our system, including:
        </p>

        <ul style="margin: 20px 0; padding-left: 20px; color: #555;">
            <li>Your profile information</li>
            <li>Your meetings and calendar data</li>
            <li>Your contacts</li>
            <li>Your surveys</li>
            <li>All other associated data</li>
        </ul>

        <div class="warning">
            <strong>⚠️ Important Notice:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>This action is <strong>permanent and irreversible</strong></li>
                <li>You will no longer be able to access your account</li>
                <li>If you did not request this deletion, please contact support immediately</li>
            </ul>
        </div>

        <p class="message">
            We're sorry to see you go. If you have any questions or concerns, please contact our support team.
        </p>

        <div class="footer">
            <p>Best regards,<br><strong>{{ config('app.name') }}</strong></p>
            <p style="margin-top: 10px; color: #999;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>


