<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to ESMS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .welcome-text {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .credential-item {
            margin-bottom: 10px;
            padding: 8px 0;
        }
        .credential-label {
            font-weight: bold;
            color: #6c757d;
            display: inline-block;
            width: 80px;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
        }
        .steps {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
        }
        .step {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        .step::before {
            content: attr(data-step);
            position: absolute;
            left: 0;
            top: 0;
            background-color: #007bff;
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">ESMS</div>
            <div>Educational School Management System</div>
        </div>

        <h1 class="welcome-text">Welcome {{ $admin->name }}!</h1>

        <p>Congratulations! Your administrator account has been successfully created in the Educational School Management System (ESMS). You are now ready to begin setting up your school.</p>

        <div class="credentials-box">
            <div class="credentials-title">🔑 Your Login Credentials</div>
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">{{ $admin->email }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Password:</span>
                <span class="credential-value">{{ $password }}</span>
            </div>
        </div>

        <div class="warning-box">
            <div class="warning-title">⚠️ Important Security Notice</div>
            <p>This is a temporary password. You will be required to change it upon your first login for security purposes.</p>
        </div>

        <div class="steps">
            <h3>Next Steps:</h3>
            <div class="step" data-step="1">
                <strong>Login to Admin Portal:</strong> Use the credentials above to access your admin account
            </div>
            <div class="step" data-step="2">
                <strong>Change Password:</strong> Update your password to something secure and memorable
            </div>
            <div class="step" data-step="3">
                <strong>Complete Profile:</strong> Fill in your personal information and preferences
            </div>
            <div class="step" data-step="4">
                <strong>Create Your School:</strong> Use the school setup wizard to create your educational institution
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Login to Admin Portal</a>
        </div>

        <div style="margin-top: 30px;">
            <h3>What happens after school creation?</h3>
            <ul>
                <li>Your school will get its own dedicated database and system</li>
                <li>You'll become the school owner with full administrative privileges</li>
                <li>You can add teachers, students, parents, and other staff members</li>
                <li>Access all school management features based on your subscription plan</li>
            </ul>
        </div>

        <div class="footer">
            <p>If you need assistance, please contact our support team.</p>
            <p><strong>ESMS Team</strong></p>
            <p>This email was sent to {{ $admin->email }}</p>
        </div>
    </div>
</body>
</html>
