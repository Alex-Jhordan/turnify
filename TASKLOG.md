# TASKLOG.md - Development Backlog

## Phase 1: Local Development Environment, Database & Conceptual Model

- [X] **Task 1.1: Configure local environment on Laragon and installation of base packages**
  * Verify Apache 2.4, PHP 8.5, and MySQL 8.4 are running in Laragon.
  * Create local MySQL database named `turnify`.
  * Initialize Laravel 13 project at the local root directory.
  * Update `.env` setting `DB_CONNECTION=mysql`, `DB_DATABASE=turnify`, `DB_USERNAME=root`, and `DB_PASSWORD=`.
  * Install the auditing package using `composer require spatie/laravel-activitylog` and publish the migration for the `activity_log` table.
  * Test database connection by executing `php artisan migrate`.

- [X] **Task 1.2: Create PHP Backed Enums for status and document types**
  * Create `app/Enums/TicketStatus.php` with cases: `Pending = 'pending'`, `Calling = 'calling'`, `InProgress = 'in_progress'`, `Completed = 'completed'`, `NoShow = 'no_show'`, `Cancelled = 'cancelled'`.
  * Create `app/Enums/UserRole.php` with cases: `Administrator = 'administrator'`, `Advisor = 'advisor'`.
  * Create `app/Enums/DocumentType.php` with cases: `DNI = 'dni'`, `Passport = 'passport'`, `CE = 'ce'`.
  * Implement Filament's `HasLabel` interface and `getLabel()` method across created Enums to provide formatted labels.

- [X] **Task 1.3: Define database migration schemas**
  * Update `database/migrations/0001_01_01_000000_create_users_table.php` adding `is_active` (boolean, default: `true`) and the softDeletes().
  * Create `database/migrations/xxxx_xx_xx_create_categories_table.php` with: `id`, `name` (varchar 100), `prefix` (varchar 10), `is_active` (boolean, default: `true`), `timestamps`.
  * Create `database/migrations/xxxx_xx_xx_create_modules_table.php` with: `id`, `module_number` (int, unique), `is_active` (boolean, default: `true`), `current_user_id` (foreignId, nullable, unique, constrained `users`), `timestamps`.
  * Create `database/migrations/xxxx_xx_xx_create_category_module_table.php` (pivot) with: `module_id` (foreignId), `category_id` (foreignId).
  * Create `database/migrations/xxxx_xx_xx_create_tickets_table.php` with: `id`, `ticket_code` (varchar 20), `category_id` (foreignId), `module_id` (foreignId, nullable), `user_id` (foreignId, nullable), `document_type` (varchar 20, default `dni`), `document_number` (varchar 30), `name` (varchar 255), `is_priority` (boolean, default `false`), `status` (varchar 30, default `pending`), `call_count` (int, default 0), `called_at` (timestamp, nullable), `started_at` (timestamp, nullable), `ended_at` (timestamp, nullable), `cancelled_at` (timestamp, nullable), `idempotency_key` (varchar 64, unique, nullable), `timestamps`.

- [X] **Task 1.4: Configure Eloquent models, relationships & audit traits**
  * Update `app/Models/User.php` adding `LogsActivity` trait (`spatie/laravel-activitylog`), `SoftDeletes` trait, casting `is_active` to boolean, and defining `hasMany` with `Ticket` and `hasOne` with `Module` (`current_user_id`).
  * Create `app/Models/Category.php` with `LogsActivity` trait and `belongsToMany` relationship with `Module` via `category_module`.
  * Create `app/Models/Module.php` with `LogsActivity` trait and `belongsToMany` with `Category`, `belongsTo` with `User` (`current_user_id`), and `hasMany` with `Ticket`.
  * Create `app/Models/Ticket.php` with `LogsActivity` trait, casting `status` to `TicketStatus::class`, `document_type` to `DocumentType::class`, `is_priority` to boolean, and `belongsTo` relationships with `Category`, `Module`, and `User`.

