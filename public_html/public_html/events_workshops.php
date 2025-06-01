<?php
session_start();
include 'connect.php'; // Maakt $conn aan

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Haal evenementen op uit de database
$events = [];
$sql_events = "SELECT event_id, title, description, event_date, event_time, location, image_url, registration_deadline 
               FROM events 
               WHERE is_active = TRUE 
               ORDER BY event_date ASC"; // Toon alle actieve evenementen, deadline check gebeurt bij de knop
$result_events = $conn->query($sql_events);
if ($result_events) {
    while ($row = $result_events->fetch_assoc()) {
        $events[] = $row;
    }
} else {
    // Handel fout bij ophalen evenementen af indien nodig
    error_log("Error fetching events: " . $conn->error);
}

// Haal registraties van gebruiker op om knoppen uit te schakelen/wijzigen
$user_registrations = [];
$sql_user_regs = "SELECT event_id FROM user_event_registrations WHERE user_id = ?";
$stmt_user_regs = $conn->prepare($sql_user_regs);
if($stmt_user_regs){
    $stmt_user_regs->bind_param("i", $current_user_id);
    $stmt_user_regs->execute();
    $result_user_regs = $stmt_user_regs->get_result();
    while($reg_row = $result_user_regs->fetch_assoc()){
        $user_registrations[$reg_row['event_id']] = true;
    }
    $stmt_user_regs->close();
}


include 'side_bar_template.php';

// Toon sessiebericht indien ingesteld
$event_feedback_message = '';
$event_feedback_type = '';
if (isset($_SESSION['event_message'])) {
    $event_feedback_message = $_SESSION['event_message']['text'];
    $event_feedback_type = $_SESSION['event_message']['type'];
    unset($_SESSION['event_message']); // Wis bericht na tonen
}
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Sustainable Travel Workshops & Events</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="events_workshops.php">Events & Workshops</a></li>
        </ul>
    </div>

    <?php if ($event_feedback_message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($event_feedback_type); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($event_feedback_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Upcoming Events & Workshops</h4>
                    <p class="card-category">
                        Expand your knowledge and connect with the community.
                    </p>
                </div>
                <div class="card-body">
                    <?php if (empty($events)): ?>
                        <p class="text-center text-muted">No upcoming events or workshops at the moment. Please check back later!</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($events as $event): ?>
                                <?php
                                $is_registered = isset($user_registrations[$event['event_id']]);
                                $deadline_passed = ($event['registration_deadline'] && strtotime($event['registration_deadline']) < time());
                                
                                $button_action = $is_registered ? 'unregister' : 'register';
                                $button_text = $is_registered ? "Unregister" : "Register";
                                $button_class = $is_registered ? "btn-danger" : "btn-primary";
                                $button_name = $is_registered ? "unregister_event_id" : "register_event_id";
                                $button_disabled = false;

                                if (!$is_registered && $deadline_passed) {
                                    $button_text = "Deadline Passed";
                                    $button_disabled = true;
                                    $button_class = "btn-secondary";
                                }
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <img src="<?php echo htmlspecialchars($event['image_url'] ?: 'assets/img/examples/default_event.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" style="height: 200px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                            <p class="card-text"><small class="text-muted">
                                                <i class="far fa-calendar-alt"></i> <?php echo date("F j, Y", strtotime($event['event_date'])); ?> <br>
                                                <i class="far fa-clock"></i> <?php echo htmlspecialchars($event['event_time']); ?> <br>
                                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                                <?php if ($event['registration_deadline']): ?>
                                                    <br><i class="fas fa-hourglass-end"></i> Reg. Deadline: <?php echo date("F j, Y H:i", strtotime($event['registration_deadline'])); ?>
                                                <?php endif; ?>
                                            </small></p>
                                            <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars(substr($event['description'], 0, 120))) . (strlen($event['description']) > 120 ? '...' : ''); ?></p>
                                            
                                            <form method="POST" action="register_event.php" class="mt-auto">
                                                <input type="hidden" name="<?php echo $button_name; ?>" value="<?php echo $event['event_id']; ?>">
                                                <input type="hidden" name="action" value="<?php echo $button_action; ?>">
                                                <button type="submit" class="btn <?php echo $button_class; ?> w-100" <?php if ($button_disabled) echo 'disabled'; ?>>
                                                    <?php echo $button_text; ?>
                                                </button>
                                            </form>
                                        </div>
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
    .card-text small i {
        margin-right: 5px;
    }
</style>
<?php
// side_bar_template.php bevat sluitende HTML tags
?>
