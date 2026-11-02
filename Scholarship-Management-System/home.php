<?php 
session_start();
if (empty($_SESSION['email'])) {         
    header("location:login.php"); 
    exit();
}
$name = $_SESSION['name'] ?? 'Scholar';
$aadh = $_SESSION['aadh'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Scholar Console</title>
    <link rel="stylesheet" href="css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .dashboard-container {
            max-width: 1100px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }
        .hero-banner {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--glass-shadow-lg);
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--un-blue-500), var(--un-cyan-500));
        }
        .action-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        .action-card {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            padding: 2rem;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: var(--glass-shadow);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }
        .action-card:hover {
            transform: translateY(-6px);
            border-color: var(--un-blue-400);
            box-shadow: 0 20px 40px rgba(0, 119, 212, 0.15);
        }
        .card-icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1.25rem;
            transition: transform var(--transition-normal);
        }
        .action-card:hover .card-icon-circle {
            transform: scale(1.1) rotate(5deg);
        }
        .card-title {
            font-family: var(--font-display);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--un-blue-900);
            margin-bottom: 0.5rem;
        }
        .card-desc {
            font-size: 0.85rem;
            color: var(--color-frost-600);
            line-height: 1.4;
        }
        @media (max-width: 800px) {
            .action-cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

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
            <li><span class="lumina-nav-badge"><i class="fa fa-shield"></i> UID: <?php echo htmlspecialchars($aadh); ?></span></li>
            <li><a href="home.php" class="lumina-nav-link active"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="about.php" class="lumina-nav-link"><i class="fa fa-info-circle"></i> About</a></li>
            <li><a href="logout.php" class="lumina-nav-link" style="color: var(--color-danger);"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </header>

    <main class="dashboard-container">
        
        <section class="hero-banner">
            <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--un-blue-500);">
                Scholar Session Active
            </span>
            <h1 style="font-family: var(--font-display); font-size: 2rem; font-weight: 800; color: var(--un-blue-900); margin: 0.25rem 0 0.5rem;">
                Welcome back, <?php echo htmlspecialchars($name); ?>
            </h1>
            <p style="color: var(--color-frost-600); font-size: 0.95rem; max-width: 650px;">
                Manage your Universal Scholastic Dossier, track live DBT grant sanction disbursements, and download official UNESCO accreditation receipts.
            </p>
        </section>

        <section class="action-cards-grid">
            
            <!-- Card 1: Multi-Step Application Stepper -->
            <a href="personal.php" class="action-card">
                <div class="card-icon-circle" style="background: rgba(0, 119, 212, 0.12); color: var(--un-blue-500); border: 1px solid rgba(0, 119, 212, 0.25);">
                    <i class="fa fa-pencil-square-o"></i>
                </div>
                <h2 class="card-title">Universal Grant Stepper</h2>
                <p class="card-desc">Complete or modify your 6-stage scholastic dossier with zero page reloads.</p>
                <span style="margin-top: 1.25rem; font-size: 0.8rem; font-weight: 700; color: var(--un-blue-600);">
                    Launch Application <i class="fa fa-arrow-right"></i>
                </span>
            </a>

            <!-- Card 2: Live Sanction Status -->
            <a href="scholarship_status.php" class="action-card">
                <div class="card-icon-circle" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25);">
                    <i class="fa fa-line-chart"></i>
                </div>
                <h2 class="card-title">Live Grant Monitor</h2>
                <p class="card-desc">Track real-time college tuition and boarding allowance sanctions.</p>
                <span style="margin-top: 1.25rem; font-size: 0.8rem; font-weight: 700; color: #10b981;">
                    Inspect Status <i class="fa fa-arrow-right"></i>
                </span>
            </a>

            <!-- Card 3: Download Acknowledgement -->
            <!-- Card 3: Download Acknowledgement -->
            <a href="acknowledgement.php" id="btnHomeAckPdf" class="action-card">
                <div class="card-icon-circle" style="background: rgba(0, 163, 224, 0.12); color: var(--un-cyan-500); border: 1px solid rgba(0, 163, 224, 0.25);">
                    <i class="fa fa-file-pdf-o"></i>
                </div>
                <h2 class="card-title">Official Receipt (PDF)</h2>
                <p class="card-desc">Stream and print your Government of Karnataka & UNESCO Acknowledgement.</p>
                <span style="margin-top: 1.25rem; font-size: 0.8rem; font-weight: 700; color: var(--un-cyan-500);">
                    View & Print PDF <i class="fa fa-file-pdf-o"></i>
                </span>
            </a>

        </section>

    </main>

    <!-- Universal HTTP Client & Client-Side Navigation Guard -->
    <script src="js/lumina-http-client.js"></script>
    <script src="js/lumina-auth-guard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.luminaGuard = new LuminaAuthGuard({ requiredRole: 'scholar' });

            const btnHomeAck = document.getElementById('btnHomeAckPdf');
            if (btnHomeAck) {
                btnHomeAck.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.luminaHttp.renderPdfModal('acknowledgement.php', 'Official Scholarship Acknowledgement Receipt');
                });
            }
        });
    </script>
</body>
</html>