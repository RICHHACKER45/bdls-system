# BDLS Database Reverse-Engineering & Documentation Plan
**System:** Barangay Doña Lucia Services (BDLS) Platform  
**Target:** Academic Thesis Compliance (UNF to 3NF Normalization, Standard Crow's Foot ERD, Comprehensive Data Dictionary)  
**Execution Date:** 2026-10-21  

---

## 1. Inventory of Scanned Migrations & Eloquent Models

### 1.1 Live Migration Files Scanned (`database/migrations/`)
1. `0001_01_01_000000_create_users_table.php`  
   - Creates core tables: `users`, `password_reset_tokens`, `sessions`.
2. `0001_01_01_000001_create_cache_table.php`  
   - Creates internal framework tables: `cache`, `cache_locks`.
3. `0001_01_01_000002_create_jobs_table.php`  
   - Creates queue worker tables: `jobs`, `job_batches`, `failed_jobs`.
4. `2026_03_30_162431_create_document_types_table.php`  
   - Creates `document_types` table.
5. `2026_03_30_171652_create_announcements_table.php`  
   - Creates `announcements` table.
6. `2026_03_30_171652_create_audit_logs_table.php`  
   - Creates `audit_logs` table.
7. `2026_03_30_171653_create_service_requests_table.php`  
   - Creates `service_requests` table with soft deletes.
8. `2026_03_30_171658_create_attachments_table.php`  
   - Creates `attachments` table.
9. `2026_03_30_171659_create_notification_logs_table.php`  
   - Creates `notification_logs` table.
10. `2026_06_04_134202_create_census_records_table.php`  
    - Creates `census_records` masterlist table with composite unique index.
11. `2026_08_26_012143_create_system_settings_table.php`  
    - Creates `system_settings` key-value configuration table.
12. `2026_10_14_160820_add_performance_composite_indexes_to_bdls_tables.php`  
    - Adds high-throughput performance composite indexes on `service_requests`, `audit_logs`, and `users`.
13. *Live Production Architectural Artifact:* `landing_page_contents`  
    - Established during initial CMS setup with JSON validation check constraint, maintained in live production database.

### 1.2 Eloquent Models Scanned (`app/Models/`)
- `User.php` (`App\Models\User`) -> Maps to `users`
- `ServiceRequest.php` (`App\Models\ServiceRequest`) -> Maps to `service_requests`
- `DocumentType.php` (`App\Models\DocumentType`) -> Maps to `document_types`
- `Attachment.php` (`App\Models\Attachment`) -> Maps to `attachments`
- `Announcement.php` (`App\Models\Announcement`) -> Maps to `announcements`
- `AuditLog.php` (`App\Models\AuditLog`) -> Maps to `audit_logs`
- `NotificationLog.php` (`App\Models\NotificationLog`) -> Maps to `notification_logs`
- `SystemSetting.php` (`App\Models\SystemSetting`) -> Maps to `system_settings`
- *Direct Query Builder Entities:* `census_records` (Queried via `DB::table('census_records')`), `landing_page_contents` (Direct table access).

---

## 2. Constraints, Keys, and Index Inventory

| Table Name | Primary Key | Foreign Keys (Constraint & Target) | Unique Indexes | Performance / Composite Indexes |
| :--- | :--- | :--- | :--- | :--- |
| `users` | `id` (BIGINT PK) | None | `contact_number`<br>`email` | `users_is_verified_ocr_attempts_index` (`is_verified`, `ocr_attempts`) |
| `service_requests` | `id` (BIGINT PK) | `user_id` -> `users.id` (CASCADE)<br>`document_type_id` -> `document_types.id` (CASCADE)<br>`released_by_admin_id` -> `users.id` (SET NULL) | None | `service_requests_status_created_at_index` (`status`, `created_at`)<br>`service_requests_user_id_status_index` (`user_id`, `status`)<br>`queue_number`<br>`document_type_id` |
| `document_types` | `id` (BIGINT PK) | None | None | None |
| `attachments` | `id` (BIGINT PK) | `service_request_id` -> `service_requests.id` (CASCADE) | None | `attachments_service_request_id_foreign` (`service_request_id`) |
| `announcements` | `id` (BIGINT PK) | `admin_id` -> `users.id` (CASCADE) | None | `announcements_admin_id_foreign` (`admin_id`) |
| `audit_logs` | `id` (BIGINT PK) | `admin_id` -> `users.id` (CASCADE) | None | `audit_logs_created_at_action_index` (`created_at`, `action`)<br>`audit_logs_admin_id_index` (`admin_id`) |
| `notification_logs` | `id` (BIGINT PK) | `user_id` -> `users.id` (CASCADE)<br>`service_request_id` -> `service_requests.id` (CASCADE) | None | `notification_logs_user_id_foreign`<br>`notification_logs_service_request_id_foreign` |
| `census_records` | `id` (BIGINT PK) | None (Master Reference Entity) | `census_records_unique_index` (`first_name`, `last_name`, `date_of_birth`) | Built into unique composite index |
| `system_settings` | `id` (BIGINT PK) | None | `system_settings_key_unique` (`key`) | Built into unique key index |
| `landing_page_contents` | `id` (BIGINT PK) | None | `landing_page_contents_section_name_unique` (`section_name`) | Built into unique key index |

---

## 3. Relationship Mapping Inventory

| Parent Entity | Cardinality & Modality | Child Entity | Relationship Verb Phrase | Foreign Key Column | On Delete Action |
| :--- | :---: | :--- | :--- | :--- | :--- |
| `users` (Resident) | Mandatory One to Optional Many (`\|\|` to `o{`) | `service_requests` | submits | `service_requests.user_id` | CASCADE |
| `users` (Admin) | Optional One to Optional Many (`\|o` to `o{`) | `service_requests` | releases | `service_requests.released_by_admin_id` | SET NULL |
| `document_types` | Mandatory One to Optional Many (`\|\|` to `o{`) | `service_requests` | categorizes | `service_requests.document_type_id` | CASCADE |
| `service_requests` | Mandatory One to Optional Many (`\|\|` to `o{`) | `attachments` | contains | `attachments.service_request_id` | CASCADE |
| `users` (Admin) | Mandatory One to Optional Many (`\|\|` to `o{`) | `announcements` | publishes | `announcements.admin_id` | CASCADE |
| `users` (Admin) | Mandatory One to Optional Many (`\|\|` to `o{`) | `audit_logs` | generates | `audit_logs.admin_id` | CASCADE |
| `users` (Resident) | Mandatory One to Optional Many (`\|\|` to `o{`) | `notification_logs` | receives | `notification_logs.user_id` | CASCADE |
| `service_requests` | Optional One to Optional Many (`\|o` to `o{`) | `notification_logs` | triggers | `notification_logs.service_request_id` | CASCADE |
| `census_records` | Zero-Retention Algorithmic Reference | `users` | validates identity of | Verified via OCR in memory | N/A (Business Logic Integrity) |

---

## 4. Unnormalized Form (UNF) Baseline Architecture Plan

To satisfy academic thesis panel scrutiny, the normalization process will reconstruct the historical paper-based recordkeeping system of Barangay Doña Lucia:
1. **Source Document:** "Physical Barangay Service Request Logbook & Clearance Application Sheet".
2. **Non-Atomic Attributes:**
   - `Full_Name`: Composite string ("Dela Cruz, Juan Jr. Santos") combining last, first, middle names and suffixes.
   - `Complete_Address`: Composite string ("House 45, Purok 3, Barangay Doña Lucia, Bulakan, Bulacan").
   - `Contact_Info`: Merged mobile and email contacts ("09171234567 / juan@gmail.com").
3. **Repeating Groups:**
   - Multi-document transactions (`Requested_Documents`: doc_name, processing_fee, requirements, processing_time).
   - Multi-file attachments (`Supporting_Documents`: requirement_files, proof_of_payment, ID_photo).
4. **Lineage Plan:**
   - **UNF:** Raw logbook sheet with repeating groups and compound strings.
   - **1NF:** Flattening repeating groups, enforcing atomic columns (`first_name`, `last_name`, `date_of_birth`, etc.), defining composite candidate keys.
   - **2NF:** Isolating partial functional dependencies into `USERS`, `SERVICE_REQUESTS`, `ATTACHMENTS`.
   - **3NF:** Isolating transitive functional dependencies into `DOCUMENT_TYPES`, `AUDIT_LOGS`, `NOTIFICATION_LOGS`, `CENSUS_RECORDS`, and `SYSTEM_SETTINGS`.
