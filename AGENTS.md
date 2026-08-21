# AGENTS.md - Technical Guidelines for AI Coding Agents

## 1. System Overview & Technology Stack
This application is an event attendance and queue management platform designed for offline Local Area Network (LAN) deployment.
* **Framework:** Laravel 13
* **Admin Panel & Resources:** Filament 5.x (using `Schemas/` and `Tables/` directory structure)
* **Frontend Component Layer:** Livewire v4.x + Alpine.js
* **CSS Framework:** Tailwind CSS v4.3
* **Real-time WebSockets:** Laravel Reverb (running on local port 6001)
* **Local Server Stack:** Laragon (Apache 2.4, PHP 8.5, MySQL 8.4)
* **Role & Audit Management:** `bezhansalleh/shield` and `spatie/laravel-activitylog`

---

## 2. Mandatory Architectural Guidelines

### 2.1 Filament 5.x Resource Structure
All Filament 5.x resources MUST follow the decoupled file structure using `Schemas/` and `Tables/` directories.
<!-- PLACEHOLDER_CODE_BLOCK_1: Filament Resource File Tree Example -->

### 2.2 Concurrency & Database Operations
* When assigning or calling tickets in `TicketAssignmentService`, ALL database queries fetching pending tickets MUST utilize pessimistic locking (`->lockForUpdate()`) inside a database transaction (`DB::transaction()`).
* Primary key and foreign key references must strictly follow standard Laravel conventions.
* Models MUST use Enums for `status` (`TicketStatus`), `document_type` (`DocumentType`), and `user_role` (`UserRole`).

### 2.3 Real-Time WebSockets Architecture
* WebSocket events MUST implement `ShouldBroadcastNow` to bypass queue delays in local operations.
* The public channel name for display updates is strictly `displays-channel`.
* WebSocket payloads must be lightweight and pass primitives or structured arrays representing ticket codes, module numbers, and status changes.

---

## 3. Workflow Execution Protocol
When implementing tasks from `TASKLOG.md`:
1. **Check Task Status:** Identify the active task in `TASKLOG.md`.
2. **Strict Scope:** Implement ONLY the classes, migrations, or components specified in the current task. Do not jump ahead to future phases.
3. **Audit Compliance:** Ensure all critical models (`Category`, `Module`, `Ticket`, `User`) maintain the `LogsActivity` trait integration.
4. **Mark Completion:** Update `TASKLOG.md` upon task completion by marking the corresponding checkbox `[x]`.