- [X] **Task 1.5: Create seed data (Seeders)**
  * Create `database/seeders/CategorySeeder.php` inserting 5 base categories (`Financiera`, `Legal`, `Migraciones`, `Tributaria`, `General`) with prefixes (`FIN`, `LEG`, `MIG`, `TRI`, `GEN`).
  * Create `database/seeders/ModuleSeeder.php` generating physical modules 1 through 50 via loop.
  * Update `database/seeders/DatabaseSeeder.php` to call `CategorySeeder` and `ModuleSeeder`.
  * Execute `php artisan migrate:fresh --seed` to verify seed data.

---

## Phase 2: Administrative Panel & Operational Management (Filament 5.x)

- [X] **Task 2.1: Install Filament 5.x & Shield**
  * Execute `composer require filament/filament:"^5.0"` and install via `php artisan filament:install --panels`.
  * Execute `composer require bezhansalleh/shield` and install via `php artisan shield:install`.
  * Verify admin access at `/admin` by creating a superadmin user.

- [X] **Task 2.2: Create Filament resource: Categories**
  * Execute `php artisan make:filament-resource Category`.
  * Configure form schema in `app/Filament/Resources/Categories/Schemas/CategoryForm.php`: `TextInput::make('name')->required()`, `TextInput::make('prefix')->required()->maxLength(10)`, `Toggle::make('is_active')->default(true)`.
  * Configure table schema in `app/Filament/Resources/Categories/Tables/CategoriesTable.php`: `TextColumn` for name, prefix, and `IconColumn` for `is_active`.
  * Ensure `app/Filament/Resources/Categories/CategoryResource.php` links `CategoryForm` and `CategoriesTable`.

- [X] **Task 2.3: Create Filament resource: Modules & Category Matrix**
  * Execute `php artisan make:filament-resource Module`.
  * Configure form schema in `app/Filament/Resources/Modules/Schemas/ModuleForm.php`: `TextInput::make('module_number')->numeric()->required()`, `Toggle::make('is_active')->default(true)`, and `Select::make('categories')->relationship('categories', 'name')->multiple()->preload()`.
  * Configure table schema in `app/Filament/Resources/Modules/Tables/ModulesTable.php`: list module number, active status, assigned categories, and current logged-in user.
  * Ensure `app/Filament/Resources/Modules/ModuleResource.php` links `ModuleForm` and `ModulesTable`.

- [X] **Task 2.4: Create Filament resource: Users & Role Assignment**
  * Execute `php artisan make:filament-resource User --simple --generate`.
  * Configure form schema in `app/Filament/Resources/Users/UserResource.php`: `name`, `email`, encrypted `password`, and `Select::make('roles')->relationship('roles', 'name')`.
  * Configure table schema in `app/Filament/Resources/Users/UserResource.php`: list users with email, role and `is_active` ToggleColumn.
  * Ensure `app/Filament/Resources/Users/UserResource.php` implement `form()` and `table()`.

- [X] **Task 2.5: Create Filament resource: Ticket Audit & Cancellation**
  * Execute `php artisan make:filament-resource Ticket --generate --view`.
  * Configure read-only view in `app/Filament/Resources/Tickets/Pages/ViewTicket.php` (infolist) to audit `code`, `category`, `document_type`, `document_number`, `name`, `status`, `is_priority`, `module`, `user`, `call_count`, `called_at`, `started_at`, `ended_at`, `cancelled_at`, `created_at` and `updated_at`.
  * Delete `CreateTicket.php` and `EditTicket.php` page files, and disable their respective routes in `app/Filament/Resources/Tickets/TicketResource.php`
  * Configure table in `app/Filament/Resources/Tickets/Tables/TicketsTable.php`: add custom `Action::make('cancel')` allowing admins to transition ticket status to `cancelled` and set `cancelled_at = now()`.
  * Ensure `app/Filament/Resources/Tickets/TicketResource.php` links components properly.

---

## Phase 3: Kiosk Self-Service Module & Issuance Logic

- [X] **Task 3.1: Create Livewire component KioskMain**
  * Execute `php artisan make:livewire KioskMain`.
  * Configure `app/Livewire/KioskMain.php` defining public properties: `$document_type`, `$document_number`, `$name`, `$category_id`, `$is_priority`, `$idempotency_key`, and flow step `$step = 1`.
  * Update `App\Enums\DocumentType` to implement `Filament\Support\Contracts\HasLabel` and add the `getLabel()` method for human-readable labels.
  * Create touch-first view in `resources/views/livewire/kiosk-main.blade.php` using Tailwind CSS v4.3 (large buttons and touch numpad).

