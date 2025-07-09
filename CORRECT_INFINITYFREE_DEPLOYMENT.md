# 🚀 CORRECT InfinityFree Deployment Guide

## 📁 **Actual File Structure (After Scanning)**

### **Backend Structure:**
```
Backend/
├── index.php              # Root router/landing page
├── public/
│   └── index.php          # Actual GraphQL API entry point  
├── src/                   # PHP classes
├── vendor/                # Composer dependencies
├── .htaccess              # URL rewriting
├── composer.json          # Dependencies
├── data.json              # Sample data
├── database_schema.sql    # Database structure
└── setup.php              # Data import script
```

### **Frontend Structure:**
```
Frontend/dist/             # Production build
├── index.html             # React app entry
├── assets/
│   ├── index-C2scszAn.js  # Bundled JavaScript
│   └── index-CHFTDVMG.css # Bundled CSS
├── cart-icon.svg          # Icons
├── cart-white.svg
└── logo.svg
```

---

## 🗃️ **Step 1: Database Setup (phpMyAdmin)**

1. **Login to InfinityFree VistaPanel**
2. **MySQL Databases** → Create new database
3. **Note your credentials:**
   - Host: `sql###.infinityfree.com`
   - Database: `if0_########_scandiweb`
   - Username: `if0_########`
   - Password: `[your password]`

4. **phpMyAdmin** → Import `Backend/database_schema.sql`

---

## 🔧 **Step 2: Configure Database Connection**

**Edit `Backend/src/Config/Database.php`** with your InfinityFree details:

```php
<?php
class Database {
    private $host = 'sql###.infinityfree.com';      // Your actual host
    private $db_name = 'if0_########_scandiweb';    // Your database name
    private $username = 'if0_########';             // Your username  
    private $password = 'your_password';            // Your password
    // ... rest of file
}
```

---

## 📤 **Step 3: Upload Files to InfinityFree htdocs**

### **🎯 CORRECT htdocs Structure:**

```
htdocs/
├── index.html              # FROM: Frontend/dist/index.html
├── assets/                 # FROM: Frontend/dist/assets/
│   ├── index-C2scszAn.js
│   └── index-CHFTDVMG.css
├── cart-icon.svg           # FROM: Frontend/dist/cart-icon.svg
├── cart-white.svg          # FROM: Frontend/dist/cart-white.svg
├── logo.svg                # FROM: Frontend/dist/logo.svg
├── api/                    # FROM: Backend/ (entire folder)
│   ├── index.php           # Backend root router
│   ├── public/
│   │   └── index.php       # GraphQL API endpoint
│   ├── src/                # PHP application code
│   ├── vendor/             # Composer dependencies
│   ├── .htaccess
│   ├── composer.json
│   ├── data.json
│   ├── database_schema.sql
│   └── setup.php
```

### **🔄 Upload Instructions:**

#### **Upload Frontend (to htdocs root):**
```
Frontend/dist/index.html          → htdocs/index.html
Frontend/dist/assets/             → htdocs/assets/
Frontend/dist/cart-icon.svg       → htdocs/cart-icon.svg
Frontend/dist/cart-white.svg      → htdocs/cart-white.svg
Frontend/dist/logo.svg            → htdocs/logo.svg
```

#### **Upload Backend (to htdocs/api/):**
```
Backend/                          → htdocs/api/
```
**(Upload the ENTIRE Backend folder as "api")**

---

## 🔄 **Step 4: Import Sample Data**

**Visit:** `https://scandiweb-test-mohamedbadr.web1337.net/api/setup.php`

This will automatically populate your database with products from `data.json`.

---

## 🔗 **Step 5: Update API Endpoint (if needed)**

The API should work automatically, but if there are CORS issues, update `Backend/public/index.php`:

```php
// Update CORS header to allow your domain
header("Access-Control-Allow-Origin: https://scandiweb-test-mohamedbadr.web1337.net");
```

---

## ✅ **Step 6: Test Your Deployment**

### **URLs to Test:**
- **Frontend:** `https://scandiweb-test-mohamedbadr.web1337.net/`
- **API Info:** `https://scandiweb-test-mohamedbadr.web1337.net/api/`
- **GraphQL API:** `https://scandiweb-test-mohamedbadr.web1337.net/api/public/index.php`

### **Expected Results:**
1. **Frontend** shows product listing with categories
2. **API Info** shows backend status page  
3. **GraphQL** responds to POST requests

---

## 🚨 **Common InfinityFree Issues & Fixes**

### **1. "Internal Server Error"**
- **Fix:** Check `.htaccess` uploaded to `htdocs/api/.htaccess`
- **Fix:** Verify file permissions (644 for files, 755 for folders)

### **2. "Database Connection Failed"**  
- **Fix:** Double-check credentials in `src/Config/Database.php`
- **Fix:** Ensure database exists in VistaPanel

### **3. "API Not Found"**
- **Fix:** Ensure `htdocs/api/public/index.php` exists
- **Fix:** Check that composer dependencies are in `htdocs/api/vendor/`

### **4. Frontend Shows Blank Page**
- **Fix:** Check browser console for JavaScript errors
- **Fix:** Verify all assets loaded correctly

---

## 📧 **Submission Checklist**

### **Repository Setup:**
1. ✅ **Push code to Bitbucket/GitHub**
2. ✅ **Share with `tests@scandiweb.com`**
3. ✅ **Keep public or properly shared**

### **Auto QA Testing:**
1. ✅ **Visit:** http://165.227.98.170/
2. ✅ **Enter:** `https://scandiweb-test-mohamedbadr.web1337.net/`
3. ✅ **Run tests & take "Passed" screenshot**

### **Email to Recruiter:**
```
Subject: Scandiweb Full Stack Test Submission - [Your Name]

Hi [Recruiter Name],

I have completed the Scandiweb Full Stack Developer test.

🔗 Live Application: https://scandiweb-test-mohamedbadr.web1337.net/
📁 Repository: [Your GitHub/Bitbucket URL]  
✅ Auto QA Results: [Screenshot attached]

Technical Details:
- Frontend: React + TypeScript + Vite
- Backend: PHP 8+ + GraphQL + MySQL
- Hosting: InfinityFree
- Features: Product listing, details, cart, orders

All requirements implemented and tested.

Best regards,
[Your Name]
```

---

## 🎯 **Quick Verification**

**After deployment, these should work:**

| Test | URL | Expected Result |
|------|-----|-----------------|
| Homepage | `https://scandiweb-test-mohamedbadr.web1337.net/` | Product grid loads |
| Category | `https://scandiweb-test-mohamedbadr.web1337.net/category/clothes` | Filtered products |
| Product | `https://scandiweb-test-mohamedbadr.web1337.net/product/[id]` | Product details |
| Cart | Click cart icon | Cart overlay opens |
| API | `https://scandiweb-test-mohamedbadr.web1337.net/api/` | Backend info page |
| GraphQL | POST to `/api/public/index.php` | JSON response |

**All systems ready for submission! 🚀** 