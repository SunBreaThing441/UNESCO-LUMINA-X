# End-to-End System Integration & Data Flow Architecture

## 1. System Integration Overview
The **Scholarship Tracking System** integrates client-side browser forms, server-side PHP business logic, physical disk storage, an authenticated external SMTP gateway (Google Mail), and a MariaDB relational database.

This document traces the complete end-to-end data pipelines, architectural sequence diagrams, Data Flow Diagrams (DFD Levels 0, 1, and 2), lifecycle state machines, and key system security considerations.

---

## 2. Data Flow Diagrams (DFD)

### 2.1 DFD Level 0 (Context Diagram)
The Context Diagram establishes the operational boundary of the system, illustrating interactions between external actors and the central software system.

```mermaid
flowchart TD
    Student(("Student / Candidate"))
    Gov(("Government Desk Officer"))
    System["Scholarship Tracking & Management System"]
    Storage[("File System<br/>(gov/upload/)")]
    MailServer["External Mail Server<br/>(Gmail SMTP SSL:465)"]
    DB[("MariaDB Database<br/>(scholarship)")]

    Student -->|"1. Registration Credentials<br/>2. Demographics & Academics<br/>3. Document Upload Streams"| System
    System -->|"1. Session State & Form Views<br/>2. PDF Acknowledgement Receipt<br/>3. Live Sanction Status"| Student

    Gov -->|"1. Administrative Login<br/>2. Review & Sanction Decisions<br/>3. Disqualification / Rejections"| System
    System -->|"1. Candidate Applications Grid<br/>2. Dossier PDF Previews"| Gov

    System -->|"Write Uploaded Binaries"| Storage
    System -->|"Dispatch SMTP Transactional Emails"| MailServer
    MailServer -->|"Deliver Sanction / Rejection Alerts"| Student

    System <-->|"Execute CRUD Queries & Foreign Key Cascades"| DB
```

---

### 2.2 DFD Level 1 (Decomposed Functional Processes)

```mermaid
flowchart TB
    subgraph Processes ["Core Functional Processes"]
        P1["1.0 User Authentication & Onboarding"]
        P2["2.0 Multi-Step Data Intake Pipeline"]
        P3["3.0 Document Ingestion & Storage"]
        P4["4.0 Application Verification & Synthesis"]
        P5["5.0 Status Ingestion & PDF Delivery"]
        P6["6.0 Administrative Evaluation & Sanction"]
        P7["7.0 Administrative Rejection & Purge"]
    end

    subgraph DataStores ["Data Stores"]
        DS_Reg[("registration")]
        DS_Pers[("personal_details")]
        DS_Coll[("college_details")]
        DS_Host[("hostel_details")]
        DS_Bank[("bank_details")]
        DS_Attach[("attachment")]
        DS_Sanct[("sanction")]
        DS_Files[("Disk: gov/upload/")]
    end

    %% External entities
    Student(("Student"))
    GovOfficer(("Government Officer"))
    MailService["PHPMailer (SMTP)"]

    %% Flow connections
    Student -->|"Aadhaar, Password, Email"| P1
    P1 -->|"INSERT"| DS_Reg
    P1 -->|"Initialize (0, 0)"| DS_Sanct

    Student -->|"Personal, Academic, Bank Data"| P2
    P2 -->|"INSERT"| DS_Pers
    P2 -->|"INSERT"| DS_Host
    P2 -->|"INSERT"| DS_Coll
    P2 -->|"INSERT"| DS_Bank

    Student -->|"Multipart Document Streams"| P3
    P3 -->|"Write Binary Files"| DS_Files
    P3 -->|"Persist File URIs"| DS_Attach

    DS_Pers & DS_Coll & DS_Host & DS_Bank & DS_Attach --> P4
    P4 -->|"Render Comprehensive Preview"| Student
    P4 -->|"FPDF Stream"| P5
    P5 -->|"Printable Acknowledgement"| Student

    GovOfficer -->|"Evaluate Candidate"| P6
    DS_Pers & DS_Sanct --> P6
    P6 -->|"UPDATE Sanction Amounts"| DS_Sanct
    P6 -->|"Trigger Approval Alert"| MailService
    MailService -->|"Deliver Email"| Student

    GovOfficer -->|"Reject Candidate"| P7
    P7 -->|"Cascade DELETE personal_details"| DS_Pers
    P7 -->|"Trigger Rejection Alert"| MailService
```

---

### 2.3 DFD Level 2 (Sanction & Email Dispatch Engine)

