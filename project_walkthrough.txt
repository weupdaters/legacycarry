# Legacy Carry - Project Brief & Walkthrough

## 1. Project Overview & Context
**Legacy Carry** is a comprehensive Backend API built with Laravel. It serves as a dual-purpose platform combining **Society/Gate Management** (similar to MyGate) and an **On-Demand Service Provider Marketplace**. 

The system allows residents of a society to hire daily wage workers (laborers/service providers) for specific jobs, securely manage their entry and exit through society guards, track their working hours, and process digital payments.

### 1.1 Key User Roles
- **Customer (Resident):** Lives in the society. Can post jobs, hire workers, approve job completion, and make payments (advance & final).
- **Labor (Service Provider/Worker):** Applies for jobs, receives entry/exit tokens for the society gate, starts/ends job timers, and receives earnings.
- **Guard:** Stationed at the society gate. Scans QR codes (entry/exit tokens) to securely allow and log the movement of visitors, vehicles, and hired workers.
- **Admin:** Platform owner who oversees operations and holds advance payments in escrow until the final payment is settled.

---

## 2. Core Modules & Connections (The "Inside Connection")

### 2.1 Jobs & Applications (`JobApplicationController`)
- **Flow:** Resident posts a `JobPost`. Workers browse and apply, creating a `JobApplication`.
- **Hiring:** When a resident changes the application status to `hired`, the system automatically generates an `Appointment` for the worker to enter the society. 
- **Notifications:** The worker instantly receives an FCM push notification that they have been hired.

### 2.2 Appointments & Access Tokens (`AppointmentController`)
- **Flow:** An `Appointment` acts as a digital gate pass for the hired worker.
- **Entry Token:** Once the appointment is approved, an `entry_token` (UUID) is generated.
- **Job Timer:** Workers/Residents can trigger `start()` and `end()` for the job to track how much time was spent.
- **Exit Token:** Once the job is completed and final payment is confirmed by the resident, an `exit_token` is generated so the worker can leave the society.

### 2.3 Gate Security & Logging (`GuardController`)
- **Flow:** Guards use the Guard API to secure the premises. 
- **Entry/Exit Approval:** Guards scan the worker's `entry_token` or `exit_token`. The system checks if the token is valid, ensures advance/final payments are made, and logs the `guard_in_time` and `guard_out_time`.
- **Visitor & Vehicle Management:** Guards can also log standard visitors (`Pass` model) and random vehicles (`VehicleLog` model).
- **History & Auditing:** The `allLogHistory` API unifies visitors, workers, and vehicles into a single timeline feed for the society, complete with resident contact info and FCM tokens for easy communication.
- **Notifications:** Scanning an entry token tells the worker to "Start Job". Scanning an exit token confirms "Job Completed".

### 2.4 Payments & Escrow (`PaymentController`)
- **Integration:** Powered by **Cashfree** payment gateway.
- **Advance Payment:** Before a worker can enter, the resident pays an advance amount. This money is routed to the `admin` account (escrow). The worker is notified.
- **Final Payment:** After the job ends, the resident pays the remaining balance. This is routed to the `employer` (worker) account, and the exit pass is unlocked. The worker is notified.
- **Earnings Tracking:** Workers can track their pending payouts (advance) and current balance (final) through the `laborEarnings` API.

### 2.5 Push Notifications (`NotificationController` & `Notify` Helper)
- **Flow:** Uses **Firebase Cloud Messaging (FCM)**.
- **Connections:** Integrated deeply across all controllers. Any major state change triggers a real-time alert to the relevant party's mobile device:
  - Hired for a job -> Worker Notified
  - Advance/Final Payment made -> Worker Notified
  - Entry gate scanned -> Resident Notified (Worker Entered) & Worker Notified (Start Job)
  - Exit gate scanned -> Worker Notified (Job Completed)

---

## 3. End-to-End Walkthrough (Use Case)

**Step 1: The Job Post**
Sukh (Resident) needs a ceiling fan repaired. He creates a Job Post in his society.

**Step 2: The Application**
John (Worker) sees the job and applies. 

**Step 3: The Hire & Advance Payment**
Sukh reviews John's profile and hires him. Sukh pays a 20% advance via Cashfree. 
- *Connection:* `JobApplication` status becomes 'hired' -> `Appointment` is auto-created -> FCM Notification sent to John.

**Step 4: The Entry**
John arrives at the society gate. He shows his `entry_token` QR code to the Guard.
- *Connection:* Guard scans code -> `GuardController::approveEntry` runs -> Validates advance is paid -> Logs entry time -> FCM Notification sent to Sukh ("Worker arrived") and John ("Start Job").

**Step 5: The Job Execution**
John reaches Sukh's house, and the job timer is started. Once the fan is fixed, the job is marked complete.

**Step 6: The Final Payment & Exit Pass**
Sukh pays the remaining 80% final payment.
- *Connection:* `PaymentController::payFinal` updates payment status -> FCM Notification sent to John -> Sukh confirms job completion -> `exit_token` is generated for John.

**Step 7: The Exit**
John goes back to the gate and shows his `exit_token`.
- *Connection:* Guard scans code -> `GuardController::approveExit` runs -> Logs exit time -> FCM Notification sent to John ("Job Completed"). The cycle is complete.
