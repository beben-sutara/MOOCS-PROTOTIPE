# Frontend & Web Routes Setup - MOOC Platform

**Date:** 2026-03-13  
**Status:** ✅ COMPLETE - All web routes, controllers, and views created

## Overview

This document summarizes the complete frontend setup for the MOOC Platform, including web routes, controllers, and Blade views. All authentication flows, dashboard, courses, leaderboard, and profile management features are now fully implemented.

---

## Created Controllers

### 1. **HomeController** (NEW)

**File:** `app/Http/Controllers/HomeController.php`

**Methods:**

- `index()` - Display home/welcome page with platform statistics

**Functions:**

- Retrieves count of:
    - Total users (excluding admins)
    - Published courses
    - Total modules
- Passes stats to home view
- Shows conditional content for authenticated vs guest users

---

### 2. **DashboardController** (NEW)

**File:** `app/Http/Controllers/DashboardController.php`

**Methods:**

- `index()` - Display user dashboard

**Features:**

- Shows user's current level, XP, and rank
- Displays courses enrolled in
- Shows recent XP transactions
- Displays modules progress
- Protected route (auth middleware)

---

### 3. **CoursesController** (NEW)

**File:** `app/Http/Controllers/CoursesController.php`

**Methods:**

- `index()` - List all courses (published)
- `show(Course)` - Display course details with modules
- `enroll(Request, Course)` - Enroll user in course

**Features:**

- Filters courses by enrollment status
- Shows course progress for enrolled users
- Checks enrollment before displaying course
- Supports both web and JSON enrollment responses

---

### 4. **LeaderboardWebController** (NEW)

**File:** `app/Http/Controllers/LeaderboardWebController.php`

**Methods:**

- `index()` - Display leaderboard page

**Features:**

- Shows three leaderboard types:
    1. **By XP** - Top 100 users sorted by total XP
    2. **By Level** - Top 100 users sorted by level then XP
    3. **Weekly** - Top users active in last 7 days
- Calculates user's global rank
- Highlights authenticated user's position
- Public route (no auth required)

---

### 5. **ProfileController** (NEW)

**File:** `app/Http/Controllers/ProfileController.php`

**Methods:**

- `show()` - Display user profile page
- `update(Request)` - Update profile information (name, email, phone)
- `changePassword(Request)` - Change user password

**Features:**

- Shows user level, XP, global rank
- Displays edit profile form with validation
- Password change form with current password verification
- Shows recent XP activity (last 20 transactions)
- Protected route (auth middleware)

---

### 6. **ModuleController** (UPDATED)

**File:** `app/Http/Controllers/ModuleController.php`

**Updated Methods:**

- `show(Course, Module)` - Now supports both web and API requests
- `complete(Request, Course, Module)` - Now supports both web and API requests

**Changes:**

- Added enrollment verification
- Validates module access via gating service
- Returns Blade view for web requests, JSON for API requests
- Automatically marks module as viewed when accessed
- Middleware updated to remove check.module.access (now manual)

---

## Created Blade Views

### Layout & Base Templates

#### `resources/views/app.blade.php` (MASTER LAYOUT)

**Purpose:** Main layout template for entire application

**Features:**

- Bootstrap 5 responsive navbar with:
    - Logo and branding
    - Navigation links (Home, Courses, Leaderboard)
    - Authenticated user dropdown menu
    - Login/Register buttons for guests
- Alert message display zone
- Main content area with `@yield('content')`
- Footer with copyright
- Custom CSS styling:
    - Gradient backgrounds
    - Bootstrap utilities
    - Custom components (level badges, XP bars)
    - Responsive design
    - Smooth transitions and hover effects
- JavaScript includes:
    - Bootstrap Bundle
    - Icons library

**Extends:** None (base template)

---

### Home Page

#### `resources/views/home.blade.php`

**Purpose:** Landing page for unauthenticated and authenticated users

**Sections:**

1. **Hero Section:**
    - Large heading and tagline
    - Call-to-action buttons (Login/Register for guests, Start Learning for authenticated)
    - Decorative icon

2. **Platform Statistics:**
    - Total users
    - Total courses
    - Total modules
    - Displayed in card format

