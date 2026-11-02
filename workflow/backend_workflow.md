# Backend Workflow & Server Logic Architecture

## 1. Overview & Backend Technology Stack
The backend subsystem of the **Scholarship Tracking System** runs on **PHP 8.x** utilizing the procedural **MySQLi** extension for relational database interactions, native **PHP Sessions** for state continuity across HTTP requests, **PHPMailer** for encrypted SMTP transmission, and **FPDF** for runtime document compilation.

### Core Backend Responsibilities
1. **Authentication & Session Orchestration**: Gatekeeping protected endpoints, verifying MD5 credentials, managing student and administrator session lifetimes.
2. **Form Data Ingestion & Sanitization**: Parsing POST payloads across multi-step wizard stages and enforcing relational integrity constraints.
3. **Multipart File Processing Engine**: Validating uploaded file types, assigning cryptographic unique identifiers, and writing binaries to persistent disk storage (`gov/upload/`).
4. **Scholarship Sanction Business Rules Engine**: Evaluating applicant demographic criteria (category, economic status, hostel residency) to compute college and hostel scholarship allocations.
5. **Transactional Messaging Subsystem**: Automated email notifications via PHPMailer (authenticated SMTP over SSL on port 465) and native PHP `mail()`.
6. **Automated Document Synthesis**: Assembling relational student dossiers into standardized PDF certificates using FPDF.

---

## 2. Server Architecture & Request Lifecycle

```mermaid
flowchart TD
    ClientReq["HTTP Client Request (GET / POST)"] --> WebServer["Web Server (Apache / PHP-FPM)"]
    WebServer --> Connect["Database Connector (connect.php / gov/connect.php)"]
    
    subgraph ExecutionLifecycle ["PHP Execution Lifecycle"]
        SessionCheck{"Session Active?<br/>($_SESSION['email'] / ['user'])"}
        AuthGuard["Redirect to login.php<br/>header('location:login.php')"]
        MethodCheck{"Request Method"}
        
        GetHandler["Query Data via MySQLi & Render HTML/FPDF"]
        PostHandler["Parse $_POST & $_FILES"]
        
        ValidateInput{"Valid Payload?"}
        ShowError["Return Error Banner to View"]
        
        DBOperation["Execute MySQLi Query<br/>(INSERT / UPDATE / DELETE)"]
        FileOp["Process Multipart Files to gov/upload/"]
        
        EmailOp["Dispatch SMTP Notification<br/>(PHPMailer / Gmail SSL:465)"]
        RedirectNext["Execute 302 Redirect to Next Stage"]
    end

    Connect --> SessionCheck
    SessionCheck -- No --> AuthGuard
    SessionCheck -- Yes --> MethodCheck
    
    MethodCheck -- GET --> GetHandler
    MethodCheck -- POST --> PostHandler
    
    PostHandler --> ValidateInput
    ValidateInput -- Failed --> ShowError
    ValidateInput -- Passed --> DBOperation
    
    DBOperation --> FileOp
    DBOperation --> EmailOp
    DBOperation --> RedirectNext
```

---

## 3. Database Connection Architecture

Database connections are established via two dedicated configuration scripts:
1. `connect.php` (Root directory, utilized by Student Portal modules)
2. `gov/connect.php` (Administrative subfolder, utilized by Government Portal modules)

### Connection Parameters
```php
$ServerName = 'localhost';
$UserName   = 'root';
$Password   = '';
$dbName     = 'scholarship';  

$connect = mysqli_connect($ServerName, $UserName, $Password, $dbName);

if(!$connect) {
    echo "Connection failed";
}
```

> [!NOTE]
> Database operations throughout the codebase utilize procedural MySQLi functions: `mysqli_query()`, `mysqli_fetch_assoc()`, `mysqli_num_rows()`, and `mysqli_fetch_array()`.

---

## 4. Authentication, Registration, & Session Management

```mermaid
sequenceDiagram
    actor Candidate
    participant Reg as registration.php
    participant DB as MariaDB (scholarship)
    participant Login as login.php
    participant Session as $_SESSION

    Candidate->>Reg: POST /registration.php (aadhaar, name, ph_no, email, pwd, cpwd)
    Reg->>DB: Check if Aadhaar or Email exists
    alt Record Exists
        DB-->>Reg: Aadhaar count > 0 OR Email count > 0
        Reg-->>Candidate: Render error: "Aadhaar / Email already exists"
    else Inputs Valid & Passwords Match
        Reg->>Reg: Compute MD5 hash: md5($pwd)
        Reg->>DB: INSERT INTO registration (aadhaar, name, number, email, password)
        Reg->>DB: INSERT INTO sanction (aadhaar, collegescholarship, hostelscholarship) VALUES ('$aadh', 0, 0)
        Reg-->>Candidate: 302 Redirect -> login.php
    end

    Candidate->>Login: POST /login.php (email, pwd)
    Login->>DB: SELECT * FROM registration WHERE email = '$em' AND password = '$pass'
    alt Credentials Match
        DB-->>Login: Return user record
        Login->>Session: Set $_SESSION['email'], $_SESSION['aadh'], $_SESSION['name'], $_SESSION['phone']
        Login-->>Candidate: 302 Redirect -> home.php
    else Auth Failed
        Login-->>Candidate: Display error message
    end
```

### Route Guarding Pattern
Every internal student script (`personal.php`, `college.php`, `bank.php`, `attachment.php`, `preview.php`, `scholarship_status.php`) enforces route protection using session verification:
```php
session_start();
if(empty($_SESSION['email'])) {         
    header("location:login.php"); 
    exit();
}
```

