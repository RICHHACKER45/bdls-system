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
* **No E-Payments:** Lahat ng serbisyo at dokumento ay P 0.00 (Libre) [13-15]. Walang payment gateway integration [16].
* **No Digital Signatures:** Ang mga dokumento ay nangangailangan ng pisikal na pirma ng Punong Barangay at Barangay Dry Seal [13-16].
* **The "Physical Interview" Protocol:** Para sa mga dokumento tulad ng First Time Jobseeker (FTJ), Solo Parent, at Senior Citizen, may requirement na "Probing Interview" at pagsagot sa mga pisikal na form (e.g., PIS Form, Oath of Undertaking) [15, 17, 18]. 
    * *System Flow:* Ang system ay gagamitin para PUMILA (Queue) at mag-text (Notify). I-u-update ng Admin ang status sa "For Appearance/Interview", magtetext ang system sa residente na pumunta sa hall, at gagawin ang manual na interview doon bago i-release [19-21].

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
* **Unified Queue Display:** Sa Admin Dashboard, makikita sa iisang table ang mga online requests (hal. `O-001`) at walk-in requests (hal. `W-001`) upang mapanatili ang pagiging patas ng pila [5, 6, 20].
* **Walk-In Architecture (Find or Create):** 
    * Dahil ginagamit ang **Single Table Inheritance (STI)** sa `users` table, lahat ng walk-in ay dapat isave sa iisang table na ito [35].
    * Gagamit si Admin ng "Search First" approach. Hahanapin muna kung may account na ang residente. Kung existing, ididikit ang `W-001` queue sa lumang account. Kung bago, gagawan ng "Shadow Profile" (walang email, random password) [35].
* **Strict Contact Number Rule:** Ayon sa DATA DICTIONARY ERD, ang `contact_number` ay `AK (Unique)` at HINDI nullable [36]. Ang buong system ay nakadepende sa SMS Notification (Context Diagram). Kung ang nag-walk-in ay walang cellphone (e.g., Lolo Peter), hihingin ni Admin ang number ng asawa/anak/apo para matugunan ang ERD constraint at maabisuhan ang pamilya [36, 37].

## 4. SMS COMPLIANCE & BILLING PROTOCOL (Strict NTC Rules)
Ang system ay gumagamit ng 3rd Party SMS API Provider na may mahigpit na limitasyon [38-46].
* **Character Limits & Segmentation:**
    * Standard GSM-7: 160 characters = 1 Credit [38, 42, 44].
    * Kapag umabot ng 161+, hahatiin ito (Concatenation via UDH) sa 153-character segments. Ang 161 chars = 2 Credits [38, 44].
    * Unicode Trap: Ang paggamit ng emoji (⚠️, 📢) o special quotes (“ ”) ay magpapababa ng limit sa 70 characters per credit [38, 45].
* **Safety Buffer:** Laging maglaan ng 15-20 characters na buffer para sa `{{name}}` at `{{queue_no}}` variables para hindi lumampas sa 1 credit per text [38, 45].
* **Manual Concatenation Indicator:** Kung hindi suportado ng provider ang native concatenation, ilalagay ang `(1/2)` sa DULO ng text upang mabasa agad ng residente ang mahalagang mensahe sa lock screen [43, 47].
* **Philippine (NTC) Anti-Spam Rules:**
    1. **NO LINKS:** Bawal ang clickable URLs, bit.ly, o website links [39].
    2. **Prefix Required:** Laging dapat mag-umpisa sa "Brgy Dona Lucia:" [39].
    3. **Night Curfew:** Bawal mag-send ng non-emergency/bulk SMS mula 9:00 PM hanggang 7:00 AM [39].
* **Credit Budget:** Maximum ~13 credits ang nakalaan bawat residente (mula registration, resends, hanggang document release) [48].

## 5. UI/UX DESIGN SYSTEM (F-Pattern & 60/30/10 Rule)
* **Visual Hierarchy (60/30/10 Rule):**
    * 60% Background: `bg-white`, `bg-slate-50` (Clean & Minimal) [49].
    * 30% Structure: `text-slate-900`, `text-slate-500`, `border-slate-200` [49].
    * 10% Accents: `bg-slate-900` (Primary Actions), `text-red-600` (Danger/Timer), `text-green-700` (Verified/Success) [49].
* **Layout:** F-Pattern layout para sa mabilis na pagbabasa mula kaliwa-pakanan, pababa [50]. Mobile-first responsive design [51].
* **Tactile Feedback:** `hover:bg-slate-800` para sa desktop, at `active:scale-95` `transition-all` para sa mobile touch targets [51, 52].
* **Visual Feedback & Safety Nets:**
    * **Global Loading Screen:** Pinipigilan ang "Spam Click" na uubos sa SMS API budget gamit ang full-screen blur overlay (`bg-slate-900/60 backdrop-blur-sm`) [53, 54].
    * **30-Second Timeout:** Kung matagal ang server (e.g., Vercel hang), lilitaw ang timeout error para hindi ma-stuck ang user [55].
    * **SPA Illusion:** Paggamit ng Vanilla JS (hiwalay na `.js` files) at `window.CONFIG` injection para mag-switch ng tabs (`switchTab()`) na hindi nare-refresh ang buong page, gamit ang Laravel sessions para i-retain ang state [55, 56].

## 6. LARAVEL 12 ARCHITECTURE & SECURITY
* **Separation of Concerns:** JS logic is extracted to `public/js/` (e.g., `resident.js`, `admin.js`, `otp.js`) para ma-cache ng browser at luminis ang Blade files [56, 57].
* **Database Transactions:** Ang mga critical na save (tulad ng pag-save ng Service Request + pag-text ng SMS) ay nakabalot sa `DB::transaction()`. Kung mag-fail ang SMS, hindi mase-save ang request sa database (Data Integrity) [56].
* **Middleware Security:** Ang mga admin functions ay protektado hindi lang sa level ng UI, kundi sa level ng Routes gamit ang `AdminMiddleware` upang maiwasan ang "Authorization Leakage" [58].
* **Performance (Query Scopes):** Pagkuha ng data gamit ang Database-level filtering (SQL) sa halip na In-Memory Collection filtering (`->where()`) upang maiwasan ang Memory Exhaustion sa Vercel [59].
* **AJAX Polling with Exponential Backoff:** Ang Admin Queue Board ay may live polling na ginagamitan ng `Promise.all()`. Kung walang bagong data matapos ang 5 cycles, magba-backoff ang interval mula 10s papuntang 30s para hindi ma-spam ang server [60].
* **HMR for Cross-Platform Dev:** Naka-setup ang Vite HMR gamit ang local IP (`192.168.1.4`) para sa real-time testing ng desktop at Samsung A06 5G (Mobile) [60, 61].

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