- [X] **Task 3.2: Implement Identity API integration**
  * Create service class `app/Services/IdentityLookupService.php`.
  * Implement `lookup(string $documentType, string $documentNumber): ?string` querying the external identity API via `Illuminate\Support\Facades\Http`.
  * Call lookup inside `app/Livewire/KioskMain.php` to auto-populate `$name` upon document input completion.

- [X] **Task 3.3: Implement code generation & idempotency service**
  * Create service class `app/Services/TicketIssuanceService.php`.
  * Implement `generateCode(Category $category, bool $isPriority): string` generating formatted codes (`PREFIX-00001` or `P-PREFIX-00001`).
  * Implement `issueTicket(array $data): Ticket` wrapped in a `try-catch` block validating `idempotency_key` unique constraints.

- [X] **Task 3.4: Build confirmation view & thermal print output**
  * Design confirmation overlay in `resources/views/livewire/kiosk-main.blade.php` displaying the generated ticket code for 5 seconds before resetting.
  * Integrate JavaScript `window.print()` / Web Print API triggering the 80mm thermal ticket layout.

---

## Phase 4: Assignment Engine, Concurrency & Advisor Panel

- [X] **Task 4.1: Ticket assignment service with pessimistic locking**
  * Create service class `app/Services/TicketAssignmentService.php`.
  * Implement `callNextTicket(Module $module, User $user): ?Ticket`:
    * Open transaction via `DB::transaction()`.
    * Fetch permitted category IDs for module.
    * Query `Ticket` filtering by `status = pending` and `category_id`, ordering by `is_priority DESC` and `created_at ASC`.
    * Apply `->lockForUpdate()` on Eloquent query to prevent MySQL 8.4 race conditions.
    * Update ticket status to `calling`, assign `module_id`, `user_id`, set `called_at = now()` and `call_count = 1`.

- [X] **Task 4.2: Advisor Panel Filament Custom Page**
  * Execute `php artisan make:filament-page AdvisorPanel` to generate `app/Filament/Pages/AdvisorPanel.php` and `resources/views/filament/pages/advisor-panel.blade.php`.
  * Execute `php artisan make:filament-theme` to compile assets and ensure correct rendering of custom Tailwind/Filament classes in the Blade view.
  * Implement stateful workflow actions in `AdvisorPanel.php`: `selectModule()`, `leaveModule()`, `callNext()`, `recall()`, `startAttention()`, `markNoShow()`, and `completeAttention()`.
  * Prevent race conditions in `selectModule()` and `leaveModule()` using `DB::transaction()`, pessimistic locking (`lockForUpdate()`), and `QueryException` handling.
  * Build conditional UI rendering in `advisor-panel.blade.php`: a module selection grid for unassigned advisors, and an active ticket management panel with lifecycle action buttons when a module is occupied.

- [X] **Task 4.3: Implement 30-minute timer & UI delay lock**
  * Embed Alpine.js in `resources/views/filament/pages/advisor-panel.blade.php` managing a 30-minute countdown upon reaching `in_progress` state.
  * Apply dynamic Tailwind CSS v4.3 classes: Green (30:00-10:00), Yellow (09:59-03:00), Red (02:59-00:00).
  * Implement Alpine `x-data` state disabling the "Mark No-Show" (`no_show`) button for 20 seconds following a call or recall action.

---

## Phase 5: Real-Time WebSockets (Laravel Reverb) & Public Display (TV)

- [X] **Task 5.1: Install and configure Laravel Reverb**
  * Execute `php artisan install:broadcasting` and select Laravel Reverb.
  * Verify `.env` variables: `BROADCAST_CONNECTION=reverb`, `REVERB_SCHEME=http`, `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST="127.0.0.1"`, `REVERB_PORT=6001`.
  * Test WebSocket server using `php artisan reverb:start --port=6001`.

