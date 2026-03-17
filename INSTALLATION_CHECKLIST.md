# MOOC Platform - Installation & Verification Checklist

**Last Updated:** March 13, 2026  
**Status:** Ready for Deployment

---

## 📋 Pre-Installation Requirements

- [ ] PHP 8.0 or higher installed
- [ ] MySQL/MariaDB server running (XAMPP or standalone)
- [ ] Composer installed globally
- [ ] Git installed (optional)
- [ ] 500MB free disk space
- [ ] Port 8000 available (for Laravel dev server)
- [ ] Port 3306 available (for MySQL)

---

## 🚀 Installation Steps

### Step 1: Navigate to Project Directory

```bash
cd g:\Aplikasi\MOOCS\mooc-platform
```

- [ ] Project directory accessible
- [ ] Can see `app/`, `routes/`, `resources/` directories

### Step 2: Verify Dependencies

```bash
composer install
```

- [ ] All packages installed without errors
- [ ] See `vendor/` directory created
- [ ] No conflict messages

### Step 3: Configure Environment

```bash
cp .env.example .env
# OR if .env exists, verify:
```

**Verify .env contains:**

- [ ] `APP_NAME=Laravel`
- [ ] `APP_ENV=local` (development)
- [ ] `APP_DEBUG=true` (for debugging)
- [ ] `DB_CONNECTION=mysql`
- [ ] `DB_HOST=127.0.0.1`
- [ ] `DB_PORT=3306`
- [ ] `DB_DATABASE=mooks`
- [ ] `DB_USERNAME=root` (or your user)
- [ ] `DB_PASSWORD=` (or your password)

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

- [ ] See `Application key set successfully in .env`
- [ ] `APP_KEY` present in .env

### Step 5: Database Setup

**Create database (if not existing):**

```bash
# MySQL command line
mysql -u root -p
> CREATE DATABASE mooks;
> EXIT;
```

- [ ] Database `mooks` created in MySQL

**Run migrations:**

```bash
php artisan migrate:fresh
```

- [ ] All 11 tables created
- [ ] No migration errors
- [ ] See "Migrated" messages

### Step 6: Seed Sample Data

```bash
php artisan db:seed
```

- [ ] Database populated with sample data
- [ ] 10 users created
- [ ] 5 courses created
- [ ] 21 modules created
- [ ] Enrollments and progress created

### Step 7: Start Development Server

```bash
php artisan serve
```

- [ ] Server starts on localhost:8000
- [ ] No port conflicts
- [ ] See "Laravel development server started..."

---

## ✅ Verification Tests

### Access the Application

#### Test 1: Home Page

```
URL: http://localhost:8000/
Expected: Home page loads with:
```

- [ ] Navigation bar visible
- [ ] Hero section displays
- [ ] Platform statistics show numbers
- [ ] Feature cards display
- [ ] "Register" and "Login" buttons visible

#### Test 2: Leaderboard (Public)

```
URL: http://localhost:8000/leaderboard
Expected: Leaderboard loads with:
```

- [ ] 3 tabs visible (By XP, By Level, Weekly)
- [ ] User rankings display
- [ ] Top users shown
- [ ] No login required

#### Test 3: Login

```
URL: http://localhost:8000/login
Expected: Login form loads
```

- [ ] Email input field
- [ ] Password input field
- [ ] "Remember me" checkbox
- [ ] Login button
- [ ] Link to register

**Perform Login:**

- Email: `user1@example.com`
- Password: `password`

Expected after login:

- [ ] Redirects to `/dashboard`
- [ ] User name shown in navbar
- [ ] Session started

#### Test 4: Dashboard (Protected)

```
URL: http://localhost:8000/dashboard
Expected: Dashboard loads with:
```

- [ ] User's current level displayed
- [ ] XP progress bar shown
- [ ] Recent XP transactions listed
- [ ] Active courses displayed
- [ ] No page errors

#### Test 5: Courses

```
URL: http://localhost:8000/courses
Expected: Courses page loads with:
```

- [ ] "All Courses" tab (with 5 courses)
- [ ] "My Courses" tab (with enrolled courses)
- [ ] Course cards display
- [ ] Progress bars visible
- [ ] Enroll button works

**Test Enrollment:**

