<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Adjusted path
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT * FROM travelhistory WHERE user_id = ? ORDER BY travel_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$travel_history_message = null;
if (isset($_SESSION['travel_history_message'])) {
    $travel_history_message = $_SESSION['travel_history_message'];
    unset($_SESSION['travel_history_message']);
}

include('../side_bar_template.php');
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">My Travel History</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="../home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="../profile.php">Profile</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="view_travel_history.php">Travel History</a></li>
        </ul>
    </div>

    <?php if ($travel_history_message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($travel_history_message['type']); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($travel_history_message['text']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Recorded Travels</h4>
                        <a href="add_travel.php" class="btn btn-primary btn-round ms-auto">
                            <i class="fa fa-plus"></i>
                            Add New Travel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Origin</th>
                                    <th>Destination</th>
                                    <th>Distance (km)</th>
                                    <th>Transport</th>
                                    <th>CO2 Emissions (kg)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date("M d, Y", strtotime($row['travel_date']))); ?></td>
                                            <td><?php echo htmlspecialchars($row['origin']); ?></td>
                                            <td><?php echo htmlspecialchars($row['destination']); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($row['distance_km'], 2)); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($row['transport_mode'])); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($row['co2_emissions'], 2)); ?></td>
                                            <td>
                                                <a href="edit_travel.php?travel_id=<?php echo $row['travel_id']; ?>" class="btn btn-link btn-primary btn-lg" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="delete_travel.php?travel_id=<?php echo $row['travel_id']; ?>" class="btn btn-link btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this travel entry?');">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No travel history found. Add your first trip!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                     <a href="../profile.php" class="btn btn-secondary">Back to Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
// The side_bar_template.php includes closing HTML tags and scripts
?>