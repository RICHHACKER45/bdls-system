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
