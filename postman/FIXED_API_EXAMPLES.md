# 🔧 FIXED ESMS API Examples & Issues Resolution

## 🚨 Issues Found & Fixed

### 1. **Authentication Issues**
- ❌ **Old Credentials**: `superadmin@example.com` / `password123`
- ✅ **Correct Credentials**: `superadmin@esms.com` / `SuperAdmin123!`

### 2. **School Creation Validation Errors**
- ❌ **Missing Fields**: `email` and `phone` were missing from school creation
- ❌ **Invalid Domain Format**: `testschool.example.com` doesn't match regex pattern
- ✅ **Fixed Domain**: Must match pattern `^[a-z0-9]+([a-z0-9\-]*[a-z0-9])?$`

---

## 🛠️ PowerShell Commands (Correct Syntax)

### 1. **Login & Get Token**
```powershell
$body = @{
    email = "superadmin@esms.com"
    password = "SuperAdmin123!"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost/esms/public/api/v1/auth/login" -Method POST -Body $body -ContentType "application/json" -Headers @{Accept="application/json"}

$token = $response.data.token
Write-Host "🔑 Auth Token: $token"
```

### 2. **Create School (With All Required Fields)**
```powershell
$headers = @{
    Accept = "application/json"
    Authorization = "Bearer $token"
    "Content-Type" = "application/json"
}

$schoolData = @{
    name = "Test School"
    email = "school@testschool.com"          # ✅ Required field
    phone = "+1234567890"                    # ✅ Required field
    domain = "testschool"                    # ✅ Valid format (no dots)
    subscription_plan_id = 1
    contact_email = "admin@testschool.com"
    contact_phone = "+1234567890"
    address = "123 School Street"
    city = "Test City"
    state = "Test State"
    country = "Test Country"
    postal_code = "12345"
    admin_first_name = "John"
    admin_last_name = "Doe"
    admin_email = "john.doe@testschool.com"
    admin_phone = "+1234567891"
} | ConvertTo-Json

$result = Invoke-RestMethod -Uri "http://localhost/esms/public/api/v1/schools" -Method POST -Body $schoolData -ContentType "application/json" -Headers $headers
Write-Host "🏫 School Created: $($result.data.name)"
```

### 3. **Get All Schools**
```powershell
$schools = Invoke-RestMethod -Uri "http://localhost/esms/public/api/v1/schools" -Method GET -Headers @{Accept="application/json"; Authorization="Bearer $token"}
Write-Host "📋 Found $($schools.data.data.Count) schools"
```

### 4. **Get Subscription Plans**
```powershell
$plans = Invoke-RestMethod -Uri "http://localhost/esms/public/api/v1/subscription-plans" -Method GET -Headers @{Accept="application/json"; Authorization="Bearer $token"}
Write-Host "💰 Available Plans: $($plans.data.data.Count)"
```

### 5. **Create School Inquiry (Public - No Auth Required)**
```powershell
$inquiryData = @{
    school_name = "Test Elementary School"
    contact_person = "Jane Smith"
    contact_email = "jane.smith@testelemschool.edu"
    contact_phone = "+1234567890"
    school_type = "Elementary"
    student_count = 250
    address = "789 Education Avenue"
    city = "Learning City"
    state = "Education State"
    country = "Education Country"
    postal_code = "12345"
    website = "https://testelemschool.edu"
    message = "We are interested in implementing your school management system."
} | ConvertTo-Json

$inquiry = Invoke-RestMethod -Uri "http://localhost/esms/public/api/v1/inquiries" -Method POST -Body $inquiryData -ContentType "application/json" -Headers @{Accept="application/json"}
Write-Host "📝 Inquiry Created: $($inquiry.data.school_name)"
```

---

## ✅ Updated Postman Collection Changes

### **Fixed in School Creation:**
- ✅ Added `"email": "school@testschool.com"`
- ✅ Added `"phone": "+1234567890"`
- ✅ Changed domain from `"testschool.example.com"` to `"testschool"`

### **Fixed in Inquiry Approval:**
- ✅ Changed domain from `"approvedschool.example.com"` to `"approvedschool"`

### **Environment Variables (Already Correct):**
- ✅ `superadmin_email`: `superadmin@esms.com`
- ✅ `superadmin_password`: `SuperAdmin123!`
- ✅ `base_url`: `http://localhost/esms/public`

---

## 🔍 Domain Validation Rules

**Valid Domain Examples:**
- ✅ `testschool`
- ✅ `my-school`
- ✅ `school123`
- ✅ `test-school-abc`

**Invalid Domain Examples:**
- ❌ `testschool.com` (contains dots)
- ❌ `test_school` (contains underscore)
- ❌ `Test-School` (contains uppercase)
- ❌ `-testschool` (starts with hyphen)
- ❌ `testschool-` (ends with hyphen)

**Regex Pattern:** `^[a-z0-9]+([a-z0-9\-]*[a-z0-9])?$`

---

## 🎯 Testing Order

1. **Ensure XAMPP is Running:**
   ```powershell
   # Make sure Apache and MySQL are running in XAMPP Control Panel
   # The application will be available at: http://localhost/esms/public
   ```

2. **Test Login First** (Get Token)
3. **Test School Creation** (With Fixed Payload)
4. **Test Other Endpoints** (Using Valid Token)

---

## 📊 Expected Responses

### **Successful Login:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "superadmin": {
            "id": 1,
            "name": "System Administrator",
            "email": "superadmin@esms.com"
        },
        "token": "3|abc123...",
        "expires_at": "2025-06-13T..."
    }
}
```

### **Successful School Creation:**
```json
{
    "success": true,
    "message": "School created successfully",
    "data": {
        "id": 1,
        "name": "Test School",
        "email": "school@testschool.com",
        "domain": "testschool",
        "status": "pending"
    }
}
```

---

## 🚫 Common Errors & Solutions

### **Error: "School email is required"**
- **Solution**: Add `"email": "school@example.com"` to payload

### **Error: "School phone is required"**  
- **Solution**: Add `"phone": "+1234567890"` to payload

### **Error: "Domain format is invalid"**
- **Solution**: Use simple domain like `"testschool"` (no dots, special chars)

### **Error: "Invalid credentials"**
- **Solution**: Use `superadmin@esms.com` / `SuperAdmin123!`

---

**🎉 All API endpoints are now properly configured and ready for testing!**
