<?php
// Ensure session is started if not already (might be started by including page)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Ensure $conn is available (might be included by calling page)
// If not, you might need: include_once 'connect.php'; 

// Default user_id if not set (e.g., for login/register pages if they use this header)
$user_id = $_SESSION['user_id'] ?? null;

$current_page = basename($_SERVER['PHP_SELF']);
$ecoligo_home_pages = [
    'home.php', 'profile.php', 'compensation_projects.php', 
    'community_chat.php', 'search_users.php', 'betalingsgeschiedenis.php', 
    'compensation_data.php', 'help.php', 'notifications.php', 'all_notifications.php',
    'abonnement.php', 'logout.php',
    'followers_list.php', 'following_list.php', 'follow_profile_details.php' 
];
$is_ecoligo_home_active = in_array($current_page, $ecoligo_home_pages);

// Notifications functions (assuming $conn is available)
if ($user_id && isset($conn)) {
    // Fetch notifications function (copied from original side_bar_template.php)
    if (!function_exists('fetchNotifications')) {
        function fetchNotifications($conn, $userId) {
            $stmt = $conn->prepare("SELECT notification_id, message, notification_type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $notifications_data = [];
            while ($row = $result->fetch_assoc()) {
                $notifications_data[] = $row;
            }
            $stmt->close();
            return $notifications_data;
        }
    }
    $notifications = fetchNotifications($conn, $user_id);

    if (!function_exists('getNotificationIcon')) {
        function getNotificationIcon($type) {
            switch ($type) {
                case 'profile_update': return 'fa fa-user';
                case 'travel_update': return 'fa fa-plane';
                default: return 'fa fa-info-circle';
            }
        }
    }
    if (!function_exists('getNotificationType')) {
        function getNotificationType($type) {
            switch ($type) {
                case 'profile_update': return 'primary';
                case 'travel_update': return 'success';
                default: return 'default';
            }
        }
    }
} else {
    $notifications = []; // Default if no user or no connection
}

// Use $page_title passed from the including page, or a default.
global $page_title_for_template; // Use a global or pass as function arg
$title_to_display = isset($page_title_for_template) ? htmlspecialchars($page_title_for_template) . " - Ecoligo" : "Ecoligo Collective";

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $title_to_display; ?></title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <?php
    global $page_specific_css; // Use a global or pass as function arg
    if (isset($page_specific_css) && is_array($page_specific_css)) {
        foreach ($page_specific_css as $css_file_url) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($css_file_url) . '" />' . "\n";
        }
    }
    ?>
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <div class="sidebar sidebar-style-2" data-background-color="dark">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="index.php" class="logo ajax-link">
              <img
                src="assets/img/kaiadmin/favicon.png"
                alt="navbar brand"
                class="navbar-brand"
                height="20"
              />
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item <?php if ($is_ecoligo_home_active) echo 'active'; ?>">
                <a
                  data-bs-toggle="collapse"
                  href="#dashboard"
                  class="ajax-link <?php if (!$is_ecoligo_home_active) echo 'collapsed'; ?>"
                  aria-expanded="<?php echo $is_ecoligo_home_active ? 'true' : 'false'; ?>"
                >
                  <i class="fas fa-home"></i>
                  <p>Ecoligo Home</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse <?php if ($is_ecoligo_home_active) echo 'show'; ?>" id="dashboard">
                  <ul class="nav nav-collapse">
                    <li class="<?php if ($current_page == 'home.php') echo 'active'; ?>">
                      <a href="home.php" class="ajax-link">
                        <span class="sub-item">Home</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'profile.php' || $current_page == 'followers_list.php' || $current_page == 'following_list.php' || $current_page == 'follow_profile_details.php') echo 'active'; ?>">
                      <a href="profile.php" class="ajax-link">
                        <span class="sub-item">Profile</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'compensation_projects.php') echo 'active'; ?>">
                      <a href="compensation_projects.php" class="ajax-link">
                        <span class="sub-item">Compensation Projects</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'community_chat.php') echo 'active'; ?>">
                      <a href="community_chat.php" class="ajax-link">
                        <span class="sub-item">Community Chat</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'search_users.php') echo 'active'; ?>">
                      <a href="search_users.php" class="ajax-link">
                        <span class="sub-item">Search Users</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'betalingsgeschiedenis.php') echo 'active'; ?>">
                      <a href="betalingsgeschiedenis.php" class="ajax-link">
                        <span class="sub-item">Payment History</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'compensation_data.php') echo 'active'; ?>">
                      <a href="compensation_data.php" class="ajax-link">
                        <span class="sub-item">Compensation Data</span>
                      </a>
                    </li>
                     <li class="<?php if ($current_page == 'help.php') echo 'active'; ?>">
                      <a href="help.php" class="ajax-link">
                        <span class="sub-item">Help</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'notifications.php' || $current_page == 'all_notifications.php') echo 'active'; ?>">
                      <a href="notifications.php" class="ajax-link">
                        <span class="sub-item">Notifications</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'abonnement.php') echo 'active'; ?>">
                      <a href="abonnement.php" class="ajax-link">
                        <span class="sub-item">Subscriptions</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'logout.php') echo 'active'; ?>">
                      <a href="logout.php"> <?php // Logout should not be an AJAX link ?>
                        <span class="sub-item">Logout</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="index.php" class="logo ajax-link">
                <img
                  src="assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <nav
                class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
              >
                <!-- Search form can be AJAXified later if needed -->
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <!-- ... other topbar icons like messages, notifications ... -->
                 <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    id="messageDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="fa fa-envelope"></i>
                  </a>
                  <ul
                    class="dropdown-menu messages-notif-box animated fadeIn"
                    aria-labelledby="messageDropdown"
                  >
                    <li>
                      <div
                        class="dropdown-title d-flex justify-content-between align-items-center"
                      >
                        Messages
                        <a href="#" class="small">Mark all as read</a>
                      </div>
                    </li>
                     <li>
                      <div class="message-notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <!-- Placeholder messages -->
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all ajax-link" href="messages.php" <?php // Make this an AJAX link if you have a messages page ?>
                        >See all messages<i class="fa fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="#" <?php // Changed from home.php to # for dropdown behavior ?>
                    id="notifDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="fa fa-bell"></i>
                    <span class="notification">
                        <?php echo count($notifications) > 10 ? '10+' : count($notifications); ?>
                    </span>
                  </a>
                  <ul
                    class="dropdown-menu notif-box animated fadeIn"
                    aria-labelledby="notifDropdown"
                  >
                    <li>
                      <div class="dropdown-title">
                        You have <?php echo count($notifications); ?> new notifications
                      </div>
                    </li>
                    <li>
                      <div class="notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <?php foreach (array_slice($notifications, 0, 5) as $notif): ?> <?php // Limit initial display ?>
                            <a href="#" class="ajax-link"> <?php // Potentially make notification links AJAX if they go to a page section ?>
                              <div class="notif-icon notif-<?php echo getNotificationType($notif['notification_type']); ?>">
                                <i class="<?php echo getNotificationIcon($notif['notification_type']); ?>"></i>
                              </div>
                              <div class="notif-content">
                                <span class="block"><?php echo htmlspecialchars($notif['message']); ?></span>
                                <span class="time"><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></span>
                              </div>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all ajax-link" href="notifications.php">See all notifications
                        <i class="fa fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </li>
                <!-- ... Quick Actions ... -->

                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <?php
                        $profile_pic_display_header = htmlspecialchars($_SESSION['profile_picture_url'] ?? 'assets/img/kaiadmin/default.jpg');
                        $first_name_display_header = htmlspecialchars($_SESSION['first_name'] ?? 'User');
                        $email_display_header = htmlspecialchars($_SESSION['email'] ?? 'user@example.com');
                        $last_name_display_header = htmlspecialchars($_SESSION['last_name'] ?? '');
                    ?>
                    <div class="avatar-sm">
                        <img
                            src="<?php echo $profile_pic_display_header; ?>"
                            alt="Profile Picture"
                            class="avatar-img rounded-circle"
                        />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Hi,</span>
                      <span class="fw-bold"><?php echo $first_name_display_header; ?></span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img
                              src="<?php echo $profile_pic_display_header; ?>"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4><?php echo $first_name_display_header . ' ' . $last_name_display_header; ?></h4>
                            <p class="text-muted"><?php echo $email_display_header; ?></p>
                            <a
                              href="profile.php"
                              class="btn btn-xs btn-secondary btn-sm ajax-link"
                              >View Profile</a
                            >
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a class="dropdown-item ajax-link" href="profile.php">My Profile</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Logout</a> <?php // Logout is not AJAX ?>
                        <?php else: ?>
                        <a class="dropdown-item" href="login.php">Login</a> <?php // Login is not AJAX ?>
                        <?php endif; ?>
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div> <!-- End main-header -->
        
        <!-- This is where page-specific content will be loaded by AJAX -->
        <div id="dynamic-page-content-wrapper">
          <!-- Initial content of the first loaded page will go here -->
