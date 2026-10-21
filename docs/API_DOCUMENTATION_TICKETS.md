# Society Management — Resident Ticket & Complaint System API Documentation

## Overview

The Ticket / Complaint Management system allows residents to report any issues (e.g., Plumbing, Electrical, Maintenance, Security, Housekeeping, Noise, Parking, etc.) from the website or mobile app.

### Multi-Tenant Automatic Society Scoping
- **Zero Trust Society ID**: The API does **not** rely on `society_id` passed by the client.
- When a resident calls `POST /api/v1/tickets`, the backend automatically resolves their society membership (`user_society_id` / resident assignment) and binds the ticket to that society.
- When a resident admin queries `GET /api/v1/admin/tickets`, the backend automatically filters the results so **only tickets belonging to that admin's society are returned**. Admins cannot view or modify tickets from other societies.
- Super Admins can optionally pass `?society_id=X` to view complaints of a specific society or view across all societies.

---

## Authentication & Headers

All endpoints require a Bearer token via Laravel Sanctum:

```http
Authorization: Bearer <sanctum_token>
Accept: application/json
Content-Type: application/json
```

---

## Summary of Endpoints

### Resident APIs

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/tickets` | List logged-in resident's tickets (supports `?status=` and `?category=`) |
| `POST` | `/api/v1/tickets` | Raise a new complaint ticket with optional photo/doc attachment |
| `GET` | `/api/v1/tickets/{id}` | View ticket details and communication thread |
| `POST` | `/api/v1/tickets/{id}/reply` | Post a reply or additional info to the ticket thread |
| `POST` | `/api/v1/tickets/{id}/close` | Mark ticket as resolved/closed by the resident |

### Society Admin APIs

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/admin/tickets` | View all tickets strictly belonging to the admin's society |
| `PATCH` | `/api/v1/admin/tickets/{id}/status` | Update ticket status (`open`, `in_progress`, `resolved`, `closed`) and add resolution notes |
| `POST` | `/api/v1/admin/tickets/{id}/assign` | Assign ticket to a staff member or resolver |

*(Note: `/api/tickets...` unversioned aliases are also available).*

---

## Detailed Endpoint Reference

### 1. Create Ticket (Resident)

Creates a new ticket and automatically binds it to the resident's registered society.

- **URL:** `POST /api/v1/tickets`
- **Content-Type:** `multipart/form-data` (if uploading file) or `application/json`

#### Request Payload:
```json
{
  "category": "Plumbing",
  "title": "Severe water leakage in bathroom ceiling",
  "description": "Water has been continuously dripping from the ceiling since morning. Needs immediate plumber visit.",
  "priority": "high",
  "attachment": "(optional binary file: jpg, png, webp, pdf, max 5MB)"
}
```

#### Fields:
- `category` (string, required): `Plumbing`, `Electrical`, `Maintenance`, `Security`, `Cleanliness`, `Noise`, `Parking`, `Other`.
- `title` (string, required, max 255): Summary/subject of the issue.
- `description` (string, required): Detailed problem description.
- `priority` (string, optional): `low`, `medium` (default), `high`, `urgent`.
- `attachment` (file, optional): Photo of the issue or document.

#### Response (201 Created):
```json
{
  "success": true,
  "status": true,
  "message": "Ticket #TKT-20261015-X9Y2 created successfully.",
  "data": {
    "id": 12,
    "ticket_number": "TKT-20261015-X9Y2",
    "society_id": 4,
    "category": "Plumbing",
    "title": "Severe water leakage in bathroom ceiling",
    "description": "Water has been continuously dripping from the ceiling since morning. Needs immediate plumber visit.",
    "priority": "high",
    "status": "open",
    "attachment_url": "https://example.com/storage/tickets/abc123.jpg",
    "created_at": "2026-10-15 15:30:00"
  }
}
```

---

### 2. List Resident's Tickets

- **URL:** `GET /api/v1/tickets?status=open&category=Plumbing`

#### Query Parameters:
- `status` (optional): `open`, `in_progress`, `resolved`, `closed`
- `category` (optional): Filter by category name
- `page` (optional): Pagination page number

