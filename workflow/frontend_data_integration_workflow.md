# UNESCO LUMINA-X | Frontend Data Integration Architecture

This document specifies the HTTP client configuration, data dispatch pipelines, multipart file streaming, FPDF binary stream decoding, and administrative SMTP sanction bindings.

---

## 1. Universal HTTP Client: `LuminaHttpClient` (`js/lumina-http-client.js`)

The `LuminaHttpClient` bridges modern asynchronous frontend components with legacy procedural PHP endpoints without disrupting native PHP `$_SESSION` semantics or database schemas.

### 1.1 Core Configuration & Protocols
* **Session Persistence:** Configured with `credentials: 'include'` (and `withCredentials: true` for XHR uploads) to ensure the `PHPSESSID` cookie is transmitted across all asynchronous requests.
* **Header Negotiation:** Automatically sets `X-Requested-With: XMLHttpRequest` and appropriate `Content-Type` headers per transmission mode.
* **Error Handling & Feedback:** Integrated cybernetic toast notification system (`luminaHttp.toast(message, type)`) and modal loading overlays.

---

## 2. Dispatch Channels & Communication Modes

### 2.1 Form-Urlencoded Mode (`postForm(url, data)`)
Used for parameter serialization when communicating with standard form-handling endpoints:
* **Target Endpoints:**
  - [`registration.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/registration.php) — Scholar registration & Aadhaar/Email validation
  - [`login.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/login.php) — Scholar credentials check
  - [`gov/login.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/login.php) — Sovereign officer authentication
  - Stepper Stages 1–4 ([`personal.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/personal.php), [`hostel.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/hostel.php), [`college.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/college.php), [`bank.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/bank.php))
* **Mechanism:** Converts objects and `FormData` into `URLSearchParams`, automatically generating `application/x-www-form-urlencoded` payloads that populate PHP's native `$_POST` superglobal.

### 2.2 Multipart / Form-Data Stream Engine (`uploadFiles(url, formData, onProgress)`)
Used for streaming binary files in Stage 5 ([`attachment.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/attachment.php)).
* **Target Directory:** Persisted to server filesystem under [`gov/upload/`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/upload/).
* **The 7 Mandatory / Conditional File Upload Streams:**
  1. `passport` — Passport Photograph (JPEG/PNG)
  2. `aadh` — Aadhaar UID Card Document (JPEG/PNG)
  3. `passbook` — Bank Passbook Front Page (JPEG/PNG)
  4. `castecertificate` / `income` — Caste & Income Certificate (JPEG/PNG)
  5. `cfeesrecipt` / `fees` — College Admission Fee Receipt (JPEG/PNG)
  6. `markssheet` — Qualifying Exam Marksheet (JPEG/PNG)
  7. `hfees` / `hostelfees` — Hostel Room / Boarding Fee Receipt (Conditional for Hostel Residents)
* **Real-Time Stream Telemetry:** Leverages `xhr.upload.onprogress` to compute upload percentages in real time, updating the UI action button dynamically (`Uploading Streams 45%... 100%`).
* **Relational Persistence:** Automatically maps uploads into the `attachment` table columns (`passportimg`, `aadhaarimg`, `passbookimg`, `incomeimg`, `feesimg`, `markssheetimg`, `hostelfeesimg`).

---

## 3. Binary FPDF Document Stream Decoding

Both [`acknowledgement.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/acknowledgement.php) and [`gov/view.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/view.php) return raw PDF binary streams (`$pdf->Output()`).

### 3.1 Interactive In-App PDF Modal Viewer (`renderPdfModal(url, title, postData)`)
* Fetches the binary stream with `Accept: application/pdf`.
* Generates an in-memory `Blob` of MIME type `application/pdf` and creates an Object URL (`URL.createObjectURL(blob)`).
* Displays an in-app viewer modal featuring:
  - Responsive embedded frame preview (`#toolbar=1`)
  - Direct Print trigger (`iframe.contentWindow.print()`)
  - Direct PDF Download link (`<a download="Dossier.pdf">`)
  - Automatic memory garbage collection via `URL.revokeObjectURL(blobUrl)` on modal dismissal.
* **Integrated Touchpoints:**
  - **Scholar Acknowledgement Receipt:** Available on Stage 6 of [`personal.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/personal.php) and Card 3 in [`home.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/home.php).
  - **Sovereign Applicant Dossier Review:** Triggered via the **View** glyph button in [`gov/data.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/data.php).

---

## 4. Sovereign Grant Sanction & PHPMailer SMTP Dispatch

In [`gov/data.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/data.php), the administrative **Accept** trigger is bound to [`gov/email.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/email.php) via `luminaHttp.dispatchSanction(aadhaar, candidateInfo)`.

### 4.1 Execution Sequence:
1. **Officer Confirmation:** Displays confirmation modal summarizing candidate category, college tuition sanction (₹20,000 or ₹65,000), and hostel stipend (+₹10,000).
2. **Cybernetic Loader:** Renders full-screen sovereign progress overlay during SMTP dispatch.
3. **Database Ledger Update:** Executes procedural query updating `sanction` (`collegescholarship`, `hostelscholarship`).
4. **PHPMailer SMTP Dispatch:** Initiates secure SMTP handshake with Google Mail relay (`smtp.gmail.com:465`, SSL) and delivers official transactional confirmation to the student's email.
5. **Dynamic DOM Telemetry:** Updates the applicant card / table row in real time:
   - Status badge transitions to **Sanctioned** (`status-sanctioned`)
   - Allocated grant amount is refreshed
   - Accept glyph button transitions to disabled **Authorized** state
   - Top counter (`#metric-sanctioned-count`) increments without a page reload.