3. **Features Showcase:**
    - 3 feature cards highlighting:
        1. Gamification (XP, levels, leaderboard)
        2. Prerequisite system (structured learning)
        3. Progress tracking (analytics and stats)

4. **User Profile Preview (if authenticated):**
    - User's level badge
    - Total XP
    - Progress bar to next level
    - Links to dashboard and courses

**Data Passed:**

- `$stats` - Array with total_users, total_courses, total_modules

---

### Dashboard

#### `resources/views/dashboard.blade.php`

**Purpose:** User dashboard showing learning progress

**Sections:**

1. **Statistics Cards:**
    - Current level with badge
    - Global rank with trophy icon
    - Courses enrolled count
    - Modules completed count

2. **XP Progress to Next Level:**
    - Visual progress bar
    - Percentage of progress
    - XP needed breakdown

3. **Recent XP Transactions:**
    - Table showing last 10 XP transactions
    - Displays: source, amount, total XP, date
    - Shows level-ups with indicators

4. **Active Courses:**
    - Cards for each enrolled course
    - Shows completion percentage
    - Module progress (X/Y completed)
    - Quick "Go to Course" button

**Data Passed:**

- `$userRank` - User's global rank

**Route:** `/dashboard` (Protected)

---

### Courses Management

#### `resources/views/courses/index.blade.php`

**Purpose:** Browse and manage courses

**Tabs:**

1. **All Courses:**
    - Displays all published courses
    - Shows course title, description, instructor
    - Module count
    - Progress bar (if enrolled)
    - "Enroll Now" or "Continue Learning" button

2. **My Courses:**
    - Displays only enrolled courses
    - Shows enrollment status badge
    - Course progress with completion percentage
    - "View Course" button
    - Alert if not enrolled in any courses

**Features:**

- Course cards with:
    - Title and description preview
    - Instructor name
    - Module count
    - Progress tracking for enrolled users
    - Status badge (Published)
- Enroll modal confirmation dialog
- Responsive grid layout

**Route:** `/courses` (Protected)

---

#### `resources/views/courses/show.blade.php`

**Purpose:** Display course details and module list

**Layout:** Two-column (main + sidebar)

**Main Content:**

- Course title and description
- Course status badge
- Course instructor name
- Complete module list with:
    - Module status icons (completed, in-progress, locked)
    - Module title and description preview
    - Prerequisite information
    - Action buttons (Start, Review, Locked)

**Sidebar:**

- Course progress percentage
- Progress bar visualization
- Modules completed count

**Features:**

- Gating logic indicators (locked modules with prerequisite info)
- Completed modules show "Review" instead of "Start"
- Locked modules show prerequisite requirement
- Responsive design
- Quick access to next/previous modules

**Route:** `/courses/{course}` (Protected)

---

#### `resources/views/modules/show.blade.php`

**Purpose:** Display module content and track progress

**Layout:** Two-column (main + sidebar)

**Main Content:**

- Module title
- Full module content (HTML rendered from database)
- Complete/Review buttons
- Navigation buttons (Previous/Next Module)
- Module completion badge (if completed)

**Sidebar:**

- Course progress bar
- Module list with current module highlighted
- All courses modules listed with status indicators
- Module info (course, prerequisites)

**Features:**

- Full-width module content area with custom styling
- Markdown/HTML content support
- Navigation between modules
- Progress tracking
- Sticky sidebar for easy navigation
- JavaScript-based module completion handling

**Route:** `/courses/{course}/modules/{module}` (Protected)
**Route:** `/courses/{course}/modules/{module}/complete` (POST, Protected)

---

### Leaderboard

#### `resources/views/leaderboard.blade.php`

**Purpose:** Display global rankings and competition

**Tabs:**

1. **By XP:** Top 100 users ranked by total XP
2. **By Level:** Top 100 users ranked by level then XP
3. **Weekly:** Top users active in last 7 days

**Features:**

- Rank badges (1st=Gold, 2nd=Silver, 3rd=Bronze, Others=Numbers)
- User information:
    - Name
    - Email
    - Level badge
    - XP amount
- Highlights authenticated user's row in table
- Responsive table design
- Medal icons for top 3