---

## 5. Document Ingestion & Storage Subsystem (`attachment.php`)

Candidate file uploads are streamed as binary payloads through `attachment.php` and persisted to the physical file system under `gov/upload/`.

```mermaid
flowchart TD
    UploadForm["POST Multipart Form (7 File Streams)"] --> ReadFiles["Read $_FILES Global Superglobal"]
    
    subgraph FileValidationLoop ["Per-File Validation & Ingestion Engine"]
        CheckErr{"$_FILES[field]['error'] === 0?"}
        RejectErr["Throw File Upload Exception"]
        
        ExtractExt["Extract Extension via pathinfo()"]
        CheckWhitelist{"Extension in ['jpg', 'jpeg', 'png']?"}
        RejectExt["Reject Unsupported MIME/Type"]
        
        GenName["Generate Unique File Name<br/>uniqid('IMG-', true) . '.' . $ext"]
        MoveDisk["Write to Disk via move_uploaded_file()<br/>Target: gov/upload/IMG-XXXXXXXX.ext"]
        SaveSession["Cache File Path in $_SESSION"]
    end

    ReadFiles --> CheckErr
    CheckErr -- Non-Zero --> RejectErr
    CheckErr -- Zero --> ExtractExt
    ExtractExt --> CheckWhitelist
    CheckWhitelist -- No --> RejectExt
    CheckWhitelist -- Yes --> GenName
    GenName --> MoveDisk
    MoveDisk --> SaveSession
    
    SaveSession --> PersistDB["INSERT INTO attachment (aadhaar, passportimg, aadhaarimg, passbookimg, incomeimg, feesimg, markssheetimg, hostelfeesimg)"]
    PersistDB --> NextPage["Redirect to preview.php"]
```

---

## 6. Business Logic: Scholarship Sanctioning Engine (`gov/email.php`)

When an authorized government desk officer reviews an application in `gov/data.php` and triggers the **Accept (`fa fa-check`)** action, the backend invokes `gov/email.php`.

### Decision Matrix & Calculation Rules

```mermaid
flowchart TD
    Trigger["Admin Clicks 'Accept' (GET /gov/email.php?aadhaar={id})"] --> QueryApplicant["Fetch personal_details & registration"]
    QueryApplicant --> EvalCategory{"Evaluate Category & Hostel Accommodation"}

    EvalCategory -->|Category: 2A, 2B, 3A, 3B<br/>Hostel: YES| Tier1["College: ₹20,000<br/>Hostel: ₹10,000"]
    EvalCategory -->|Category: 2A, 2B, 3A, 3B<br/>Hostel: NO| Tier2["College: ₹20,000<br/>Hostel: ₹0"]
    EvalCategory -->|Category: Cat-1, SC, ST<br/>Hostel: YES| Tier3["College: ₹65,000<br/>Hostel: ₹10,000"]
    EvalCategory -->|Category: Cat-1, SC, ST<br/>Hostel: NO| Tier4["College: ₹65,000<br/>Hostel: ₹0"]

    Tier1 --> UpdateDB["UPDATE sanction SET collegescholarship = ..., hostelscholarship = ... WHERE aadhaar = ..."]
    Tier2 --> UpdateDB
    Tier3 --> UpdateDB
    Tier4 --> UpdateDB

    UpdateDB --> ConfigMail["Initialize PHPMailer Instance"]
    ConfigMail --> DispatchMail["Dispatch Automated Approval Email via Gmail SMTP"]
    DispatchMail --> AlertDone["Browser Alert: 'Sent Successfully' & Redirect to data.php"]
```

### Sanction Rules Reference Table

| Category Code | Hostel Resident | Sanctioned Tuition (INR) | Sanctioned Hostel (INR) | Total Disbursement (INR) |
| :--- | :---: | :---: | :---: | :---: |
| **Category 2A / 2B / 3A / 3B** | Yes | ₹20,000 | ₹10,000 | **₹30,000** |
| **Category 2A / 2B / 3A / 3B** | No | ₹20,000 | ₹0 | **₹20,000** |
| **Category 1 / SC / ST** | Yes | ₹65,000 | ₹10,000 | **₹75,000** |
| **Category 1 / SC / ST** | No | ₹65,000 | ₹0 | **₹65,000** |

---

## 7. Email Notification Dispatch Architecture

The system utilizes **PHPMailer v6** via secure SMTP socket connections:

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'omkarjavali1@gmail.com';
$mail->Password   = 'rcrcfnyoqdcyyfzo'; // Google App Password
$mail->SMTPSecure = 'ssl';
$mail->Port       = 465;

$mail->setFrom('omkarjavali1@gmail.com');
$mail->addAddress($result['email']);
$mail->Subject = "Scholarship Sanction Notification";
$mail->Body    = "Hello {$a['name']}, your scholarship has been sanctioned. Please check your scholarship status on the portal.";
$mail->send();
```

---

## 8. Administrative Cascade Deletion & Rejection Engine (`gov/delete.php`)

When an administrator rejects an incomplete or ineligible applicant:
1. Candidate email address is retrieved from `personal_details`.
2. A formal rejection email is transmitted notifying the student to reapply before the deadline.
3. Upon email delivery, the primary record in `personal_details` is deleted:
   ```sql
   DELETE FROM personal_details WHERE aadhaar = '$a[aadhaar]';
   ```
4. **Relational Cascade Mechanism**: Because the foreign keys in `college_details`, `hostel_details`, `bank_details`, and `attachment` specify `ON DELETE CASCADE`, the database engine automatically purges all connected child records atomically.
