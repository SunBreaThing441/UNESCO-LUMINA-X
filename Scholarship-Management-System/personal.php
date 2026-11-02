<?php
session_start();
$message = "";
error_reporting(E_ERROR | E_PARSE);
require_once("connect.php");

if (empty($_SESSION['email'])) {
    header("location:login.php");
    exit();
}

$aa = $_SESSION["aadh"];

// Query existing student records across all relational tables
$sql = "SELECT * FROM `registration` WHERE aadhaar = '$aa'";
$reg_res = mysqli_query($connect, $sql);
$b = $reg_res->fetch_assoc();

$sql = "SELECT * FROM `personal_details` WHERE aadhaar = '$aa'";
$pers_res = mysqli_query($connect, $sql);
$a = $pers_res->fetch_assoc();

$sql = "SELECT * FROM `hostel_details` WHERE aadhaar = '$aa'";
$host_res = mysqli_query($connect, $sql);
$hostel_data = $host_res ? $host_res->fetch_assoc() : null;

$sql = "SELECT * FROM `college_details` WHERE aadhaar = '$aa'";
$coll_res = mysqli_query($connect, $sql);
$coll_data = $coll_res ? $coll_res->fetch_assoc() : null;

$sql = "SELECT * FROM `bank_details` WHERE aadhaar = '$aa'";
$bank_res = mysqli_query($connect, $sql);
$bank_data = $bank_res ? $bank_res->fetch_assoc() : null;

$sql = "SELECT * FROM `attachment` WHERE aadhaar = '$aa'";
$att_res = mysqli_query($connect, $sql);
$att_data = $att_res ? $att_res->fetch_assoc() : null;

