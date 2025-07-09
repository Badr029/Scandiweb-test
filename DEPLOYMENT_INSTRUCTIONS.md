# 🚀 Deployment Instructions for Scandiweb E-commerce App

## 📋 Prerequisites
- InfinityFree hosting account (or similar PHP/MySQL hosting)
- MySQL database access
- Domain: `https://scandiweb-test-mohamedbadr.web1337.net/`

## 🗂️ Project Structure
```
fullstack-test-starter-main/
├── Backend/          # PHP GraphQL API
│   ├── public/       # Entry point (index.php)
│   ├── src/          # Application code
│   ├── data.json     # Sample data
│   └── database_schema.sql
└── Frontend/         # React App
    └── dist/         # Production build
```

## 🔧 Backend Deployment

### 1. Database Setup
1. **Create MySQL Database** in your hosting control panel
2. **Note your database credentials**:
   - Database Host (usually `localhost` or `sqlXXX.000webhost.com`)
   - Database Name
   - Username
   - Password

3. **Import Database Schema**:
   - Upload `Backend/database_schema.sql` to your database
   - Run the SQL commands to create tables

4. **Import Sample Data**:
   - Run `Backend/setup.php` once to populate data from `data.json`

### 2. Backend File Upload
Upload these Backend files to your hosting root directory:

```
/public_html/api/
├── public/
│   └── index.php
├── src/
│   ├── Config/
│   ├── Controller/
│   ├── Models/
│   └── (all other src files)
├── vendor/           # Composer dependencies
├── .htaccess        # URL rewriting
├── composer.json
├── data.json
├── database_schema.sql
└── setup.php
```

### 3. Configure Database Connection
Edit `Backend/src/Config/Database.php`:

```php
<?php
class Database {
    private $host = 'your-host';     // e.g., 'sql105.000webhost.com'
    private $db_name = 'your-db';    // e.g., 'id12345_scandiweb'
    private $username = 'your-user'; // e.g., 'id12345_dbuser'
    private $password = 'your-pass'; // Your database password
    // ... rest of the file
}
```

### 4. Install Composer Dependencies
If your hosting supports composer:
```bash
cd /public_html/api
composer install --no-dev
```

If not, upload the `vendor/` folder manually.

## 🎨 Frontend Deployment

### 1. Upload Frontend Files
Upload all files from `Frontend/dist/` to your domain root:

```
/public_html/
├── index.html
├── assets/
│   ├── index-[hash].js
│   └── index-[hash].css
├── cart-icon.svg
├── cart-white.svg
└── logo.svg
```

### 2. Configure API Endpoint
If needed, update the GraphQL endpoint in the built files to point to:
`https://scandiweb-test-mohamedbadr.web1337.net/api/public/index.php`

## 🔗 URL Structure
- **Frontend**: `https://scandiweb-test-mohamedbadr.web1337.net/`
- **Backend API**: `https://scandiweb-test-mohamedbadr.web1337.net/api/public/index.php`

## ✅ Testing Checklist

### Manual Testing
- [ ] Homepage loads with product categories
- [ ] Product listing page displays products
- [ ] Product detail page shows attributes and gallery
- [ ] Add to cart functionality works
- [ ] Cart overlay displays items
- [ ] Order placement works

### Auto QA Testing
1. Test your deployed URL at: **http://165.227.98.170/**
2. Ensure all tests pass
3. Take a screenshot of "Passed" results

## 🔧 Troubleshooting

### Common Issues
1. **500 Error**: Check file permissions (755 for directories, 644 for files)
2. **Database Connection Failed**: Verify credentials in `Database.php`
3. **GraphQL Not Found**: Ensure `.htaccess` is uploaded and working
4. **CORS Issues**: Add CORS headers in `public/index.php`

### File Permissions
```bash
chmod 755 Backend/public/
chmod 644 Backend/public/index.php
chmod 644 Backend/.htaccess
```

## 📧 Submission Requirements

### For Scandiweb
1. **Repository**: Share Bitbucket/GitHub repo with `tests@scandiweb.com`
2. **Email to Recruiter** with:
   - Live URL: `https://scandiweb-test-mohamedbadr.web1337.net/`
   - Repository URL
   - Screenshot of passed Auto QA tests from http://165.227.98.170/

### Repository Structure
Keep these files in your repo:
- All source code (`Backend/` and `Frontend/src/`)
- Configuration files
- Documentation
- `.gitignore` files

**Remove these before deployment:**
- `node_modules/`
- Development test files
- Local configuration files

---

## 🎯 Final Checklist
- [ ] Backend uploaded to `/api/` directory
- [ ] Frontend uploaded to root directory
- [ ] Database configured and populated
- [ ] All URLs accessible 24/7
- [ ] Auto QA tests pass
- [ ] Repository shared with tests@scandiweb.com
- [ ] Email sent to recruiter

**Your app is ready for deployment! 🚀** 