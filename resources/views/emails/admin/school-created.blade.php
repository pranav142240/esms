<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Created Successfully</title>
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
            color: #28a745;
            margin-bottom: 10px;
        }
        .success-badge {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .school-info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .school-info-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-item {
            margin-bottom: 10px;
            padding: 8px 0;
        }
        .info-label {
            font-weight: bold;
            color: #6c757d;
            display: inline-block;
            width: 120px;
        }
        .info-value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #1e7e34;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .feature-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 5px;
        }
        .feature-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        .feature-desc {
            color: #6c757d;
            font-size: 14px;
        }
        .celebration {
            text-align: center;
            font-size: 48px;
            margin: 20px 0;
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
            <div class="logo">🎉 ESMS</div>
            <div class="success-badge">✅ School Created Successfully</div>
        </div>

        <div class="celebration">🏫✨</div>

        <h1 style="text-align: center; color: #28a745;">Congratulations {{ $admin->name }}!</h1>

        <p style="text-align: center; font-size: 18px;">Your school <strong>"{{ $schoolName }}"</strong> has been successfully created and is now ready for use!</p>

        <div class="school-info-box">
            <div class="school-info-title">🏫 School Information</div>
            <div class="info-item">
                <span class="info-label">School Name:</span>
                <span class="info-value">{{ $schoolData['school_name'] }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">School Email:</span>
                <span class="info-value">{{ $schoolData['school_email'] }}</span>
            </div>
            @if(isset($schoolData['school_phone']))
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $schoolData['school_phone'] }}</span>
            </div>
            @endif
            <div class="info-item">
                <span class="info-label">School Portal:</span>
                <span class="info-value"><a href="{{ $tenantUrl }}" style="color: #007bff;">{{ $tenantUrl }}</a></span>
            </div>
            <div class="info-item">
                <span class="info-label">Subscription:</span>
                <span class="info-value">{{ ucfirst($schoolData['subscription_plan']) }} Plan</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ $tenantUrl }}" class="button">Access Your School Portal</a>
        </div>

        <h3>🚀 What's Next?</h3>
        <p>Your school is now fully operational with its own dedicated system. Here's what you can do:</p>

        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-title">👥 User Management</div>
                <div class="feature-desc">Add teachers, students, parents, and staff members to your school</div>
            </div>
            <div class="feature-box">
                <div class="feature-title">📚 Academic Management</div>
                <div class="feature-desc">Create classes, subjects, and manage academic activities</div>
            </div>
            <div class="feature-box">
                <div class="feature-title">📊 Attendance & Grades</div>
                <div class="feature-desc">Track student attendance and manage examination results</div>
            </div>
            <div class="feature-box">
                <div class="feature-title">💰 Financial Management</div>
                <div class="feature-desc">Handle fee collection, expenses, and financial reporting</div>
            </div>
            <div class="feature-box">
                <div class="feature-title">📖 Library System</div>
                <div class="feature-desc">Manage library books, issues, and member records</div>
            </div>
            <div class="feature-box">
                <div class="feature-title">📈 Reports & Analytics</div>
                <div class="feature-desc">Generate comprehensive reports and track school performance</div>
            </div>
        </div>

        <div style="background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 20px; margin: 20px 0;">
            <h4 style="color: #007bff; margin-top: 0;">🔐 Important Notes:</h4>
            <ul>
                <li>You are now the school owner with full administrative privileges</li>
                <li>Your school has its own dedicated database for complete data isolation</li>
                <li>All future logins should be done through your school portal URL</li>
                <li>You can create additional admin accounts within your school if needed</li>
            </ul>
        </div>

        <div class="footer">
            <p>Welcome to the ESMS family! We're excited to see your school grow and succeed.</p>
            <p><strong>ESMS Team</strong></p>
            <p>School Portal: <a href="{{ $tenantUrl }}">{{ $tenantUrl }}</a></p>
        </div>
    </div>
</body>
</html>
