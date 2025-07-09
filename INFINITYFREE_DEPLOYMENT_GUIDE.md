# 🚀 InfinityFree Deployment Guide for Scandiweb App

## 📋 Your InfinityFree Setup
- **Domain**: `https://scandiweb-test-mohamedbadr.web1337.net/`
- **Control Panel**: VistaPanel
- **Database**: phpMyAdmin
- **File Manager**: htdocs folder

---

## 🗃️ Step 1: Database Setup (phpMyAdmin)

### 1.1 Access phpMyAdmin
1. Login to your InfinityFree VistaPanel
2. Click **"MySQL Databases"**
3. Note your database details:
   - **Database Name**: (something like `if0_12345678_scandiweb`)
   - **Username**: (something like `if0_12345678`)
   - **Password**: (your chosen password)
   - **Host**: (usually `sql200.infinityfree.com` or similar)

### 1.2 Create Tables
1. Click **"phpMyAdmin"** in your VistaPanel
2. Select your database from the left sidebar
3. Click **"Import"** tab
4. Upload `Backend/database_schema.sql`
5. Click **"Go"** to create tables

---

## 🔧 Step 2: Configure Database Connection

Edit `Backend/src/Config/Database.php` with your InfinityFree details:

```php
<?php
class Database {
    private $host = 'sql200.infinityfree.com';        // Your InfinityFree SQL host
    private $db_name = 'if0_12345678_scandiweb';      // Your database name
    private $username = 'if0_12345678';               // Your database username
    private $password = 'your_chosen_password';       // Your database password
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
```

---

## 📁 Step 3: Upload Files to InfinityFree

### 3.1 File Structure in htdocs
Your `htdocs` folder should look like this:

```
htdocs/
├── index.html                 # Frontend (from Frontend/dist/)
├── assets/                    # Frontend assets
│   ├── index-[hash].js
│   └── index-[hash].css
├── cart-icon.svg             # Frontend icons
├── cart-white.svg
├── logo.svg
└── api/                      # Backend folder
    ├── public/
    │   └── index.php
    ├── src/
    │   ├── Config/
    │   │   └── Database.php  # Your updated database config
    │   ├── Controller/
    │   ├── Models/
    │   └── ...
    ├── vendor/               # Composer dependencies
    ├── .htaccess
    ├── composer.json
    ├── data.json
    ├── database_schema.sql
    └── setup.php
```

### 3.2 Upload Frontend Files
1. Go to **"File Manager"** in VistaPanel
2. Navigate to `htdocs/`
3. Upload these files from `Frontend/dist/`:
   - `index.html` → `htdocs/index.html`
   - `assets/` folder → `htdocs/assets/`
   - `cart-icon.svg` → `htdocs/cart-icon.svg`
   - `cart-white.svg` → `htdocs/cart-white.svg`
   - `logo.svg` → `htdocs/logo.svg`

### 3.3 Upload Backend Files
1. Create folder `htdocs/api/`
2. Upload ALL files from `Backend/` to `htdocs/api/`:
   - `public/` folder
   - `src/` folder (with your updated `Database.php`)
   - `vendor/` folder
   - `.htaccess`
   - `composer.json`
   - `data.json`
   - `database_schema.sql`
   - `setup.php`

---

## 🔄 Step 4: Populate Database with Sample Data

### Method 1: Using Browser
1. Visit: `https://scandiweb-test-mohamedbadr.web1337.net/api/setup.php`
2. This will automatically import data from `data.json`
3. You should see success messages

### Method 2: Using phpMyAdmin
1. Go to phpMyAdmin
2. Select your database
3. Click **"Import"**
4. Upload a SQL file with INSERT statements (if you have one)

---

## 🧪 Step 5: Test Your Deployment

### 5.1 Test Frontend
Visit: `https://scandiweb-test-mohamedbadr.web1337.net/`
- Should show product categories and products
- Navigation should work

### 5.2 Test Backend API
Visit: `https://scandiweb-test-mohamedbadr.web1337.net/api/public/index.php`
- Should show GraphQL playground or API response

### 5.3 Test Full Functionality
- Browse products
- View product details
- Add items to cart
- Place orders

---

## 🔧 Step 6: Troubleshooting InfinityFree Issues

### Common InfinityFree Problems:

1. **"Internal Server Error"**
   - Check file permissions (should be 644 for files, 755 for folders)
   - Verify `.htaccess` is uploaded
   - Check error logs in VistaPanel

2. **"Database Connection Failed"**
   - Double-check database credentials in `Database.php`
   - Ensure database exists and is active
   - Try connecting via phpMyAdmin first

3. **"File Not Found" for API**
   - Ensure `api/` folder exists in `htdocs`
   - Check `.htaccess` is in `htdocs/api/.htaccess`
   - Verify `public/index.php` exists

4. **CORS Errors**
   - InfinityFree may block some CORS requests
   - API and frontend are on same domain, so should work

---

## ✅ Final Testing Checklist

### Manual Testing:
- [ ] Homepage loads: `https://scandiweb-test-mohamedbadr.web1337.net/`
- [ ] API responds: `https://scandiweb-test-mohamedbadr.web1337.net/api/public/index.php`
- [ ] Product listing works
- [ ] Product details work
- [ ] Add to cart works
- [ ] Cart overlay works
- [ ] Order placement works

### Auto QA Testing:
1. Visit: **http://165.227.98.170/**
2. Enter: `https://scandiweb-test-mohamedbadr.web1337.net/`
3. Run all tests
4. Take screenshot of "Passed" results

---

## 📧 Submission to Scandiweb

### Repository Setup:
1. Create Bitbucket/GitHub repository
2. Upload your code (Frontend/src, Backend/, config files)
3. Share with `tests@scandiweb.com`

### Email to Recruiter:
**Subject**: Scandiweb Full Stack Test Submission

**Body**:
```
Hello,

I have completed the Scandiweb Full Stack Developer test task.

🔗 Live Application: https://scandiweb-test-mohamedbadr.web1337.net/
📁 Repository: [Your repo URL]
✅ Auto QA Results: [Attach screenshot from http://165.227.98.170/]

The application includes:
- Product Listing Page with categories
- Product Details Page with attributes and gallery
- Shopping cart functionality
- Order placement via GraphQL API
- Full responsive design with smooth animations

Best regards,
[Your Name]
```

---

## 🎯 Quick Start Commands

If you need to re-upload or update:

1. **Update database config**: Edit `htdocs/api/src/Config/Database.php`
2. **Re-import data**: Visit `https://scandiweb-test-mohamedbadr.web1337.net/api/setup.php`
3. **Update frontend**: Replace files in `htdocs/`
4. **Update backend**: Replace files in `htdocs/api/`

**Your app is now ready for InfinityFree! 🚀** 