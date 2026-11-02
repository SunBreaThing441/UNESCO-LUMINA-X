<?php
session_start();
$is_scholar = !empty($_SESSION['email']);
$is_admin = !empty($_SESSION['user']);
$scholar_name = $_SESSION['name'] ?? 'Scholar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Universal Scholastic Grant Portal</title>
    
    <!-- Design System & Icons -->
    <link rel="stylesheet" href="css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .portal-hero {
            text-align: center;
            padding: 4.5rem 1.5rem 3rem;
            max-width: 980px;
            margin: 0 auto;
        }
        .portal-headline {
            font-family: var(--font-display);
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--un-blue-900);
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 1.25rem;
        }
        .portal-headline span {
            background: linear-gradient(135deg, var(--un-blue-600) 0%, var(--un-cyan-500) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .portal-subtext {
            font-size: 1.15rem;
            color: var(--color-frost-600);
            max-width: 720px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
        }
        .portal-cta-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            flex-wrap: wrap;
            margin-bottom: 3.5rem;
        }
        .portal-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.75rem;
            max-width: 1140px;
            margin: 0 auto 3.5rem;
            padding: 0 1.5rem;
        }
        @media (max-width: 900px) {
            .portal-grid {
                grid-template-columns: 1fr;
            }
        }
        .portal-card {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            padding: 2.25rem 2rem;
            box-shadow: var(--glass-shadow-md);
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .portal-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--glass-shadow-lg);
            border-color: rgba(0, 119, 212, 0.3);
        }
        .portal-card-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(0, 119, 212, 0.1);
            color: var(--un-blue-500);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1.25rem;
        }
        .portal-card-title {
            font-family: var(--font-display);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--un-blue-900);
            margin-bottom: 0.75rem;
        }
        .portal-card-desc {
            font-size: 0.9rem;
            color: var(--color-frost-600);
            line-height: 1.6;
        }
        .portal-card-desc ul {
            padding-left: 1.25rem;
            margin-top: 0.75rem;
        }
        .portal-card-desc li {
            margin-bottom: 0.4rem;
        }
        .session-banner {
            max-width: 1140px;
            margin: 1.5rem auto 0;
            padding: 0 1.5rem;
        }
        .session-alert {
            background: rgba(0, 119, 212, 0.08);
            border: 1px solid rgba(0, 119, 212, 0.2);
            border-radius: 14px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
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
            <li><a href="about.php" class="lumina-nav-link"><i class="fa fa-info-circle"></i> About Charter</a></li>
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

    <?php if ($is_scholar): ?>
        <div class="session-banner">
            <div class="session-alert">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fa fa-check-circle" style="color: #059669; font-size: 1.25rem;"></i>
                    <div>
                        <strong>Active Scholar Session:</strong> Welcome back, <?php echo htmlspecialchars($scholar_name); ?>.
                    </div>
                </div>
                <a href="home.php" class="btn-lumina btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    <i class="fa fa-arrow-right"></i> Open Scholar Dashboard
                </a>
            </div>
        </div>
    <?php elseif ($is_admin): ?>
        <div class="session-banner">
            <div class="session-alert" style="background: rgba(15, 23, 42, 0.06); border-color: rgba(15, 23, 42, 0.15);">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fa fa-shield" style="color: var(--un-blue-600); font-size: 1.25rem;"></i>
                    <div>
                        <strong>Sovereign Console Active:</strong> Administrative verification credentials verified.
                    </div>
                </div>
                <a href="gov/data.php" class="btn-lumina btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    <i class="fa fa-desktop"></i> Open Command Center
                </a>
            </div>
        </div>
    <?php endif; ?>

    <main>
        <section class="portal-hero">
            <span class="lumina-nav-badge" style="display: inline-block; margin-bottom: 1.25rem; font-size: 0.82rem; padding: 0.4rem 1rem;">
                <i class="fa fa-shield"></i> Unified Scholastic Grant Infrastructure
            </span>
            <h1 class="portal-headline">Empowering Academic Futures with <span>Global Education Grants</span></h1>
            <p class="portal-subtext">
                UNESCO LUMINA-X democratizes access to higher education through a secure, transparent, and decentralized grant disbursement protocol for eligible scholars.
            </p>

            <div class="portal-cta-group">
                <a href="registration.php" class="btn-lumina btn-primary" style="padding: 0.95rem 2rem; font-size: 1.05rem;">
                    <i class="fa fa-user-plus"></i> New Scholar Enrollment
                </a>
                <a href="login.php" class="btn-lumina btn-secondary" style="padding: 0.95rem 1.85rem; font-size: 1.05rem;">
                    <i class="fa fa-sign-in"></i> Scholar Authentication Gateway
                </a>
                <a href="gov/login.php" class="btn-lumina btn-secondary" style="padding: 0.95rem 1.75rem; font-size: 1.05rem; border-color: rgba(0, 163, 224, 0.4);">
                    <i class="fa fa-shield"></i> Sovereign Desk
                </a>
            </div>
        </section>

        <section class="portal-grid">
            <div class="portal-card">
                <div class="portal-card-icon">
                    <i class="fa fa-balance-scale"></i>
                </div>
                <h3 class="portal-card-title">Eligibility Criteria</h3>
                <div class="portal-card-desc">
                    Scholars from historically underrepresented backgrounds are prioritized:
                    <ul>
                        <li>Backward Caste, Minority & OBC categories</li>
                        <li>Annual Family Income verified below ₹1,00,000</li>
                        <li>Enrolled in recognized undergraduate or post-matric programs</li>
                    </ul>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card-icon">
                    <i class="fa fa-file-text-o"></i>
                </div>
                <h3 class="portal-card-title">Required Credentials</h3>
                <div class="portal-card-desc">
                    Digital documents required for verification:
                    <ul>
                        <li>Official College Registration / USN identifier</li>
                        <li>Government Caste & Income Certificates</li>
                        <li>Institutional Tuition Fee Receipts</li>
                        <li>Aadhaar UID and Valid Identity Proof</li>
                    </ul>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card-icon">
                    <i class="fa fa-headphones"></i>
                </div>
                <h3 class="portal-card-title">Sovereign Helpdesk</h3>
                <div class="portal-card-desc">
                    Official welfare departments offering support:
                    <ul>
                        <li><strong>Social Welfare Directorate:</strong> 9008400010</li>
                        <li><strong>Department of Minorities:</strong> 080-22535931</li>
                        <li><strong>Department of OBC Welfare:</strong> 8050770005</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <footer style="text-align: center; padding: 2.5rem 1rem; color: var(--color-frost-500); font-size: 0.85rem; border-top: 1px solid var(--glass-border);">
        UNESCO LUMINA-X &bull; Global Education Equity Initiative &bull; Sovereign Grant Disbursement Protocol
    </footer>

</body>
</html>