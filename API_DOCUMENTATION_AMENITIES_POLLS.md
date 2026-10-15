# Legacy Carry — Amenity Booking & Poll System REST API Documentation

This document describes the complete REST API contract for the **Amenity Booking System** and **Poll & Voting System** in Legacy Carry (Laravel 12 + Sanctum). These APIs are designed to power both the website dashboards and any future mobile applications (Android / iOS / Flutter).

---

## 1. Authentication & Common Headers

All requests require authentication via Laravel Sanctum Bearer tokens.

```http
Authorization: Bearer <SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```

### Standard Response Envelope

#### Success (`200 OK` or `201 Created`)
```json
{
  "success": true,
  "status": true,
  "message": "Operation completed successfully",
  "data": { ... }
}
```

#### Validation Failure (`422 Unprocessable Entity`)
```json
{
  "success": false,
  "status": false,
  "message": "Validation failed",
  "errors": {
    "field_name": [
      "The field_name is required."
    ]
  }
}
```

#### Conflict / Concurrency Failure (`409 Conflict`)
```json
{
  "success": false,
  "status": false,
  "message": "The selected time slot is unavailable or already booked. Please choose another time."
}
```

---

## 2. Amenity Booking APIs

### 2.1 List Amenities
- **Endpoint**: `GET /api/v1/amenities` (or `GET /api/amenities`)
- **Purpose**: Get all active amenities available for the authenticated resident's society.
- **Auth**: `auth:sanctum` (Resident, Super Admin)
- **Parameters**: None

#### Example Request
```bash
curl -X GET "https://your-domain.com/api/v1/amenities" \
  -H "Authorization: Bearer 1|abc123xyz" \
  -H "Accept: application/json"
```

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Amenities fetched successfully",
  "data": [
    {
      "id": 1,
      "name": "Clubhouse Hall",
      "description": "Spacious community hall for events.",
      "image": "https://your-domain.com/storage/amenities/clubhouse.jpg",
      "booking_mode": "admin_approval",
      "opening_time": "08:00",
      "closing_time": "22:00",
      "available_days": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      "slot_duration_minutes": 60,
      "max_booking_duration_minutes": 180,
      "advance_booking_days": 14,
      "fee": 500.00
    }
  ]
}
```

---

### 2.2 Get Amenity Details
- **Endpoint**: `GET /api/v1/amenities/{id}`
- **Purpose**: Fetch details of a specific amenity.
- **Auth**: `auth:sanctum`
- **Path Parameter**: `id` (integer) — Amenity ID

#### Example Request
```bash
curl -X GET "https://your-domain.com/api/v1/amenities/1" \
  -H "Authorization: Bearer 1|abc123xyz" \
  -H "Accept: application/json"
```

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Amenity details fetched successfully",
  "data": {
    "id": 1,
    "society_id": 1,
    "name": "Swimming Pool",
    "description": "Olympic size swimming pool.",
    "image": null,
    "status": "active",
    "booking_mode": "automatic",
    "opening_time": "06:00",
    "closing_time": "21:00",
    "available_days": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    "slot_duration_minutes": 60,
    "max_booking_duration_minutes": 120,
    "advance_booking_days": 7,
    "fee": 0.00
  }
}
```

---

### 2.3 Check Slot Availability
- **Endpoint**: `GET /api/v1/amenities/{id}/availability?date=YYYY-MM-DD`
- **Purpose**: Get real-time time slot availability on a specific date.
- **Auth**: `auth:sanctum`
- **Path Parameter**: `id` (integer) — Amenity ID
- **Query Parameter**: `date` (string, `YYYY-MM-DD`, required)

