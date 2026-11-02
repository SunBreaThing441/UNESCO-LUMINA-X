<?php
require_once('connect.php');
$message = "";
$message1 = "";
$message2 = "";

$aadh = (isset($_POST['aadhaar']) == TRUE) ? trim($_POST['aadhaar']) : '';
$user = (isset($_POST['name']) == TRUE) ? trim($_POST['name']) : '';
$phno = (isset($_POST['ph_no']) == TRUE) ? trim($_POST['ph_no']) : '';
$em = (isset($_POST['email']) == TRUE) ? trim($_POST['email']) : '';
$raw_pass = (isset($_POST['pwd']) == TRUE) ? $_POST['pwd'] : '';
$raw_cpass = (isset($_POST['cpwd']) == TRUE) ? $_POST['cpwd'] : '';
$pass = md5($raw_pass);
$cpass = md5($raw_cpass); 

if (!(empty($aadh) && empty($em))) {
    // Sanitize inputs for procedural SQL
    $safe_aadh = mysqli_real_escape_string($connect, $aadh);
    $safe_em = mysqli_real_escape_string($connect, $em);
    $safe_user = mysqli_real_escape_string($connect, $user);
    $safe_phno = mysqli_real_escape_string($connect, $phno);

    $aadh_err = "SELECT * FROM `registration` WHERE aadhaar = '$safe_aadh'";
    $q = mysqli_query($connect, $aadh_err);
    $aadh_count = mysqli_num_rows($q);

    $em_err = "SELECT * FROM `registration` WHERE email = '$safe_em'";
    $eq = mysqli_query($connect, $em_err);
    $em_count = mysqli_num_rows($eq);

    if ($aadh_count > 0) {
        $message = "Aadhaar UID is already registered in the sovereign database.";
    } elseif ($em_count > 0) {
        $message1 = "Official Email address is already linked to an existing account.";
    } else {   
        if ($pass === $cpass && !empty($raw_pass)) {
            $sql = "INSERT INTO registration (aadhaar, name, number, email, password) VALUES ('$safe_aadh', '$safe_user', '$safe_phno', '$safe_em', '$pass')";
            $result = mysqli_query($connect, $sql);

            if ($result) {
                $sql = "INSERT INTO sanction (aadhaar, collegescholarship, hostelscholarship) VALUES ('$safe_aadh', '0', '0')";
                $result = mysqli_query($connect, $sql);
                if ($result) {
                    header("location: login.php?notice=registered");
                    exit();
                }
            } else {
                $message = "Database synchronization error during account provisioning.";
            }
        } else {
            $message2 = "Security Passwords do not match. Please verify both entries.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Scholar Enrollment & Registration</title>
    
    <!-- Design System & Iconography -->
    <link rel="stylesheet" href="css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .auth-container {
            max-width: 580px;
            margin: 3.5rem auto 3rem;
            padding: 0 1.5rem;
        }
        .auth-card {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--glass-shadow-lg);
            position: relative;
            overflow: hidden;
        }
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--un-blue-500), var(--un-cyan-500));
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--un-blue-500) 0%, var(--un-cyan-500) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
            box-shadow: 0 6px 18px rgba(0, 119, 212, 0.35);
        }
        .auth-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--un-blue-900);
            letter-spacing: -0.02em;
        }
        .auth-subtitle {
            font-size: 0.85rem;
            color: var(--color-frost-600);
            margin-top: 0.25rem;
        }
        .auth-alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .pwd-requirement {
            font-size: 0.75rem;
            color: var(--color-frost-500);
            margin-top: 0.35rem;
        }
    </style>