```mermaid
flowchart TD
    OfficerAction["Officer Clicks 'Accept' (gov/email.php)"] --> ReadData["Query personal_details & sanction by Aadhaar"]
    
    subgraph DecisionMatrix ["Eligibility & Calculation Rule Matrix"]
        CheckCategory{"Reservation Category?"}
        CheckHostel1{"Hostel = 'yes'?"}
        CheckHostel2{"Hostel = 'yes'?"}

        Rule1["Category: 2A/2B/3A/3B<br/>College: ₹20,000 | Hostel: ₹10,000"]
        Rule2["Category: 2A/2B/3A/3B<br/>College: ₹20,000 | Hostel: ₹0"]
        Rule3["Category: Cat-1/SC/ST<br/>College: ₹65,000 | Hostel: ₹10,000"]
        Rule4["Category: Cat-1/SC/ST<br/>College: ₹65,000 | Hostel: ₹0"]
    end

    ReadData --> CheckCategory
    CheckCategory -- "2A, 2B, 3A, 3B" --> CheckHostel1
    CheckCategory -- "Cat-1, SC, ST" --> CheckHostel2
    
    CheckHostel1 -- Yes --> Rule1
    CheckHostel1 -- No --> Rule2
    CheckHostel2 -- Yes --> Rule3
    CheckHostel2 -- No --> Rule4

    Rule1 & Rule2 & Rule3 & Rule4 --> UpdateSanct["Execute SQL UPDATE on sanction table"]
    
    subgraph SMTPDispatch ["SMTP Dispatch Pipeline"]
        InitPHPMailer["Initialize PHPMailer & configure Host, Port 465, SSL"]
        SetPayload["Attach recipient email, subject, formatted sanction message"]
        SendSocket["Transmit through Gmail SMTP SSL socket"]
    end

    UpdateSanct --> InitPHPMailer
    InitPHPMailer --> SetPayload
    SetPayload --> SendSocket
    SendSocket --> Complete["Alert 'Sent Successfully' & Redirect to data.php"]
```

---

## 3. Comprehensive Sequence Diagrams

### 3.1 End-to-End Student Application Lifecycle

```mermaid
sequenceDiagram
    autonumber
    actor Student
    participant Browser
    participant Reg as registration.php
    participant Login as login.php
    participant AppWizard as Multi-Step Form (personal -> college -> bank -> attachment)
    participant Storage as gov/upload/
    participant DB as MariaDB (scholarship)
    participant Ack as acknowledgement.php

    Student->>Browser: Fill Registration Form (Aadhaar, Phone, Email, Password)
    Browser->>Reg: POST /registration.php
    Reg->>DB: Check if Aadhaar / Email duplicate exists
    Reg->>DB: INSERT INTO registration (with MD5 hash)
    Reg->>DB: INSERT INTO sanction (collegescholarship=0, hostelscholarship=0)
    Reg-->>Browser: Redirect to login.php

    Student->>Browser: Enters Email and Password
    Browser->>Login: POST /login.php
    Login->>DB: SELECT * FROM registration WHERE email AND password match
    Login->>Browser: Initialize PHP Session & Redirect to home.php

    Student->>Browser: Navigates through Personal, Hostel, College, Bank screens
    Browser->>AppWizard: POST demographic, institution, and banking payloads
    AppWizard->>DB: INSERT INTO personal_details, hostel_details, college_details, bank_details

    Student->>Browser: Selects 7 mandatory documents and submits
    Browser->>AppWizard: POST multipart/form-data (attachment.php)
    AppWizard->>Storage: Move files to gov/upload/IMG-[uniqid].[ext]
    AppWizard->>DB: INSERT INTO attachment (store file paths)
    AppWizard-->>Browser: Redirect to preview.php

    Browser->>Browser: Render summary table & load uploaded images from gov/upload/
    Student->>Browser: Clicks "Download Acknowledgement"
    Browser->>Ack: Request PDF generation
    Ack->>DB: Fetch student profile from personal_details, college_details, hostel_details
    Ack-->>Browser: Stream FPDF binary document for viewing/saving
```

---

### 3.2 Administrative Review, Sanction, and Automated Disbursement Pipeline

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Government Officer
    participant GovUI as gov/data.php
    participant Dossier as gov/view.php
    participant Sanction as gov/email.php
    participant DB as MariaDB
    participant Mailer as PHPMailer (Gmail SMTP)
    actor Student

    Admin->>GovUI: Opens Admin Dashboard (gov/data.php)
    GovUI->>DB: SELECT * FROM personal_details
    DB-->>GovUI: Return all submitted applicant dossiers
    GovUI-->>Admin: Render responsive candidate table

    Admin->>GovUI: Clicks "VIEW" icon (gov/view.php?aadhaar=...)
    GovUI->>Dossier: GET /gov/view.php
    Dossier->>DB: Query personal_details, college_details, hostel_details, attachment
    Dossier-->>Admin: Stream synthesized FPDF candidate dossier with embedded photo

    Admin->>GovUI: Clicks "ACCEPT" icon (gov/email.php?aadhaar=...)
    GovUI->>Sanction: GET /gov/email.php
    Sanction->>DB: Query applicant category and hostel flag
    Sanction->>Sanction: Calculate scholarship values via decision matrix
    Sanction->>DB: UPDATE sanction SET collegescholarship=..., hostelscholarship=...
    Sanction->>Mailer: Send notification email to applicant
    Mailer-->>Student: Deliver "Scholarship Sanctioned" alert
    Sanction-->>Admin: Display alert "Sent Successfully" & redirect to data.php