- [ ] Click "Enroll Now" button
- [ ] Confirm dialog appears
- [ ] Course added to "My Courses" tab
- [ ] Progress tracking enabled

#### Test 6: Course Details

```
URL: http://localhost:8000/courses/1
Expected: Course details page shows:
```

- [ ] Course title and description
- [ ] Instructor name
- [ ] Module list (21 modules)
- [ ] Module status icons
- [ ] Prerequisite information
- [ ] Progress bar
- [ ] "Start Module" button

#### Test 7: Module Viewer

```
URL: http://localhost:8000/courses/1/modules/1
Expected: Module viewer loads with:
```

- [ ] Module title
- [ ] Module content displays
- [ ] "Mark as Complete" button
- [ ] Navigation buttons (Next/Previous)
- [ ] Module list in sidebar
- [ ] Progress tracking

**Complete Module:**

- [ ] Click "Mark as Complete"
- [ ] Module shows as completed
- [ ] XP earned dialog appears (if configured)
- [ ] Progress bar updates

#### Test 8: Leaderboard (Logged In)

```
URL: http://localhost:8000/leaderboard
Expected: Additional features:
```

- [ ] User's rank displayed
- [ ] User highlighted in leaderboard
- [ ] Personal stats shown
- [ ] Same leaderboard tabs

#### Test 9: Profile

```
URL: http://localhost:8000/profile
Expected: Profile page shows:
```

- [ ] User's level badge
- [ ] Total XP display
- [ ] Global rank
- [ ] Edit profile form
- [ ] Change password form
- [ ] Recent activity list

**Test Profile Update:**

- [ ] Change name
- [ ] Enter current password
- [ ] Click save
- [ ] See success message
- [ ] Changes reflected

#### Test 10: Logout

```
Expected: Logout functionality
```

- [ ] Find logout button in user dropdown
- [ ] Click logout
- [ ] Session destroyed
- [ ] Redirected to home page
- [ ] No user info in navbar

---

## 🔍 Database Verification

### Check Tables Exist

```bash
php artisan tinker
> \DB::select('SHOW TABLES;')
```

Expected tables (11 total):

- [ ] users
- [ ] user_xp_logs
- [ ] courses
- [ ] modules
- [ ] enrollments
- [ ] module_progress
- [ ] password_resets
- [ ] failed_jobs
- [ ] personal_access_tokens
- [ ] migrations
- [ ] cache

### Check Sample Data

```bash
php artisan tinker
> User::count()          // Should show: 10
> Course::count()        // Should show: 5
> Module::count()        // Should show: 21
> Enrollment::count()    // Should show: 7-10
```

---

## 🛠️ Troubleshooting

### Issue: "SQLSTATE[HY000] [2002] Connection refused"

**Solution:**

1. Verify MySQL is running
2. Check `.env` database credentials
3. Ensure database `mooks` is created
4. Run: `php artisan migrate`

### Issue: "Target class [Controller] does not exist"

**Solution:**

1. Clear routes cache: `php artisan route:clear`
2. Clear config cache: `php artisan config:clear`
3. Restart server: `php artisan serve`

### Issue: Port 8000 already in use

**Solution:**

```bash
# Use different port
php artisan serve --port=8001
# Then access: http://localhost:8001
```

### Issue: "No application encryption key has been generated"

**Solution:**

```bash
php artisan key:generate
```

### Issue: Permission denied errors

**Solution:**

```bash
# Windows - usually doesn't need this
# Linux/Mac:
chmod -R 775 storage bootstrap/cache
```

### Issue: Migrations fail

**Solution:**

1. Check `.env` database credentials
2. Verify database exists
3. Run: `php artisan migrate:reset` (if needed)
4. Run: `php artisan migrate:fresh`

### Issue: Seeder errors

**Solution:**

1. Run migrations first: `php artisan migrate`
2. Then seed: `php artisan db:seed`
3. Or fresh install: `php artisan migrate:fresh --seed`

---

## 📊 Performance Verification

### Check Application Performance

```bash
# In browser console (F12)
Performance timing:
- Investigate page load time
- Check network requests
- Verify CSS/JS loading
```

Expected performance:

