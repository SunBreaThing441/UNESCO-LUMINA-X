<?php
require_once('connect.php');
$errorMessage = "";

if (isset($_POST["submit"])) {
    $u = $_POST['user'];
    $pass = $_POST['pwd'];
    if (!(empty($u) && empty($pass))) {
        $query = "SELECT * FROM `gov` WHERE user = '$u' AND password = '$pass'";
        $result = mysqli_query($connect, $query);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            session_start();
            $_SESSION["user"] = $u;
            header("location: data.php");
            exit();
        } else {
            $errorMessage = "Authentication failed: Invalid officer credentials or security key.";
        }
    }
}

// Check for redirect notifications or unauthorized route triggers
$noticeMessage = "";
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized_access') {
    $errorMessage = "Access Restricted: You must authenticate with Sovereign Desk credentials to access the command center.";
} elseif (isset($_GET['notice']) && $_GET['notice'] === 'sovereign_session_terminated') {
    $noticeMessage = "Sovereign console session successfully terminated.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Sovereign Authority Gateway</title>

    <!-- Design System & Iconography -->
    <link rel="stylesheet" href="../css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .gov-auth-container {
            max-width: 480px;
            margin: 5rem auto 2rem;
            padding: 0 1.5rem;
        }
        .gov-auth-card {
            background: var(--cmd-glass-card);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid var(--cmd-glass-border);
            border-radius: 22px;
            padding: 2.75rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 35px rgba(0, 163, 224, 0.15);
            position: relative;
            overflow: hidden;
        }
        .gov-auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0077D4, #00A3E0, #34d399);
        }
        .gov-auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .gov-auth-emblem {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--un-blue-600) 0%, var(--un-cyan-500) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 24px rgba(0, 163, 224, 0.35);
        }
        .gov-auth-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
        }
        .gov-auth-subtitle {
            font-size: 0.82rem;
            color: var(--un-cyan-400);
            margin-top: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
        }
        .gov-security-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(0, 163, 224, 0.12);
            border: 1px solid rgba(0, 163, 224, 0.25);
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-family: var(--font-mono);
            color: var(--un-cyan-400);
            margin-top: 0.75rem;
        }
        .gov-alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #f87171;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .gov-alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34d399;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .gov-form-input {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(7, 14, 29, 0.7);
            border: 1px solid var(--cmd-glass-border);
            border-radius: 10px;
            color: #fff;
            font-family: var(--font-body);
            font-size: 0.9rem;
            outline: none;
            transition: all var(--transition-fast);
        }
        .gov-form-input:focus {
            border-color: var(--un-cyan-400);
            box-shadow: 0 0 16px rgba(0, 163, 224, 0.35);
            background: rgba(7, 14, 29, 0.9);
        }
    </style>
</head>
<body class="cmd-center-body">

    <!-- Sovereign Navbar -->
    <header class="lumina-navbar cmd-navbar">
        <a href="login.php" class="lumina-brand">
            <div class="lumina-brand-emblem" style="background: linear-gradient(135deg, #0077D4, #00A3E0);">
                <i class="fa fa-shield"></i>
            </div>
            <div class="lumina-brand-text">
                <span class="lumina-brand-title cmd-brand-title">LUMINA-X SOVEREIGN COMMAND</span>
                <span class="lumina-brand-subtitle" style="color: var(--un-cyan-400);">Government & Administrative Gateway</span>
            </div>
        </a>

        <ul class="lumina-nav-items">
            <li><a href="../login.php" class="lumina-nav-link" style="color: #60a5fa;"><i class="fa fa-user"></i> Scholar Portal</a></li>
        </ul>
    </header>

    <main class="gov-auth-container">
        <div class="gov-auth-card">

            <div class="gov-auth-header">
                <div class="gov-auth-emblem">
                    <i class="fa fa-key"></i>
                </div>
                <h1 class="gov-auth-title">Desk Officer Authentication</h1>
                <p class="gov-auth-subtitle">Government of Karnataka Verification Console</p>
                <div class="gov-security-badge">
                    <i class="fa fa-lock"></i> ISO/IEC 27001 SECURED WORKSPACE
                </div>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="gov-alert-danger">
                    <i class="fa fa-shield"></i>
                    <div><?php echo htmlspecialchars($errorMessage); ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($noticeMessage)): ?>
                <div class="gov-alert-success">
                    <i class="fa fa-check-circle"></i>
                    <div><?php echo htmlspecialchars($noticeMessage); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="govLoginForm">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" style="color: var(--color-frost-200);">Administrative Username</label>
                    <input type="text" name="user" class="gov-form-input" placeholder="e.g., admin" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 1.75rem;">
                    <label class="form-label" style="color: var(--color-frost-200);">Security Key / Passphrase</label>
                    <input type="password" name="pwd" class="gov-form-input" placeholder="••••••••" required>
                </div>

                <button type="submit" name="submit" value="Log in" class="btn-lumina btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; background: linear-gradient(135deg, var(--un-blue-600) 0%, var(--un-cyan-500) 100%);">
                    <i class="fa fa-unlock-alt"></i> Authorize Sovereign Session
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.75rem; font-size: 0.8rem; color: var(--color-frost-300);">
                <i class="fa fa-info-circle"></i> Unauthorized administrative access attempts are audited and logged.
            </div>

        </div>
    </main>

    <!-- Universal HTTP Client & Sovereign Form Integration -->
    <script src="../js/lumina-http-client.js"></script>
    <script>
        document.getElementById('govLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btnSubmit = form.querySelector('button[type="submit"]');
            const originalBtnHtml = btnSubmit.innerHTML;

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i> Authorizing Officer Keys...';

            try {
                const formData = new FormData(form);
                formData.append('submit', 'Log in');

                const response = await window.luminaHttp.postForm('login.php', formData);
                const responseText = await response.text();

                if (responseText.includes('gov-alert-danger') || responseText.includes('Authentication failed')) {
                    window.luminaHttp.toast('Access Denied: Invalid officer credentials or security key.', 'danger');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnHtml;
                    return;
                }

                window.luminaHttp.toast('Sovereign Clearance Verified! Loading Command Center...', 'success', 2500);
                setTimeout(() => {
                    window.location.href = 'data.php';
                }, 800);

            } catch (err) {
                console.error('Sovereign auth error:', err);
                form.submit();
            }
        });
    </script>
</body>
</html>