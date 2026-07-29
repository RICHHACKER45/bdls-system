# Web-Based Service Request Queuing and Notification System for Local Communities Using SMS Technology

A modern, highly optimized Single Page Application (SPA) developed for **Barangay Doña Lucia, Quezon, Nueva Ecija**. This system modernizes the traditional barangay service request process by providing a unified digital queue, real-time request tracking, and automated SMS notifications compliant with Philippine NTC regulations.

---

## 🚀 Core Features

- **Real-Time Live Dashboard:** Powered by Laravel Reverb and React. The active queue, announcements, and document processing statuses update in real-time across all resident and admin screens without refreshing the page.
- **Unified Digital Queuing:** Seamlessly manages both Walk-in and Online service requests in a single, fair queuing dashboard.
- **Advanced Batch Processing:** Administrators can group multiple requests, apply batch actions, and dispatch real-time SMS updates in one click using a persistent Floating Action Button (FAB).
- **Shadow Profiles for Walk-ins:** Instantly registers walk-in residents with a prefix queue number (e.g., `W-001`) without requiring an email or complex password setup.
- **Zero-Retention OCR Engine:** Valid IDs and user selfies are sent directly to the Google Cloud Vision API for AI text extraction, instantly verified against the Census database, and immediately purged from memory to prevent data breaches.
- **NTC-Compliant SMS Engine:** Integrates a robust SMS notification system featuring a 160-character limit optimizer, Background Queue Jobs, and Night Curfew filters to protect residents from late-night spam.
- **"Human-in-the-Loop" Workflow:** Tracks the lifecycle of documents (e.g., _For Interview_, _Processing_, _Ready for Release_) to accommodate required physical appearances and signatures.
- **Hybrid Payment System:** Allows residents to pay via GCash (Online) or Cash (Walk-in), uploading verification receipts for frictionless processing.

---

## 🛠️ Technology Stack

This system has completely migrated from legacy Blade/Vanilla JS to a robust **React SPA architecture**.

- **Backend:** Laravel 12 (PHP 8.2)
- **Frontend SPA Framework:** React 19 + Inertia.js v3
- **Styling:** Tailwind CSS v4 (Strict component-based atomic design)
- **Real-time WebSockets:** Laravel Reverb + Laravel Echo
- **Job Queues:** Laravel Horizon / Database Queue (for async SMS & Notifications)
- **Database:** MySQL

---

## 🗄️ Database Architecture

The system uses a highly optimized relational database structure:

1. `users`: The core table using Single Table Inheritance for Admins, Online Residents, and Walk-in Residents.
2. `document_types`: Seeded with the 13 official barangay request types, fees, and requirements.
3. `service_requests`: The central transaction table linking `user_id` and `document_type_id`.
4. `attachments`: Stores extra uploaded requirements via a 1:N relationship.
5. `jobs` / `failed_jobs`: Handles the Background Queues for dispatching SMS notifications asynchronously.
6. `notification_logs`: A fail-safe database log of every SMS/Email sent, including delivery status.

---

## 🛡️ Security & Enterprise Architecture

This system has passed strict Laravel 12 Architecture & Security Audits:

- **Zero-Retention Identity Verification:** Ensures that user-uploaded sensitive IDs are never stored on disk.
- **Memory Protection:** Bypasses heavy ORM calculations in dashboards, favoring indexed raw DB queries and `DocumentType` baseline metrics.
- **Strict Password Enforcement:** User accounts require a minimum of 8 characters, with at least 1 uppercase, 1 lowercase, 1 number, and 1 symbol.
- **Atomic Database Transactions:** Critical operations (like creating a request and sending an SMS) are wrapped in `DB::transaction()`. If the SMS API fails, the database rolls back to maintain data integrity.
- **Rate Limiting & Cooldowns:** Protects the SMS API budget by rate-limiting OTP resends and applying IP-based blocks.

---

## 💻 Installation & Setup Guide

Follow these simple steps to clone, configure, and launch the BDLS project locally.

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & npm (or Bun)
- MySQL Database

### 1. Clone the Repository

```bash
git clone https://github.com/RICHHACKER45/bdls-system.git
cd bdls-system
```

### 2. Install Backend & Frontend Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

Copy the example environment file and generate your application key:

```bash
cp .env.example .env
php artisan key:generate
```

Open your `.env` file and configure your Database, Reverb, and SMS API credentials:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bdls_db
DB_USERNAME=root
DB_PASSWORD=

# WebSocket Setup (Laravel Reverb)
REVERB_APP_ID=123456
REVERB_APP_KEY=your_reverb_key
REVERB_APP_SECRET=your_reverb_secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# SMS API Configuration
SMS_DRIVER=api
SMS_API_URL=your_api_url_here
SMS_API_KEY=your_api_key_here
SMS_SENDER_NAME=your_sender_name
SMS_FROM_NUMBER=your_number
SMS_PREFIX="Brgy Dona Lucia: "
```

### 4. Run Migrations & Seeders

Build the database tables and populate them with the 13 Document Types and default test accounts.

```bash
npm run dbreset
# Which executes: php artisan migrate:fresh --seed
```

### 5. Launch the Application (Concurrently)

Because we are using Vite, Tailwind v4, Background Queues, and WebSockets (Reverb), you need to run all services concurrently. We have built a simple command to do this automatically:

```bash
npm start
```

_What `npm start` does behind the scenes:_

1. Starts the PHP Artisan Server (`localhost:8000`)
2. Starts the Vite Frontend Bundler (`bun run dev`)
3. Starts the Background Job Worker (`php artisan queue:work`)
4. Starts the WebSocket Server (`php artisan reverb:start`)

**You're all set!** Access the application at: `http://localhost:8000`
