# On-Site Event Attendance & Queue Management System

A high-performance, real-time queue and physical consultation management system built for high-concurrency event environments. The platform operates fully offline over an isolated Local Area Network (LAN), handling kiosk ticket issuance, advisor station calls, audio-visual public screen announcements, and real-time operational analytics.

---

## Key Features

* **Touchscreen Kiosk (`kiosk`):** Rapid identity lookup, category selection, priority queuing, idempotency protection, and 80mm thermal ticket printing.
* **Advisor Management Station (`advisor`):** Single-click ticket calling with pessimistic database locking, ticket recalls, no-show safety delays, and a 30-minute consultation countdown timer.
* **Public TV Display Board (`display`):** Real-time WebSockets broadcast using Laravel Reverb, client-side Web Speech API (Text-to-Speech) vocal readout, and dynamic split-screen queue layouts.
* **Administrative Operations (`admin`):** Full resource management with Filament 5.x, dynamic category-to-module matrix rebalancing, soft deletes, and event audit logs.
* **Analytics & Exports:** Real-time KPI dashboards, category distribution charts, and filterable data exports in Excel (`.xlsx`) and PDF formats.

---

## Tech Stack

* **Backend Framework:** Laravel 13
* **PHP Version:** PHP 8.5
* **Database Engine:** MySQL 8.4 (InnoDB)
* **Admin Panel:** Filament 5.x (using `Schemas/` and `Tables/` structure)
* **Frontend Reactive Components:** Livewire v4.x & Alpine.js
* **Styling Framework:** Tailwind CSS v4.3
* **WebSocket Engine:** Laravel Reverb (Port 6001)
* **Local Web Server:** Laragon (Apache 2.4)

---

## Local Environment Installation

### 1. Prerequisites
Ensure Laragon is running with Apache 2.4, PHP 8.5, and MySQL 8.4 enabled.

### 2. Database Setup
Create a local MySQL database named `turnify`.

### 3. Repository Setup
Clone the repository and install dependencies:

```
# Clone the repository
git clone https://github.com/your-username/turnify.git
cd turnify

# Install PHP dependencies
composer install

# Install Frontend dependencies & build assets
pnpm install
pnpm run build
```

### 4. Environment Configuration
Copy the `.env.example` file and configure database and Reverb variables:

```
APP_NAME=Turnify
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://192.168.1.100:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=turnify
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=reverb
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

REVERB_APP_ID=turnify-app
REVERB_APP_KEY=turnify-key
REVERB_APP_SECRET=turnify-secret
REVERB_HOST="192.168.1.100"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 5. Migration & Seed Execution
Run migrations and populate the initial database structure:

```
# Generate Application Key
php artisan key:generate

# Run database migrations and populate default seeders
php artisan migrate:fresh --seed

# (Optional) Create administrative user for Filament Panel
php artisan make:filament-user
```

### 6. Start Real-time Broadcasting
Launch the local Laravel Reverb server:

```
# Start Laravel Reverb WebSocket Server for real-time TV displays
php artisan reverb:start --host=0.0.0.0 --port=8080
```

---

## Project Documentation Structure

All system architecture and technical requirements specifications are documented under `.github/docs/`:

* **`01-vision-and-business-rules.md`**: Project scope, core business rules, and user roles.
* **`02-hardware-and-network-architecture.md`**: Topology, local network isolation, and hardware specs.
* **`03-technical-architecture-and-data-model.md`**: Database schema, Eloquent relationships, and WebSocket flow.
* **`04-software-requirements-specification.md`**: Detailed functional and non-functional requirements (SRS).
* **`05-ui-ux-design-and-wireframes.md`**: Interface guidelines, color coding rules, and print layouts.
* **`TASKLOG.md`**: Step-by-step development roadmap and task backlog.
* **`AGENTS.md`**: Coding guidelines for AI agents and development assistants.