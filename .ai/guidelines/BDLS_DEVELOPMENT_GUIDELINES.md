# BDLS Development Guidelines (React + Inertia.js Architecture)

**AI INSTRUCTION / LANGUAGE CONTEXT:** STRICTLY ENGLISH. All code comments, AI-generated code, and system modifications must adhere to these English guidelines to prevent multi-language confusion in the codebase.

This document serves as the primary master guideline for all system modifications, AI prompts, and code integration within the Barangay Doña Lucia Services (BDLS) platform. 

The system has fully migrated to a Single Page Application (SPA) architecture. The use of deprecated technologies, such as Vanilla JS in `public/js/` or traditional Laravel Blade views (except for the master root file), is strictly prohibited.

---

## 1. Frontend Architecture (React 18 + Inertia.js)
The entire user interface is powered by React. No full-page reloads should occur when navigating between pages.

*   **File Locations:** All UI components must be written inside `resources/js/Pages/` (for actual views) and `resources/js/Layouts/` (for layout wrappers).
*   **Routing and Links:** Instead of standard HTML `<a>` tags, strictly use the `<Link>` component from `@inertiajs/react` to preserve state and ensure seamless transitions. Use `ziggy-js` to call Laravel routes directly inside React.
*   **Form Management:** Avoid manual `fetch` or `axios` for standard form submissions. Use the `useForm` hook from Inertia.js (`const { data, setData, post, processing, errors } = useForm({...})`) as it automatically catches and binds backend validation errors to the frontend.
*   **Styling (Tailwind CSS):** The entire design system is built on Tailwind CSS. Because we are using React, always use `className=` instead of `class=`.

---

## 2. Backend and Security Policies (The Laravel Way)
The backend is powered by Laravel 11. All security protocols must be strictly followed to protect resident data privacy.

*   **Zero-Retention Policy (Automated ID Verification):** The system no longer saves Valid ID or Selfie image files to the local server or database to prevent data breaches. Images are temporarily held in memory, passed to the Google Cloud Vision API for OCR text extraction, matched against the `census_records` table, and immediately discarded.
*   **Strict Password Policy:** Simple passwords are not allowed. All passwords (during registration or updates) must contain a minimum of 8 characters, including at least one uppercase letter, one lowercase letter, one number, and **one symbol** (e.g., `Juan!1234`).
*   **SMS & OTP Rate Limiting:** All OTP requests and SMS broadcast actions are protected by a Rate Limiter (e.g., a 60-second cooldown timer and a 3-strike IP block) to prevent spamming and conserve API credits.
*   **Live Updates (Websockets):** For real-time queue or dashboard updates, the system utilizes Laravel Reverb and React `window.Echo` listeners. Any state change on the dashboard must be refreshed via silent polling using `router.reload({ only: [...], preserveScroll: true, preserveState: true })` to prevent disrupting the user's current screen activity.

---

## 3. The Blueprint for Atomic Code Commits
To maintain a clean and highly traceable Git history, the following rules must be strictly adhered to before committing any code:

1.  **One Single Logical Task per Commit:** A commit must contain only the files modified for one specific feature, bug fix, or refactor. Do not bundle unrelated modifications together.
2.  **Strict Code Staging:** Only stage (`git add <file>`) the exact files required for the target task. Do not use `git add .` if there are unrelated drafts or experiments in the workspace.
3.  **Runnable Code Guarantee:** The system must successfully compile and run without errors before committing. Never commit broken code or syntax errors.
4.  **Formal Commit Prefixes:** Always start the commit message with one of the following standard prefixes:
    *   `feat:` For a new feature or backend logic addition (e.g., `feat: inject Laravel Echo listener to AdminDashboard`)
    *   `fix:` For resolving a bug (e.g., `fix: enforce password symbol requirement in regex`)
    *   `refactor:` For restructuring existing code without changing its external behavior
    *   `style:` For UI changes, Tailwind CSS class modifications, or code formatting
