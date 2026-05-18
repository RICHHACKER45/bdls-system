# BDLS React + Inertia.js Migration Guidelines

## 1. Role and Objective
Act as an Enterprise Senior Full-Stack Engineer specializing in Laravel 11, React 18, Inertia.js, and Tailwind CSS.
Your objective is to migrate my existing Laravel Blade + Vanilla JS application into a React SPA using Inertia.js.

## 2. Directory Operations (Deletions & Exemptions)
**WHAT TO DELETE/MERGE:**
- Delete all files inside `public/js/` (`admin.js`, `resident.js`, `signup.js`, `otp.js`, `guest-cleanup.js`). Their logic MUST be migrated to React components.
- Delete `resources/views/welcome.blade.php`.
- Delete the entire `resources/views/auth/` directory.
- Delete `resources/views/resident/dashboard.blade.php` and `resources/views/resident/layouts/app.blade.php`.
- Delete `resources/views/admin/admin-panel.blade.php` and `resources/views/admin/layouts/admin.blade.php`.

**CRITICAL EXEMPTIONS (DO NOT TOUCH):**
- DO NOT touch or delete `resources/views/admin/pdf/analytics.blade.php` and `resources/views/admin/pdf/release_logbook.blade.php`. The backend `Barryvdh\DomPDF` requires them.
- DO NOT touch `resources/views/emails/bdls_notification.blade.php`.
- DO NOT touch `resources/views/errors/` directory.
- DO NOT alter Laravel Models, Migrations, Database architecture, or API routing logic.

## 3. Base Configuration & Setup
1. **Packages:** Assume `@inertiajs/react`, `react`, `react-dom`, `@vitejs/plugin-react` are installed.
2. **Vite:** Update `vite.config.js` to use the React plugin and set the input to `resources/js/app.jsx`.
3. **Master Blade:** Create `resources/views/app.blade.php` containing `@inertiaHead`, `@viteReactRefresh`, and `@vite(['resources/css/app.css', 'resources/js/app.jsx'])`.
4. **React Engine:** Create `resources/js/app.jsx` using `createInertiaApp` and configure it to resolve `.jsx` components from `resources/js/Pages/**/*.jsx`. Include `ziggy-js` for routing.

## 4. Components Architecture (What to Create)
Create the following React structures by combining the old Blade HTML and Vanilla JS logic:

- **Layouts (`resources/js/Layouts/`):**
  - `AdminLayout.jsx` (Migrated from `admin.blade.php` + sidebar logic from `admin.js`).
  - `ResidentLayout.jsx` (Migrated from `resident/layouts/app.blade.php` + sidebar logic from `resident.js`).

- **Pages (`resources/js/Pages/`):**
  - `Welcome.jsx`
  - `Auth/Login.jsx`, `Auth/Signup.jsx`, `Auth/Otp.jsx`, `Auth/ForgotPassword.jsx`, `Auth/ResetPassword.jsx`.
  - `Resident/Dashboard.jsx`
  - `Admin/Dashboard.jsx`

## 5. Coding Standards & Hook Translations
- **Tailwind:** Retain all Tailwind utility classes but convert `class=` to `className=`.
- **Forms:** Replace `<form action="...'>` with `useForm()` hook from `@inertiajs/react`.
- **Links:** Replace `<a href="...'>` with `<Link href="...">` to prevent page reloads.
- **Modals & Tabs:** Convert old vanilla JS functions (`switchTab`, `openModal`, `classList.remove('hidden')`) into React state using `const [activeTab, setActiveTab] = useState(...)`.
- **AJAX Polling:** Translate the `setInterval` functions from the old `admin.js` and `resident.js` into React `useEffect` hooks. They should fetch data via `axios` or `fetch` from the existing API endpoints (`route('admin.api.pending_count')`, etc.) and update the state.
- **Specific Feature:** In `Auth/Signup.jsx`, implement the 5MB file validation using `onChange` and the `FileReader` Blob method exactly as it was in `signup.js`. Implement `sessionStorage` draft saving.

## 6. Controller Updates
Modify `HomeController.php`, `AuthController.php`, `AdminDashboardController.php`, and `ServiceRequestController.php` to replace all instances of `return view(...)` with `return Inertia::render(...)`.