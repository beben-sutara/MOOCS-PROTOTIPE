# 🚀 MOOC Platform API Documentation

Complete REST API documentation untuk MOOC platform dengan gating logic dan XP/leveling system.

---

## 📡 Base URL

```
http://localhost:8000/api
```

---

## 🔐 Authentication

### Available Methods

1. **Sanctum Token** (Recommended for API)

    ```bash
    POST /api/login
    Content-Type: application/json

    {
        "email": "user@example.com",
        "password": "password"
    }
    ```

    Response akan include token yang digunakan di header:

    ```
    Authorization: Bearer {token}
    ```

2. **Session Cookie** (untuk web)
    ```bash
    POST /login
    ```

### Usage dalam Requests

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" https://localhost:8000/api/user
```

---

## 📊 API Endpoints

### 🏆 Leaderboard Endpoints

#### 1. Get Top Users by XP (Global Leaderboard)

```
GET /api/leaderboard/xp
```

**Query Parameters:**

- `limit` (integer, optional): Default 100, max 100

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Ahmad Hendra",
            "email": "ahmad@mooc.local",
            "level": 15,
            "xp": 3200,
            "rank": 1
        },
        {
            "id": 2,
            "name": "Raka Wijaya",
            "email": "raka@mooc.local",
            "level": 12,
            "xp": 2500,
            "rank": 2
        }
    ],
    "user_rank": 45,
    "total_users": 7
}
```

**Example Requests:**

```bash
# Get top 20 users
curl http://localhost:8000/api/leaderboard/xp?limit=20

# With authentication
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/leaderboard/xp
```

---

#### 2. Get Top Users by Level

```
GET /api/leaderboard/level
```

**Parameters:**

- `limit` (integer, optional): Default 100

**Response:** Similar to XP leaderboard, sorted by level descending

---

#### 3. Get Leaderboard Statistics

```
GET /api/leaderboard/stats
```

**Response:**

```json
{
    "success": true,
    "data": {
        "total_users": 7,
        "total_instructors": 2,
        "top_user": {
            "name": "Ahmad Hendra",
            "level": 15,
            "xp": 3200
        },
        "average_level": 10.5,
        "average_xp": 1885.7
    }
}
```

---

#### 4. Get Weekly Leaderboard

```
GET /api/leaderboard/weekly
```

**Description:** Top users yang aktif dalam 7 hari terakhir

**Response:** Similar to leaderboard with timestamp filtering

---

#### 5. Get Leaderboard by Course

```
GET /api/leaderboard/course/{courseId}
```

**Parameters:**

- `courseId` (integer, required): Course ID
- `limit` (integer, optional): Default 100

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 3,
            "name": "Dina Kusuma",
            "level": 9,
            "xp": 1800,
            "rank": 1,
            "progress": 95.5
        }
    ],
    "course_id": 1
}
```

---

#### 6. Filter Leaderboard by Level

```
GET /api/leaderboard/level/{level}
```

**Parameters:**

- `level` (integer, required): Specific level (1-100)
- `limit` (integer, optional): Default 100

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "name": "Maya Cahyani",
            "level": 10,
            "xp": 2100,
            "rank": 1
        }
    ],
    "level": 10,
    "count": 3
}
```

---

### 👤 User XP Endpoints

#### 1. Get User XP Summary

```
GET /api/user/xp-summary
Authorization: Bearer {token}
```

**Description:** Get lengkap XP & leveling info untuk authenticated user

**Response:**

```json
{
    "success": true,
    "data": {
        "current_xp": 2500,
        "current_level": 12,
        "next_level_xp": 2836,
        "xp_until_next_level": 336,
        "xp_progress_percentage": 91.1,
        "total_xp_in_current_level": 335,
        "is_max_level": false,
        "rank": 2,
        "rank_percentage": 28.5,
        "last_xp_earned_at": "2026-03-13T06:50:00Z"
    }
}
```

---

#### 2. Get User XP History

```
GET /api/user/xp-logs
Authorization: Bearer {token}
```

**Query Parameters:**

