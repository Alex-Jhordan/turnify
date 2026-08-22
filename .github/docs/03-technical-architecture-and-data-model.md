# 03. Technical Architecture and Data Model

## 1. Technology Stack and Software Infrastructure

The application follows a Modular Monolith architecture built on Laravel, consolidating business logic, administrative management, and real-time broadcasting into a single codebase.

* **Backend Framework:** Laravel v13.x (PHP 8.5)
* **Admin Panel & CRUDs:** Filament 5.x
* **Access Control & Roles:** Filament Shield (`spatie/laravel-permission`)
* **Frontend Reactivity:** Livewire v4.x / Alpine.js
* **Web Server & Database:** Laragon (Apache 2.4, PHP 8.5, MySQL 8.4)
* **WebSocket Engine (Real-Time):** Laravel Reverb on local network (Port 6001)
* **Administrative Audit Logging:** `spatie/laravel-activitylog`
* **Styling & Layouts:** Tailwind CSS v4.3

---

## 2. Database Model (ERD) and Table Definitions

Naming conventions strictly enforce standard Laravel database guidelines: lower `snake_case` for table/column names and upper `PascalCase` for ENUM/backed enum states. String status values are stored as `VARCHAR` in MySQL for flexibility and validated via PHP Backed Enums in the source code. SoftDeletes (`deleted_at`) are being implemented in the main models (users) to preserve the integrity of the analytical history in the event of logical deletions.

### 2.1 Table Schemas

#### Table: `users`
Stores system access accounts for administrators and advisors. Roles and permissions are managed externally via Filament Shield tables (`roles`, `model_has_roles`, etc.).

| Field | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier. |
| `name` | varchar(255) | NOT NULL | Full user name. |
| `email` | varchar(255) | UNIQUE, NOT NULL | Email address / authentication login. |
| `password` | varchar(255) | NOT NULL | Encrypted password (Bcrypt). |
| `is_active` | boolean | DEFAULT true | Account active status. |
| `created_at` | timestamp | NULLABLE | Creation timestamp. |
| `updated_at` | timestamp | NULLABLE | Last update timestamp. |
| `deleted_at` | timestamp | NULLABLE | Soft delete timestamp. |

#### Table: `categories`
Represents advisory domains or specialties available during the event.

| Field | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | bigint | PRIMARY KEY, AUTO_INCREMENT | Category identifier. |
| `name` | varchar(100) | NOT NULL | Display name (e.g., Financiera). |
| `prefix` | varchar(10) | NOT NULL | Ticket prefix code (e.g., FIN). |
| `is_active` | boolean | DEFAULT true | Availability status on kiosks. |
| `created_at` | timestamp | NULLABLE | Creation timestamp. |
| `updated_at` | timestamp | NULLABLE | Last update timestamp. |

#### Table: `modules`
Represents the 50 physical consultation points located at the venue.

| Field | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | bigint | PRIMARY KEY, AUTO_INCREMENT | Module identifier. |
| `module_number` | int | UNIQUE, NOT NULL | Physical visible module number (1 to 50). |
| `is_active` | boolean | DEFAULT true | Operational availability flag. |
| `current_user_id` | bigint | FOREIGN KEY (`users.id`), NULLABLE | Advisor currently occupying the module. |
| `created_at` | timestamp | NULLABLE | Creation timestamp. |
| `updated_at` | timestamp | NULLABLE | Last update timestamp. |

#### Table: `category_module` (Pivot Table)
Many-to-Many dynamic mapping defining permitted categories per module.

| Field | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `module_id` | bigint | FOREIGN KEY (`modules.id`) ON DELETE CASCADE | Associated module ID. |
| `category_id` | bigint | FOREIGN KEY (`categories.id`) ON DELETE CASCADE | Permitted category ID. |

#### Table: `tickets`
Central core table tracking the lifecycle of each issued ticket.

| Field | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | bigint | PRIMARY KEY, AUTO_INCREMENT | Ticket identifier. |
| `code` | varchar(20) | NOT NULL | Public printed code (e.g., `FIN-00001`, `P-FIN-00002`). |
| `category_id` | bigint | FOREIGN KEY (`categories.id`) | Requested category ID. |
| `module_id` | bigint | FOREIGN KEY (`modules.id`), NULLABLE | Assigned module ID when called. |
| `user_id` | bigint | FOREIGN KEY (`users.id`), NULLABLE | Assigned advisor ID. |
| `document_type` | varchar(20) | DEFAULT 'DNI' | Identity document type (DNI, Passport, CE). |
| `document_number` | varchar(30) | NOT NULL | Identification document number. |
| `name` | varchar(255) | NOT NULL | Attendee name from API lookup or form. |
| `is_priority` | boolean | DEFAULT false | Preferential care flag. |
| `status` | varchar(30) | DEFAULT 'pending' | Status enum (`pending`, `calling`, `in_progress`, `completed`, `no_show`, `cancelled`). |
| `call_count` | int | DEFAULT 0 | Counter for initial calls and recalls. |
| `called_at` | timestamp | NULLABLE | Timestamp of first call action. |
| `started_at` | timestamp | NULLABLE | Timestamp of session start (30-min timer start). |
| `ended_at` | timestamp | NULLABLE | Timestamp of session completion. |
| `cancelled_at` | timestamp | NULLABLE | Timestamp when marked `no_show` or `cancelled`. |
| `idempotency_key` | varchar(64) | UNIQUE, NULLABLE | Request hash token to prevent duplicate creation. |
| `created_at` | timestamp | NULLABLE | Ticket issuance timestamp at kiosk. |
| `updated_at` | timestamp | NULLABLE | Record update timestamp. |

