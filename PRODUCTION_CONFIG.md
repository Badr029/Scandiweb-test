# 🔧 Production Configuration for Your Deployment

## 🌐 Your Specific URLs

### InfinityFree Backend
- **Domain**: `scandiweb-test-MohamedBadr.wuaze.com`
- **API URL**: `https://scandiweb-test-MohamedBadr.wuaze.com/api/`
- **File Upload Path**: `htdocs/api/` (in InfinityFree File Manager)

### Vercel Frontend (will be assigned after deployment)
- **URL**: `https://scandiweb-test.vercel.app` (example)
- **Root Directory**: `Frontend`

## ⚙️ Configuration Steps

### 1. Frontend Environment Configuration
Create `Frontend/.env.production` with:
```env
VITE_API_URL=https://scandiweb-test-MohamedBadr.wuaze.com/api/
```

### 2. Backend CORS Configuration
Update `Backend/public/index.php` with your Vercel URL (after deployment):
```php
// Replace YOUR_VERCEL_URL with actual URL after Vercel deployment
header("Access-Control-Allow-Origin: https://YOUR_VERCEL_URL");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
```

### 3. Database Configuration
Update `Backend/src/Config/Database.php` with your InfinityFree database credentials:
```php
private $host = "sql123.infinityfree.com";              // From InfinityFree control panel
private $db_name = "epiz_XXXXXXX_scandiweb";            // Your database name
private $username = "epiz_XXXXXXX";                     // Your database username  
private $password = "your_chosen_password";             // Your database password
```

## 📂 File Upload Structure for InfinityFree

Upload to `htdocs/api/` in this structure:
```
htdocs/
└── api/
    ├── public/
    │   └── index.php
    ├── src/
    │   ├── Config/
    │   │   └── Database.php
    │   ├── Controller/
    │   │   └── GraphQL.php
    │   └── Models/
    ├── vendor/              ⚠️ CRITICAL - PHP dependencies
    │   ├── autoload.php
    │   ├── composer/
    │   ├── nikic/
    │   ├── webonyx/
    │   └── ... (other dependencies)
    ├── composer.json
    ├── composer.lock
    ├── database_schema.sql
    ├── data.json
    └── .htaccess
```

⚠️ **IMPORTANT**: The `vendor` folder contains all PHP dependencies installed by Composer (FastRoute, GraphQL libraries, etc.). Without it, your backend will not work!

## 🚀 Deployment Checklist

### Backend (InfinityFree)
- [ ] Upload files to `htdocs/api/`
- [ ] Create MySQL database
- [ ] Import `database_schema.sql`
- [ ] Import data from `data.json`
- [ ] Update `Database.php` with credentials
- [ ] Test API: `https://scandiweb-test-MohamedBadr.wuaze.com/api/`

### Frontend (Vercel)
- [ ] Push to GitHub
- [ ] Connect repository to Vercel
- [ ] Set Root Directory to `Frontend`
- [ ] Add environment variable: `VITE_API_URL=https://scandiweb-test-MohamedBadr.wuaze.com/api/`
- [ ] Deploy and get Vercel URL
- [ ] Update backend CORS with Vercel URL

### Final Testing
- [ ] Test GraphQL API endpoint
- [ ] Test frontend functionality
- [ ] Run Auto QA: http://165.227.98.170/
- [ ] Take screenshot of "Passed" status

## 🎯 Expected Final URLs
- **Frontend**: `https://scandiweb-test.vercel.app` (or similar)
- **Backend**: `https://scandiweb-test-MohamedBadr.wuaze.com/api/` 