- `limit` (integer, optional): Default 50, max 100
- `offset` (integer, optional): Default 0

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 4,
            "amount": 100,
            "source": "module_completed",
            "previous_xp": 2400,
            "current_xp": 2500,
            "previous_level": 12,
            "current_level": 12,
            "leveled_up": false,
            "metadata": {
                "module_id": 1,
                "module_title": "Installation & Setup"
            },
            "created_at": "2026-03-13T06:50:00Z"
        }
    ],
    "pagination": {
        "total": 125,
        "limit": 50,
        "offset": 0
    }
}
```

---

#### 3. Get User XP Analytics

```
GET /api/user/xp-analytics
Authorization: Bearer {token}
```

**Description:** Analisis lengkap XP yang diterima user

**Response:**

```json
{
    "success": true,
    "data": {
        "total_xp_earned": 2500,
        "avg_xp_per_day": 185.2,
        "avg_xp_per_transaction": 83.3,
        "most_common_source": "module_completed",
        "most_common_source_count": 30,
        "level_ups": [
            {
                "level": 5,
                "earned_at": "2026-03-10T10:15:00Z"
            },
            {
                "level": 12,
                "earned_at": "2026-03-12T14:30:00Z"
            }
        ],
        "total_transactions": 30
    }
}
```

---

#### 4. Get User Rank

```
GET /api/user/rank
Authorization: Bearer {token}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "user_id": 4,
        "name": "Raka Wijaya",
        "level": 12,
        "xp": 2500,
        "rank": 2
    }
}
```

---

#### 5. Get Specific User's XP Info

```
GET /api/users/{userId}/xp
```

**Parameters:**

- `userId` (integer, required): User ID

**Response:**

```json
{
    "success": true,
    "data": {
        "user_id": 4,
        "name": "Raka Wijaya",
        "level": 12,
        "xp": 2500,
        "next_level_xp": 2836,
        "progress": 91.1
    }
}
```

---

#### 6. Award XP to User (Admin/Instructor Only)

```
POST /api/users/{userId}/award-xp
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**

```json
{
    "amount": 100,
    "source": "bonus_activity",
    "metadata": {
        "reason": "Participation in discussion"
    }
}
```

**Response:**

```json
{
  "success": true,
  "message": "XP berhasil diberikan",
  "data": {
    "previous_xp": 2400,
    "current_xp": 2500,
    "previous_level": 12,
    "current_level": 12,
    "leveled_up": false,
    "next_level_xp": 2836,
    "xp_progress": 91.1
  },
  "user_summary": {
    "current_xp": 2500,
    "current_level": 12,
    ...
  }
}
```

**Validation Errors:**

```json
{
    "success": false,
    "message": "Anda tidak memiliki izin untuk memberikan XP",
    "errors": {
        "amount": ["The amount field is required."]
    }
}
```

---

### 📚 Module Endpoints

#### 1. Get All Modules in Course

```
GET /api/courses/{courseId}/modules
Authorization: Bearer {token}
```

**Description:** Get semua modules dalam course dengan access control validation

**Response:**

```json
{
    "course": {
        "id": 1,
        "title": "Laravel Fundamentals",
        "description": "..."
    },
    "modules": [
        {
            "id": 1,
            "title": "Installation & Setup",
            "order": 1,
            "is_locked": false,
            "is_completed": false,
            "is_viewed": false
        },
        {
            "id": 2,
            "title": "Routing Basics",
            "order": 2,
            "is_locked": true,
            "reason": "Requires prerequisite module"
        }
    ],
    "progress": {
        "completed_modules": 1,
        "total_modules": 5,
        "completion_percentage": 20
    }
}
```

---

#### 2. Get Module Details

```
GET /api/courses/{courseId}/modules/{moduleId}
Authorization: Bearer {token}
```

**Description:** Get detailed module content (jika user punya akses)

**Response:**

```json
{
    "module": {
        "id": 1,
        "title": "Installation & Setup",
        "content": "# Installation Guide\n\n## Prerequisites...",
        "order": 1,
        "is_locked": false
    },
    "prerequisites": {
        "required_module": null,
        "status": "completed"
    },
    "next_module": {
        "id": 2,
        "title": "Routing Basics",
        "order": 2
    },
    "previous_module": null,
    "user_progress": {
        "is_viewed": true,
        "is_completed": false
    }
}
```

---

#### 3. Mark Module as Completed

```
POST /api/courses/{courseId}/modules/{moduleId}/complete
Authorization: Bearer {token}
Content-Type: application/json
```

**Body (optional):**

```json
{
    "completion_time": "15:30",
    "metadata": {
        "quiz_score": 95
    }
}
```

**Response:**

```json
{
    "message": "Modul telah diselesaikan",
    "progress": {
        "is_viewed": true,
        "is_completed": true,
        "updated_at": "2026-03-13T07:00:00Z"
    },
    "course_progress": {
        "completed_modules": 2,
        "total_modules": 5,
        "completion_percentage": 40
    },
    "xp_awarded": {
        "amount": 100,
        "new_level": 12,
        "leveled_up": false
    }
}
```

**Error Responses:**

```json
{
    "success": false,
    "message": "Module tidak dapat diakses - prerequisite tidak selesai"
}
```

---

## 💡 Usage Examples

### Example 1: User Login & Get Own XP Summary

```bash
# 1. Login (jika menggunakan session)
curl -X POST http://localhost:8000/login \
  -d "email=raka@mooc.local&password=password"

# 2. Get user XP summary (dengan cookie)
curl -b cookies.txt http://localhost:8000/api/user/xp-summary

# Atau dengan Sanctum token:
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/user/xp-summary
```