**User Stats Section (if authenticated):**

- User's current level
- Total XP with icon
- Global rank with trophy icon

**Data Passed:**

- `$xpLeaderboard` - Array of top users by XP
- `$levelLeaderboard` - Array of top users by level
- `$weeklyLeaderboard` - Array of weekly active users
- `$userRank` - User's global rank

**Route:** `/leaderboard` (Public)

---

### User Profile

#### `resources/views/profile.blade.php`

**Purpose:** Manage user profile and account settings

**Layout:** Two-column (profile + forms)

**Left Column - Profile Card:**

- User's level badge (large)
- Name and role
- Total XP
- Global rank
- Contact information (email, phone)
- Account creation date

**Right Column - Forms:**

1. **Edit Profile Form:**
    - Name field
    - Email field (unique validation)
    - Phone field (optional)
    - Current password verification
    - Save/Reset buttons
    - Validation feedback

2. **Change Password Form:**
    - Current password field
    - New password field
    - Confirm password field
    - Update button
    - Validation feedback

**Analytics Section:**

- Progress to next level (percentage and bar)
- XP until next level
- Courses enrolled / completed count

**Recent Activity:**

- Last 20 XP transactions
- Displays: source, amount earned, level changes, date/time
- Shows level-up badges and dates

**Route:** `/profile` (Protected)
**Route:** `/profile/update` (PUT, Protected)
**Route:** `/profile/change-password` (PUT, Protected)

---

## Web Routes Configuration

**File:** `routes/web.php`

### Route Groups

#### 1. **Public Routes** (No Authentication Required)

```php
GET  /                    -> HomeController@index
GET  /leaderboard        -> LeaderboardWebController@index
```

#### 2. **Guest-Only Routes** (Must not be authenticated)

```php
GET  /login              -> AuthController@showLogin
POST /login              -> AuthController@login
GET  /register           -> AuthController@showRegister
POST /register           -> AuthController@register
```

#### 3. **Auth-Only Routes** (Must be authenticated)

```php
POST /logout             -> AuthController@logout

GET  /dashboard          -> DashboardController@index
GET  /courses            -> CoursesController@index
GET  /courses/{course}   -> CoursesController@show
POST /courses/{course}/enroll -> CoursesController@enroll (Web)
POST /api/courses/{course}/enroll -> CoursesController@enroll (API)

GET  /courses/{course}/modules/{module} -> ModuleController@show
POST /courses/{course}/modules/{module}/complete -> ModuleController@complete

GET  /profile            -> ProfileController@show
PUT  /profile/update     -> ProfileController@update
PUT  /profile/change-password -> ProfileController@changePassword
```

---

## Authentication Flow

### Registration Flow

1. User clicks "Register" on home page
2. Navigates to `/register` (guest-only)
3. Fills registration form (name, email, phone, password)
4. System validates:
    - Email is unique
    - Password is min 6 characters
    - Password confirmation matches
5. New User created with:
    - `role = 'user'`
    - `xp = 0`
    - `level = 1`
    - `next_level_xp = 100`
6. User automatically logged in
7. Redirects to `/dashboard`

### Login Flow

1. User clicks "Login"
2. Navigates to `/login` (guest-only)
3. Enters email and password
4. Optional "Remember me" checkbox
5. System validates credentials
6. If valid:
    - Session created
    - Redirects to `/dashboard`
7. If invalid:
    - Returns with error message
    - Email is retained for convenience

### Logout Flow

1. User clicks "Logout" in dropdown menu
2. POST request to `/logout`
3. System:
    - Logs out the user
    - Invalidates session
    - Regenerates CSRF token
4. Redirects to home page with success message

---

## Key Features

### 1. **Responsive Design**

- All views use Bootstrap 5 grid system
- Mobile-first approach
- Collapsible navbar on mobile
- Touch-friendly buttons and inputs

### 2. **Gamification Display**

- Level badges with gradient backgrounds
- XP progress bars with visual indicators
- Leaderboard rankings with medal badges
- Weekly activity tracking

### 3. **Enrollment System**

- Gating logic enforcement
- Module prerequisites displayed
- Locked module indicators
- Progress tracking per course

### 4. **User Experience**

