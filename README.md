# Web-Based Service Request Queuing and Notification System for Local Communities Using SMS Technology [1]

A Capstone Project developed for **Barangay Doña Lucia, Quezon, Nueva Ecija** [2]. This system modernizes the traditional barangay service request process by providing a unified digital queue, real-time request tracking, and automated SMS notifications compliant with Philippine NTC regulations [2-5].

---

## 🚀 Core Features

- **Unified Digital Queuing:** Seamlessly manages both Walk-in and Online service requests in a single, fair queuing dashboard [3, 4].
- **Shadow Profiles for Walk-ins:** Utilizes Single Table Inheritance (STI) to instantly register walk-in residents with a prefix queue number (e.g., `W-001`) without requiring an email or complex password setup [6].
- **NTC-Compliant SMS Engine:** Integrates a robust SMS notification system featuring a 160-character limit optimizer, Unicode (Emoji) trap filtering, Link Blocker, and a Night Curfew (9 PM to 7 AM) safeguard to prevent spam [5, 7-9].
- **13 Supported Document Types:** Strictly follows the Citizen's Charter ("Ang Barangay Requests"), supporting documents like Barangay Clearance, Certificate of Indigency, and First Time Jobseekers (FTJ) [10-14].
- **"Human-in-the-Loop" Workflow:** Includes functional status markers like `For Interview` and `Ready for Release` to accommodate required physical appearances and physical signatures as mandated by the barangay [15-17].
- **F-Pattern & 60/30/10 UI/UX:** Built with a strict Tailwind v4 design system to ensure a clean, accessible, and fast-loading interface on both mobile and desktop devices [18-20].

---

## 🛠️ Technology Stack

- **Backend:** Laravel 12 (PHP 8.2) [21]
- **Frontend:** Blade Templates, Tailwind CSS v4, Vanilla JavaScript (Separated Logic) [21-23]
- **Database:** MySQL [24]
- **SMS Provider:** 3rd Party API (Fortmed/Custom) [25]

---

## 🗄️ Database Architecture (ERD Summary)

The system uses a highly optimized relational database structure [24, 26]:

1.  `users`: The core table using Single Table Inheritance for Admins, Online Residents, and Walk-in Residents [6]. Includes soft-locking and rejection tracking [27].
2.  `document_types`: Seeded with the 13 official barangay request types and their descriptions [28].
3.  `service_requests`: The central transaction table linking `user_id` and `document_type_id` [29, 30].
4.  `attachments`: Stores extra uploaded requirements via a 1:N relationship [31, 32].
5.  `audit_logs`: Tracks administrative actions (Approvals, Rejections, Deletions) [33].
6.  `notification_logs`: A fail-safe database log of every SMS/Email sent, including delivery status [34, 35].

---

## 🛡️ Security & Enterprise Architecture

This system has passed a strict Laravel 12 Architecture and Security Audit [36]:

- **Centralized Authorization:** Uses a custom `AdminMiddleware` registered in `bootstrap/app.php` to protect administrative routes from unauthorized access [37-40].
- **Memory Protection:** Implements Eloquent Query Scopes (`scopePending`, `scopeApproved`) in the `User` model to filter records at the database level, preventing Memory Exhaustion (OOM) [41, 42].
- **Atomic Database Transactions:** Critical operations (like creating a request and sending an SMS) are wrapped in `DB::transaction()`. If the SMS API fails, the database rolls back to maintain data integrity [22, 43, 44].
- **Rate Limiting & Cooldowns:** Protects the SMS API budget by rate-limiting OTP resends and applying IP-based temporary blocks for spam attempts [45, 46].
- **Form Requests:** Extracts complex validation logic into dedicated classes like `RegisterRequest` [47, 48].

---

## 💻 Installation & Setup Guide

Follow these steps to clone, install, and run the BDLS project on your local machine.

### Prerequisites

- PHP 8.2 or higher [21]
- Composer
- Node.js & npm
- MySQL Database

### 1. Clone the Repository

```bash
git clone https://github.com/RICHHACKER45/bdls-system.git
cd bdls-system
```

2. Install Backend & Frontend Dependencies

```bash
composer install
npm install
```

3. Environment Configuration
   Copy the example environment file and generate your application key.

```
cp .env.example .env
php artisan key:generate
```

Open your .env file and configure your Database and SMS API credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bdls_db
DB_USERNAME=root
DB_PASSWORD=

# SMS API Configuration
SMS_DRIVER=api
SMS_API_URL=your_api_url_here
SMS_API_KEY=your_api_key_here
SMS_SENDER_NAME=your_sender_name
SMS_FROM_NUMBER=your_number
SMS_PREFIX="Brgy Dona Lucia: "
```

4. Run Migrations & Seeders
   This will build the database tables and populate them with the 13 Document Types and default test accounts

```
php artisan migrate:fresh --seed
```

5. Compile Assets & Run the Server
   Because we are using Vite and Tailwind v4, you must compile the frontend assets before running the PHP server

RUN:

```
npm start
```

to start the php artisan engine & npm dev concurrently
Access the application at: http://localhost:8000
