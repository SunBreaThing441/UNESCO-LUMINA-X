<?php
$message = "";
$message1 = "";

require_once('connect.php');

if (isset($_POST['submit'])) {
    $em = $_POST['email'];
    $pass = md5($_POST['pwd']);

    if (!(empty($em) && empty($pass))) {
        $query = "SELECT * FROM `registration` WHERE email = '$em' AND password = '$pass'";

        $result = mysqli_query($connect, $query);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            $a = $result->fetch_assoc();
            $name = $a['name'];
            $aadh = $a['aadhaar'];
            $number = $a['number'];
            session_start();
            $_SESSION["email"] = $em;
            $_SESSION["aadh"] = $aadh;
            $_SESSION["name"] = $name;
            $_SESSION["phone"] = $number;
            header("location: home.php");
            exit();
        } else {
            $message = "Invalid email or credentials.";
            $message1 = "Please verify your password.";
        }
    }
}

$notice = "";
if (isset($_GET['notice'])) {
    if ($_GET['notice'] === 'auth_required') {
        $notice = "Authentication required: Please log in to access your scholar dashboard.";
    } elseif ($_GET['notice'] === 'session_terminated') {
        $notice = "Your scholar session has been securely terminated.";
    } elseif ($_GET['notice'] === 'session_inactivity_timeout') {
        $notice = "Session locked due to 15 minutes of inactivity for academic credential protection.";
    } elseif ($_GET['notice'] === 'registered') {
        $notice = "Scholar registration successful! Please authenticate with your new credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Scholar Authentication</title>
    
    <link rel="stylesheet" href="css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .auth-container {
            max-width: 460px;
            margin: 4rem auto 2rem;
            padding: 0 1.5rem;
        }
        .auth-card {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(24px) saturate(180%);
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
            padding: 0.65rem 0.9rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

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
            <li><a href="registration.php" class="lumina-nav-link"><i class="fa fa-user-plus"></i> Register</a></li>
            <li><a href="gov/login.php" class="lumina-nav-link" style="color: var(--un-cyan-500);"><i class="fa fa-shield"></i> Gov Portal</a></li>
        </ul>
    </header>

    <main class="auth-container">
        <div class="auth-card">
            
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fa fa-lock"></i>
                </div>
                <h1 class="auth-title">Scholar Login</h1>
                <p class="auth-subtitle">Enter your registered credentials to access the grant network.</p>
            </div>

            <?php if (!empty($notice)): ?>
                <div class="auth-alert" style="background: rgba(0, 119, 212, 0.1); border-color: rgba(0, 119, 212, 0.25); color: var(--un-blue-600);">
                    <i class="fa fa-info-circle"></i>
                    <div><?php echo htmlspecialchars($notice); ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($message) || !empty($message1)): ?>
                <div class="auth-alert">
                    <i class="fa fa-exclamation-triangle"></i>
                    <div>
                        <?php echo htmlspecialchars($message); ?> <?php echo htmlspecialchars($message1); ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label">Registered Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="scholar@example.com" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label">Account Password</label>
                    <input type="password" name="pwd" class="form-input" placeholder="••••••••" required>
                </div>

                <button type="submit" name="submit" value="Log in" class="btn-lumina btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem;">
                    <i class="fa fa-sign-in"></i> Authenticate & Enter
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.75rem; font-size: 0.85rem; color: var(--color-frost-600);">
                Unregistered candidate? <a href="registration.php" style="color: var(--un-blue-500); font-weight: 700; text-decoration: none;">Create an Account</a>
            </div>

        </div>
    </main>

    <!-- Universal HTTP Client & Form Integration -->
    <script src="js/lumina-http-client.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btnSubmit = form.querySelector('button[type="submit"]');
            const originalBtnHtml = btnSubmit.innerHTML;

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i> Authenticating Credentials...';

            try {
                const formData = new FormData(form);
                formData.append('submit', 'Log in'); // Ensure isset($_POST['submit']) passes

                const response = await window.luminaHttp.postForm('login.php', formData);
                const responseText = await response.text();

                if (responseText.includes('Invalid email or credentials') || responseText.includes('auth-alert')) {
                    window.luminaHttp.toast('Authentication failed: Invalid email or password.', 'danger');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnHtml;
                    return;
                }

                // Successful login (redirected or session established)
                window.luminaHttp.toast('Authentication Verified! Transferring to Scholar Console...', 'success', 2500);
                setTimeout(() => {
                    window.location.href = 'home.php';
                }, 800);

            } catch (err) {
                console.error('Login dispatch error:', err);
                form.submit(); // Fallback to standard form post
            }
        });
    </script>
</body>
</html>