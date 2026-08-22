# 01. Vision, Business Rules, and Operational Flow

## 1. Project Context and Objectives

### 1.1 Context
The event brings together entrepreneurs and small business owners for personalized technical and management consulting sessions.
* **Estimated Attendance:** 15,000 total attendees.
* **Infrastructure:** 50 physical individual consultation modules.
* **Consultation Duration:** 30 fixed minutes per session.
* **Theoretical Maximum Capacity:** 100 consultations per hour globally (800 consultations per 8-hour shift).

The Queue Management System (QMS) aims to structure attendee demand, eliminate physical queues at consultation modules, optimize advisor availability, and provide real-time centralized visibility of attendee flow.

### 1.2 System Objectives
* **Self-Service Registration:** Enable fast attendee check-in via kiosk terminals, identifying specific advisory needs.
* **Automated Load Distribution:** Distribute workload across available modules using virtual queues categorized by topic.
* **Public Call Projection:** Display called tickets on high-visibility screens to guide attendees without overcrowding consultation areas.
* **Advisor Interface:** Provide an intuitive dashboard for advisors to manage session lifecycles and track countdown timers.
* **Operational Metrics:** Capture real-time operational data for supervisor decision-making during and after the event.

---

## 2. Core Business Rules

### 2.1 Centralized Virtual Queue Model by Category
To prevent complex manual rebalancing between physical modules, tickets are **not assigned** to a specific module upon creation.
* **Ticket Emission:** Upon requesting a ticket at a kiosk, the attendee selects a single category of interest. The system generates a unique code (e.g., `FIN-00001`) and saves the ticket with `status = pending`.
* **Dynamic Assignment:** Modules are configured to service one or more categories. When an advisor clicks "Call Next", the system evaluates the global pending queue for the module's enabled categories and assigns the matching ticket.
* **Module Flexibility:** If a module changes its permitted categories during runtime, it immediately pulls tickets from the updated categories on the next call without requiring manual record transfers.

### 2.2 Calling Algorithm and Prioritization
Selecting the next ticket from the centralized queue follows a strict hierarchical order:
1. **Priority Flag (`is_priority`):** Tickets with `is_priority = true` (e.g., preferential attendees) take precedence over regular tickets within the same category.
2. **Registration Age (`created_at ASC`):** For tickets with identical priority status, the system assigns the ticket with the oldest creation timestamp.

### 2.3 Absence Handling (No-Show) and Cancellation Policy
Strict non-attendance policies ensure predictability and prevent schedule drift:
* **Invocation:** When a ticket is called, its status changes from `pending` to `calling`. Advisors can re-call the ticket to trigger additional alerts.
* **No-Show:** If the attendee does not appear within the grace period (2–3 minutes), the advisor marks the ticket as `no_show`.
* **Cancellation:** To fix registration errors or user abandonments, a ticket can be transitioned to `cancelled`. This state is irreversible. Re-activation or manual re-insertion into the queue is strictly prohibited; the attendee must register at a kiosk to obtain a new ticket at the end of the global queue.

### 2.4 Consultation Time Controls
* **Standard Duration:** Maximum budgeted time per session is 30 minutes.
* **Visual Countdown:** The advisor panel displays a countdown timer that begins the moment the attendee arrives at the module and the advisor starts the session (`started_at`).
* **Early Completion or Pause:** Advisors may end the session early if the query is resolved, or pause the timer exclusively during technical or emergency disruptions.

### 2.5 Queue Closure by Capacity Limit
To manage the disparity between total event attendance (15,000) and actual service capacity (800):
* The system allows setting hard limits on total tickets issued per category.
* Enables manual or automatic suspension of ticket issuing at kiosks when estimated wait times exceed the event's closing schedule.

---

## 3. Roles and Permissions Matrix

The system defines four strict operational access profiles:

| Role | Description | Core Permissions & Scope |
| :--- | :--- | :--- |
| `administrator` | Technical staff and general event supervisors. | • Full access to Filament Admin Panel.<br>• Create, edit, and deactivate system users and advisors.<br>• Global configuration of categories and physical modules.<br>• Remote reassignment of active categories per module.<br>• Real-time statistical dashboard access and data export. |
| `advisor` | Domain specialists conducting consultations at modules. | • Select and pair with an available physical module at login.<br>• Configure assigned categories for their module (if permitted).<br>• Execute ticket actions: `call_next`, `re_call`, `start`, `pause`, `mark_no_show`, `complete`.<br>• View current pending ticket list for assigned categories. |
| `display` | Public monitors or laptops projecting real-time status. | • Read-only access to the public call view.<br>• Automatic WebSocket connection to listen for call events.<br>• Client-side Text-to-Speech (TTS) audio playback. |
| `kiosk` | Touchscreen terminals for self-service registration. | • Exclusive access to the ticket request interface.<br>• Query identification API (DNI lookup).<br>• Silent thermal ticket printing trigger. |

---

## 4. General Operational Lifecycle Flow

1. **Check-in & Registration (`kiosk`):**
   * Attendee interacts with the kiosk, inputs identification number (DNI, Passport, or CE), and selects a topic.
   * Kiosk prints thermal ticket with a unique code; record is stored in database as `status = pending`.
2. **Dynamic Waiting:**
   * Ticket remains `pending` in the central category queue.
   * Attendee monitors public TV displays showing active calls and recent history.
3. **Public Calling (`advisor` & `display`):**
   * Advisor clicks "Call Next" -> Ticket status transitions to `calling`.
   * Public display updates, triggers Text-to-Speech audio announcement, and appends code to recent calls list.
4. **Session Execution (`advisor`):**
   * Attendee arrives at module. Advisor clicks "Start Session" -> Ticket status transitions to `in_progress`, starting the 30-minute timer.
5. **Session Resolution (`advisor` or `admin`):**
   * **Completed (`completed`):** Session concludes successfully -> Advisor clicks "Finish Session", ticket updates to `completed`, module returns to idle.
   * **Absent (`no_show`):** Attendee fails to show after recall -> Advisor marks as `no_show`, ticket closes, module returns to idle.
   * **Cancelled (`cancelled`):** Administrative cancellation via supervisor panel for errors or early departures prior to calling.