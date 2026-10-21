# Legacy Carry - Complete System Architecture & Lifecycle Guide

## 1. System Vision & Overview
**Legacy Carry** is a comprehensive, production-grade enterprise backend platform built with **Laravel 12**. It seamlessly unites two critical domains:
1. **Gated Society & Gate Security Management**: Resident directories, visitor passes, vehicle logs, society notices, amenity slot bookings, community polls, and helpdesk support tickets.
2. **On-Demand Labor & Service Marketplace with Escrow**: Job posting, worker bidding, secure escrow-based advance/final payments via Cashfree, automated gate pass generation, and real-time FCM mobile alerts.

---

## 2. Actor Roles & Permissions Matrix

| Actor / Role | Primary Responsibilities | Key Capabilities |
| :--- | :--- | :--- |
| **Super Admin** | Platform operations, society approvals, system logs, finance & escrow | Manages societies, global users, payout approvals, settings, system audits |
| **Resident (Customer)** | Apartment owner / tenant | Posts jobs, hires workers, books amenities, votes in polls, creates helpdesk tickets, approves visitor passes |
| **Labor (Worker)** | Skilled / daily-wage service provider | Applies for jobs, tracks timer/work duration, uses entry/exit QR passes at the gate, tracks earnings & requests payouts |
| **Guard** | Security gate checkpoint officer | Scans QR passes for laborers, visitors, and vehicles; verifies advance/exit clearance; logs entry/exit timestamps |
| **Resident Admin** | Elected society management committee member | Moderates society notices, approves amenity bookings, creates community polls, resolves tickets |

---

## 3. Start-to-End Core Workflows (Lifecycle)

### Workflow A: On-Demand Service Hiring with Gate Escrow

```
[Resident: Sukh]
       │
       ▼ (1) Creates Job Post ("Fan Repair")
[Worker: John]
       │
       ▼ (2) Applies for Job (JobApplication)
[Resident: Sukh]
       │
       ▼ (3) Accepts Application & Pays 20% Advance via Cashfree
[Platform Escrow]
       │ ── Cashfree Escrow locks Advance
       │ ── System creates Appointment & generates secure UUID `entry_token`
       │ ── Worker John receives FCM Push Notification ("You are hired!")
       ▼
[Gate Checkpoint]
       │
       ▼ (4) John arrives at Society Gate & presents `entry_token` QR code
[Guard: Gate]
       │
       ▼ (5) Guard scans `entry_token` (`GuardController::approveEntry`)
             ── System validates Advance payment status
             ── Logs `guard_in_time` & logs scan history
             ── Push Notification sent to Resident ("Worker John has arrived at gate")
             ── Push Notification sent to Worker ("Start your job timer")
       ▼
[Job Execution]
       │
       ▼ (6) Worker fixes fan; Timer tracked; Worker submits job completion
[Resident Confirmation & Settlement]
       │
       ▼ (7) Sukh verifies work satisfaction & pays remaining 80% Final Payment
             ── Cashfree processes final balance to Worker's wallet
             ── System unlocks secure UUID `exit_token`
             ── Worker notified ("Payment received & Exit Pass ready")
       ▼
[Gate Exit]
       │
       ▼ (8) John presents `exit_token` QR code to Guard
[Guard: Gate]
       │
       ▼ (9) Guard scans `exit_token` (`GuardController::approveExit`)
             ── Logs `guard_out_time`
             ── Appointment status marked "Completed"
             ── Worker departs; Notification sent ("Job completed successfully")
```

---

### Workflow B: Society Amenities & Community Management

```
[Resident] ──> Books Amenity (Clubhouse / Gym / Tennis Court)
                  │
                  ▼
         [Slot Conflict Validation]
                  │
                  ├─ If requires approval: [Resident Admin / Admin Review] ──> Approved / Rejected
                  └─ If instant: Confirmed immediately
                  │
                  ▼
         FCM Notification sent to Resident & Calendar sync
```

---

### Workflow C: Gate Visitor & Vehicle Management

```
[Visitor / Delivery] ──> Society Gate Checkpoint
                               │
                               ▼
                       Guard logs details (`PassController` / `VehicleController`)
                               │
                               ▼
                Resident receives instant In-App notification
                               │
                               ▼
                Resident grants entry approval -> Guard opens boom barrier
```

---

## 4. Database Entities & Relationships

| Entity / Model | Database Table | Purpose |
| :--- | :--- | :--- |
| `User` | `users` | Unified authentication, roles (`super_admin`, `resident`, `guard`, `labor`, `employee`) |
| `Society` | `societies` | Master society entity (name, address, city, state, pincode) |
| `Flat` | `flats` | Residential units inside a society |
| `ResidentAssignment`| `resident_assignments` | Maps users to their specific society flat and residential tenure |
| `JobPost` | `job_posts` | Service requests created by residents |
| `JobApplication` | `job_applications` | Worker applications for posted jobs with quotes and status |
| `Appointment` | `appointments` | Gate pass contract holding `entry_token`, `exit_token`, timer, and guard timestamps |
| `Payment` | `payments` | Records Cashfree transactions (advance escrow, final payment, fees) |
| `Withdrawal` | `withdrawals` | Payout requests made by laborers to withdraw earnings to bank/UPI |
| `Amenity` | `amenities` | Society facilities available for resident booking |
| `AmenityBooking` | `amenity_bookings` | Booking slots with state management (`pending`, `confirmed`, `rejected`, `cancelled`) |
| `Poll` & `PollVote` | `polls`, `poll_votes` | Community voting with single/multi-choice and live results |
| `Ticket` & `TicketReply`| `tickets`, `ticket_replies`| Resident complaints, maintenance requests, and admin resolutions |
| `SocietyNotice` | `society_notices` | Announcements targeted to full society or specific flats |
| `Pass` & `VehicleLog` | `passes`, `vehicle_logs` | Real-time visitor and vehicle tracking logs at security checkpoints |

---

## 5. Technology Stack & Key Integrations

- **Framework:** Laravel 12.x on PHP 8.2+
- **Payment Gateway:** Cashfree Payment Gateway (Checkout SDK & Payouts API)
- **Push Notifications:** Firebase Cloud Messaging (FCM via `kreait/laravel-firebase` & `PushNotificationService`)
- **Real-Time Data / Chat:** Google Cloud Firestore & Firebase Realtime Database
- **Reporting & Exports:** Laravel Excel (`maatwebsite/excel`) for administrative CSV/XLSX reports
- **Security:** Laravel Sanctum Token Authentication, Granular Role-Based Permission System (`has_permission`), CSRF & Safe Request Filters
