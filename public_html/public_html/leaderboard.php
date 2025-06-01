<?php
session_start();
include 'connect.php';

// Zorg dat de gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Fetch Data for Leaderboards ---

// Top CO2-uitstoters
$top_emitters = [];
$sql_top_emitters = "SELECT
                        u.user_id,
                        u.first_name,
                        u.last_name,
                        u.profile_picture_url,
                        SUM(th.co2_emissions) AS total_emitted
                    FROM
                        users u
                    JOIN
                        travelhistory th ON u.user_id = th.user_id
                    WHERE th.co2_emissions > 0
                    GROUP BY
                        u.user_id, u.first_name, u.last_name, u.profile_picture_url
                    ORDER BY
                        total_emitted DESC
                    LIMIT 10"; // Toon top 10
$result_top_emitters = $conn->query($sql_top_emitters);
if ($result_top_emitters) {
    while ($row = $result_top_emitters->fetch_assoc()) {
        $top_emitters[] = $row;
    }
} else {
    // Optioneel: Log fout als query mislukt
    // error_log("Error fetching top emitters: " . $conn->error);
}

// Top CO2-compensatoren - Dynamisch berekend
$top_compensators = [];
$sql_top_compensators = "SELECT
                            u.user_id,
                            u.first_name,
                            u.last_name,
                            u.profile_picture_url,
                            (
                                COALESCE((SELECT SUM(d.amount * (cp_d.effectiveness / 100))
                                          FROM donations d
                                          JOIN compensationprojects cp_d ON d.project_id = cp_d.project_id
                                          WHERE d.user_id = u.user_id), 0)
                                +
                                COALESCE((SELECT SUM(10 * (cp_s.effectiveness / 100)) -- Gaat uit van $10 per abonnementsperiode van compensation_data.php
                                          FROM subscriptions s
                                          JOIN compensationprojects cp_s ON s.project_id = cp_s.project_id
                                          WHERE s.user_id = u.user_id AND s.next_payment_date >= CURDATE()), 0) -- Actieve abonnementen
                            ) AS total_compensated
                        FROM
                            users u
                        HAVING total_compensated > 0
                        ORDER BY
                            total_compensated DESC
                        LIMIT 10";

$result_top_compensators = $conn->query($sql_top_compensators);
if ($result_top_compensators) {
    while ($row = $result_top_compensators->fetch_assoc()) {
        $top_compensators[] = $row;
    }
} else {
    // Optioneel: Log fout of stel een bericht in voor weergave
    // error_log("Error fetching top compensators: " . $conn->error);
    // $compensator_error_message = "Could not retrieve compensator leaderboard data: " . $conn->error;
}

include('side_bar_template.php'); 
?>

<style>
.leaderboard-card .card-body {
    padding: 1rem;
}
.leaderboard-list {
    list-style: none;
    padding-left: 0;
}
.leaderboard-list li {
    display: flex;
    align-items: center;
    padding: 0.75rem 0.25rem;
    border-bottom: 1px solid #eee;
}
.leaderboard-list li:last-child {
    border-bottom: none;
}
.leaderboard-rank {
    font-weight: bold;
    min-width: 30px; /* Aangepast voor mogelijk dubbelcijferige rangen */
    text-align: center;
}
.leaderboard-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-left: 10px;
    margin-right: 15px;
    object-fit: cover;
}
.leaderboard-name {
    flex-grow: 1;
    font-size: 0.95rem;
}
.leaderboard-score {
    font-weight: 500;
    color: #007bff; /* Primaire kleur voor score */
}
.leaderboard-score.emitter {
    color: #dc3545; /* Gevaarkleur voor uitstoot */
}
</style>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Community Leaderboards</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Community</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="leaderboard.php">Leaderboards</a>
            </li>
        </ul>
    </div>

    <!-- Leaderboards Row -->
    <div class="row mt-4">
        <!-- Top CO2 Emitters Leaderboard -->
        <div class="col-md-6">
            <div class="card card-round leaderboard-card">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-smog text-danger"></i> Top CO2 Emitters</h4>
                </div>
                <div class="card-body">
                    <?php if (!$result_top_emitters): ?>
                        <p class="text-danger text-center">Error loading emitters leaderboard: <?php echo htmlspecialchars($conn->error); ?></p>
                    <?php elseif (empty($top_emitters)): ?>
                        <p class="text-muted text-center">No emissions data available for leaderboard.</p>
                    <?php else: ?>
                        <ul class="leaderboard-list">
                            <?php foreach ($top_emitters as $index => $emitter): ?>
                                <li>
                                    <span class="leaderboard-rank"><?php echo $index + 1; ?>.</span>
                                    <img src="<?php echo htmlspecialchars(trim($emitter['profile_picture_url']) ?: 'assets/img/kaiadmin/default.jpg'); ?>" alt="User" class="leaderboard-avatar">
                                    <span class="leaderboard-name"><?php echo htmlspecialchars($emitter['first_name'] . ' ' . $emitter['last_name']); ?></span>
                                    <span class="leaderboard-score emitter"><?php echo number_format($emitter['total_emitted'], 1); ?> kg</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top CO2 Compensators Leaderboard -->
        <div class="col-md-6">
            <div class="card card-round leaderboard-card">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-recycle text-success"></i> Top CO2 Compensators</h4>
                </div>
                <div class="card-body">
                    <?php if (!$result_top_compensators): ?>
                        <p class="text-danger text-center">Error loading compensators leaderboard: <?php echo htmlspecialchars($conn->error); ?></p>
                    <?php elseif (empty($top_compensators)): ?>
                        <p class="text-muted text-center">No compensation data available for leaderboard.</p>
                    <?php else: ?>
                        <ul class="leaderboard-list">
                            <?php foreach ($top_compensators as $index => $compensator): ?>
                                <li>
                                    <span class="leaderboard-rank"><?php echo $index + 1; ?>.</span>
                                    <img src="<?php echo htmlspecialchars(trim($compensator['profile_picture_url']) ?: 'assets/img/kaiadmin/default.jpg'); ?>" alt="User" class="leaderboard-avatar">
                                    <span class="leaderboard-name"><?php echo htmlspecialchars($compensator['first_name'] . ' ' . $compensator['last_name']); ?></span>
                                    <span class="leaderboard-score text-success"><?php echo number_format($compensator['total_compensated'], 1); ?> kg</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// side_bar_template.php includes the closing </div> for main-panel, 
// then </div> for wrapper, then </body> and </html>.
// It also includes common JS files.
?>