</head>
<body>

    <!-- UNESCO Sovereign Navbar -->
    <header class="lumina-navbar">
        <a href="login.php" class="lumina-brand">
            <div class="lumina-brand-emblem">
                <i class="fa fa-globe"></i>
            </div>
            <div class="lumina-brand-text">
                <span class="lumina-brand-title">UNESCO LUMINA-X</span>
                <span class="lumina-brand-subtitle">Global Grant & Scholarship Network</span>
            </div>
        </a>

        <ul class="lumina-nav-items">
            <li><a href="login.php" class="lumina-nav-link"><i class="fa fa-sign-in"></i> Login</a></li>
            <li><a href="gov/login.php" class="lumina-nav-link" style="color: var(--un-cyan-500);"><i class="fa fa-shield"></i> Gov Portal</a></li>
        </ul>
    </header>

    <main class="auth-container">
        <div class="auth-card">
            
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fa fa-user-plus"></i>
                </div>
                <h1 class="auth-title">Scholar Enrollment</h1>
                <p class="auth-subtitle">Create a verifiable academic grant account in the universal ledger.</p>
            </div>

            <?php if (!empty($message) || !empty($message1) || !empty($message2)): ?>
                <div class="auth-alert">
                    <i class="fa fa-exclamation-triangle" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                    <div>
                        <?php 
                            if (!empty($message)) echo htmlspecialchars($message) . " "; 
                            if (!empty($message1)) echo htmlspecialchars($message1) . " "; 
                            if (!empty($message2)) echo htmlspecialchars($message2); 
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="registration.php" id="registrationForm">
                
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label"><i class="fa fa-id-card-o"></i> Aadhaar UID (12 Digits)</label>
                    <input type="text" name="aadhaar" class="form-input" placeholder="e.g. 123456789012" 
                           pattern="[0-9]{12}" minlength="12" maxlength="12" inputmode="numeric" required 
                           value="<?php echo htmlspecialchars($aadh); ?>" autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label"><i class="fa fa-user"></i> Full Legal Name (As per Aadhaar)</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. John Doe" required 
                           value="<?php echo htmlspecialchars($user); ?>">
                </div>

                <div class="form-row" style="margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label"><i class="fa fa-phone"></i> Contact Mobile</label>
                        <input type="tel" name="ph_no" class="form-input" placeholder="e.g. 9876543210" 
                               pattern="[0-9]{10}" maxlength="10" inputmode="tel" required 
                               value="<?php echo htmlspecialchars($phno); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="scholar@example.com" required 
                               value="<?php echo htmlspecialchars($em); ?>">
                    </div>
                </div>

                <div class="form-row" style="margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label"><i class="fa fa-lock"></i> Security Password</label>
                        <input type="password" name="pwd" id="pwd" class="form-input" placeholder="••••••••" 
                               required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                        <div class="pwd-requirement">Min 8 chars, 1 uppercase, 1 lowercase & 1 digit</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa fa-check-circle-o"></i> Confirm Password</label>
                        <input type="password" name="cpwd" id="cpwd" class="form-input" placeholder="••••••••" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn-lumina btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem;">
                    <i class="fa fa-shield"></i> Initialize Account & Provision Keys
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.75rem; font-size: 0.85rem; color: var(--color-frost-600);">
                Already registered in the network? <a href="login.php" style="color: var(--un-blue-500); font-weight: 700; text-decoration: none;">Authenticate & Enter</a>
            </div>

        </div>
    </main>

    <!-- Universal HTTP Client & Form Integration -->
    <script src="js/lumina-http-client.js"></script>
    <script>
        document.getElementById('registrationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btnSubmit = form.querySelector('button[type="submit"]');
            const pwd = document.getElementById('pwd').value;
            const cpwd = document.getElementById('cpwd').value;

            if (pwd !== cpwd) {
                window.luminaHttp.toast('Security passwords do not match. Please verify both fields.', 'danger');
                return;
            }

            const aadhaar = form.querySelector('input[name="aadhaar"]').value.trim();
            if (!/^\d{12}$/.test(aadhaar)) {
                window.luminaHttp.toast('Aadhaar UID must consist of exactly 12 numeric digits.', 'warning');
                return;
            }

            const originalBtnHtml = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i> Provisioning Sovereign Academic Identity...';

            try {
                const formData = new FormData(form);
                // Dispatch form data as application/x-www-form-urlencoded
                const response = await window.luminaHttp.postForm('registration.php', formData);
                const responseText = await response.text();

                // Check for server-side validation error indicators
                if (responseText.includes('Aadhaar UID is already registered') || responseText.includes('already exists')) {
                    window.luminaHttp.toast('Registration Halted: Aadhaar UID already exists in the system.', 'danger');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnHtml;
                    return;
                }

                if (responseText.includes('Official Email address is already linked') || responseText.includes('Email already exists')) {
                    window.luminaHttp.toast('Registration Halted: Email is already linked to an existing account.', 'danger');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnHtml;
                    return;
                }

                if (responseText.includes('Passwords do not match')) {
                    window.luminaHttp.toast('Registration Halted: Passwords do not match.', 'danger');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnHtml;
                    return;
                }

                // Success: Account provisioned, redirect to login
                window.luminaHttp.toast('Account Provisioned Successfully! Transferring to Scholar Authentication...', 'success', 3000);
                setTimeout(() => {
                    window.location.href = 'login.php?notice=registered';
                }, 1200);

            } catch (err) {
                console.error('Registration dispatch error:', err);
                window.luminaHttp.toast('Fallback to standard gateway dispatch...', 'info');
                form.submit(); // Graceful fallback
            }
        });
    </script>
</body>
</html>