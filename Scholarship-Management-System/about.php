<?php
session_start();
$is_scholar = !empty($_SESSION['email']);
$is_admin = !empty($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | About the Education Equity Charter</title>
    
    <!-- Design System & Icons -->
    <link rel="stylesheet" href="css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .about-container {
            max-width: 900px;
            margin: 3.5rem auto;
            padding: 0 1.5rem;
        }
        .about-card {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--glass-shadow-lg);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .about-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--un-blue-500), var(--un-cyan-500));
        }
        .about-title {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 800;
            color: var(--un-blue-900);
            margin-bottom: 1rem;
        }
        .about-quote {
            font-size: 1.1rem;
            font-style: italic;
            color: var(--un-blue-600);
            border-left: 3px solid var(--un-blue-500);
            padding-left: 1rem;
            margin: 1.5rem 0 2rem;
        }
        .about-section {
            margin-bottom: 2.25rem;
        }
        .about-section h3 {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--un-blue-900);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .about-section p {
            font-size: 0.95rem;
            color: var(--color-frost-600);
            line-height: 1.7;
        }
    </style>
</head>
<body>

    <!-- UNESCO Sovereign Navbar -->
    <header class="lumina-navbar">
        <a href="index.php" class="lumina-brand">
            <div class="lumina-brand-emblem">
                <i class="fa fa-globe"></i>
            </div>
            <div class="lumina-brand-text">
                <span class="lumina-brand-title">UNESCO LUMINA-X</span>
                <span class="lumina-brand-subtitle">Global Grant & Scholarship Network</span>
            </div>
        </a>

        <ul class="lumina-nav-items">
            <li><a href="index.php" class="lumina-nav-link"><i class="fa fa-home"></i> Home</a></li>
            <?php if ($is_scholar): ?>
                <li><a href="home.php" class="lumina-nav-link" style="color: var(--un-blue-500); font-weight: 700;"><i class="fa fa-tachometer"></i> Console</a></li>
                <li><a href="logout.php" class="lumina-nav-link"><i class="fa fa-sign-out"></i> Logout</a></li>
            <?php elseif ($is_admin): ?>
                <li><a href="gov/data.php" class="lumina-nav-link" style="color: var(--un-cyan-500); font-weight: 700;"><i class="fa fa-shield"></i> Command Center</a></li>
                <li><a href="gov/logout.php" class="lumina-nav-link"><i class="fa fa-sign-out"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="registration.php" class="lumina-nav-link"><i class="fa fa-user-plus"></i> Register</a></li>
                <li><a href="login.php" class="lumina-nav-link"><i class="fa fa-sign-in"></i> Login</a></li>
                <li><a href="gov/login.php" class="lumina-nav-link" style="color: var(--un-cyan-500);"><i class="fa fa-shield"></i> Gov Portal</a></li>
            <?php endif; ?>
        </ul>
    </header>

    <main class="about-container">
        <div class="about-card">
            
            <h1 class="about-title">The Universal Education Charter</h1>
            <p class="about-quote">"Education is a fundamental human right, essential for the exercise of all other human rights." &mdash; UNESCO Principle</p>

            <div class="about-section">
                <h3><i class="fa fa-compass" style="color: var(--un-blue-500);"></i> Mission & Purpose</h3>
                <p>
                    Across the globe, talented scholars encounter structural financial barriers that threaten their continuation of higher education. Under the UNESCO LUMINA-X mandate, our unified scholastic grant framework operates in coordination with the Karnataka Higher Education Welfare Directorate to ensure every deserving student receives direct scholastic aid, tuition subsidies, and hostel assistance.
                </p>
            </div>

            <div class="about-section">
                <h3><i class="fa fa-bullseye" style="color: var(--un-cyan-500);"></i> Core Objectives</h3>
                <p>
                    The principal objective is establishing equity through automated verification, eliminating arbitrary processing delays, and disbursing state-backed educational grants directly to validated beneficiaries. By integrating real-time telemetry, procedural safety, and transparent tracking, scholars can monitor their disbursement lifecycle from application submission to sanction approval.
                </p>
            </div>

            <div class="about-section">
                <h3><i class="fa fa-shield" style="color: var(--un-blue-600);"></i> Sovereign Verification Integrity</h3>
                <p>
                    All document archives and disbursements are validated by accredited welfare desk officers. Strict role isolation ensures candidates' records remain tamper-proof and accessible solely to authorized evaluation personnel.
                </p>
            </div>

            <div style="margin-top: 2.5rem; text-align: center;">
                <a href="registration.php" class="btn-lumina btn-primary" style="padding: 0.85rem 1.75rem; margin-right: 0.75rem;">
                    <i class="fa fa-user-plus"></i> Enroll as Scholar
                </a>
                <a href="index.php" class="btn-lumina btn-secondary" style="padding: 0.85rem 1.5rem;">
                    <i class="fa fa-arrow-left"></i> Back to Gateway
                </a>
            </div>

        </div>
    </main>

    <footer style="text-align: center; padding: 2rem 1rem; color: var(--color-frost-500); font-size: 0.85rem;">
        UNESCO LUMINA-X &bull; Global Education Equity Initiative &bull; All Rights Reserved
    </footer>

</body>
</html>