#### Response (200 OK):
```json
{
  "success": true,
  "status": true,
  "message": "Tickets fetched successfully",
  "data": [
    {
      "id": 12,
      "ticket_number": "TKT-20261015-X9Y2",
      "category": "Plumbing",
      "title": "Severe water leakage in bathroom ceiling",
      "priority": "high",
      "status": "open",
      "attachment_url": "https://example.com/storage/tickets/abc123.jpg",
      "resolved_at": null,
      "created_at": "2026-10-15 15:30:00"
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

### 3. Get Ticket Details & Communication Thread

- **URL:** `GET /api/v1/tickets/{id}`

#### Response (200 OK):
```json
{
  "success": true,
  "status": true,
  "message": "Ticket details fetched successfully",
  "data": {
    "id": 12,
    "ticket_number": "TKT-20261015-X9Y2",
    "society": {
      "id": 4,
      "name": "Green Valley Heights"
    },
    "resident": {
      "id": 45,
      "name": "Rahul Verma",
      "phone": "+91 9876543210"
    },
    "assigned_to": {
      "id": 18,
      "name": "Ramesh (Plumber / Maintenance)"
    },
    "category": "Plumbing",
    "title": "Severe water leakage in bathroom ceiling",
    "description": "Water has been continuously dripping from the ceiling since morning. Needs immediate plumber visit.",
    "priority": "high",
    "status": "in_progress",
    "attachment_url": "https://example.com/storage/tickets/abc123.jpg",
    "resolution_notes": null,
    "resolved_at": null,
    "closed_at": null,
    "created_at": "2026-10-15 15:30:00",
    "replies": [
      {
        "id": 1,
        "user_id": 18,
        "user_name": "Ramesh (Plumber / Maintenance)",
        "user_role": "admin",
        "message": "We have received your ticket. A technician will visit flat today at 4:30 PM.",
        "attachment_url": null,
        "created_at": "2026-10-15 15:45:00"
      }
    ]
  }
}
```

---

### 4. Reply to Ticket Thread (Resident / Admin)

- **URL:** `POST /api/v1/tickets/{id}/reply`
- **Content-Type:** `multipart/form-data` or `application/json`

#### Request Payload:
```json
{
  "message": "Thank you, I will be available at 4:30 PM.",
  "attachment": "(optional file)"
}
```

#### Response (201 Created):
```json
{
  "success": true,
  "status": true,
  "message": "Reply posted successfully.",
  "data": {
    "id": 2,
    "ticket_id": 12,
    "message": "Thank you, I will be available at 4:30 PM.",
    "attachment_url": null,
    "created_at": "2026-10-15 15:50:00"
  }
}
```

---

### 5. Close Ticket (Resident)

- **URL:** `POST /api/v1/tickets/{id}/close`

#### Response (200 OK):
```json
{
  "success": true,
  "status": true,
  "message": "Ticket closed successfully.",
  "data": {
    "id": 12,
    "status": "closed"
  }
}
```

---

### 6. Admin: List Society Tickets (Automatic Society Scoping)

Resident admin automatically views only tickets for their society.

- **URL:** `GET /api/v1/admin/tickets?status=open&priority=urgent`

#### Query Parameters:
- `status` (optional): `open`, `in_progress`, `resolved`, `closed`
- `priority` (optional): `low`, `medium`, `high`, `urgent`
- `category` (optional): Filter by category
- `page` (optional): Page number

#### Response (200 OK):
```json
{
  "success": true,
  "status": true,
  "message": "Society tickets fetched successfully",
  "data": [
    {
      "id": 12,
      "ticket_number": "TKT-20261015-X9Y2",
      "society_id": 4,
      "society_name": "Green Valley Heights",
      "resident_id": 45,
      "resident_name": "Rahul Verma",
      "resident_phone": "+91 9876543210",
      "category": "Plumbing",
      "title": "Severe water leakage in bathroom ceiling",
      "priority": "high",
      "status": "in_progress",
      "attachment_url": "https://example.com/storage/tickets/abc123.jpg",
      "created_at": "2026-10-15 15:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

### 7. Admin: Update Ticket Status & Resolution Notes

- **URL:** `PATCH /api/v1/admin/tickets/{id}/status`

#### Request Payload:
```json
{
  "status": "resolved",
  "resolution_notes": "Plumber replaced the faulty overhead gasket. Leakage resolved and tested."
}
```

#### Response (200 OK):
```json
{
  "success": true,
  "status": true,
  "message": "Ticket status updated to resolved.",
  "data": {
    "id": 12,
    "status": "resolved",
    "resolution_notes": "Plumber replaced the faulty overhead gasket. Leakage resolved and tested.",
    "resolved_at": "2026-10-15 17:00:00"
  }
}
```

---

### 8. Admin: Assign Ticket to Staff

- **URL:** `POST /api/v1/admin/tickets/{id}/assign`

#### Request Payload:
```json
{
  "assigned_to": 18
}
```

#### Response (200 OK):
```json
{
  "success": true,
  "status": true,
  "message": "Ticket assigned successfully."
}
```
