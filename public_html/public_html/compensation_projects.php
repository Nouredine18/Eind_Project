<?php
session_start();
include 'connect.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialiseer filtervariabelen
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <!-- Include necessary CSS and JS files -->
    <link rel="stylesheet" href="path/to/your/css/file.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <style>
        .card-img-top {
            height: 300px;
            object-fit: cover;
        }
        .card-body {
            height: 100%;
        }
    </style>
</head>
<body>
  <?php include('side_bar_template.php') ?>
    <!-- Your existing HTML content -->

    <div class="container">
        <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                    <h3 class="fw-bold mb-3">Dashboard</h3>
                    <h6 class="op-7 mb-2">Free Bootstrap 5 Admin Dashboard</h6>
                </div>
                <div class="ms-md-auto py-2 py-md-0">
                    <a href="#" class="btn btn-label-info btn-round me-2">Manage</a>
                    <a href="#" class="btn btn-primary btn-round">Add Customer</a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary bubble-shadow-small">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <?php
                                // Haal totale CO2-uitstoot vd gebruiker op
                                $sql = "SELECT SUM(co2_emissions) AS total_emissions 
                                        FROM travelhistory
                                        WHERE user_id = ?";
                                $stmt = $conn->prepare($sql);
                                if ($stmt === false) {
                                    die("Error preparing statement: " . $conn->error);
                                }
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    $data = $result->fetch_assoc();
                                    $totalEmissions = $data['total_emissions'] ?? 0;
                                } else {
                                    $totalEmissions = 0;
                                }
                                $stmt->close();
                                ?>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">CO-2 Emission</p>
                                        <h4 class="card-title"><p>CO2 uitstoot: <?= number_format($totalEmissions, 2) ?> kg</p>
                                        <?php if ($totalEmissions > 1000) { ?>
                                            <p style="color: red;">Overweeg duurzamere reisopties!</p>
                                        <?php } ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Voeg CO2 compensatieprojecten toe onder deze lijn -->
                <div class="col-md-12">
                    <h3 class="fw-bold mb-3">CO2 Compensation Projects</h3>
                    <div class="row">
                        <div class="col-md-12">
                            <form method="get" action="compensation_projects.php" class="mb-4">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search projects" value="<?= htmlspecialchars($search) ?>">
                                    <select name="filter_type" class="form-select">
                                        <option value="">Filter by</option>
                                        <option value="effectiveness" <?= $filter_type == 'effectiveness' ? 'selected' : '' ?>>Effectiveness</option>
                                        <option value="created_at" <?= $filter_type == 'created_at' ? 'selected' : '' ?>>Created At</option>
                                        <option value="updated_at" <?= $filter_type == 'updated_at' ? 'selected' : '' ?>>Last Updated</option>
                                    </select>
                                    <input type="text" name="filter_value" class="form-control" placeholder="Filter value" value="<?= htmlspecialchars($filter_value) ?>">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        // Bouw SQL query dynamisch op basis v filters
                        $sql = "SELECT project_id, name, description, project_type, effectiveness, created_at, updated_at, compensationProjectImage FROM compensationprojects WHERE 1=1";

                        if (!empty($search)) {
                            $sql .= " AND (name LIKE ? OR description LIKE ?)";
                        }

                        if (!empty($filter_type) && !empty($filter_value)) {
                            if ($filter_type == 'effectiveness') {
                                $sql .= " AND effectiveness >= ?";
                            } elseif ($filter_type == 'created_at' || $filter_type == 'updated_at') {
                                $sql .= " AND $filter_type >= ?";
                            }
                        }

                        $stmt = $conn->prepare($sql);
                        if ($stmt === false) {
                            die("Error preparing statement: " . $conn->error);
                        }

                        if (!empty($search) && !empty($filter_type) && !empty($filter_value)) {
                            $search_param = '%' . $search . '%';
                            $stmt->bind_param("sss", $search_param, $search_param, $filter_value);
                        } elseif (!empty($search)) {
                            $search_param = '%' . $search . '%';
                            $stmt->bind_param("ss", $search_param, $search_param);
                        } elseif (!empty($filter_type) && !empty($filter_value)) {
                            $stmt->bind_param("s", $filter_value);
                        }

                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($project = $result->fetch_assoc()) {
                        ?>
                        <div class="col-md-4">
                            <div class="card">
                                <img src="<?= htmlspecialchars($project['compensationProjectImage']) ?>" class="card-img-top" alt="<?= htmlspecialchars($project['name']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($project['name']) ?></h5>
                                    <p class="card-text"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                                    <p><strong>Effectiveness:</strong> <?= htmlspecialchars($project['effectiveness']) ?>%</p>
                                    <p><strong>Created At:</strong> <?= htmlspecialchars($project['created_at']) ?></p>
                                    <p><strong>Last Updated:</strong> <?= htmlspecialchars($project['updated_at']) ?></p>
                                    <a href="project_details.php?project_id=<?= $project['project_id'] ?>" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                            echo "<p>No projects found.</p>";
                        }
                        ?>
                    </div>
                </div>
                <!-- Einde van CO2 compensatieprojecten -->
            </div>
        </div>
    </div>
</body>
</html>