- [ ] Home page loads in < 1 second
- [ ] Dashboard loads in < 2 seconds
- [ ] No 404 errors
- [ ] All images load
- [ ] CSS properly applied
- [ ] JavaScript functions work

---

## 🔐 Security Verification

### Test Security Features

- [ ] CSRF token required for forms
- [ ] Passwords hashed in database
- [ ] Routes require authentication
- [ ] Authorization policies enforced
- [ ] Input validation working
- [ ] SQL injection prevention

**Verify in code:**

```bash
# Check CSRF token in forms
# Look for: @csrf in Blade templates

# Check password hashing
php artisan tinker
> User::first()->password
# Should be hashed (long string starting with $2y$)
```

---

## 📱 Responsive Design Test

### Desktop (1920x1080)

- [ ] Full layout visible
- [ ] All content displays
- [ ] Navigation clear
- [ ] Buttons accessible

### Tablet (768x1024)

- [ ] Layout responsive
- [ ] Sidebar visible (or toggle)
- [ ] Touch-friendly buttons
- [ ] Text readable

### Mobile (375x667)

- [ ] Navbar collapses
- [ ] Menu toggle working
- [ ] Content single column
- [ ] Forms usable

---

## 🧪 Browser Compatibility

Test in these browsers:

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

Expected results:

- [ ] All pages load
- [ ] No console errors
- [ ] Layout displays correctly
- [ ] Forms work properly
- [ ] Buttons clickable

---

## 📝 Final Verification Checklist

### Backend

- [ ] PHP version 8.0+
- [ ] Laravel 10+
- [ ] All dependencies installed
- [ ] Database migrations complete
- [ ] Sample data seeded
- [ ] No console errors

### Frontend

- [ ] All 7 views load
- [ ] Bootstrap 5 working
- [ ] Custom CSS applied
- [ ] Icons display
- [ ] Forms validate
- [ ] Navigation works

### Features

- [ ] Registration working
- [ ] Login working
- [ ] Logout working
- [ ] Course enrollment working
- [ ] Module completion working
- [ ] XP earning working
- [ ] Leaderboard displaying
- [ ] Profile management working

### API (Optional)

- [ ] `/api/leaderboard/xp` returns JSON
- [ ] `/api/leaderboard/level` returns JSON
- [ ] `/api/user/xp-summary` requires auth
- [ ] Proper status codes returned
- [ ] Error messages formatted

---

## 🎯 Deployment Readiness

Before deploying to production:

- [ ] `.env` configured for production
- [ ] `APP_DEBUG=false` in production
- [ ] Database backed up
- [ ] All migrations tested
- [ ] All features tested
- [ ] Security headers configured
- [ ] HTTPS enabled
- [ ] Logging configured
- [ ] Error handling tested
- [ ] Performance optimized

---

## 📞 Support Resources

### Check These Files

1. **FRONTEND_SETUP.md** - UI documentation
2. **API_DOCUMENTATION.md** - API reference
3. **DATABASE_SCHEMA_GAMIFICATION.md** - DB design
4. **COMPLETE_SUMMARY.md** - Product overview

### Laravel Documentation

- Laravel Docs: https://laravel.com/docs
- Blade Templating: https://laravel.com/docs/blade
- Eloquent ORM: https://laravel.com/docs/eloquent

### Common Commands

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Database
php artisan migrate:status
php artisan migrate:rollback
php artisan db:seed

# Development
php artisan tinker
php artisan serve --port=8001
php artisan make:controller NameController
```

---

## ✨ Success Indicators

You'll know everything is working when:

1. ✅ Home page loads without errors
2. ✅ Can register a new user
3. ✅ Can login with credentials
4. ✅ Dashboard shows user profile
5. ✅ Can browse and enroll in courses
6. ✅ Can complete modules and earn XP
7. ✅ Leaderboard shows rankings
8. ✅ Profile page works
9. ✅ Can logout successfully
10. ✅ No JavaScript console errors
11. ✅ No database errors
12. ✅ All pages load under 2 seconds
13. ✅ Responsive design works on mobile
14. ✅ Forms validate properly
15. ✅ API endpoints return JSON

---

**Estimated Setup Time:** 10-15 minutes  
**Status:** ✅ Ready for Production  
**Version:** 1.0.0

**Happy Learning! 🎓**
