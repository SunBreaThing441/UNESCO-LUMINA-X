<?php
session_start();
if (empty($_SESSION['email'])) {         
    header("location:login.php"); 
    exit();
}

require_once("connect.php");
$aa = $_SESSION['aadh'];
$name = $_SESSION['name'] ?? 'Scholar';

$sql = "SELECT * FROM `sanction` WHERE aadhaar = '$aa'";
$result = mysqli_query($connect, $sql);
$a = $result ? $result->fetch_assoc() : null;

$colGrant = (int)($a['collegescholarship'] ?? 0);
$hstGrant = (int)($a['hostelscholarship'] ?? 0);
$totalGrant = $colGrant + $hstGrant;
$isSanctioned = $totalGrant > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Live Grant Disbursement Monitor</title>
    
    <link rel="stylesheet" href="css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .status-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }
        .status-hero {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--glass-shadow-lg);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .status-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #00A3E0);
        }
        .grant-cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .grant-card {
            background: var(--glass-bg-primary);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            padding: 2rem;
            box-shadow: var(--glass-shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }
        .grant-badge {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--un-blue-600);
            background: rgba(0, 119, 212, 0.1);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            margin-bottom: 1rem;
        }
        .grant-amount {
            font-family: var(--font-mono);
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--un-blue-900);
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }
        .grant-status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .total-grant-banner {
            background: linear-gradient(135deg, var(--un-blue-800) 0%, var(--un-blue-600) 100%);
            color: #fff;
            border-radius: 18px;
            padding: 2rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 119, 212, 0.25);
        }
        @media (max-width: 700px) {
            .grant-cards-grid {
                grid-template-columns: 1fr;
            }
            .total-grant-banner {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
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
            <li><span class="lumina-nav-badge"><i class="fa fa-shield"></i> UID: <?php echo htmlspecialchars($aa); ?></span></li>
            <li><a href="home.php" class="lumina-nav-link"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="logout.php" class="lumina-nav-link" style="color: var(--color-danger);"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </header>

    <main class="status-container">
        
        <section class="status-hero">
            <div style="width:64px; height:64px; border-radius:50%; margin:0 auto 1.25rem; display:flex; align-items:center; justify-content:center; font-size:1.6rem; <?php echo $isSanctioned ? 'background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3);' : 'background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid rgba(245,158,11,0.3);'; ?>">
                <i class="fa <?php echo $isSanctioned ? 'fa-check' : 'fa-hourglass-half'; ?>"></i>
            </div>

            <h1 style="font-family: var(--font-display); font-size: 1.85rem; font-weight: 800; color: var(--un-blue-900);">
                <?php echo $isSanctioned ? 'Grant Sanction Approved & Disbursed' : 'Dossier Under Administrative Appraisal'; ?>
            </h1>
            
            <p style="color: var(--color-frost-600); font-size: 0.95rem; max-width: 600px; margin: 0.5rem auto 0;">
                <?php if ($isSanctioned): ?>
                    Your scholarship grants have been officially approved by the Government Desk and authorized for DBT bank credit.
                <?php else: ?>
                    Your application is queued in the verification matrix. Once inspected, sanctioned grants will reflect in real time.
                <?php endif; ?>
            </p>
        </section>

        <!-- Grant Breakdown Cards -->
        <section class="grant-cards-grid">
            
            <!-- College Scholarship -->
            <div class="grant-card">
                <span class="grant-badge"><i class="fa fa-university"></i> Academic Tuition Grant</span>
                <div class="grant-amount">₹<?php echo number_format($colGrant); ?></div>
                <div class="grant-status-indicator" style="<?php echo $colGrant > 0 ? 'color:#10b981;' : 'color:#f59e0b;'; ?>">
                    <i class="fa <?php echo $colGrant > 0 ? 'fa-check-circle' : 'fa-circle-o-notch fa-spin'; ?>"></i>
                    <?php echo $colGrant > 0 ? 'Sanctioned to Bank' : 'Awaiting Desk Approval'; ?>
                </div>
            </div>

            <!-- Hostel Scholarship -->
            <div class="grant-card">
                <span class="grant-badge"><i class="fa fa-building"></i> Residential Hostel Stipend</span>
                <div class="grant-amount">₹<?php echo number_format($hstGrant); ?></div>
                <div class="grant-status-indicator" style="<?php echo $hstGrant > 0 ? 'color:#10b981;' : 'color:#f59e0b;'; ?>">
                    <i class="fa <?php echo $hstGrant > 0 ? 'fa-check-circle' : 'fa-circle-o-notch fa-spin'; ?>"></i>
                    <?php echo $hstGrant > 0 ? 'Sanctioned to Bank' : 'Awaiting Desk Approval'; ?>
                </div>
            </div>

        </section>

        <!-- Total Disbursement Banner -->
        <section class="total-grant-banner">
            <div>
                <span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.85;">
                    Combined Authorized Allocation
                </span>
                <h2 style="font-family: var(--font-display); font-size: 1.5rem; font-weight: 800; margin-top: 0.25rem;">
                    Total Scholastic Disbursement
                </h2>
            </div>
            <div style="font-family: var(--font-mono); font-size: 2.4rem; font-weight: 800; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                ₹<?php echo number_format($totalGrant); ?>
            </div>
        </section>

        <!-- Quick Back Navigation -->
        <div style="text-align: center; margin-top: 2.5rem;">
            <a href="home.php" class="btn-lumina btn-secondary">
                <i class="fa fa-arrow-left"></i> Return to Scholar Console
            </a>
        </div>

    </main>

    <!-- Universal HTTP Client & Client-Side Navigation Guard -->
    <script src="js/lumina-http-client.js"></script>
    <script src="js/lumina-auth-guard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.luminaGuard = new LuminaAuthGuard({ requiredRole: 'scholar' });
        });
    </script>
</body>
</html>