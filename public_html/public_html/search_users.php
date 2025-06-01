<?php
session_start();
include 'connect.php'; // Establishes $conn

if (!isset($_SESSION['user_id'])) {
    // For AJAX requests, if not logged in, send an error. For direct access, redirect.
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
        exit();
    } else {
        header('Location: login.php');
        exit();
    }
}

$current_user_id = $_SESSION['user_id'];
$action_message = null;

// Handle Follow/Unfollow Actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $user_to_affect_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_to_affect_id === 0 || $user_to_affect_id === $current_user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user ID or cannot follow yourself.']);
        exit();
    }

    if ($_POST['action'] == 'follow') {
        $stmt = $conn->prepare("INSERT IGNORE INTO user_followers (follower_id, following_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $current_user_id, $user_to_affect_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User followed.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to follow user: ' . $stmt->error]);
        }
        $stmt->close();
    } elseif ($_POST['action'] == 'unfollow') {
        $stmt = $conn->prepare("DELETE FROM user_followers WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $current_user_id, $user_to_affect_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User unfollowed.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to unfollow user: ' . $stmt->error]);
        }
        $stmt->close();
    }
    exit();
}

$search_results = [];
$is_search_active = isset($_GET['search_term']) && trim($_GET['search_term']) !== '';
$list_title = "Suggested Users"; // Default title for the list
$stmt_prepared_successfully = false; // Flag to track if statement was prepared

if ($is_search_active) {
    $search_term_value = trim($_GET['search_term']);
    $search_query_like = "%" . $search_term_value . "%";
    $list_title = "Search Results for \"" . htmlspecialchars($search_term_value) . "\"";

    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.profile_picture_url,
                   EXISTS(SELECT 1 FROM user_followers uf WHERE uf.follower_id = ? AND uf.following_id = u.user_id) as is_following
            FROM users u
            WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?) AND u.user_id != ?
            ORDER BY u.first_name, u.last_name
            LIMIT 20";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isssi", $current_user_id, $search_query_like, $search_query_like, $search_query_like, $current_user_id);
        $stmt_prepared_successfully = true;
    } else {
        $action_message = "Error preparing search statement: " . $conn->error;
    }
} else {
    // No search term, display some default users
    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.profile_picture_url,
                   EXISTS(SELECT 1 FROM user_followers uf WHERE uf.follower_id = ? AND uf.following_id = u.user_id) as is_following
            FROM users u
            WHERE u.user_id != ?
            ORDER BY u.first_name, u.last_name 
            LIMIT 20";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $current_user_id, $current_user_id);
        $stmt_prepared_successfully = true;
    } else {
        $action_message = "Error preparing user list statement: " . $conn->error;
    }
}

if ($stmt_prepared_successfully) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    } else {
        $action_message = "Error executing user query: " . $stmt->error;
    }
    $stmt->close();
}
// If $stmt_prepared_successfully is false, $action_message should already contain the prepare error from the blocks above.

include 'side_bar_template.php'; // Includes HTML head, sidebar, and topbar
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Search Users</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Community</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="search_users.php">Search Users</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Find and Follow Users</h4>
                </div>
                <div class="card-body">
                    <?php if ($action_message): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($action_message); ?></div>
                    <?php endif; ?>
                    <div id="ajax-message" class="alert" style="display:none;"></div>

                    <form method="GET" action="search_users.php" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search_term" class="form-control" placeholder="Enter name or email..." value="<?php echo isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : ''; ?>" >
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>

                    <?php if (!empty($list_title)): ?>
                        <h5 class="mb-3"><?php echo $list_title; ?></h5>
                    <?php endif; ?>

                    <?php if (empty($search_results)): ?>
                        <?php if ($is_search_active): ?>
                            <p class="text-muted">No users found matching your search criteria.</p>
                        <?php else: // Not a search, and no users found (e.g. initial load, no other users) ?>
                            <p class="text-muted">No other users to display at the moment.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="list-group" id="user-search-results">
                            <?php foreach ($search_results as $user): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center search-result-item" data-user-id="<?php echo $user['user_id']; ?>">
                                    <div>
                                        <img src="<?php echo htmlspecialchars(trim($user['profile_picture_url']) ?: 'assets/img/kaiadmin/default.jpg'); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>" class="avatar-xs rounded-circle me-2">
                                        <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                    </div>
                                    <button class="btn btn-sm <?php echo $user['is_following'] ? 'btn-danger btn-unfollow' : 'btn-success btn-follow'; ?>" data-user-id="<?php echo $user['user_id']; ?>">
                                        <?php echo $user['is_following'] ? 'Unfollow' : 'Follow'; ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-xs { width: 32px; height: 32px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultsContainer = document.getElementById('user-search-results');
    const ajaxMessageDiv = document.getElementById('ajax-message');

    function displayAjaxMessage(message, type = 'info') {
        ajaxMessageDiv.textContent = message;
        ajaxMessageDiv.className = 'alert alert-' + type; // e.g., alert-success, alert-danger
        ajaxMessageDiv.style.display = 'block';
        setTimeout(() => { ajaxMessageDiv.style.display = 'none'; }, 3000);
    }

    if (resultsContainer) {
        resultsContainer.addEventListener('click', function(event) {
            const button = event.target.closest('button.btn-follow, button.btn-unfollow');
            if (!button) return;

            event.preventDefault();
            const userIdToAffect = button.dataset.userId;
            const action = button.classList.contains('btn-follow') ? 'follow' : 'unfollow';

            const formData = new FormData();
            formData.append('action', action);
            formData.append('user_id', userIdToAffect);

            fetch('search_users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayAjaxMessage(data.message, 'success');
                    if (action === 'follow') {
                        button.classList.remove('btn-success', 'btn-follow');
                        button.classList.add('btn-danger', 'btn-unfollow');
                        button.textContent = 'Unfollow';
                    } else {
                        button.classList.remove('btn-danger', 'btn-unfollow');
                        button.classList.add('btn-success', 'btn-follow');
                        button.textContent = 'Follow';
                    }
                } else {
                    displayAjaxMessage(data.message || 'An error occurred.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                displayAjaxMessage('A network error occurred. Please try again.', 'danger');
            });
        });
    }
});
</script>

<?php
// The side_bar_template.php includes closing tags for main-panel, wrapper, body, html,
// and also includes common JavaScript files at the end of its body.
?>
