<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'connect.php'; // Maakt $conn aan

// Importeer PHPMailer klassen in de globale namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vereis autoload.php voor PHPMailer
require __DIR__ . '/vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
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
$current_user_first_name = $_SESSION['first_name'] ?? 'Someone'; // Pak huidige gebruikersnaam voor e-mail
$error_message = null; 
$upload_dir = 'uploads/community_chat_images/'; // upload map relatief aan dit script


$absolute_upload_path = __DIR__ . '/' . $upload_dir;
$normalized_absolute_upload_path = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolute_upload_path), DIRECTORY_SEPARATOR);

if (!is_dir($normalized_absolute_upload_path)) {
    if (!mkdir($normalized_absolute_upload_path, 0775, true) && !is_dir($normalized_absolute_upload_path)) {
        $error_message = "Error: Upload directory '{$upload_dir}' does not exist at '{$normalized_absolute_upload_path}' and could not be created. Please create it manually and ensure it's writable.";
    } else {
        if (!is_writable($normalized_absolute_upload_path)) {
            $error_message = "Error: Upload directory '{$normalized_absolute_upload_path}' was created but is not writable. Please check server permissions.";
        }
    }
} elseif (!is_writable($normalized_absolute_upload_path)) {
    $error_message = "Error: Upload directory '{$normalized_absolute_upload_path}' exists but is not writable. Please check server permissions (e.g., chmod 775 or 777 for testing, then restrict).";
}


// Functie om antwoord notificatie e-mail te sturen
function sendReplyNotificationEmail($recipient_email, $recipient_name, $replier_name, $reply_content_snippet, $original_message_snippet) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@ecoligocollective.com'; 
        $mail->Password   = 'Nouredinetah18!';   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective Community');
        $mail->addAddress($recipient_email, $recipient_name);
        $mail->addReplyTo('info@ecoligocollective.com', 'Ecoligo Collective');

        $mail->isHTML(true);
        $mail->Subject = $replier_name . ' replied to your message on Ecoligo Collective';
        
        $email_body = "<h3>Hi {$recipient_name},</h3>";
        $email_body .= "<p><strong>{$replier_name}</strong> has replied to your message in the community chat.</p>";
        $email_body .= "<p><strong>Your original message snippet:</strong><br><em>\"" . htmlspecialchars(substr($original_message_snippet, 0, 100)) . "...\"</em></p>";
        $email_body .= "<p><strong>Their reply:</strong><br><em>\"" . htmlspecialchars(substr($reply_content_snippet, 0, 150)) . "...\"</em></p>";
        $email_body .= "<p>You can view the full conversation here: <a href='https://ecoligocollective.com/community_chat.php'>Community Chat</a></p>"; // Vervang met je echte domein/pad
        $email_body .= "<p>Thanks,<br>The Ecoligo Collective Team</p>";
        
        $mail->Body    = $email_body;
        $mail->AltBody = "Hi {$recipient_name},\n\n{$replier_name} has replied to your message in the community chat.\n\nYour original message snippet:\n\"" . substr($original_message_snippet, 0, 100) . "...\"\n\nTheir reply:\n\"" . substr($reply_content_snippet, 0, 150) . "...\"\n\nYou can view the full conversation here: https://yourdomain.com/community_chat.php\n\nThanks,\nThe Ecoligo Collective Team"; // Vervang met je echte domein/pad

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function handleImageUpload($file_input_name, $upload_dir) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES[$file_input_name];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // Max 5MB voor afbeeldingen

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            return ['error' => 'Invalid file type (' . htmlspecialchars($mime_type) . '). Only JPG, PNG, GIF allowed.'];
        }
        if ($file['size'] > $max_size) {
            return ['error' => 'File size (' . round($file['size'] / 1024 / 1024, 2) . 'MB) exceeds 5MB limit.'];
        }

        $filename = uniqid('img_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $destination_dir_abs = rtrim(__DIR__ . '/' . $upload_dir, '/') . '/';
        $destination_file_abs = $destination_dir_abs . $filename;
        $web_accessible_path = rtrim($upload_dir, '/') . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination_file_abs)) {
            return ['success' => $web_accessible_path];
        } else {
            $upload_error_code = $file['error'];
            $php_upload_errors = [
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            ];
            $error_detail = isset($php_upload_errors[$upload_error_code]) ? $php_upload_errors[$upload_error_code] : 'Unknown upload error.';
            return ['error' => 'Failed to move uploaded file to ' . htmlspecialchars($destination_file_abs) . '. Detail: ' . $error_detail . ' Ensure directory is writable.'];
        }
    } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_error_code = $_FILES[$file_input_name]['error'];
        $php_upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        $error_detail = isset($php_upload_errors[$upload_error_code]) ? $php_upload_errors[$upload_error_code] : 'Unknown file upload error code: ' . $upload_error_code;
        return ['error' => 'File upload error: ' . $error_detail];
    }
    return null;
}