```

---

### 3.3 Administrative Candidate Rejection & Cascading Database Purge

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Government Officer
    participant GovUI as gov/data.php
    participant Purge as gov/delete.php
    participant DB as MariaDB
    actor Student

    Admin->>GovUI: Clicks "REJECT" icon (gov/delete.php?aadhaar=...)
    GovUI->>Purge: GET /gov/delete.php
    Purge->>DB: SELECT email, name FROM personal_details WHERE aadhaar = '$aa'
    Purge->>Student: Dispatch rejection email notice via PHP mail()
    Purge->>DB: DELETE FROM personal_details WHERE aadhaar = '$aa'
    Note over DB: Foreign Key CASCADE triggers automatically:<br/>1. Purges college_details<br/>2. Purges hostel_details<br/>3. Purges bank_details<br/>4. Purges attachment
    Purge-->>GovUI: 302 Redirect to data.php
```

---

## 4. Application Lifecycle State Machine

```mermaid
stateDiagram-v2
    [*] --> UNREGISTERED

    UNREGISTERED --> REGISTERED : Candidate registers account<br/>(registration.php)
    REGISTERED --> PROFILE_IN_PROGRESS : Fills personal details<br/>(personal.php)
    
    PROFILE_IN_PROGRESS --> HOSTEL_RECORDED : Fills hostel form<br/>(Hostel = YES)
    PROFILE_IN_PROGRESS --> ACADEMICS_RECORDED : Fills college form<br/>(Hostel = NO)
    HOSTEL_RECORDED --> ACADEMICS_RECORDED : Submits hostel details
    
    ACADEMICS_RECORDED --> BANK_RECORDED : Submits banking credentials<br/>(bank.php)
    BANK_RECORDED --> DOCUMENTS_ATTACHED : Uploads 7 files<br/>(attachment.php)
    
    DOCUMENTS_ATTACHED --> SUBMITTED : Application finalized<br/>(preview.php & acknowledgement.php)
    
    state AdministrativeReview {
        SUBMITTED --> UNDER_VERIFICATION : Desk Officer opens dossier
        UNDER_VERIFICATION --> SANCTIONED : Desk Officer accepts<br/>(gov/email.php)
        UNDER_VERIFICATION --> REJECTED : Desk Officer rejects<br/>(gov/delete.php)
    }

    SANCTIONED --> DISBURSED : Scholarship amounts reflected in sanction table
    REJECTED --> PROFILE_IN_PROGRESS : Candidate re-submits corrected profile
```

---

## 5. Security & Architectural Enhancements

While functional, several legacy patterns in the implementation can be fortified for production enterprise deployments:

### 5.1 SQL Injection Prevention
- **Current Pattern**: Direct string interpolation in procedural SQL queries:
  ```php
  $query = "SELECT * FROM `registration` where email = '$em' and password = '$pass'";
  ```
- **Recommended Hardening**: Implement parameterized prepared statements via PDO or MySQLi:
  ```php
  $stmt = $connect->prepare("SELECT * FROM registration WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  ```

### 5.2 Password Storage Cryptography
- **Current Pattern**: MD5 hashing (`md5($_POST['pwd'])`), vulnerable to pre-computed rainbow table attacks.
- **Recommended Hardening**: Migrate to native PHP password hashing utilizing **BCRYPT** or **Argon2id**:
  ```php
  $hashedPassword = password_hash($rawPassword, PASSWORD_BCRYPT, ['cost' => 12]);
  // Verification:
  if (password_verify($enteredPassword, $hashedPassword)) { /* Validated */ }
  ```

### 5.3 File Upload Armor
- **Current Pattern**: Only checks file extension strings (`pathinfo()`).
- **Recommended Hardening**:
  1. Inspect MIME magic bytes via `finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmp_path)`.
  2. Block execution in the upload folder by configuring `.htaccess`:
     ```apache
     <FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|cgi)$">
         Deny from all
     </FilesMatch>
     ```
  3. Enforce maximum file size bounds (e.g., 2MB).

### 5.4 Credential & Secret Isolation
- **Current Pattern**: Hardcoded Google App Passwords and database credentials in source files.
- **Recommended Hardening**: Extract secrets into an ignored `.env` file using a loader like `vlucas/phpdotenv`.