- [X] **Task 5.2: Create TicketCalledEvent**
  * Create `app/Events/TicketCalledEvent.php` implementing `ShouldBroadcastNow`.
  * Define public properties: `$ticketId`, `$ticketCode`, `$moduleNumber`, `$categoryName`, `$isRecall`.
  * Configure `broadcastOn()` pointing to `new Channel('displays-channel')`.
  * Dispatch event inside `callNext()` and `recall()` in `AdvisorPanel`.

- [X] **Task 5.3: Create TicketStatusUpdatedEvent**
  * Create `app/Events/TicketStatusUpdatedEvent.php` implementing `ShouldBroadcastNow`.
  * Define public properties: `$ticketId`, `$status`.
  * Configure `broadcastOn()` pointing to `new Channel('displays-channel')`.
  * Dispatch event inside `startAttention()` and `markNoShow()` in `AdvisorPanel`.

- [X] **Task 5.4: Create Display component & view**
  * Execute `php artisan livewire:layout` to generate the default application layout at `resources/views/layouts/app.blade.php`.
  * Execute `php artisan make:livewire pages::display` to create the single-file page component at `resources/views/pages/⚡display.blade.php`.
  * Build view `resources/views/pages/⚡display.blade.php` using Tailwind v4.3 CSS Grid split into a Main Area (70%) displaying the current ticket with the status `calling` and a Side Grid (30%) showing the history of the last 5 tickets with the status `calling` except for the one in the main area.
  * Configure Laravel Echo listening on `displays-channel` for `TicketCalledEvent`.
  * Register page route in `routes/web.php` using `Route::livewire('/display', 'pages::display')->name('display')`.
  * Configure environment variables in `.env` for Vite and Laravel Reverb (`VITE_REVERB_APP_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, `VITE_REVERB_SCHEME`).

- [X] **Task 5.5: Text-to-Speech (TTS) integration for main displayed ticket**
  * Create script `resources/js/display-tts.js` to handle `window.speechSynthesis` announcements.
  * Import `display-tts.js` in `resources/js/app.js`.
  * Dispatch `announce-current-ticket` event from Livewire in `onTicketCalled` in `resources/views/pages/⚡display.blade.php` to trigger immediate voice prompt (`"Turno [Code], acérquese al Módulo [Number]"`).

---

## Phase 6: Analytics Dashboard, Reports & Data Export

- [ ] **Task 6.1: Install export dependencies**
  * Execute `composer require maatwebsite/excel`.
  * Execute `composer require barryvdh/laravel-dompdf`.

- [ ] **Task 6.2: Build Filament 5.x stats widgets**
  * Create widget `app/Filament/Widgets/StatsOverview.php` rendering KPIs: Total Tickets, Completed Sessions, Avg Wait Time (`called_at - created_at`), Avg Session Duration (`ended_at - started_at`).
  * Create widget `app/Filament/Widgets/TicketsPerCategoryChart.php` rendering ticket distribution per category.

- [ ] **Task 6.3: Implement data export class**
  * Create `app/Exports/TicketsExport.php` implementing `FromQuery`, `WithHeadings`, `WithMapping`.
  * Map columns: Ticket Code, Category, Module, Advisor, Document, Status, Issuance Time, Call Time, Start Time, End Time, Total Session Duration.

- [ ] **Task 6.4: Reports page in Filament admin**
  * Create custom page `app/Filament/Pages/ReportsPage.php`.
  * Build form filters for date range, category selection, and module selection.
  * Add action buttons to download filtered reports in `.xlsx` and `.pdf` formats.

---

## Phase 7: Load Testing, Concurrency Simulation & Local Deployment

- [ ] **Task 7.1: Concurrency simulation command**
  * Create Artisan command `app/Console/Commands/SimulateConcurrenceCommand.php`.
  * Simulate 50 concurrent ticket call requests verifying that `lockForUpdate()` prevents double assignments.

- [ ] **Task 7.2: Laragon server & Laravel optimization**
  * Tune MySQL 8.4 `my.ini` setting `max_connections = 200` and optimizing `innodb_buffer_pool_size`.
  * Run Laravel 13 optimization commands: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
  * Test LAN accessibility via local domain (`.test`) or static IP (`http://192.168.1.100`) from multiple devices connected to the local network.