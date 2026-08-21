<laravel-boost-guidelines>
=== .ai/BDLS_DEVELOPMENT_GUIDELINES rules ===

### 🚀 MASTER TASK: UPDATE CUSTOM DEVELOPMENT GUIDELINES (GEMINI ARCHITECTURE)

Hey Antigravity Dev Agent! We are developing **Barangay Doña Lucia Services (BDLS)**, a low-latency SPA monolith using **Laravel 12**, **React 19**, and **Inertia.js v3**.

This system's master architecture and core logic are designed and verified by **Gemini Notebook**. To leverage our `laravel/boost` compilation workflow, we need to update our custom guidelines file located at `.ai/BDLS_DEVELOPMENT_GUIDELINES`. This file is consumed by the autoconfigure compiler to generate our root `AGENTS.md` file.

Please overwrite or create the file strictly at `.ai/BDLS_DEVELOPMENT_GUIDELINES` with the clean, production-ready specifications below as instructed by **Gemini**.

---

### 📝 WRITE THIS CONTENT INTO `.ai/BDLS_DEVELOPMENT_GUIDELINES`

# BDLS Development Guidelines (React + Inertia.js Architecture)

**AI INSTRUCTION / LANGUAGE CONTEXT:** STRICTLY ENGLISH. All code comments, AI-generated code, and system modifications must adhere to these English guidelines to prevent multi-language confusion in the codebase.

This document serves as the primary master guideline for all system modifications, AI prompts, and code integration within the Barangay Doña Lucia Services (BDLS) platform.

---

## 1. Frontend Architecture (React 19 + Inertia.js v3)

- **File Locations:** All UI components must be written inside `resources/js/Pages/` (for actual views) and `resources/js/Layouts/` (for layout wrappers).
- **SPA Routing:** Instead of standard HTML `<a>` tags, strictly use the `<Link>` component from `@inertiajs/react` to preserve state. Use `route()` from Ziggy-js to call Laravel routes directly inside React.
- **Form Management:** Avoid manual `fetch` or `axios` for standard form submissions. Use the `useForm` hook from Inertia.js (`const { data, setData, post, processing, errors } = useForm({...})`) as it automatically catches and binds backend validation errors to the frontend.
- **Styling (Tailwind CSS):** Use Tailwind CSS v4 utility classes. Because we are using React, always use `className=` instead of `class=`.

---

## 2. Backend & Security Policies (The Laravel Way)

- **Zero-Retention Policy (Automated ID Verification):** Resident signups capture a Valid ID. Google Cloud Vision OCR API is used to extract the name and match it against the Census Masterlist (`census_records`). Selfie/ID photos must be processed IN-MEMORY only and immediately discarded—NEVER store ID/selfie files in public directories or save file paths in the database.
- **Cloudflare Turnstile CAPTCHA:** Mandatory shield on four (4) critical forms to prevent API credit exhaustion and spambots:
    1. Resident Signup Form
    2. User Login Form
    3. Service Request Submission Form
    4. Forgot Password Form
- **Strict Password Policy:** Simple passwords are not allowed. All passwords (during registration or updates) must contain a minimum of 8 characters, including at least one uppercase letter, one lowercase letter, one number, and one symbol (e.g., `Juan!1234`).
- **Secure File Storage:** Private document attachments (GCash receipts, clearance requirements) must be stored strictly in the private storage directory (`storage/app/private/`). Public access is blocked; files must be streamed securely through `FileController@serveSecureFile` with directory traversal protection and strict ownership/admin authorization checks.

---

## 3. NTC Compliance & SMS Services (SmsService.php)

- **Link Blocker Policy:** SMS messages must NEVER contain URLs/links. The system enforces this via regex checks (`not_regex:/(http|https|www\.)/i`) to prevent blocking of our shared API gateway.
- **Night Curfew Restriction:** Mass SMS announcements/text blasts are strictly prohibited during curfew hours (9:00 PM to 7:00 AM).
- **Unicode Sanitizer:** Automatically strip emojis and smart quotes before sending to prevent character set inflation (keeping standard messages under 160 characters).
- **Unverified Block:** Prevent sending transactional updates to numbers that haven't verified their contact via OTP to save API credits.

---

## 4. The Blueprint for Atomic Code Commits

To maintain a clean and highly traceable Git history, the following rules must be strictly adhered to:

1. **One Single Logical Task per Commit:** A commit must contain only the files modified for one specific feature, bug fix, or refactor. Do not bundle unrelated modifications together.
2. **Strict Code Staging:** Only stage (`git add <file>`) the exact files required for the target task. Do not use `git add .` if there are unrelated drafts or experiments in the workspace.
3. **Runnable Code Guarantee:** The system must successfully compile and run without errors before committing. Never commit broken code or syntax errors.
4. **Formal Commit Prefixes:** Always start the commit message with one of the following standard prefixes:
    - `feat:` For a new feature or backend logic addition
    - `fix:` For resolving a bug
    - `refactor:` For restructuring existing code without behavior changes
    - `style:` For UI changes, Tailwind CSS class modifications, or formatting

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- tightenco/ziggy (ZIGGY) - v2
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- @inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- laravel-echo (ECHO) - v2
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4

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

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `bun run build`, `bun run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

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

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

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

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `bun run build` or ask the user to run `bun run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

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

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

## Repository Scanning Rules

- You MUST strictly ignore files and directories listed in the `.ignore` (or `.gitignore`) file.
- NEVER index, read, or summarize paths like `/vendor/`, `/node_modules/`, `/storage/`, or `.env`.

</laravel-boost-guidelines>