---

## 3. Ticket State Machine (`status`)

The operational transitions for the `status` column in the `tickets` table proceed through defined lifecycle states.

```
+------------------+
       |     pending      |  (Emitido en tótem)
       +--------+---------+
                |
                v
       +------------------+
       |     calling      |  (Llamado por asesor desde el módulo)
       +--------+---------+
                |
    +-----------+-----------+
    |           |           |
    v           v           v
+---+---+   +---+---+   +---+---+
| in_   |   | no_   |   |cancel-| (Anulación manual por Admin
|progress   | show  |   |  led  |  desde Filament en cualquier
+---+---+   +-------+   +-------+  punto del flujo)
    |       (Ausencia   (Estado final
    v        tras t.     por anulación)
+---+---+    tope)
|comple-|
|  ted  |  (Atención finalizada)
+-------+
```

---

## 4. Concurrency Control and Race Condition Prevention

With up to 50 advisors potentially clicking "Call Next" simultaneously in the same millisecond, the backend enforces **Pessimistic Locking** at the database layer via MySQL `FOR UPDATE`.

### 4.1 Backend Ticket Assignment Algorithm
Calls execute inside an isolated database transaction:

```
<?php

use Illuminate\Support\Facades\DB;
use App\Models\Ticket;

$assignedTicket = DB::transaction(function () use ($module, $user) {
    $categoryIds = $module->categories()->pluck('categories.id');

    $ticket = Ticket::where('status', 'pending')
        ->whereIn('category_id', $categoryIds)
        ->orderBy('is_priority', 'desc')
        ->orderBy('created_at', 'asc')
        ->lockForUpdate()
        ->first();

    if ($ticket) {
        $ticket->update([
            'status' => 'calling',
            'module_id' => $module->id,
            'user_id' => $user->id,
            'called_at' => now(),
        ]);

        return $ticket;
    }

    return null;
});
```

**Technical Guarantee:** The `lockForUpdate()` method locks the selected row in `tickets` at the storage engine level. If two advisors trigger assignment simultaneously, the second transaction waits until the first commits its transition to `calling`, preventing double assignment of the same ticket.

---

## 5. Ticket Recall Rules and UI Timers

### 5.1 Recall Logic
When an advisor clicks "Recall" on a ticket in `calling` state:
* **Preserve Called Timestamp:** `called_at` remains unchanged to preserve accurate tolerance metrics.
* **Increment Counter:** `call_count` increases by 1 (`call_count = call_count + 1`).
* **Display Queueing:** Re-broadcasts the event via WebSockets to be appended to the end of the public audio playback queue.

### 5.2 UI Delay Protection in Advisor Panel
To prevent premature ticket cancellation before audio vocalization completes:
* **No-Show Button Lockout:** Clicking "Call Next" or "Recall" temporarily disables the "Mark No-Show" button for 20 seconds, displaying a countdown indicator.
* **Immediate Session Start:** The "Start Session" (`in_progress`) button remains enabled continuously. If the attendee is immediately present, the advisor can start the session without delay.

---

## 6. Architecture Idempotency Controls

To prevent duplicate records from network retries or double clicks, idempotency is enforced at key entry points:

### 6.1 Kiosk Idempotency (`kiosk`)
* Kiosk session generates a unique `idempotency_key` upon loading the registration form.
* The form submission includes this key during insertion.
* Duplicate submissions trigger a `UNIQUE` constraint failure, returning the original record safely without creating a duplicate ticket.

### 6.2 Advisor Panel Idempotency (`advisor`)
State transitions require state preconditions prior to executing updates.
* *Example:* Changing status to `in_progress` requires `status = calling`. A second duplicate action fails the precondition check and is ignored.

---

## 7. Real-Time WebSocket Architecture (Laravel Reverb)

Event broadcasting between the core server and public TV screens relies on Laravel Reverb over the LAN.

### 7.1 Event Dispatching Flow
1. Advisor executes "Call Next" or "Recall".
2. Transaction commits successfully in MySQL.
3. Backend dispatches `TicketCalledEvent` implementing `ShouldBroadcastNow` for immediate synchronous execution without queue delay.
4. Laravel Reverb transmits the payload to `displays-channel`.

### 7.2 WebSocket Payload Structure

```
{
  "event": "TicketCalledEvent",
  "data": {
    "code": "FIN-00001",
    "module_number": 5,
    "category_name": "Financiera",
    "called_at": "2026-08-20 10:15:30"
  }
}
```

### 7.3 Visual Layout and Playback Queue (`display`)
The public display interface is split into two operational zones:
* **Main Highlight Area (70% viewport):** Prominently displays the ticket actively being announced in the current 20-second window alongside client-side Text-to-Speech audio playback.
* **Side Queue Grid (30% viewport):** Displays a dynamic list of the 8–10 most recently called tickets currently in `calling` state.

**Queue Processing Rules:**
1. Incoming WebSocket events push into a FIFO array managed in JavaScript.
2. Each item occupies the Main Highlight Area for a 20-second window while playing voice audio (`"Ticket FIN-00001, please proceed to Module 5"`).
3. After 20 seconds, the ticket shifts into the top position of the Side Queue Grid, clearing the Main Highlight Area for the next queued item.
4. If an advisor transitions a ticket to `in_progress`, `no_show`, or `cancelled`, a secondary WebSocket event immediately removes it from the Side Queue Grid.