#### Example Request
```bash
curl -X GET "https://your-domain.com/api/v1/amenities/1/availability?date=2026-11-01" \
  -H "Authorization: Bearer 1|abc123xyz" \
  -H "Accept: application/json"
```

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Slot availability fetched successfully",
  "data": {
    "date": "2026-11-01",
    "is_available_date": true,
    "amenity_name": "Swimming Pool",
    "booking_mode": "automatic",
    "fee": 0.0,
    "slots": [
      {
        "start_time": "06:00",
        "end_time": "07:00",
        "is_available": true,
        "status": "available",
        "reason": null
      },
      {
        "start_time": "07:00",
        "end_time": "08:00",
        "is_available": false,
        "status": "confirmed",
        "reason": "Already booked"
      }
    ]
  }
}
```

---

### 2.4 Create Amenity Booking
- **Endpoint**: `POST /api/v1/amenity-bookings`
- **Purpose**: Submit a booking for a time slot. Uses database row-locking (`lockForUpdate`) to prevent concurrency conflicts.
- **Auth**: `auth:sanctum`
- **Request Body**:
```json
{
  "amenity_id": 1,
  "booking_date": "2026-11-01",
  "start_time": "08:00",
  "end_time": "09:00",
  "admin_notes": "Family leisure"
}
```

#### Example Request
```bash
curl -X POST "https://your-domain.com/api/v1/amenity-bookings" \
  -H "Authorization: Bearer 1|abc123xyz" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"amenity_id":1,"booking_date":"2026-11-01","start_time":"08:00","end_time":"09:00","admin_notes":"Family swimming"}'
```

#### Example Response (`201 Created`)
```json
{
  "success": true,
  "status": true,
  "message": "Booking confirmed successfully!",
  "data": {
    "id": 105,
    "amenity_id": 1,
    "amenity_name": "Swimming Pool",
    "booking_date": "2026-11-01",
    "start_time": "08:00",
    "end_time": "09:00",
    "status": "confirmed",
    "fee": 0.0,
    "created_at": "2026-10-15 17:15:00"
  }
}
```

#### Conflict Error (`409 Conflict`)
```json
{
  "success": false,
  "status": false,
  "message": "The selected time slot is unavailable or already booked. Please choose another time.",
  "errors": {
    "slot": [
      "The selected time slot is unavailable or already booked. Please choose another time."
    ]
  }
}
```

---

### 2.5 My Bookings History
- **Endpoint**: `GET /api/v1/amenity-bookings`
- **Purpose**: List bookings created by the authenticated resident.
- **Auth**: `auth:sanctum`
- **Query Parameter (Optional)**: `status` (`pending`, `confirmed`, `rejected`, `cancelled`)

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Bookings fetched successfully",
  "data": [
    {
      "id": 105,
      "amenity_id": 1,
      "amenity_name": "Swimming Pool",
      "booking_date": "2026-11-01",
      "start_time": "08:00",
      "end_time": "09:00",
      "status": "confirmed",
      "rejection_reason": null,
      "can_cancel": true,
      "created_at": "2026-10-15 17:15:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### 2.6 Cancel Booking
- **Endpoint**: `POST /api/v1/amenity-bookings/{id}/cancel`
- **Purpose**: Cancel a booking if permitted.
- **Auth**: `auth:sanctum`
- **Request Body (Optional)**:
```json
{
  "reason": "Change of plans"
}
```

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Booking cancelled successfully.",
  "data": {
    "id": 105,
    "status": "cancelled",
    "cancelled_at": "2026-10-15 17:20:00"
  }
}
```

---

## 3. Poll & Voting APIs