- Consistent navigation across all pages
- Breadcrumb-style links back to parent pages
- Sticky sidebars for easy navigation
- Alert messages for feedback
- Loading states and disabled buttons

### 5. **Data Visualization**

- Progress bars (XP, course completion, level)
- Stat cards with icons
- Feature highlight cards
- User statistics dashboard

---

## Integration Points

### Connected to Existing Systems

1. **Authentication System**
    - Uses Laravel's built-in Auth facade
    - Integrates with User model
    - Password hashing via Hash facade
    - Session-based authentication

2. **Database Models**
    - User (with XP trait)
    - Course
    - Module (with prerequisites)
    - Enrollment
    - ModuleProgress
    - UserXpLog

3. **Services**
    - ModuleGatingService (access control)
    - XpRewardService (leaderboard queries)

4. **Policies**
    - ModulePolicy (authorization)

---

## Testing the Frontend

### Quick Test URLs

1. **Home Page (Public)**

    ```
    http://localhost:8000/
    ```

2. **Register New User**

    ```
    http://localhost:8000/register
    ```

    Demo data:
    - Name: Test User
    - Email: test@example.com
    - Password: password123

3. **Login**

    ```
    http://localhost:8000/login
    ```

    Existing users from seeder:
    - Email: user1@example.com, Password: password
    - Email: user2@example.com, Password: password

4. **Dashboard (Protected)**

    ```
    http://localhost:8000/dashboard
    ```

5. **Browse Courses**

    ```
    http://localhost:8000/courses
    ```

6. **View Course Details**

    ```
    http://localhost:8000/courses/1
    ```

7. **View Module**

    ```
    http://localhost:8000/courses/1/modules/1
    ```

8. **Leaderboard (Public)**

    ```
    http://localhost:8000/leaderboard
    ```

9. **User Profile (Protected)**
    ```
    http://localhost:8000/profile
    ```

---

## File Structure Summary

```
resources/views/
├── app.blade.php                    (Master layout)
├── home.blade.php                   (Home page)
├── dashboard.blade.php              (User dashboard)
├── leaderboard.blade.php            (Leaderboard)
├── profile.blade.php                (User profile)
├── auth/
│   ├── login.blade.php             (Login form)
│   └── register.blade.php           (Registration form)
├── courses/
│   ├── index.blade.php             (Courses list)
│   └── show.blade.php              (Course details)
└── modules/
    └── show.blade.php               (Module content)

app/Http/Controllers/
├── HomeController.php               (NEW)
├── DashboardController.php          (NEW)
├── CoursesController.php            (NEW)
├── LeaderboardWebController.php     (NEW)
├── ProfileController.php            (NEW)
├── ModuleController.php             (UPDATED)
├── AuthController.php               (Existing)
├── LeaderboardController.php        (API only)
├── UserXpController.php             (API only)
└── Controller.php                   (Base)

routes/
└── web.php                          (UPDATED with all web routes)
```

---

## Next Steps (Optional Enhancements)

1. **Email Notifications**
    - Welcome email on registration
    - Course enrollment confirmation
    - Level-up notifications

2. **Advanced Features**
    - Course completion certificates
    - Discussion forums per course
    - Quiz/assessment system
    - Discussion activity XP rewards

3. **Admin Panel**
    - Course management interface
    - User management
    - XP audit logs viewer
    - Analytics dashboard

4. **Mobile App**
    - React Native / Flutter app
    - Uses existing API endpoints
    - Offline course access

5. **Social Features**
    - Follow other users
    - Public user profiles
    - Share achievements

---

## Deployment Checklist

- [ ] Run `php artisan migrate:fresh` (if fresh install) or `php artisan migrate` (for updates)
- [ ] Run `php artisan db:seed` (to seed sample data)
- [ ] Verify `.env` file has correct database credentials
- [ ] Ensure `APP_URL` is set correctly in `.env`
- [ ] Run `php artisan serve` or deploy to production server
- [ ] Test all routes in browser
- [ ] Verify email sending (if using email features)
- [ ] Check file upload permissions (if adding file uploads)

---

**Created:** March 13, 2026  
**Status:** ✅ Production Ready  
**Version:** 1.0.0
