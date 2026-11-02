# UNESCO LUMINA-X | Authentication & Route Guard Security Architecture

This document details the security and authentication framework engineered for the **UNESCO LUMINA-X Global Grant & Scholarship Tracking System**. It outlines server-side session controls, role isolation mechanisms, client-side route guards, and mitigation strategies against common web vulnerabilities.

---

## 1. Threat Model & Architecture Overview

The system maintains two strictly isolated user realms sharing a common MariaDB database (`scholarship`):

```
+----------------------------------------------------------------------------------+
|                            UNESCO LUMINA-X PORTAL                                |
+----------------------------------------------------------------------------------+
             |                                                  |
             v                                                  v
   [ Scholar Workspace ]                              [ Sovereign Admin Desk ]
   Role: 'scholar'                                    Role: 'sovereign'
   Protected by: `$_SESSION['email']`                 Protected by: `$_SESSION['user']`
   UID Identity: `$_SESSION['aadh']`                  Console: `/gov/data.php`
   Heartbeat: `/auth_status.php`                      Heartbeat: `/gov/auth_status.php`
   Gate: `/login.php`                                 Gate: `/gov/login.php`
```

---

## 2. Server-Side Session Enforcement (Zero-Trust Tier)

### 2.1 Missing `exit()` Vulnerability Remediation
In legacy PHP scripts, issuing `header("location: login.php")` without an immediate `exit();` allowed unauthorized requests to continue executing the rest of the script (including database updates and sensitive data rendering). 

**Standard Enforced Across All Protected Endpoints:**
```php
session_start();
if (empty($_SESSION['email'])) {
    header("location:login.php?notice=auth_required");
    exit(); // Terminate execution immediately
}
```

### 2.2 Sovereign Authority Endpoint Guards
All administrative execution scripts inside `/gov/` are guarded to ensure unauthenticated visitors cannot inspect applicant records, delete applications, or disburse grant sanctions:
* [`gov/data.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/data.php) — Officer Command Center
* [`gov/view.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/view.php) — PDF Document & Dossier Viewer
* [`gov/delete.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/delete.php) — Application Rejection & Record Cleanup
* [`gov/email.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/email.php) — SMTP Grant Sanction Notification
* [`gov/e_mail.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/e_mail.php) — Native Mail Grant Sanction Notification

### 2.3 IDOR (Insecure Direct Object Reference) Protection
Previously, [`view.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/view.php) loaded documents based solely on `$_GET['aadhaar']`, allowing any authenticated student to view another student's documents.
* **Fix Applied:** When accessed by a scholar, the requested Aadhaar is strictly validated against the authenticated session:
```php
$current_aadh = $_SESSION['aadh'] ?? '';
$req_aadh = isset($_GET['aadhaar']) ? $_GET['aadhaar'] : $current_aadh;

if (empty($_SESSION['user']) && $req_aadh !== $current_aadh) {
    header("location:home.php");
    exit();
}
```

---

## 3. Session Probe Micro-APIs

To enable seamless SPA and asynchronous client-side guarding without breaking legacy PHP session semantics, lightweight JSON probes were deployed:

### 3.1 Scholar Session Probe ([`auth_status.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/auth_status.php))
* **Endpoint:** `GET /auth_status.php`
* **Response (Authenticated):**
```json
{
  "authenticated": true,
  "role": "scholar",
  "user_id": "123456789012",
  "name": "Jane Scholar",
  "email": "jane@example.com"
}
```
* **Response (Unauthenticated):**
```json
{
  "authenticated": false,
  "role": null
}
```

### 3.2 Sovereign Admin Session Probe ([`gov/auth_status.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/auth_status.php))
* **Endpoint:** `GET /gov/auth_status.php`
* **Response (Authenticated):**
```json
{
  "authenticated": true,
  "role": "sovereign",
  "user": "gov_officer_1",
  "timestamp": 1728956000
}
```

---

## 4. Client-Side Navigation Guard ([`lumina-auth-guard.js`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/js/lumina-auth-guard.js))

The client-side navigation guard provides reactive security and UX telemetry:

### 4.1 Features
1. **Heartbeat Session Synchronization:** Periodic probes (`checkSession()`) verify session validity asynchronously using `credentials: 'include'`.
2. **Strict Workspace Isolation:** If an active Scholar session navigates to an Administrative Sovereign page, or vice versa, the guard halts access and redirects to the appropriate workspace.
3. **Inactivity Monitoring:** Tracks DOM events (`mousemove`, `keydown`, `scroll`, `touchstart`). If inactive for 15 minutes, a 2-minute countdown modal warns the user before securely redirecting to `logout.php`.
4. **Frame-Busting / Clickjacking Mitigation:** Enforces `window.top.location = window.self.location` to prevent nesting inside hostile frames.

---

## 5. Modernized Gateways & Portals

1. [`index.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/index.php) & [`about.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/about.php): Cohesive landing and charter pages with session-awareness.
2. [`registration.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/registration.php): Futuristic enrollment portal with Aadhaar (12-digit) and phone validation, password complexity enforcement, and redirect to `login.php?notice=registered`.
3. [`login.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/login.php) & [`gov/login.php`](file:///D:/PROJECT_S/Scholarship%20Tracking%20System/Scholarship-Management-System/gov/login.php): Brand-aligned glassmorphic authentication gateways with rich status alerts.