// Legacy Backend POST Processing (Preserved Exactly)
if (isset($_POST['next'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $aadh = $_POST['aadh'];
    $phone = $_POST['phone'];
    $addr = $_POST['addr'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $cat = $_POST['cat'];
    $caste = $_POST['caste'];
    $income = $_POST['income'];
    $hostel = $_SESSION['hostel'] = $_POST['hostel'];

    if ($a['aadhaar'] == null) {
        $sql = "INSERT INTO personal_details(aadhaar, name, email, number, gender, address, dob, category, caste, income, hostel) 
                VALUES ('$aadh', '$name', '$email', '$phone', '$gender', '$addr', '$dob', '$cat', '$caste', '$income', '$hostel')";
        $result = mysqli_query($connect, $sql);
    } else {
        $sql = "UPDATE personal_details SET address='$addr', gender='$gender', dob='$dob', category='$cat', caste='$caste', income='$income', hostel='$hostel' WHERE aadhaar='$aadh'";
        $result = mysqli_query($connect, $sql);
    }

    if ($result) {
        if ($a['hostel'] === 'yes' || $hostel == 'yes') {
            header("location:hostel.php");
        } else {
            header("location:college.php");
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Universal Grant Application Stepper</title>
    
    <!-- Design System & Iconography -->
    <link rel="stylesheet" href="css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

    <!-- UNESCO Sovereign Navbar -->
    <header class="lumina-navbar">
        <a href="home.php" class="lumina-brand">
            <div class="lumina-brand-emblem">
                <i class="fa fa-globe"></i>
            </div>
            <div class="lumina-brand-text">
                <span class="lumina-brand-title">UNESCO LUMINA-X</span>
                <span class="lumina-brand-subtitle">Global Grant & Scholarship Network</span>
            </div>
        </a>

        <ul class="lumina-nav-items">
            <li><span class="lumina-nav-badge"><i class="fa fa-shield"></i> UID: <?php echo htmlspecialchars($b['aadhaar']); ?></span></li>
            <li><a href="home.php" class="lumina-nav-link"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="scholarship_status.php" class="lumina-nav-link"><i class="fa fa-line-chart"></i> Status</a></li>
            <li><a href="logout.php" class="lumina-nav-link" style="color: var(--color-danger);"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </header>

    <!-- Main Interactive Stepper Container -->
    <main class="stepper-wrapper">
        
        <!-- Stepper Header Card -->
        <section class="stepper-header-card">
            <div class="stepper-title-group">
                <div>
                    <h1 class="stepper-heading">Universal Scholastic Dossier (USD)</h1>
                    <p class="stepper-subheading">Complete all stages sequentially for international verification and financial sanction.</p>
                </div>
                <div class="stepper-progress-pill" id="stepperProgressText">
                    <i class="fa fa-clock-o"></i> Stage 1 of 6 (0%)
                </div>
            </div>

            <!-- Cybernetic Stepper Progress Track -->
            <div class="stepper-track-container">
                <div class="stepper-track-line"></div>
                <div class="stepper-track-fill" id="stepperProgressFill" style="width: 0%;"></div>

                <button type="button" class="stepper-node active" data-step="1" title="Personal Demographics">
                    <div class="stepper-node-circle">01</div>
                    <span class="stepper-node-label">Demographics</span>
                </button>

                <button type="button" class="stepper-node" data-step="2" title="Hostel Residency">
                    <div class="stepper-node-circle">02</div>
                    <span class="stepper-node-label">Residential</span>
                </button>

                <button type="button" class="stepper-node" data-step="3" title="Academic Profile">
                    <div class="stepper-node-circle">03</div>
                    <span class="stepper-node-label">Academics</span>
                </button>

                <button type="button" class="stepper-node" data-step="4" title="Banking Credentials">
                    <div class="stepper-node-circle">04</div>
                    <span class="stepper-node-label">Disbursement</span>
                </button>

                <button type="button" class="stepper-node" data-step="5" title="Credential Vault">
                    <div class="stepper-node-circle">05</div>
                    <span class="stepper-node-label">Credential Vault</span>
                </button>

                <button type="button" class="stepper-node" data-step="6" title="Executive Preview">
                    <div class="stepper-node-circle">06</div>
                    <span class="stepper-node-label">Review & Order</span>
                </button>
            </div>
        </section>

        <!-- Stepper Dynamic Body Container -->
        <div class="stepper-content-card">

            <!-- ===============================================================
                 STAGE 1: PERSONAL DEMOGRAPHICS
                 =============================================================== -->
            <div class="stepper-stage-panel active" data-step="1">
                <h2 class="stage-title"><i class="fa fa-user-circle" style="color:var(--un-blue-500)"></i> Stage 1: Scholar Demographic Identity</h2>
                <p class="stage-desc">Verify your foundational identification records and select accommodation preference.</p>

                <form id="form-step-1">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Full Legal Name <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="name" id="name" value="<?php echo htmlspecialchars($b['name']); ?>" readonly required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Verified Email Address <span class="required-mark">*</span></label>
                            <input type="email" class="form-input" name="email" id="email" value="<?php echo htmlspecialchars($b['email']); ?>" readonly required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Aadhaar Identification Number <span class="required-mark">*</span></label>
                            <input type="text" class="form-input form-input-mono" name="aadh" id="aadh" value="<?php echo htmlspecialchars($b['aadhaar']); ?>" readonly required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Primary Contact Number <span class="required-mark">*</span></label>
                            <input type="text" class="form-input form-input-mono" name="phone" id="phone" value="<?php echo htmlspecialchars($b['number']); ?>" readonly required>
                        </div>
                    </div>

                    <div class="form-group full-width" style="margin-bottom: 1.5rem;">
                        <label class="form-label">Permanent Residential Postal Address <span class="required-mark">*</span></label>
                        <input type="text" class="form-input" name="addr" id="addr" placeholder="Enter house no, street, colony, district, pin code" value="<?php echo htmlspecialchars($a['address'] ?? ''); ?>" required>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Gender <span class="required-mark">*</span></label>
                            <select name="gender" id="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo ($a['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($a['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($a['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth <span class="required-mark">*</span></label>
                            <input type="date" class="form-input" name="dob" id="dob" value="<?php echo htmlspecialchars($a['dob'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Socio-Economic Category <span class="required-mark">*</span></label>
                            <select name="cat" id="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="cat1" <?php echo ($a['category'] == 'cat1') ? 'selected' : ''; ?>>Category 1 (Disadvantaged)</option>
                                <option value="cat2a" <?php echo ($a['category'] == 'cat2a') ? 'selected' : ''; ?>>Category 2A</option>
                                <option value="cat2b" <?php echo ($a['category'] == 'cat2b') ? 'selected' : ''; ?>>Category 2B</option>
                                <option value="cat3a" <?php echo ($a['category'] == 'cat3a') ? 'selected' : ''; ?>>Category 3A</option>
                                <option value="cat3b" <?php echo ($a['category'] == 'cat3b') ? 'selected' : ''; ?>>Category 3B</option>
                                <option value="sc" <?php echo ($a['category'] == 'sc') ? 'selected' : ''; ?>>Scheduled Caste (SC)</option>
                                <option value="st" <?php echo ($a['category'] == 'st') ? 'selected' : ''; ?>>Scheduled Tribe (ST)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Specific Sub-Caste Name <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="caste" id="caste" placeholder="e.g., Kuruba, Lingayat, Vokkaliga, etc." value="<?php echo htmlspecialchars($a['caste'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Annual Family Gross Income (INR) <span class="required-mark">*</span></label>
                            <input type="number" class="form-input form-input-mono" name="income" id="income" placeholder="e.g., 120000" min="5000" value="<?php echo htmlspecialchars($a['income'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <!-- Hostel Residency Radio Cards -->
                    <div class="form-group full-width" style="margin-top: 1rem;">
                        <label class="form-label">Residential Accommodation Status <span class="required-mark">*</span></label>
                        <div class="radio-cards-container">
                            <label class="radio-card <?php echo ($a['hostel'] == 'yes') ? 'active' : ''; ?>">
                                <input type="radio" name="hostel" value="yes" <?php echo ($a['hostel'] == 'yes') ? 'checked' : ''; ?> required>
                                <div class="radio-card-content">
                                    <span class="radio-card-title"><i class="fa fa-building" style="color:var(--un-blue-500)"></i> Hostel Resident</span>
                                    <span class="radio-card-desc">Eligible for +₹10,000 annual boarding stipend</span>
                                </div>
                            </label>

                            <label class="radio-card <?php echo ($a['hostel'] != 'yes') ? 'active' : ''; ?>">
                                <input type="radio" name="hostel" value="no" <?php echo ($a['hostel'] != 'yes') ? 'checked' : ''; ?> required>
                                <div class="radio-card-content">
                                    <span class="radio-card-title"><i class="fa fa-home" style="color:var(--color-frost-600)"></i> Day Scholar</span>
                                    <span class="radio-card-desc">Tuition grant allocation only</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===============================================================
                 STAGE 2: HOSTEL DETAILS (CONDITIONAL)
                 =============================================================== -->
            <div class="stepper-stage-panel" data-step="2">
                <h2 class="stage-title"><i class="fa fa-building-o" style="color:var(--un-blue-500)"></i> Stage 2: Hostel Residency Verification</h2>
                <p class="stage-desc">Provide your registered accommodation and institutional hostel license credentials.</p>

                <form id="form-step-2">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Hostel Official Name <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="hname" id="hname" placeholder="Enter full official hostel name" value="<?php echo htmlspecialchars($hostel_data['hostelname'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hostel Registration / Room License Number <span class="required-mark">*</span></label>
                            <input type="number" class="form-input form-input-mono" name="hno" id="hno" placeholder="e.g., 20261048" value="<?php echo htmlspecialchars($hostel_data['hostelRegistrationNumber'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Hostel Taluka / Subdivision <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="htaluka" id="htaluka" placeholder="e.g., Belagavi Taluka" value="<?php echo htmlspecialchars($hostel_data['hosteltaluka'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hostel District <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="hdistrict" id="hdistrict" placeholder="e.g., Belagavi" value="<?php echo htmlspecialchars($hostel_data['hosteldistrict'] ?? ''); ?>" required>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===============================================================
                 STAGE 3: COLLEGE & ACADEMIC DETAILS
                 =============================================================== -->
            <div class="stepper-stage-panel" data-step="3">
                <h2 class="stage-title"><i class="fa fa-graduation-cap" style="color:var(--un-blue-500)"></i> Stage 3: Academic Institution & USN Credentials</h2>
                <p class="stage-desc">Link your enrolled university, degree discipline, and prior academic merit scores.</p>

                <form id="form-step-3">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">University Seat Number (USN / Enrolment ID) <span class="required-mark">*</span></label>
                            <input type="text" class="form-input form-input-mono" name="usn" id="usn" placeholder="e.g., 2SD21CS045" value="<?php echo htmlspecialchars($coll_data['usn'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Affiliated University / Board <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="university" id="university" placeholder="e.g., Visvesvaraya Technological University" value="<?php echo htmlspecialchars($coll_data['university'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">College / Institution Name <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="collegename" id="collegename" placeholder="Enter college name" value="<?php echo htmlspecialchars($coll_data['collegename'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">College Taluka <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="collegetaluka" id="collegetaluka" placeholder="Taluka" value="<?php echo htmlspecialchars($coll_data['collegetaluka'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">College District <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="collegedistrict" id="collegedistrict" placeholder="District" value="<?php echo htmlspecialchars($coll_data['collegedistrict'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Course / Degree Program <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="course" id="course" placeholder="e.g., B.E. Computer Science" value="<?php echo htmlspecialchars($coll_data['course'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Seat Quota Type <span class="required-mark">*</span></label>
                            <select name="seattype" id="seattype" class="form-select" required>
                                <option value="Government" <?php echo ($coll_data['seattype'] == 'Government') ? 'selected' : ''; ?>>Government Quota (KEA/CET)</option>
                                <option value="Management" <?php echo ($coll_data['seattype'] == 'Management') ? 'selected' : ''; ?>>Management Quota</option>
                                <option value="SNQ" <?php echo ($coll_data['seattype'] == 'SNQ') ? 'selected' : ''; ?>>Supernumerary Quota (SNQ)</option>
                                <option value="Payment" <?php echo ($coll_data['seattype'] == 'Payment') ? 'selected' : ''; ?>>Payment Seat</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Present Year of Study <span class="required-mark">*</span></label>
                            <select name="year" id="year" class="form-select" required>
                                <option value="1" <?php echo ($coll_data['presentyear'] == '1') ? 'selected' : ''; ?>>Year 1</option>
                                <option value="2" <?php echo ($coll_data['presentyear'] == '2') ? 'selected' : ''; ?>>Year 2</option>
                                <option value="3" <?php echo ($coll_data['presentyear'] == '3') ? 'selected' : ''; ?>>Year 3</option>
                                <option value="4" <?php echo ($coll_data['presentyear'] == '4') ? 'selected' : ''; ?>>Year 4</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Annual Tuition Fees Paid (INR) <span class="required-mark">*</span></label>
                            <input type="number" class="form-input form-input-mono" name="fees" id="fees" placeholder="e.g., 65000" value="<?php echo htmlspecialchars($coll_data['fees'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prior Qualifying Year <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="pyear" id="pyear" placeholder="e.g., 2" value="<?php echo htmlspecialchars($coll_data['previousyear'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Marks Achieved & Percentage (%) <span class="required-mark">*</span></label>
                            <div style="display:flex; gap:0.5rem;">
                                <input type="number" class="form-input form-input-mono" name="marks" id="marks" placeholder="Marks" style="width:50%" value="<?php echo htmlspecialchars($coll_data['marks'] ?? ''); ?>" required>
                                <input type="number" class="form-input form-input-mono" name="percentage" id="percentage" placeholder="%" min="35" max="100" style="width:50%" value="<?php echo htmlspecialchars($coll_data['percentage'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===============================================================
                 STAGE 4: BANKING & DISBURSEMENT
                 =============================================================== -->
            <div class="stepper-stage-panel" data-step="4">
                <h2 class="stage-title"><i class="fa fa-university" style="color:var(--un-blue-500)"></i> Stage 4: Direct Benefit Transfer (DBT) Banking Credentials</h2>
                <p class="stage-desc">Aadhaar-seeded bank account credentials for direct electronic remittance.</p>

                <form id="form-step-4">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Name as Printed in Passbook <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="passbookname" id="passbookname" placeholder="Exact passbook legal name" value="<?php echo htmlspecialchars($bank_data['passbookname'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Commercial Bank Name <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="bname" id="bname" placeholder="e.g., State Bank of India" value="<?php echo htmlspecialchars($bank_data['bankname'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Primary Account Number <span class="required-mark">*</span></label>
                            <input type="text" class="form-input form-input-mono" name="accnumber" id="accnumber" placeholder="Enter bank account number" value="<?php echo htmlspecialchars($bank_data['accountnumber'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Re-enter Account Number to Confirm <span class="required-mark">*</span></label>
                            <input type="text" class="form-input form-input-mono" name="acnumber" id="acnumber" placeholder="Confirm account number" value="<?php echo htmlspecialchars($bank_data['accountnumber'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Branch Name <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="branch" id="branch" placeholder="Branch location" value="<?php echo htmlspecialchars($bank_data['branch'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Branch District <span class="required-mark">*</span></label>
                            <input type="text" class="form-input" name="bdistrict" id="bdistrict" placeholder="District" value="<?php echo htmlspecialchars($bank_data['bankdistrict'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">11-Digit IFSC Code <span class="required-mark">*</span></label>
                            <input type="text" class="form-input form-input-mono" name="ifsc" id="ifsc" placeholder="e.g., SBIN0001234" maxlength="11" value="<?php echo htmlspecialchars($bank_data['ifsc'] ?? ''); ?>" required>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===============================================================
                 STAGE 5: HOLOGRAPHIC CREDENTIAL VAULT (ATTACHMENTS)
                 =============================================================== -->
            <div class="stepper-stage-panel" data-step="5">
                <h2 class="stage-title"><i class="fa fa-cloud-upload" style="color:var(--un-blue-500)"></i> Stage 5: Holographic Credential Vault</h2>
                <p class="stage-desc">Upload high-resolution scans of mandatory credentials (JPG/PNG, Max 5MB each).</p>

                <form id="form-step-5" enctype="multipart/form-data">
                    <div class="upload-grid">
                        
                        <!-- 1. Passport Photo -->
                        <div class="upload-card" data-field="Passport Photo">
                            <img class="upload-preview-thumb">
                            <i class="fa fa-camera upload-icon"></i>
                            <span class="upload-card-label">Passport Photograph</span>
                            <span class="upload-card-hint">Drag & drop or browse image</span>
                            <input type="file" name="passport" class="upload-file-input" accept="image/jpeg,image/png,image/jpg" required>
                            <span class="upload-badge-success"></span>
                        </div>

                        <!-- 2. Aadhaar Card -->
                        <div class="upload-card" data-field="Aadhaar Card">
                            <img class="upload-preview-thumb">
                            <i class="fa fa-id-card upload-icon"></i>
                            <span class="upload-card-label">Aadhaar Card Scan</span>
                            <span class="upload-card-hint">Front & back UID copy</span>
                            <input type="file" name="aadh" class="upload-file-input" accept="image/jpeg,image/png,image/jpg" required>
                            <span class="upload-badge-success"></span>
                        </div>

                        <!-- 3. Passbook -->
                        <div class="upload-card" data-field="Bank Passbook">
                            <img class="upload-preview-thumb">
                            <i class="fa fa-book upload-icon"></i>
                            <span class="upload-card-label">Bank Passbook Front Page</span>
                            <span class="upload-card-hint">Visible IFSC & Account</span>
                            <input type="file" name="passbook" class="upload-file-input" accept="image/jpeg,image/png,image/jpg" required>
                            <span class="upload-badge-success"></span>
                        </div>

                        <!-- 4. Income / Caste -->
                        <div class="upload-card" data-field="castecertificate">
                            <img class="upload-preview-thumb">
                            <i class="fa fa-file-text upload-icon"></i>
                            <span class="upload-card-label">Caste & Income Certificate</span>
                            <span class="upload-card-hint">Official Revenue Dept certificate</span>
                            <input type="file" name="castecertificate" class="upload-file-input" accept="image/jpeg,image/png,image/jpg" required>
                            <span class="upload-badge-success"></span>
                        </div>

                        <!-- 5. College Fees Receipt -->
                        <div class="upload-card" data-field="cfeesrecipt">
                            <img class="upload-preview-thumb">
                            <i class="fa fa-ticket upload-icon"></i>
                            <span class="upload-card-label">College Admission Fee Receipt</span>
                            <span class="upload-card-hint">Current academic session</span>
                            <input type="file" name="cfeesrecipt" class="upload-file-input" accept="image/jpeg,image/png,image/jpg" required>
                            <span class="upload-badge-success"></span>
                        </div>

                        <!-- 6. Marksheet -->
                        <div class="upload-card" data-field="markssheet">
                            <img class="upload-preview-thumb">
                            <i class="fa fa-check-square-o upload-icon"></i>
                            <span class="upload-card-label">Qualifying Exam Marksheet</span>
                            <span class="upload-card-hint">Official board transcript</span>
                            <input type="file" name="markssheet" class="upload-file-input" accept="image/jpeg,image/png,image/jpg" required>
                            <span class="upload-badge-success"></span>
                        </div>

                        <!-- 7. Hostel Fees (Conditional/Optional) -->
                        <div class="upload-card" data-field="hfees">
                            <img class="upload-preview-thumb">
                            <i class="fa fa-building upload-icon"></i>
                            <span class="upload-card-label">Hostel Fee Receipt</span>
                            <span class="upload-card-hint">Optional for Day Scholars</span>
                            <input type="file" name="hfees" class="upload-file-input" accept="image/jpeg,image/png,image/jpg">
                            <span class="upload-badge-success"></span>
                        </div>

                    </div>
                </form>
            </div>

            <!-- ===============================================================
                 STAGE 6: EXECUTIVE PREVIEW & FINAL VERIFICATION
                 =============================================================== -->
            <div class="stepper-stage-panel" data-step="6">
                <h2 class="stage-title"><i class="fa fa-file-text-o" style="color:var(--un-blue-500)"></i> Stage 6: Universal Scholastic Dossier Verification</h2>
                <p class="stage-desc">Review your verified data before final submission and download your official UNESCO Acknowledgement.</p>

                <!-- Synthesized Dossier Card -->
                <div class="preview-dossier-card">
                    <div class="preview-dossier-header">
                        <div>
                            <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; opacity:0.85;">Document Reference</div>
                            <div style="font-family:var(--font-mono); font-size:1.1rem; font-weight:700;">USD-<?php echo htmlspecialchars($b['aadhaar']); ?></div>
                        </div>
                        <span style="background:rgba(16,185,129,0.25); border:1px solid #10b981; padding:0.35rem 0.85rem; border-radius:20px; font-size:0.8rem; font-weight:700; color:#fff;">
                            <i class="fa fa-check-circle"></i> VERIFIED IN SESSION
                        </span>
                    </div>

                    <!-- Personal Demographics Grid -->
                    <div class="preview-grid-section">
                        <div class="preview-section-title"><i class="fa fa-user"></i> Scholar Demographics</div>
                        <div class="preview-data-grid">
                            <div class="preview-item"><span class="preview-item-label">Name</span><span class="preview-item-value" id="prev_name">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Aadhaar UID</span><span class="preview-item-value font-mono" id="prev_aadhaar">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Email</span><span class="preview-item-value" id="prev_email">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Phone</span><span class="preview-item-value" id="prev_phone">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Gender / DOB</span><span class="preview-item-value"><span id="prev_gender">-</span>, <span id="prev_dob">-</span></span></div>
                            <div class="preview-item"><span class="preview-item-label">Category / Caste</span><span class="preview-item-value"><span id="prev_category">-</span> (<span id="prev_caste">-</span>)</span></div>
                            <div class="preview-item"><span class="preview-item-label">Annual Income</span><span class="preview-item-value" id="prev_income">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Residential Mode</span><span class="preview-item-value" id="prev_hostel">-</span></div>
                        </div>
                    </div>

                    <!-- Academic Profile Grid -->
                    <div class="preview-grid-section">
                        <div class="preview-section-title"><i class="fa fa-graduation-cap"></i> Academic Credentials</div>
                        <div class="preview-data-grid">
                            <div class="preview-item"><span class="preview-item-label">USN / Roll Number</span><span class="preview-item-value font-mono" id="prev_usn">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">University</span><span class="preview-item-value" id="prev_university">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">College Institution</span><span class="preview-item-value" id="prev_college">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Degree Course</span><span class="preview-item-value" id="prev_course">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Academic Merit Score</span><span class="preview-item-value" id="prev_percentage">-</span></div>
                        </div>
                    </div>

                    <!-- Banking Grid -->
                    <div class="preview-grid-section">
                        <div class="preview-section-title"><i class="fa fa-bank"></i> DBT Remittance Destination</div>
                        <div class="preview-data-grid">
                            <div class="preview-item"><span class="preview-item-label">Banking Institution</span><span class="preview-item-value" id="prev_bankname">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">Account Number</span><span class="preview-item-value font-mono" id="prev_accnumber">-</span></div>
                            <div class="preview-item"><span class="preview-item-label">IFSC Code</span><span class="preview-item-value font-mono" id="prev_ifsc">-</span></div>
                        </div>
                    </div>

                    <!-- Document Uploads Preview -->
                    <div class="preview-grid-section">
                        <div class="preview-section-title"><i class="fa fa-file-image-o"></i> Attached Credential Vault</div>
                        <div id="prev_docs_grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:1rem;">
                            <!-- Populated dynamically by JS -->
                        </div>
                    </div>
                </div>

                <!-- Final Action Download Button -->
                <div style="text-align: center; margin: 2rem 0 1rem;">
                    <form action="acknowledgement.php" method="POST" id="ackForm">
                        <button type="button" id="btnOpenAckPdf" class="btn-lumina btn-primary" style="font-size:1.05rem; padding:1rem 2.5rem; cursor:pointer;">
                            <i class="fa fa-file-pdf-o"></i> View & Print Official Acknowledgement (PDF)
                        </button>
                    </form>
                    <p style="font-size:0.8rem; color:var(--color-frost-600); margin-top:0.75rem;">
                        <i class="fa fa-lock"></i> Your dossier is cryptographically registered with the administrative review desk.
                    </p>
                </div>
            </div>

            <!-- Stepper Interactive Controls Footer -->
            <div class="stepper-actions-bar">
                <button type="button" class="btn-lumina btn-secondary" id="btnPrev">
                    <i class="fa fa-arrow-left"></i> Previous Stage
                </button>
                <button type="button" class="btn-lumina btn-primary" id="btnNext">
                    Save & Continue <i class="fa fa-arrow-right"></i>
                </button>
            </div>

        </div>
    </main>

    <!-- Component JavaScript Logic -->
    <script src="js/lumina-http-client.js"></script>
    <script src="js/lumina-stepper.js"></script>
    <script src="js/lumina-auth-guard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.luminaGuard = new LuminaAuthGuard({ requiredRole: 'scholar' });

            // Interactive PDF Dossier Viewer Trigger
            const btnAck = document.getElementById('btnOpenAckPdf');
            if (btnAck) {
                btnAck.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.luminaHttp.renderPdfModal('acknowledgement.php', 'Official Scholarship Acknowledgement Receipt', { next4: 'Next' });
                });
            }
        });
    </script>
</body>
</html>