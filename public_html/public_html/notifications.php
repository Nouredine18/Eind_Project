<?php
session_start();
include 'connect.php'; // Establishes $conn

if (!isset($_SESSION['user_id'])) {
    // Voor AJAX-verzoeken, stuur een fout als niet ingelogd. Voor directe toegang, stuur door.
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
$error_message = null;

// Handel het markeren van meldingen als gelezen af
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if ($_POST['action'] === 'mark_read' && isset($_POST['notification_id'])) {
        $notification_id = intval($_POST['notification_id']);
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $current_user_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to mark notification as read.']);
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'mark_all_read') {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $current_user_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'All notifications marked as read.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to mark all notifications as read.']);
        }
        $stmt->close();
    }
    exit();
}

// Haal meldingen op voor de huidige gebruiker (al gedaan in side_bar_template.php, maar we willen hier mogelijk meer details of paginering)
// Voor deze pagina halen we opnieuw op om ervoor te zorgen dat we alle gegevens hebben en in de toekomst kunnen pagineren indien nodig.
$user_notifications = [];
$sql_notifications = "SELECT notification_id, message, notification_type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt_notifications = $conn->prepare($sql_notifications);
if ($stmt_notifications) {
    $stmt_notifications->bind_param("i", $current_user_id);
    $stmt_notifications->execute();
    $result_notifications = $stmt_notifications->get_result();
    while ($row = $result_notifications->fetch_assoc()) {
        $user_notifications[] = $row;
    }
    $stmt_notifications->close();
} else {
    $error_message = "Error fetching notifications: " . $conn->error;
}

// Voeg side_bar_template.php toe, die ook meldingen ophaalt voor de dropdown
// Opmerking: De $notifications variabele in side_bar_template.php staat los van $user_notifications hier.
include 'side_bar_template.php'; 
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Notifications</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Ecoligo Home</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="notifications.php">Notifications</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Your Notifications</h4>
                        <button class="btn btn-sm btn-primary" id="mark-all-read-btn">Mark All as Read</button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    <div id="ajax-message-notifications" class="alert" style="display:none;"></div>

                    <?php if (empty($user_notifications)): ?>
                        <p class="text-center text-muted">You have no notifications.</p>
                    <?php else: ?>
                        <div class="list-group" id="notifications-list">
                            <?php foreach ($user_notifications as $notif): ?>
                                <div class="list-group-item notification-item <?php echo $notif['is_read'] ? '' : 'list-group-item-warning unread-notification'; ?>" data-notification-id="<?php echo $notif['notification_id']; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            <i class="<?php echo getNotificationIcon($notif['notification_type']); ?> me-2 text-<?php echo getNotificationType($notif['notification_type']); ?>"></i>
                                            <?php echo htmlspecialchars($notif['message']); ?>
                                        </h5>
                                        <small><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></small>
                                    </div>
                                    <div class="d-flex w-100 justify-content-end mt-2">
                                        <?php if (!$notif['is_read']): ?>
                                            <button class="btn btn-sm btn-success mark-read-btn">Mark as Read</button>
                                        <?php endif; ?>
                                    </div>
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
    .unread-notification {
        background-color: #fff3cd !important; /* Bootstrap waarschuwing lichte achtergrond */
        border-left: 5px solid #ffc107; /* Bootstrap waarschuwingskleur */
    }
    .notification-item h5 {
        font-weight: <?php echo $notif['is_read'] ? 'normal' : 'bold'; ?>;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationsList = document.getElementById('notifications-list');
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    const ajaxMessageDiv = document.getElementById('ajax-message-notifications');

    function displayAjaxMessage(message, type = 'info') {
        ajaxMessageDiv.textContent = message;
        ajaxMessageDiv.className = 'alert alert-' + type; // e.g., alert-success, alert-danger
        ajaxMessageDiv.style.display = 'block';
        setTimeout(() => { ajaxMessageDiv.style.display = 'none'; }, 3000);
    }

    if (notificationsList) {
        notificationsList.addEventListener('click', function(event) {
            const button = event.target.closest('button.mark-read-btn');
            if (!button) return;

            const notificationItem = button.closest('.notification-item');
            const notificationId = notificationItem.dataset.notificationId;

            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('notification_id', notificationId);

            fetch('notifications.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    notificationItem.classList.remove('list-group-item-warning', 'unread-notification');
                    notificationItem.querySelector('h5').style.fontWeight = 'normal';
                    button.remove(); // Remove the "Mark as Read" button
                    // Werk optioneel het aantal meldingen in de zijbalk bij als deze zichtbaar/dynamisch is
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

    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to mark all notifications as read?')) {
                const formData = new FormData();
                formData.append('action', 'mark_all_read');

                fetch('notifications.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayAjaxMessage(data.message, 'success');
                        document.querySelectorAll('.notification-item.unread-notification').forEach(item => {
                            item.classList.remove('list-group-item-warning', 'unread-notification');
                            item.querySelector('h5').style.fontWeight = 'normal';
                            const btn = item.querySelector('.mark-read-btn');
                            if (btn) btn.remove();
                        });
                        // Werk optioneel het aantal meldingen in de zijbalk bij
                    } else {
                        displayAjaxMessage(data.message || 'An error occurred.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    displayAjaxMessage('A network error occurred. Please try again.', 'danger');
                });
            }
        });
    }
});
</script>

<?php
// The side_bar_template.php includes closing tags for main-panel, wrapper, body, html,
// and also includes common JavaScript files at the end of its body.
// No need to call it again here as it's included above.
?>