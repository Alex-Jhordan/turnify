# 04. Software Requirements Specification (SRS)

## 1. Scope and Universal Business Rules

The system must manage the end-to-end physical consultation lifecycle during the event. This includes issuing tickets, calling attendees via public screens with Text-to-Speech audio, enforcing strict 30-minute session timers per module, and capturing operational analytics.

### 1.1 Universal Business Rules (BR)

* **`BR-01` Priority Escalation:** Tickets marked with preferential care (`is_priority = true`) must be prioritized by the allocation algorithm over standard pending tickets (`status = pending`), maintaining arrival order (`created_at ASC`) within the priority group.
* **`BR-02` Session Duration Limit:** Standard duration per physical consultation is strictly capped at 30 minutes.
* **`BR-03` Single Active Ticket Limit:** A document number (`document_number`) cannot hold more than one active ticket simultaneously (`status` in `pending`, `calling`, `in_progress`).

---

## 2. Functional Requirements (FR) by Module

### 2.1 Module 1: Ticket Issuance & Kiosks (`kiosk`)

* **`FR-01` Attendee Identification Input:** Provide a touch-optimized UI for attendees to select document type (`document_type`: DNI, Passport, CE) and input their document number (`document_number`).
* **`FR-02` Identity API Integration:** Perform a synchronous query to an external identity API upon document entry to auto-populate full name (`name`). If the API fails or the document is not found, fall back gracefully to manual input.
* **`FR-03` Category Selection & Priority Flag:** Display active categories exclusively (`is_active = true`) and allow toggling a preferential status checkbox (`is_priority`) before confirming.
* **`FR-04` Kiosk Idempotency Enforcement:** Generate a unique `idempotency_key` per kiosk session to prevent duplicate ticket records caused by double-tapping the touch screen.
* **`FR-05` Ticket Output & Silent Printing:** Display the generated ticket code (e.g., `FIN-001` or `P-FIN-001` for priority) on screen upon record creation and trigger local thermal printing via browser directives.

### 2.2 Module 2: Consultation & Advisor Panel (`advisor`)

* **`FR-06` Advisor Login & Station Pairing:** Authenticate advisors and allow them to pair with an active physical station (`module_number` from 1 to 50) for their active session.
* **`FR-07` Call Next Ticket Assignment:** Execute "Call Next" using a database transaction with pessimistic locking (`FOR UPDATE`) to assign the oldest queued ticket matching the module's mapped categories.
* **`FR-08` Ticket Recall (RECALL):** Allow re-calling a ticket currently in `calling` state. This increments `call_count` and re-dispatches the notification to the public screen without altering the original `called_at` timestamp.
* **`FR-09` Session Start & Countdown Timer:** Allow transitioning ticket status from `calling` to `in_progress` upon attendee arrival. This triggers a visual 30-minute countdown timer in the advisor dashboard.
* **`FR-10` No-Show Handling & UI Protection:** Provide a "Mark No-Show" action (`status = no_show`). Temporarily disable this button in the UI for 20 seconds following any call or recall action to prevent accidental closures before audio broadcast completes.
* **`FR-11` Session Completion & Recess Pause:** Allow completing a session (`status = completed`, recording `ended_at`). Provide a "Recess / Break" action that unassigns the advisor (`current_user_id = NULL`) without deactivating the physical module record.

### 2.3 Module 3: Public Display & Call Board (`display`)

* **`FR-12` Real-Time Event Subscription:** Listen to the local Laravel Reverb WebSocket channel (`displays-channel`) to process real-time call broadcasts triggered by advisor modules.
* **`FR-13` Display Layout Partitioning:** Structure the public TV interface into two primary regions: Main Highlight Area (70% viewport) for the current active announcement, and Side Queue Grid (30% viewport) listing recent tickets in `calling` state.
* **`FR-14` Audio Playback Queue & Synthesis:** Manage a client-side FIFO queue where each event occupies the Main Highlight Area for 20 seconds while playing Web Speech API (Text-to-Speech) audio: `"Turno [Ticket Code], acérquese al Módulo [Module Number]"`.
* **`FR-15` Dynamic Display Removal:** Remove tickets automatically from the public display grid upon receiving a status change event to `in_progress`, `no_show`, or `cancelled`.

### 2.4 Module 4: System Administration & Rebalancing (`administrator` - Filament)

* **`FR-16` User & Role Management:** Manage user credentials and permissions using Filament Shield (`ADMINISTRATOR`, `ADVISOR` roles) with soft deletion (`deleted_at`) enabled for audit compliance.
* **`FR-17` Dynamic Infrastructure Rebalancing:** Enable administrators to toggle module status (`is_active`) and reassign M:N category relationships (`category_module`) in real time to adapt to category demand spikes.
* **`FR-18` Administrative Ticket Cancellation:** Provide administrative overrides to manually cancel tickets (`status = cancelled`, updating `cancelled_at`) in cases of registration errors or early user departures.

### 2.5 Module 5: Analytics, Reporting & Audit

* **`FR-19` Real-Time Operational Dashboard:** Display live KPIs: total tickets issued, completed sessions, average wait time, average session duration, and ticket volume breakdown by category.
* **`FR-20` Data Export Capabilities:** Export ticket records and operational performance metrics to Excel (`.xlsx`) and PDF formats with date range, category, and module filters.
* **`FR-21` Activity Logging:** Automatically record administrative and configuration changes to a dedicated `activity_log` table, tracking user ID, timestamp, and modified values.

---

## 3. Non-Functional Requirements (NFR)

### 3.1 Performance and Response Times

* **`NFR-01` Local Broadcast Latency:** WebSocket delivery latency from server dispatch (Laravel Reverb) to public display rendering must remain under 100 milliseconds over the local network (LAN).
* **`NFR-02` Peak Concurrency Throughput:** The web server and database stack must handle peak concurrent bursts of up to 50 calls per second without dirty reads or duplicate ticket assignments.

### 3.2 Usability and UI Constraints

* **`NFR-03` Touch Screen Optimization:** Kiosk interfaces must feature a touch-first design with touch targets of at least 48x48 pixels and optimized numeric keypads to avoid physical keyboards.
* **`NFR-04` Long-Distance Screen Readability:** Public display boards must utilize high-contrast themes and font sizing readable from a minimum distance of 15 meters.

### 3.3 Reliability and Autonomy

* **`NFR-05` Isolated Network Autonomy:** The system must operate fully offline within an isolated Local Area Network (LAN), maintaining all ticket issuance, queue logic, display calls, and database operations without public internet access.