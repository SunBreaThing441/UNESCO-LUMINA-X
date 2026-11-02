<?php
session_start();
if (empty($_SESSION['user'])) {
    header("location:login.php?error=unauthorized_access");
    exit();
}
require_once('connect.php');

// Fetch complete applicant records joined with sanction status
$selq = "SELECT p.*, s.collegescholarship, s.hostelscholarship 
         FROM personal_details p 
         LEFT JOIN sanction s ON p.aadhaar = s.aadhaar
         ORDER BY p.aadhaar DESC";
$q = mysqli_query($connect, $selq);

$totalApplicants = 0;
$pendingReview = 0;
$sanctionedCount = 0;
$totalDisbursed = 0;

$applicants = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $applicants[] = $row;
        $totalApplicants++;
        $collegeGrant = (int)($row['collegescholarship'] ?? 0);
        $hostelGrant = (int)($row['hostelscholarship'] ?? 0);
        $combinedGrant = $collegeGrant + $hostelGrant;

        if ($combinedGrant > 0) {
            $sanctionedCount++;
            $totalDisbursed += $combinedGrant;
        } else {
            $pendingReview++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESCO LUMINA-X | Sovereign Authority Command Center</title>

    <!-- Design System & Iconography -->
    <link rel="stylesheet" href="../css/lumina-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="cmd-center-body">

    <!-- Sovereign Administrative Navigation Bar -->
    <header class="lumina-navbar cmd-navbar">
        <a href="data.php" class="lumina-brand">
            <div class="lumina-brand-emblem" style="background: linear-gradient(135deg, #0077D4, #00A3E0);">
                <i class="fa fa-shield"></i>
            </div>
            <div class="lumina-brand-text">
                <span class="lumina-brand-title cmd-brand-title">LUMINA-X SOVEREIGN COMMAND</span>
                <span class="lumina-brand-subtitle" style="color: var(--un-cyan-400);">Government of Karnataka Desk & UNESCO Operations</span>
            </div>
        </a>

        <ul class="lumina-nav-items">
            <li>
                <span class="lumina-nav-badge" style="background: rgba(0, 163, 224, 0.15); color: var(--un-cyan-400); border-color: rgba(0, 163, 224, 0.3);">
                    <i class="fa fa-circle" style="color: #10b981; font-size: 0.6rem;"></i> Live System Connected
                </span>
            </li>
            <li><a href="data.php" class="lumina-nav-link active" style="color: #fff;"><i class="fa fa-cubes"></i> Applicants Grid</a></li>
            <li><a href="logout.php" class="lumina-nav-link" style="color: var(--color-danger);"><i class="fa fa-power-off"></i> Exit Console</a></li>
        </ul>
    </header>

    <!-- Main Command Center Workspace -->
    <main class="cmd-container">
        
        <!-- Section 1: Executive KPI Metrics Strip -->
        <section class="metrics-strip">
            <div class="metric-card">
                <div class="metric-icon-box metric-icon-blue">
                    <i class="fa fa-users"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Total Applicants</span>
                    <span class="metric-value" id="metric-total-count"><?php echo $totalApplicants; ?></span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon-box metric-icon-amber">
                    <i class="fa fa-hourglass-half"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Pending Review</span>
                    <span class="metric-value"><?php echo $pendingReview; ?></span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon-box metric-icon-green">
                    <i class="fa fa-check-circle-o"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Sanctioned Grants</span>
                    <span class="metric-value" id="metric-sanctioned-count"><?php echo $sanctionedCount; ?></span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon-box metric-icon-cyan">
                    <i class="fa fa-money"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Disbursed Capital</span>
                    <span class="metric-value">₹<?php echo number_format($totalDisbursed); ?></span>
                </div>
            </div>
        </section>

        <!-- Section 2: Command & Control Filter Ribbon -->
        <section class="cmd-ribbon">
            <div class="search-box-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input type="text" id="cmdSearchInput" class="cmd-search-input" placeholder="Search by Candidate Name, Aadhaar UID, Phone, or Email...">
            </div>

            <div class="filter-group">
                <select id="filterCategory" class="cmd-select">
                    <option value="">All Categories</option>
                    <option value="cat1">Category 1</option>
                    <option value="cat2a">Category 2A</option>
                    <option value="cat2b">Category 2B</option>
                    <option value="cat3a">Category 3A</option>
                    <option value="cat3b">Category 3B</option>
                    <option value="sc">Scheduled Caste (SC)</option>
                    <option value="st">Scheduled Tribe (ST)</option>
                </select>

                <select id="filterHostel" class="cmd-select">
                    <option value="">All Residencies</option>
                    <option value="yes">Hostel Residents</option>
                    <option value="no">Day Scholars</option>
                </select>

                <select id="filterStatus" class="cmd-select">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending Review</option>
                    <option value="sanctioned">Sanctioned</option>
                </select>

                <!-- View Switcher -->
                <div class="view-toggle-pills">
                    <button type="button" class="view-toggle-btn active" id="btnViewCards" title="Card Matrix View">
                        <i class="fa fa-th-large"></i> Cards
                    </button>
                    <button type="button" class="view-toggle-btn" id="btnViewTable" title="Tabular Matrix View">
                        <i class="fa fa-list"></i> Table
                    </button>
                </div>
            </div>
        </section>

        <!-- Section 3A: High-Tech Responsive Cards Grid -->
        <section class="applicants-grid" id="cardsContainer">
            <?php if (empty($applicants)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: var(--cmd-glass-card); border-radius: 18px; border: 1px solid var(--cmd-glass-border);">
                    <i class="fa fa-folder-open-o" style="font-size: 3rem; color: var(--un-cyan-400); margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff;">No Applicant Dossiers Found</h3>
                    <p style="color: var(--color-frost-300); font-size: 0.9rem; margin-top: 0.5rem;">Registered scholarship candidates will appear here automatically.</p>
                </div>
            <?php else: ?>
                <?php foreach ($applicants as $app): 
                    $colGrant = (int)($app['collegescholarship'] ?? 0);
                    $hstGrant = (int)($app['hostelscholarship'] ?? 0);
                    $isSanctioned = ($colGrant + $hstGrant) > 0;
                    $statusClass = $isSanctioned ? 'sanctioned' : 'pending';
                    $initials = strtoupper(substr($app['name'] ?? 'U', 0, 1));
                ?>
                    <article class="applicant-card" 
                             data-name="<?php echo strtolower(htmlspecialchars($app['name'])); ?>"
                             data-aadhaar="<?php echo htmlspecialchars($app['aadhaar']); ?>"
                             data-email="<?php echo strtolower(htmlspecialchars($app['email'])); ?>"
                             data-phone="<?php echo htmlspecialchars($app['number']); ?>"
                             data-category="<?php echo strtolower(htmlspecialchars($app['category'])); ?>"
                             data-hostel="<?php echo strtolower(htmlspecialchars($app['hostel'])); ?>"
                             data-status="<?php echo $statusClass; ?>">
                        
                        <!-- Header with Avatar and Name -->
                        <div class="applicant-header">
                            <div class="applicant-avatar"><?php echo $initials; ?></div>
                            <div class="applicant-title-info">
                                <h3 class="applicant-name" title="<?php echo htmlspecialchars($app['name']); ?>">
                                    <?php echo htmlspecialchars($app['name']); ?>
                                </h3>
                                <div class="applicant-uid-badge">
                                    <i class="fa fa-id-badge"></i> <?php echo htmlspecialchars($app['aadhaar']); ?>
                                </div>
                            </div>
                            <?php if ($isSanctioned): ?>
                                <span style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 12px; white-space: nowrap;">
                                    <i class="fa fa-check"></i> Sanctioned
                                </span>
                            <?php else: ?>
                                <span style="background: rgba(245, 158, 11, 0.2); border: 1px solid #f59e0b; color: #fbbf24; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 12px; white-space: nowrap;">
                                    <i class="fa fa-clock-o"></i> Pending
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Applicant Contact & Demographics Grid -->
                        <div class="applicant-details-grid">
                            <div class="detail-col">
                                <span class="detail-lbl">Contact Phone</span>
                                <span class="detail-val font-mono"><i class="fa fa-phone" style="color: var(--un-cyan-400);"></i> <?php echo htmlspecialchars($app['number']); ?></span>
                            </div>
                            <div class="detail-col">
                                <span class="detail-lbl">Category / Caste</span>
                                <span class="detail-val"><?php echo strtoupper(htmlspecialchars($app['category'])); ?> (<?php echo htmlspecialchars($app['caste']); ?>)</span>
                            </div>
                            <div class="detail-col">
                                <span class="detail-lbl">Annual Income</span>
                                <span class="detail-val font-mono">₹<?php echo number_format((float)($app['income'] ?? 0)); ?></span>
                            </div>
                            <div class="detail-col">
                                <span class="detail-lbl">Residency Mode</span>
                                <span class="detail-val">
                                    <?php if ($app['hostel'] === 'yes'): ?>
                                        <span style="color: var(--un-cyan-400);"><i class="fa fa-building"></i> Hostel Resident</span>
                                    <?php else: ?>
                                        <span style="color: var(--color-frost-300);"><i class="fa fa-home"></i> Day Scholar</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Email display -->
                        <div style="font-size: 0.8rem; color: var(--color-frost-300); display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <i class="fa fa-envelope-o" style="color: var(--un-cyan-400);"></i> <?php echo htmlspecialchars($app['email']); ?>
                        </div>

                        <!-- Polished Action Glyphs Strip -->
                        <div class="applicant-actions">
                            <!-- Glyph 1: View PDF Dossier -->
                            <a href="view.php?aadhaar=<?php echo urlencode($app['aadhaar']); ?>" target="_blank" class="btn-glyph btn-glyph-view" title="Generate & Inspect Complete PDF Dossier">
                                <i class="fa fa-file-pdf-o"></i> View
                            </a>

                            <!-- Glyph 2: Accept & Sanction Grant -->
                            <a href="email.php?aadhaar=<?php echo urlencode($app['aadhaar']); ?>" 
                               class="btn-glyph btn-glyph-accept" 
                               onclick="return confirmSanction('<?php echo htmlspecialchars(addslashes($app['name'])); ?>', '<?php echo htmlspecialchars($app['category']); ?>', '<?php echo htmlspecialchars($app['hostel']); ?>')"
                               title="Authorize Financial Sanction and Transmit Automated Email">
                                <i class="fa fa-check-circle"></i> Accept
                            </a>

                            <!-- Glyph 3: Reject & Purge -->
                            <a href="delete.php?aadhaar=<?php echo urlencode($app['aadhaar']); ?>" 
                               class="btn-glyph btn-glyph-reject" 
                               onclick="return confirmRejection('<?php echo htmlspecialchars(addslashes($app['name'])); ?>')"
                               title="Disqualify Application and Trigger Cascading Record Purge">
                                <i class="fa fa-trash"></i> Reject
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- Section 3B: Cybernetic Tabular Matrix View -->
        <section class="cmd-table-container" id="tableContainer">
            <table class="cmd-table">
                <thead>
                    <tr>
                        <th>Aadhaar Identification</th>
                        <th>Applicant Name</th>
                        <th>Contact Number</th>
                        <th>Email Destination</th>
                        <th>Category</th>
                        <th>Residency</th>
                        <th>Status</th>
                        <th style="text-align: center;">Operational Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): 
                        $colGrant = (int)($app['collegescholarship'] ?? 0);
                        $hstGrant = (int)($app['hostelscholarship'] ?? 0);
                        $isSanctioned = ($colGrant + $hstGrant) > 0;
                    ?>
                        <tr class="table-row-item"
                            data-name="<?php echo strtolower(htmlspecialchars($app['name'])); ?>"
                            data-aadhaar="<?php echo htmlspecialchars($app['aadhaar']); ?>"
                            data-email="<?php echo strtolower(htmlspecialchars($app['email'])); ?>"
                            data-phone="<?php echo htmlspecialchars($app['number']); ?>"
                            data-category="<?php echo strtolower(htmlspecialchars($app['category'])); ?>"
                            data-hostel="<?php echo strtolower(htmlspecialchars($app['hostel'])); ?>"
                            data-status="<?php echo $isSanctioned ? 'sanctioned' : 'pending'; ?>">
                            <td class="font-mono" style="color: var(--un-cyan-400);"><?php echo htmlspecialchars($app['aadhaar']); ?></td>
                            <td style="font-weight: 700; color: #fff;"><?php echo htmlspecialchars($app['name']); ?></td>
                            <td class="font-mono"><?php echo htmlspecialchars($app['number']); ?></td>
                            <td><?php echo htmlspecialchars($app['email']); ?></td>
                            <td><span style="background: rgba(0, 119, 212, 0.2); padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.75rem; color: #60a5fa;"><?php echo strtoupper(htmlspecialchars($app['category'])); ?></span></td>
                            <td><?php echo ($app['hostel'] === 'yes') ? 'Hostel' : 'Day Scholar'; ?></td>
                            <td>
                                <?php if ($isSanctioned): ?>
                                    <span style="color: #34d399; font-weight: 700; font-size: 0.8rem;"><i class="fa fa-check"></i> Sanctioned</span>
                                <?php else: ?>
                                    <span style="color: #fbbf24; font-weight: 700; font-size: 0.8rem;"><i class="fa fa-clock-o"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <a href="view.php?aadhaar=<?php echo urlencode($app['aadhaar']); ?>" target="_blank" class="btn-glyph btn-glyph-view" style="padding: 0.35rem 0.75rem;">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </a>
                                    <a href="email.php?aadhaar=<?php echo urlencode($app['aadhaar']); ?>" 
                                       class="btn-glyph btn-glyph-accept" 
                                       style="padding: 0.35rem 0.75rem;"
                                       onclick="return confirmSanction('<?php echo htmlspecialchars(addslashes($app['name'])); ?>', '<?php echo htmlspecialchars($app['category']); ?>', '<?php echo htmlspecialchars($app['hostel']); ?>')">
                                        <i class="fa fa-check-circle"></i>
                                    </a>
                                    <a href="delete.php?aadhaar=<?php echo urlencode($app['aadhaar']); ?>" 
                                       class="btn-glyph btn-glyph-reject" 
                                       style="padding: 0.35rem 0.75rem;"
                                       onclick="return confirmRejection('<?php echo htmlspecialchars(addslashes($app['name'])); ?>')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </main>

    <!-- Client-Side Filter & Modal Interactivity -->
    <script>
        // High-assurance sanction confirmation
        function confirmSanction(name, category, hostel) {
            let collegeAmt = (category === 'cat1' || category === 'sc' || category === 'st') ? '₹65,000' : '₹20,000';
            let hostelAmt = (hostel === 'yes') ? ' + ₹10,000 (Hostel)' : '';
            return confirm(`SOVEREIGN GRANT AUTHORIZATION:\n\nAuthorize scholarship sanction for ${name}?\nAllocated Grant: ${collegeAmt}${hostelAmt}\n\nAutomated transactional notification will be dispatched immediately via SMTP.`);
        }

        // High-assurance rejection confirmation
        function confirmRejection(name) {
            return confirm(`PERMANENT DISQUALIFICATION WARNING:\n\nAre you sure you want to reject the application for ${name}?\n\nThis will trigger an automated rejection email and permanently purge all academic, hostel, and banking records via cascading relational delete.`);
        }

        // Dynamic Instant Search & Multi-Filter Logic
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('cmdSearchInput');
            const categorySelect = document.getElementById('filterCategory');
            const hostelSelect = document.getElementById('filterHostel');
            const statusSelect = document.getElementById('filterStatus');

            const cardItems = document.querySelectorAll('.applicant-card');
            const tableRows = document.querySelectorAll('.table-row-item');

            function applyFilters() {
                const searchVal = searchInput.value.toLowerCase().trim();
                const catVal = categorySelect.value.toLowerCase();
                const hostelVal = hostelSelect.value.toLowerCase();
                const statusVal = statusSelect.value.toLowerCase();

                function matches(el) {
                    const name = el.getAttribute('data-name') || '';
                    const aadh = el.getAttribute('data-aadhaar') || '';
                    const email = el.getAttribute('data-email') || '';
                    const phone = el.getAttribute('data-phone') || '';
                    const cat = el.getAttribute('data-category') || '';
                    const hostel = el.getAttribute('data-hostel') || '';
                    const status = el.getAttribute('data-status') || '';

                    const matchesSearch = !searchVal || name.includes(searchVal) || aadh.includes(searchVal) || email.includes(searchVal) || phone.includes(searchVal);
                    const matchesCategory = !catVal || cat === catVal;
                    const matchesHostel = !hostelVal || hostel === hostelVal;
                    const matchesStatus = !statusVal || status === statusVal;

                    return matchesSearch && matchesCategory && matchesHostel && matchesStatus;
                }

                cardItems.forEach(card => {
                    card.style.display = matches(card) ? 'flex' : 'none';
                });

                tableRows.forEach(row => {
                    row.style.display = matches(row) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', applyFilters);
            categorySelect.addEventListener('change', applyFilters);
            hostelSelect.addEventListener('change', applyFilters);
            statusSelect.addEventListener('change', applyFilters);

            // View Mode Switcher (Cards vs Table)
            const btnCards = document.getElementById('btnViewCards');
            const btnTable = document.getElementById('btnViewTable');
            const cardsContainer = document.getElementById('cardsContainer');
            const tableContainer = document.getElementById('tableContainer');

            btnCards.addEventListener('click', () => {
                btnCards.classList.add('active');
                btnTable.classList.remove('active');
                cardsContainer.style.display = 'grid';
                tableContainer.style.display = 'none';
            });

            btnTable.addEventListener('click', () => {
                btnTable.classList.add('active');
                btnCards.classList.remove('active');
                cardsContainer.style.display = 'none';
                tableContainer.style.display = 'block';
            });

            // Universal HTTP Data Integration: In-App PDF Dossier Viewer
            document.querySelectorAll('.btn-glyph-view').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = btn.getAttribute('href');
                    const card = btn.closest('.applicant-card') || btn.closest('.table-row-item');
                    const name = card ? card.getAttribute('data-name').toUpperCase() : 'Candidate';
                    window.luminaHttp.renderPdfModal(url, `Official Dossier: ${name}`);
                });
            });

            // Universal HTTP Data Integration: Async Sanction Dispatcher & SMTP Trigger
            document.querySelectorAll('.btn-glyph-accept').forEach(btn => {
                btn.removeAttribute('onclick'); // Delegate to client dispatcher
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const card = btn.closest('.applicant-card') || btn.closest('.table-row-item');
                    if (!card) return;

                    const aadhaar = card.getAttribute('data-aadhaar');
                    const name = card.getAttribute('data-name')?.toUpperCase() || 'Candidate';
                    const category = card.getAttribute('data-category');
                    const hostel = card.getAttribute('data-hostel');

                    if (confirmSanction(name, category, hostel)) {
                        await window.luminaHttp.dispatchSanction(aadhaar, { name, category, hostel });
                    }
                });
            });

            // Universal HTTP Data Integration: Async Rejection & Database Cascading Purge
            document.querySelectorAll('.btn-glyph-reject').forEach(btn => {
                btn.removeAttribute('onclick'); // Delegate to client dispatcher
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const card = btn.closest('.applicant-card') || btn.closest('.table-row-item');
                    if (!card) return;

                    const aadhaar = card.getAttribute('data-aadhaar');
                    const name = card.getAttribute('data-name')?.toUpperCase() || 'Candidate';

                    if (confirmRejection(name)) {
                        await window.luminaHttp.dispatchRejection(aadhaar, name);
                    }
                });
            });
        });
    </script>

    <!-- Universal HTTP Client & Sovereign Navigation Guard -->
    <script src="../js/lumina-http-client.js"></script>
    <script src="../js/lumina-auth-guard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.luminaGuard = new LuminaAuthGuard({ requiredRole: 'sovereign' });
        });
    </script>
</body>
</html>