// Handel AJAX acties af
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action == 'send_message') {
        $message_content = isset($_POST['message_content']) ? trim($_POST['message_content']) : '';
        $parent_message_id = isset($_POST['parent_message_id']) && !empty($_POST['parent_message_id']) ? intval($_POST['parent_message_id']) : null;
        $message_type = 'text';
        $image_url = null;

        $image_upload_result = handleImageUpload('message_image', $upload_dir);

        if ($image_upload_result && isset($image_upload_result['error'])) {
            echo json_encode(['status' => 'error', 'message' => $image_upload_result['error']]);
            exit();
        }
        if ($image_upload_result && isset($image_upload_result['success'])) {
            $image_url = $image_upload_result['success'];
            $message_type = 'image';
        }

        if (empty($message_content) && $message_type === 'text') {
            echo json_encode(['status' => 'error', 'message' => 'Message content cannot be empty for a text message.']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO community_messages (user_id, message_content, parent_message_id, message_type, image_url) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isiss", $current_user_id, $message_content, $parent_message_id, $message_type, $image_url);
            if ($stmt->execute()) {
                $new_message_id = $stmt->insert_id;
                $fetch_sql = "SELECT cm.message_id, cm.user_id as sender_user_id, cm.message_content, cm.timestamp, cm.message_type, cm.image_url, cm.is_deleted, cm.edited_at,
                                     u.first_name, u.last_name, u.profile_picture_url,
                                     cm.parent_message_id,
                                     pm.message_content as parent_message_content_snippet,
                                     pmu.first_name as parent_user_first_name, pmu.last_name as parent_user_last_name
                              FROM community_messages cm
                              JOIN users u ON cm.user_id = u.user_id
                              LEFT JOIN community_messages pm ON cm.parent_message_id = pm.message_id
                              LEFT JOIN users pmu ON pm.user_id = pmu.user_id
                              WHERE cm.message_id = ?";
                $stmt_new = $conn->prepare($fetch_sql);
                $stmt_new->bind_param("i", $new_message_id);
                $stmt_new->execute();
                $new_message_data = $stmt_new->get_result()->fetch_assoc();
                $stmt_new->close();

                // Bij antwoord, stuur e-mail notificatie naar auteur v origineel bericht
                if ($parent_message_id && $new_message_data) {
                    $stmt_parent_author = $conn->prepare(
                        "SELECT u.email, u.first_name, cm.message_content 
                         FROM community_messages cm 
                         JOIN users u ON cm.user_id = u.user_id 
                         WHERE cm.message_id = ?"
                    );
                    if ($stmt_parent_author) {
                        $stmt_parent_author->bind_param("i", $parent_message_id);
                        $stmt_parent_author->execute();
                        $parent_author_result = $stmt_parent_author->get_result()->fetch_assoc();
                        $stmt_parent_author->close();

                        if ($parent_author_result && $parent_author_result['email']) {
                            // Stuur geen mail als gebruiker op zichzelf reageert
                            if ($parent_author_result['email'] != ($_SESSION['email'] ?? '')) {
                                $reply_content_for_email = !empty($message_content) ? $message_content : ($message_type === 'image' ? '[Image]' : '...');
                                sendReplyNotificationEmail(
                                    $parent_author_result['email'],
                                    $parent_author_result['first_name'],
                                    $current_user_first_name,
                                    $reply_content_for_email,
                                    $parent_author_result['message_content']
                                );
                            }
                        }
                    }
                }
                echo json_encode(['status' => 'success', 'message_data' => $new_message_data]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send message: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
        }
    } elseif ($action == 'delete_message') {
        $message_id_to_delete = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
        if ($message_id_to_delete > 0) {
            $stmt_owner = $conn->prepare("SELECT user_id FROM community_messages WHERE message_id = ?");
            $stmt_owner->bind_param("i", $message_id_to_delete);
            $stmt_owner->execute();
            $owner_result = $stmt_owner->get_result()->fetch_assoc();
            $stmt_owner->close();

            if ($owner_result && $owner_result['user_id'] == $current_user_id) {
                $stmt_delete = $conn->prepare("UPDATE community_messages SET is_deleted = 1, message_content = 'Message deleted by user.', image_url = NULL WHERE message_id = ?");
                $stmt_delete->bind_param("i", $message_id_to_delete);
                if ($stmt_delete->execute()) {
                    echo json_encode(['status' => 'success', 'message_id' => $message_id_to_delete]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to delete message.']);
                }
                $stmt_delete->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this message.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid message ID.']);
        }
    } elseif ($action == 'edit_message') {
        $message_id_to_edit = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
        $new_content = isset($_POST['new_content']) ? trim($_POST['new_content']) : '';

        if ($message_id_to_edit > 0 && !empty($new_content)) {
            $stmt_owner = $conn->prepare("SELECT user_id, message_type FROM community_messages WHERE message_id = ? AND is_deleted = 0");
            $stmt_owner->bind_param("i", $message_id_to_edit);
            $stmt_owner->execute();
            $owner_result = $stmt_owner->get_result()->fetch_assoc();
            $stmt_owner->close();

            if ($owner_result && $owner_result['user_id'] == $current_user_id && $owner_result['message_type'] == 'text') {
                $stmt_edit = $conn->prepare("UPDATE community_messages SET message_content = ?, edited_at = NOW() WHERE message_id = ?");
                $stmt_edit->bind_param("si", $new_content, $message_id_to_edit);
                if ($stmt_edit->execute()) {
                    echo json_encode(['status' => 'success', 'message_id' => $message_id_to_edit, 'new_content' => $new_content, 'edited_at' => date('M d, Y h:i A')]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to edit message.']);
                }
                $stmt_edit->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Cannot edit this message or permission denied.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data for editing.']);
        }
    }
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'fetch_messages') {
    header('Content-Type: application/json');
    $last_message_id = isset($_GET['last_message_id']) ? intval($_GET['last_message_id']) : 0;

    $sql_fetch_new = "SELECT cm.message_id, cm.user_id as sender_user_id, cm.message_content, cm.timestamp, cm.message_type, cm.image_url, cm.is_deleted, cm.edited_at,
                             u.first_name, u.last_name, u.profile_picture_url,
                             cm.parent_message_id,
                             pm.message_content as parent_message_content_snippet,
                             pmu.first_name as parent_user_first_name, pmu.last_name as parent_user_last_name
                      FROM community_messages cm
                      JOIN users u ON cm.user_id = u.user_id
                      LEFT JOIN community_messages pm ON cm.parent_message_id = pm.message_id AND pm.is_deleted = 0
                      LEFT JOIN users pmu ON pm.user_id = pmu.user_id
                      WHERE cm.message_id > ?
                      ORDER BY cm.timestamp ASC";
    $stmt_fetch_new = $conn->prepare($sql_fetch_new);
    if ($stmt_fetch_new) {
        $stmt_fetch_new->bind_param("i", $last_message_id);
        $stmt_fetch_new->execute();
        $result_fetch_new = $stmt_fetch_new->get_result();
        $new_messages_data = [];
        while ($row = $result_fetch_new->fetch_assoc()) {
            $new_messages_data[] = $row;
        }
        $stmt_fetch_new->close();
        echo json_encode(['status' => 'success', 'messages' => $new_messages_data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement for fetching messages: ' . $conn->error]);
    }
    exit();
}

$messages = [];
$sql_fetch_initial = "SELECT cm.message_id, cm.user_id as sender_user_id, cm.message_content, cm.timestamp, cm.message_type, cm.image_url, cm.is_deleted, cm.edited_at,
                             u.first_name, u.last_name, u.profile_picture_url,
                             cm.parent_message_id,
                             pm.message_content as parent_message_content_snippet,
                             pmu.first_name as parent_user_first_name, pmu.last_name as parent_user_last_name
                      FROM community_messages cm
                      JOIN users u ON cm.user_id = u.user_id
                      LEFT JOIN community_messages pm ON cm.parent_message_id = pm.message_id AND pm.is_deleted = 0
                      LEFT JOIN users pmu ON pm.user_id = pmu.user_id
                      ORDER BY cm.timestamp ASC
                      LIMIT 100"; 

$result_fetch_initial = $conn->query($sql_fetch_initial);
if ($result_fetch_initial) {
    while ($row = $result_fetch_initial->fetch_assoc()) {
        $messages[] = $row;
    }
} else {
    $error_message = "Failed to fetch messages: " . $conn->error;
}

include 'side_bar_template.php'; 
?>

<!-- Page-specific content for community_chat.php starts here -->
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Community Chat</h3>
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
                <a href="community_chat.php">Chat</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Live Chat</h4>
                </div>
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" id="initial-error-message"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    <div id="ajax-error-message" class="alert alert-danger" style="display: none;"></div>
                    <div id="ajax-success-message" class="alert alert-success" style="display: none;"></div>

                    <div class="chat-window" id="chat-window">
                        <?php if (empty($messages)): ?>
                            <p class="text-center text-muted" id="no-messages-placeholder">No messages yet. Be the first to say something!</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div id="message-item-<?php echo $msg['message_id']; ?>" class="message-item <?php echo ($msg['sender_user_id'] == $current_user_id) ? 'sent' : 'received'; ?>" data-message-id="<?php echo $msg['message_id']; ?>" data-sender-id="<?php echo $msg['sender_user_id']; ?>">
                                    <div class="message-avatar">
                                        <img src="<?php echo htmlspecialchars(trim((string)$msg['profile_picture_url']) ?: 'assets/img/kaiadmin/default.jpg'); ?>" alt="<?php echo htmlspecialchars($msg['first_name']); ?>" class="avatar-xs rounded-circle">
                                    </div>
                                    <div class="message-content-wrapper">
                                        <div class="message-header">
                                            <strong><?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?></strong>
                                            <span class="message-time"><?php echo date('M d, Y h:i A', strtotime($msg['timestamp'])); ?></span>
                                        </div>
                                        <?php if ($msg['parent_message_id'] && $msg['parent_user_first_name']): ?>
                                            <div class="reply-quote">
                                                <small>Replying to <?php echo htmlspecialchars($msg['parent_user_first_name'] . ' ' . $msg['parent_user_last_name']); ?>:</small>
                                                <p class="reply-quote-text"><em><?php echo htmlspecialchars(substr($msg['parent_message_content_snippet'], 0, 50) . (strlen($msg['parent_message_content_snippet']) > 50 ? '...' : '')); ?></em></p>
                                            </div>
                                        <?php endif; ?>
                                        <div class="message-text-content">
                                            <?php if ($msg['is_deleted']): ?>
                                                <p class="deleted-message"><em>Message deleted by user.</em></p>
                                            <?php elseif ($msg['message_type'] == 'image' && $msg['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($msg['image_url']); ?>" alt="User uploaded image" class="chat-image">
                                                <?php if (!empty($msg['message_content'])): ?>
                                                    <p><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></p>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <p><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($msg['edited_at'] && !$msg['is_deleted']): ?>
                                            <span class="edited-indicator">(edited)</span>
                                        <?php endif; ?>
                                        <div class="message-actions">
                                            <button class="btn btn-sm btn-link action-reply" title="Reply"><i class="fas fa-reply"></i></button>
                                            <?php if ($msg['sender_user_id'] == $current_user_id && !$msg['is_deleted']): ?>
                                                <?php if ($msg['message_type'] == 'text'): ?>
                                                <button class="btn btn-sm btn-link action-edit" title="Edit"><i class="fas fa-edit"></i></button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-link action-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div id="reply-to-indicator" style="display:none; margin-bottom: 5px; padding: 5px; background-color: #f0f0f0; border-radius: 3px;">
                        Replying to: <span id="reply-to-user"></span> <button id="cancel-reply" class="btn btn-xs btn-danger">&times;</button>
                        <input type="hidden" id="parent-message-id-input" name="parent_message_id_form">
                    </div>

                    <form method="POST" action="community_chat.php" class="mt-3" id="chat-form" enctype="multipart/form-data">
                        <div class="input-group">
                            <textarea name="message_content" id="message-content-input" class="form-control" placeholder="Type your message..." rows="2"></textarea>
                            <label for="message-image-input" class="btn btn-info mb-0 d-flex align-items-center">
                                <i class="fas fa-paperclip"></i>
                            </label>
                            <input type="file" name="message_image" id="message-image-input" style="display:none;" accept="image/png, image/jpeg, image/gif">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                        <div id="image-preview-container" class="mt-2" style="display:none;">
                            <img id="image-preview" src="#" alt="Image Preview" style="max-height: 100px; max-width: 100px; border-radius: 5px;"/>
                            <button type="button" id="remove-image-preview" class="btn btn-xs btn-danger">&times;</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-window {
    height: 70vh; 
    overflow-y: auto; 
    border: 1px solid #dee2e6; 
    padding: 15px; 
    margin-bottom: 20px;
    background-color: #f8f9fa;
    border-radius: .25rem;
}
.message-item {
    display: flex;
    margin-bottom: 15px;
    align-items: flex-end; 
}
.message-item.sent {
    flex-direction: row-reverse;
}
.message-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}
.message-item.sent .message-avatar { margin-left: 10px; }
.message-item.received .message-avatar { margin-right: 10px; }

.message-content-wrapper {
    padding: 10px 15px;
    border-radius: 15px;
    max-width: 75%;
    display: flex;
    flex-direction: column;
    position: relative; 
}
.message-item.sent .message-content-wrapper {
    background-color: #007bff; 
    color: white;
    border-bottom-right-radius: 0; 
}
.message-item.received .message-content-wrapper {
    background-color: #e9ecef; 
    color: #212529;
    border-bottom-left-radius: 0; 
}
.message-header {
    margin-bottom: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.message-header strong { font-size: 0.9em; font-weight: 600; }
.message-time { font-size: 0.75em; color: #adb5bd; }
.message-item.sent .message-time { color: #cce5ff; }

.message-text-content p { margin: 0; word-wrap: break-word; font-size: 0.95em; line-height: 1.4; }
.avatar-xs { width: 40px; height: 40px; }

message-content-wrapper::before {
    content: ""; position: absolute; bottom: 0; width: 0; height: 0; border: 10px solid transparent;
}
.message-item.sent .message-content-wrapper::before {
    right: -10px; border-left-color: #007bff; border-right: 0; border-bottom-color: #007bff;
}
.message-item.received .message-content-wrapper::before {
    left: -10px; border-right-color: #e9ecef; border-left: 0; border-bottom-color: #e9ecef;
}
.chat-image { max-width: 100%; height: auto; border-radius: 5px; margin-top: 5px; margin-bottom: 5px; }
.deleted-message em { color: #6c757d; }
.edited-indicator { font-size: 0.7em; color: #999; margin-left: 5px; }
.message-actions { margin-top: 5px; opacity: 0; transition: opacity 0.3s ease; }
.message-item:hover .message-actions { opacity: 1; }
.message-actions .btn-link { padding: 0.2rem 0.4rem; font-size: 0.8rem; color: #6c757d; }
.message-item.sent .message-actions .btn-link { color: #cce5ff; }
.message-actions .btn-link:hover { color: #0056b3; }
.message-item.sent .message-actions .btn-link:hover { color: #fff; }

.reply-quote {
    background-color: rgba(0,0,0,0.05);
    padding: 5px 8px;
    border-radius: 5px;
    margin-bottom: 5px;
    border-left: 3px solid #007bff;
}
.message-item.sent .reply-quote {
    background-color: rgba(255,255,255,0.1);
    border-left-color: #cce5ff;
}
.reply-quote small { font-size: 0.8em; display: block; }
.reply-quote-text em { font-size: 0.9em; color: #555; }
.message-item.sent .reply-quote-text em { color: #eee; }

#image-preview-container button { margin-left: 5px; }

.highlight-message {
    background-color: #fff3cd !important; 
    transition: background-color 0.5s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-content-input');
    const imageInput = document.getElementById('message-image-input');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removeImagePreviewBtn = document.getElementById('remove-image-preview');

    const chatWindow = document.getElementById('chat-window');
    const ajaxErrorMessageDiv = document.getElementById('ajax-error-message');
    const ajaxSuccessMessageDiv = document.getElementById('ajax-success-message');
    const noMessagesPlaceholder = document.getElementById('no-messages-placeholder');
    
    const replyToIndicator = document.getElementById('reply-to-indicator');
    const replyToUserSpan = document.getElementById('reply-to-user');
    const parentMessageIdInput = document.getElementById('parent-message-id-input');
    const cancelReplyBtn = document.getElementById('cancel-reply');

    const currentUserId = <?php echo json_encode($current_user_id); ?>;
    let lastMessageId = 0;

    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/[&<>"']/g, match => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match]));
    }

    function formatMessageTimestamp(isoTimestamp) {
        if (!isoTimestamp) return '';
        const date = new Date(isoTimestamp);
        const options = { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
        return date.toLocaleString(undefined, options);
    }

    function renderMessage(msg, prepend = false) {
        if (noMessagesPlaceholder) noMessagesPlaceholder.style.display = 'none';

        const existingMsgElement = chatWindow.querySelector(`.message-item[data-message-id="${msg.message_id}"]`);
        if (existingMsgElement) {
            const textContentDiv = existingMsgElement.querySelector('.message-text-content');
            const editedIndicatorSpan = existingMsgElement.querySelector('.edited-indicator') || document.createElement('span');
            editedIndicatorSpan.classList.add('edited-indicator');
            
            if (msg.is_deleted) {
                textContentDiv.innerHTML = `<p class="deleted-message"><em>Message deleted by user.</em></p>`;
                const actionsDiv = existingMsgElement.querySelector('.message-actions');
                if (actionsDiv) actionsDiv.style.display = 'none';
                editedIndicatorSpan.textContent = '';
            } else {
                let contentHTML = '';
                if (msg.message_type === 'image' && msg.image_url) {
                    contentHTML += `<img src="${escapeHTML(msg.image_url)}" alt="User uploaded image" class="chat-image">`;
                }
                if (msg.message_content) {
                     contentHTML += `<p>${escapeHTML(msg.message_content).replace(/\n/g, '<br>')}</p>`;
                }
                textContentDiv.innerHTML = contentHTML;
                if (msg.edited_at) {
                    editedIndicatorSpan.textContent = '(edited)';
                    if (!existingMsgElement.querySelector('.edited-indicator')) {
                         existingMsgElement.querySelector('.message-content-wrapper').appendChild(editedIndicatorSpan);
                    }
                }
            }
            return;
        }

        const messageItem = document.createElement('div');
        messageItem.id = `message-item-${msg.message_id}`; // Add ID here
        messageItem.classList.add('message-item', msg.sender_user_id == currentUserId ? 'sent' : 'received');
        messageItem.dataset.messageId = msg.message_id;
        messageItem.dataset.senderId = msg.sender_user_id;

        const avatarSrc = msg.profile_picture_url && msg.profile_picture_url.trim() !== '' ? msg.profile_picture_url : 'assets/img/kaiadmin/default.jpg';
        const senderName = `${escapeHTML(msg.first_name)} ${escapeHTML(msg.last_name)}`;
        const messageTime = formatMessageTimestamp(msg.timestamp);
        
        let replyQuoteHTML = '';
        if (msg.parent_message_id && msg.parent_user_first_name) {
            const parentName = `${escapeHTML(msg.parent_user_first_name)} ${escapeHTML(msg.parent_user_last_name)}`;
            const parentSnippet = escapeHTML(msg.parent_message_content_snippet ? msg.parent_message_content_snippet.substring(0, 50) + (msg.parent_message_content_snippet.length > 50 ? '...' : '') : 'Original message');
            replyQuoteHTML = `
                <div class="reply-quote">
                    <small>Replying to ${parentName}:</small>
                    <p class="reply-quote-text"><em>${parentSnippet}</em></p>
                </div>`;
        }

        let messageBodyHTML = '';
        if (msg.is_deleted) {
            messageBodyHTML = `<p class="deleted-message"><em>Message deleted by user.</em></p>`;
        } else if (msg.message_type === 'image' && msg.image_url) {
            messageBodyHTML = `<img src="${escapeHTML(msg.image_url)}" alt="User uploaded image" class="chat-image">`;
            if (msg.message_content) {
                 messageBodyHTML += `<p>${escapeHTML(msg.message_content).replace(/\n/g, '<br>')}</p>`;
            }
        } else {
            messageBodyHTML = `<p>${escapeHTML(msg.message_content).replace(/\n/g, '<br>')}</p>`;
        }
        
        const editedIndicatorHTML = (msg.edited_at && !msg.is_deleted) ? `<span class="edited-indicator">(edited)</span>` : '';

        let actionsHTML = `<button class="btn btn-sm btn-link action-reply" title="Reply"><i class="fas fa-reply"></i></button>`;
        if (msg.sender_user_id == currentUserId && !msg.is_deleted) {
            if (msg.message_type === 'text') {
                 actionsHTML += `<button class="btn btn-sm btn-link action-edit" title="Edit"><i class="fas fa-edit"></i></button>`;
            }
            actionsHTML += `<button class="btn btn-sm btn-link action-delete" title="Delete"><i class="fas fa-trash"></i></button>`;
        }

        messageItem.innerHTML = `
            <div class="message-avatar">
                <img src="${escapeHTML(avatarSrc)}" alt="${senderName}" class="avatar-xs rounded-circle">
            </div>
            <div class="message-content-wrapper">
                <div class="message-header">
                    <strong>${senderName}</strong>
                    <span class="message-time">${messageTime}</span>
                </div>
                ${replyQuoteHTML}
                <div class="message-text-content">${messageBodyHTML}</div>
                ${editedIndicatorHTML}
                <div class="message-actions">${actionsHTML}</div>
            </div>
        `;
        if (prepend) {
            chatWindow.insertBefore(messageItem, chatWindow.firstChild);
        } else {
            chatWindow.appendChild(messageItem);
        }
        
        if (parseInt(msg.message_id) > lastMessageId) {
            lastMessageId = parseInt(msg.message_id);
        }
    }

    function displayAjaxMessage(message, type = 'error') {
        const div = type === 'error' ? ajaxErrorMessageDiv : ajaxSuccessMessageDiv;
        const otherDiv = type === 'error' ? ajaxSuccessMessageDiv : ajaxErrorMessageDiv;
        div.textContent = message;
        div.style.display = 'block';
        otherDiv.style.display = 'none';
        setTimeout(() => div.style.display = 'none', 3000);
    }
    
    function clearReplyState() {
        replyToIndicator.style.display = 'none';
        parentMessageIdInput.value = '';
        replyToUserSpan.textContent = '';
    }

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.src = event.target.result;
                imagePreviewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    removeImagePreviewBtn.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.src = '#';
        imagePreviewContainer.style.display = 'none';
    });

    chatWindow.addEventListener('click', function(event) {
        const target = event.target.closest('button');
        if (!target) return;

        const messageItem = target.closest('.message-item');
        const messageId = messageItem.dataset.messageId;

        if (target.classList.contains('action-reply')) {
            const senderName = messageItem.querySelector('.message-header strong').textContent;
            replyToUserSpan.textContent = senderName;
            parentMessageIdInput.value = messageId;
            replyToIndicator.style.display = 'block';
            messageInput.focus();
        } else if (target.classList.contains('action-delete')) {
            if (confirm('Are you sure you want to delete this message?')) {
                const formData = new FormData();
                formData.append('action', 'delete_message');
                formData.append('message_id', messageId);
                fetch('community_chat.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderMessage({ message_id: data.message_id, is_deleted: true });
                        displayAjaxMessage('Message deleted.', 'success');
                    } else {
                        displayAjaxMessage(data.message || 'Could not delete message.');
                    }
                }).catch(err => displayAjaxMessage('Network error.'));
            }
        } else if (target.classList.contains('action-edit')) {
            const textContentDiv = messageItem.querySelector('.message-text-content p');
            if (!textContentDiv) return;
            const currentText = textContentDiv.innerText;
            
            const newText = prompt('Edit your message:', currentText);
            if (newText !== null && newText.trim() !== currentText.trim()) {
                const formData = new FormData();
                formData.append('action', 'edit_message');
                formData.append('message_id', messageId);
                formData.append('new_content', newText.trim());
                fetch('community_chat.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then data => {
                    if (data.status === 'success') {
                        renderMessage({ 
                            message_id: data.message_id, 
                            message_content: data.new_content, 
                            edited_at: data.edited_at,
                            sender_user_id: messageItem.dataset.senderId, 
                            first_name: messageItem.querySelector('.message-header strong').textContent.split(' ')[0], 
                            last_name: messageItem.querySelector('.message-header strong').textContent.split(' ')[1] || '', 
                            timestamp: messageItem.querySelector('.message-time').textContent, 
                            message_type: 'text' 
                        });
                        displayAjaxMessage('Message edited.', 'success');
                    } else {
                        displayAjaxMessage(data.message || 'Could not edit message.');
                    }
                }).catch(err => displayAjaxMessage('Network error.'));
            }
        }
    });

    cancelReplyBtn.addEventListener('click', clearReplyState);

    const existingMessages = chatWindow.querySelectorAll('.message-item[data-message-id]');
    if (existingMessages.length > 0) {
        lastMessageId = parseInt(existingMessages[existingMessages.length - 1].dataset.messageId);
    }

    if (chatForm) {
        chatForm.addEventListener('submit', function(event) {
            event.preventDefault();
            ajaxErrorMessageDiv.style.display = 'none';
            ajaxSuccessMessageDiv.style.display = 'none';
            
            const messageContent = messageInput.value.trim();
            const imageFile = imageInput.files[0];

            if (messageContent === '' && !imageFile) {
                displayAjaxMessage('Message or image cannot be empty.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('message_content', messageContent);
            if (parentMessageIdInput.value) {
                formData.append('parent_message_id', parentMessageIdInput.value);
            }
            if (imageFile) {
                formData.append('message_image', imageFile);
            }

            fetch('community_chat.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.message_data) {
                    messageInput.value = '';
                    imageInput.value = '';
                    imagePreview.src = '#';
                    imagePreviewContainer.style.display = 'none';
                    clearReplyState();
                    fetchMessages();
                } else {
                    displayAjaxMessage(data.message || 'Could not send message.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                displayAjaxMessage('A network error occurred. Please try again.');
            });
        });
    }

    function fetchMessages() {
        fetch(`community_chat.php?action=fetch_messages&last_message_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.messages.length > 0) {
                let newMessagesAdded = false;
                data.messages.forEach(msg => {
                    if (!chatWindow.querySelector(`.message-item[data-message-id="${msg.message_id}"]`)) {
                        renderMessage(msg);
                        newMessagesAdded = true;
                    }
                });
                if (newMessagesAdded) {
                     chatWindow.scrollTop = chatWindow.scrollHeight;
                }
            } else if (data.status === 'error') {
                console.error('Error fetching messages:', data.message);
            }
        })
        .catch(error => console.error('Error fetching messages:', error));
    }

    if (chatWindow) {
        // Scroll naar bericht-ID in URL hash, indien aanwezig
        if (window.location.hash) {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
                // Highlight het bericht tijdelijk
                targetElement.classList.add('highlight-message');
                setTimeout(() => targetElement.classList.remove('highlight-message'), 3000);
            }
        } else {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
    }
    setInterval(fetchMessages, 3500);
});
</script>

<?php
?>