### Example 2: Get Leaderboard & Check Your Rank

```bash
# Get top 10 users
curl http://localhost:8000/api/leaderboard/xp?limit=10

# Get with authentication to see your rank
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/leaderboard/xp?limit=100
```

### Example 3: Complete Module & Earn XP

```bash
# View module
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/courses/1/modules/1

# Complete module
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"completion_time": "12:30"}' \
  http://localhost:8000/api/courses/1/modules/1/complete

# Response akan include XP awarded dan level up info
```

### Example 4: Instructor Awards XP (Bonus Activity)

```bash
curl -X POST \
  -H "Authorization: Bearer INSTRUCTOR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50,
    "source": "discussion_participation",
    "metadata": {
      "discussion_id": 10,
      "quality": "excellent"
    }
  }' \
  http://localhost:8000/api/users/4/award-xp
```

### Example 5: Get Analytics & Level Up History

```bash
# Get user analytics
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/user/xp-analytics

# Get XP history with pagination
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/user/xp-logs?limit=20&offset=0"
```

### Example 6: JavaScript/Fetch Example

```javascript
// Get leaderboard
fetch("/api/leaderboard/xp?limit=50")
    .then((res) => res.json())
    .then((data) => {
        console.log("Top users:", data.data);
        console.log("Your rank:", data.user_rank);
    });

// With authentication
const token = localStorage.getItem("auth_token");
fetch("/api/user/xp-summary", {
    headers: {
        Authorization: `Bearer ${token}`,
    },
})
    .then((res) => res.json())
    .then((data) => console.log("Your XP:", data.data));
```

---

## 🔄 Status Codes

| Code | Meaning                       |
| ---- | ----------------------------- |
| 200  | Success                       |
| 201  | Created                       |
| 400  | Bad Request - Invalid input   |
| 401  | Unauthorized - Login required |
| 403  | Forbidden - No permission     |
| 404  | Not Found                     |
| 422  | Validation Error              |
| 500  | Server Error                  |

---

## ⚠️ Error Responses

### Validation Error

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "amount": ["The amount must be at least 1."],
        "source": ["The source field is required."]
    }
}
```

### Authorization Error

```json
{
    "success": false,
    "message": "Anda tidak memiliki izin untuk memberikan XP"
}
```

### Module Not Found

```json
{
    "message": "No query results found for model [App\\Models\\Module]."
}
```

---

## 🔐 Rate Limiting

Belum diimplementasikan, tapi recommended:

- 60 requests per minute untuk public endpoints
- 100 requests per minute untuk authenticated users
- 1000 requests per minute untuk admin

---

## 📝 Common Workflows

### 1. User Journey: Complete Course

```
1. User enrolls in course
   GET /api/courses/{id}/modules

2. User views and completes modules one by one
   GET /api/courses/{id}/modules/{id}
   POST /api/courses/{id}/modules/{id}/complete

3. Each completion:
   - Marks module complete
   - Awards XP
   - Unlocks next module
   - May trigger level up notification

4. Check progress
   GET /api/user/xp-summary
   GET /api/leaderboard/xp
```

### 2. Instructor Workflow: Monitor & Award

```
1. View leaderboard
   GET /api/leaderboard/course/{courseId}

2. Check student progress
   GET /api/users/{userId}/xp
   GET /api/user/xp-analytics (if student allows)

3. Award bonus for participation
   POST /api/users/{userId}/award-xp
```

### 3. Admin Workflow: Manage Platform

```
1. View overall stats
   GET /api/leaderboard/stats

2. Check daily active users
   GET /api/leaderboard/weekly

3. Monitor top performers
   GET /api/leaderboard/level
```

---

## 🧪 Testing Endpoints

### Using cURL

```bash
# Test public endpoint
curl http://localhost:8000/api/leaderboard/stats

# Test with authentication (requires token)
curl -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  http://localhost:8000/api/user/xp-summary

# Test POST with JSON
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"amount": 100, "source": "test"}' \
  http://localhost:8000/api/users/1/award-xp
```

### Using Postman

1. Create collection: "MOOC Platform API"
2. Set base URL: `{{base_url}}/api`
3. Create environment var: `base_url = http://localhost:8000`
4. Create requests untuk setiap endpoint
5. Add auth token ke request headers

### Using Insomnia

Same as Postman, tapi dengan UI yang lebih clean

---

## 📞 Support

Untuk issues atau questions:

1. Check dokumentasi: [XP_AND_LEVELING_DOCS.md](XP_AND_LEVELING_DOCS.md)
2. Check examples di quick start: [XP_LEVELING_QUICKSTART.md](XP_LEVELING_QUICKSTART.md)
3. Check tests: [tests/Feature/XpAndLevelingTest.php](tests/Feature/XpAndLevelingTest.php)

---

**Last Updated**: March 13, 2026
**API Version**: 1.0
**Status**: Production Ready ✅
