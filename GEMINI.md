<laravel-boost-guidelines>
=== .ai/senior-instructor rules ===

# BDLS (Barangay Doña Lucia System) - Master Architecture & Business Logic

**Project Name:** Web-Based Service Request Queuing and Notification System for Local Communities Using SMS Technology [1].
**Locale:** Barangay Doña Lucia, Quezon, Nueva Ecija [2].

## 1. SYSTEM OVERVIEW & OBJECTIVES

Ang BDLS ay HINDI pamalit sa mga national systems tulad ng Barangay Information Management System (BIMS) [3, 4]. Ito ay isang "frontline service support layer" na nakatutok sa tatlong bagay:

1. **Unified Digital Queue:** Pinagsamang pila para sa Walk-in at Online requests [5, 6].
2. **Real-time Tracking:** Upang maiwasan ang pabalik-balik na pagtatanong ng mga residente na nakakaabala sa workflow ng barangay staff [7, 8].
3. **Automated SMS Notification:** Ang pangunahing tulay ng komunikasyon na may optional Email fallback para sa digital receipts [6, 9].

## 2. CORE BUSINESS RULES (Based on Citizen's Charter)

Ayon sa "Ang Barangay Requests" (Citizen's Charter), ang system ay mahigpit na susunod sa manual na proseso ng barangay [10-12].

- **No E-Payments:** Lahat ng serbisyo at dokumento ay P 0.00 (Libre) [13-15]. Walang payment gateway integration [16].
- **No Digital Signatures:** Ang mga dokumento ay nangangailangan ng pisikal na pirma ng Punong Barangay at Barangay Dry Seal [13-16].
- **The "Physical Interview" Protocol:** Para sa mga dokumento tulad ng First Time Jobseeker (FTJ), Solo Parent, at Senior Citizen, may requirement na "Probing Interview" at pagsagot sa mga pisikal na form (e.g., PIS Form, Oath of Undertaking) [15, 17, 18].
    - _System Flow:_ Ang system ay gagamitin para PUMILA (Queue) at mag-text (Notify). I-u-update ng Admin ang status sa "For Appearance/Interview", magtetext ang system sa residente na pumunta sa hall, at gagawin ang manual na interview doon bago i-release [19-21].

### Supported Document Types (13 Categories):

1. Certificate of Residency [22]
2. Barangay Clearance / Good Moral [23]
3. Senior Citizen Certification [24]
4. Solo Parent Certification [25]
5. Certificate of Indigency [26]
6. Pagpapatunay sa Hanapbuhay [27]
7. Certificate of Non-Residence [28]
8. First Time Jobseeker (FTJ) [29]
9. BARC Certification [30]
10. Certificate of Low Income [31]
11. Certificate of Co-Habitation [32]
12. PWD Certification [33]
13. Special Purpose Certification [34]

## 3. WALK-IN & UNIFIED QUEUING LOGIC

- **Unified Queue Display:** Sa Admin Dashboard, makikita sa iisang table ang mga online requests (hal. `O-001`) at walk-in requests (hal. `W-001`) upang mapanatili ang pagiging patas ng pila [5, 6, 20].
- **Walk-In Architecture (Find or Create):**
    - Dahil ginagamit ang **Single Table Inheritance (STI)** sa `users` table, lahat ng walk-in ay dapat isave sa iisang table na ito [35].
    - Gagamit si Admin ng "Search First" approach. Hahanapin muna kung may account na ang residente. Kung existing, ididikit ang `W-001` queue sa lumang account. Kung bago, gagawan ng "Shadow Profile" (walang email, random password) [35].
- **Strict Contact Number Rule:** Ayon sa DATA DICTIONARY ERD, ang `contact_number` ay `AK (Unique)` at HINDI nullable [36]. Ang buong system ay nakadepende sa SMS Notification (Context Diagram). Kung ang nag-walk-in ay walang cellphone (e.g., Lolo Peter), hihingin ni Admin ang number ng asawa/anak/apo para matugunan ang ERD constraint at maabisuhan ang pamilya [36, 37].

## 4. SMS COMPLIANCE & BILLING PROTOCOL (Strict NTC Rules)

Ang system ay gumagamit ng 3rd Party SMS API Provider na may mahigpit na limitasyon [38-46].

- **Character Limits & Segmentation:**
    - Standard GSM-7: 160 characters = 1 Credit [38, 42, 44].
    - Kapag umabot ng 161+, hahatiin ito (Concatenation via UDH) sa 153-character segments. Ang 161 chars = 2 Credits [38, 44].
    - Unicode Trap: Ang paggamit ng emoji (⚠️, 📢) o special quotes (“ ”) ay magpapababa ng limit sa 70 characters per credit [38, 45].
- **Safety Buffer:** Laging maglaan ng 15-20 characters na buffer para sa `{{name}}` at `{{queue_no}}` variables para hindi lumampas sa 1 credit per text [38, 45].
- **Manual Concatenation Indicator:** Kung hindi suportado ng provider ang native concatenation, ilalagay ang `(1/2)` sa DULO ng text upang mabasa agad ng residente ang mahalagang mensahe sa lock screen [43, 47].
- **Philippine (NTC) Anti-Spam Rules:**
    1. **NO LINKS:** Bawal ang clickable URLs, bit.ly, o website links [39].
    2. **Prefix Required:** Laging dapat mag-umpisa sa "Brgy Dona Lucia:" [39].
    3. **Night Curfew:** Bawal mag-send ng non-emergency/bulk SMS mula 9:00 PM hanggang 7:00 AM [39].
- **Credit Budget:** Maximum ~13 credits ang nakalaan bawat residente (mula registration, resends, hanggang document release) [48].

## 5. UI/UX DESIGN SYSTEM (F-Pattern & 60/30/10 Rule)

- **Visual Hierarchy (60/30/10 Rule):**
    - 60% Background: `bg-white`, `bg-slate-50` (Clean & Minimal) [49].
    - 30% Structure: `text-slate-900`, `text-slate-500`, `border-slate-200` [49].
    - 10% Accents: `bg-slate-900` (Primary Actions), `text-red-600` (Danger/Timer), `text-green-700` (Verified/Success) [49].
- **Layout:** F-Pattern layout para sa mabilis na pagbabasa mula kaliwa-pakanan, pababa [50]. Mobile-first responsive design [51].
- **Tactile Feedback:** `hover:bg-slate-800` para sa desktop, at `active:scale-95` `transition-all` para sa mobile touch targets [51, 52].
- **Visual Feedback & Safety Nets:**
    - **Global Loading Screen:** Pinipigilan ang "Spam Click" na uubos sa SMS API budget gamit ang full-screen blur overlay (`bg-slate-900/60 backdrop-blur-sm`) [53, 54].
    - **30-Second Timeout:** Kung matagal ang server (e.g., Vercel hang), lilitaw ang timeout error para hindi ma-stuck ang user [55].
    - **SPA Illusion:** Paggamit ng Vanilla JS (hiwalay na `.js` files) at `window.CONFIG` injection para mag-switch ng tabs (`switchTab()`) na hindi nare-refresh ang buong page, gamit ang Laravel sessions para i-retain ang state [55, 56].

## 6. LARAVEL 12 ARCHITECTURE & SECURITY

- **Separation of Concerns:** JS logic is extracted to `public/js/` (e.g., `resident.js`, `admin.js`, `otp.js`) para ma-cache ng browser at luminis ang Blade files [56, 57].
- **Database Transactions:** Ang mga critical na save (tulad ng pag-save ng Service Request + pag-text ng SMS) ay nakabalot sa `DB::transaction()`. Kung mag-fail ang SMS, hindi mase-save ang request sa database (Data Integrity) [56].
- **Middleware Security:** Ang mga admin functions ay protektado hindi lang sa level ng UI, kundi sa level ng Routes gamit ang `AdminMiddleware` upang maiwasan ang "Authorization Leakage" [58].
- **Performance (Query Scopes):** Pagkuha ng data gamit ang Database-level filtering (SQL) sa halip na In-Memory Collection filtering (`->where()`) upang maiwasan ang Memory Exhaustion.
- **AJAX Polling with Exponential Backoff:** Ang Admin Queue Board ay may live polling na ginagamitan ng `Promise.all()`. Kung walang bagong data matapos ang 5 cycles, magba-backoff ang interval mula 10s papuntang 30s para hindi ma-spam ang server [60].
- **HMR for Cross-Platform Dev:** Naka-setup ang Vite HMR gamit ang local IP (`192.168.1.4`) para sa real-time testing ng desktop at Samsung A06 5G (Mobile) [60, 61].

## 7. DATABASE SCHEMA (Core Tables)

Ayon sa DATA DICTIONARY ERD [36, 62]:

1. **users:** Single Table Inheritance para sa admin, residente, at walk-in.
2. **document_types:** Naglalaman ng 13 Barangay Requests at requirements_description nito.
3. **service_requests:** Ang core transaction table (nakakabit sa user_id at document_type_id).
4. **attachments:** Para sa extra uploaded files bukod sa Valid ID.
5. **notification_logs:** Ang audit trail ng lahat ng SMS na ipinadala kasama ang delivery status.
6. **audit_logs:** Taga-record ng mga actions ni Admin (Approve, Reject, Update Status).
7. **announcements:** Para sa pag-broadcast ng mensahe sa lahat ng verified residents.

## 8.Language

I prefer to use Filipino and English (or Taglish)

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
    - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app\Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app\Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