### 3.1 List Polls
- **Endpoint**: `GET /api/v1/polls` (or `GET /api/polls`)
- **Purpose**: List active or past polls for the authenticated resident's society.
- **Auth**: `auth:sanctum`
- **Query Parameter (Optional)**: `status` (`active` [default], `closed`)

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Polls fetched successfully",
  "data": [
    {
      "id": 2,
      "title": "Install EV Charging Stations in Society?",
      "description": "Proposed installation of 10 Level-2 fast chargers in Basement B1.",
      "start_date": "2026-10-15 10:00:00",
      "end_date": "2026-10-22 23:59:59",
      "status": "active",
      "is_open": true,
      "results_visibility": "after_vote",
      "has_voted": false,
      "user_selected_option_id": null,
      "can_view_results": false,
      "options": [
        {
          "id": 5,
          "option_text": "Strongly Support"
        },
        {
          "id": 6,
          "option_text": "Neutral"
        },
        {
          "id": 7,
          "option_text": "Oppose"
        }
      ],
      "results": null
    }
  ]
}
```

---

### 3.2 Cast a Vote
- **Endpoint**: `POST /api/v1/polls/{id}/vote`
- **Purpose**: Cast a single vote for an answer option. Protected by `UNIQUE(poll_id, user_id)` constraint and ownership validation.
- **Auth**: `auth:sanctum`
- **Path Parameter**: `id` (integer) — Poll ID
- **Request Body**:
```json
{
  "poll_option_id": 5
}
```

#### Example Request
```bash
curl -X POST "https://your-domain.com/api/v1/polls/2/vote" \
  -H "Authorization: Bearer 1|abc123xyz" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"poll_option_id": 5}'
```

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Your vote has been submitted successfully!",
  "data": {
    "poll_id": 2,
    "selected_option_id": 5,
    "voted_at": "2026-10-15 17:25:00",
    "results": {
      "total_votes": 1,
      "is_tie": false,
      "winner_count": 1,
      "options": [
        {
          "id": 5,
          "option_text": "Strongly Support",
          "votes_count": 1,
          "percentage": 100.0,
          "is_winner": true
        },
        {
          "id": 6,
          "option_text": "Neutral",
          "votes_count": 0,
          "percentage": 0.0,
          "is_winner": false
        },
        {
          "id": 7,
          "option_text": "Oppose",
          "votes_count": 0,
          "percentage": 0.0,
          "is_winner": false
        }
      ]
    }
  }
}
```

#### Duplicate Vote Error (`409 Conflict`)
```json
{
  "success": false,
  "status": false,
  "message": "You have already cast your vote in this poll."
}
```

---

### 3.3 Get Poll Results
- **Endpoint**: `GET /api/v1/polls/{id}/results`
- **Purpose**: Get live vote counts, percentages, and tied/winner flags (if allowed by visibility policy).
- **Auth**: `auth:sanctum`

#### Example Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Poll results calculated successfully",
  "data": {
    "poll_id": 2,
    "title": "Install EV Charging Stations in Society?",
    "status": "active",
    "results": {
      "total_votes": 45,
      "is_tie": false,
      "winner_count": 1,
      "options": [
        {
          "id": 5,
          "option_text": "Strongly Support",
          "votes_count": 35,
          "percentage": 77.8,
          "is_winner": true
        },
        {
          "id": 6,
          "option_text": "Neutral",
          "votes_count": 6,
          "percentage": 13.3,
          "is_winner": false
        },
        {
          "id": 7,
          "option_text": "Oppose",
          "votes_count": 4,
          "percentage": 8.9,
          "is_winner": false
        }
      ]
    }
  }
}
```

#### Unauthorized Results Access (`403 Forbidden`)
```json
{
  "success": false,
  "status": false,
  "message": "Results are not visible yet according to poll settings. Please cast your vote or wait until the poll closes."
}
```

---

### 3.4 My Participating Polls
- **Endpoint**: `GET /api/v1/my-polls`
- **Purpose**: List polls where the resident has cast their vote.
- **Auth**: `auth:sanctum`

---

## 4. Admin Management APIs

### 4.1 Approve Amenity Booking
- **Endpoint**: `POST /api/v1/admin/amenity-bookings/{id}/approve`
- **Auth**: `auth:sanctum` (Super Admin or `amenity_booking_approve` permission)

### 4.2 Reject Amenity Booking
- **Endpoint**: `POST /api/v1/admin/amenity-bookings/{id}/reject`
- **Auth**: `auth:sanctum` (Super Admin or `amenity_booking_approve` permission)
- **Request Body**:
```json
{
  "reason": "Clubhouse is reserved for scheduled maintenance on that day."
}
```
