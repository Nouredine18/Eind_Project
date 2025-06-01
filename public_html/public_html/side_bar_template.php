<?php
$asset_prefix = '';
// Controleer of het huidige script wordt uitgevoerd vanuit de 'co2_calculation' map
// of een andere bekende submap die één niveau lager is dan public_html.
if (strpos($_SERVER['SCRIPT_NAME'], '/co2_calculation/') !== false) {
    $asset_prefix = '../';
}

$user_id = $_SESSION['user_id'];

// --- Start: Added for active sidebar state ---
$current_page = basename($_SERVER['PHP_SELF']);
$ecoligo_home_pages = [
    'home.php', 'profile.php', 'compensation_projects.php', 
    'community_chat.php', 'search_users.php', 'betalingsgeschiedenis.php', 
    'compensation_data.php', 'help.php', 'notifications.php', 'all_notifications.php', 
    'abonnement.php', 'logout.php',
    'followers_list.php', 'following_list.php', 'follow_profile_details.php',
    'leaderboard.php', // Leaderboard pagina toegevoegd
    'rewards.php', // Beloningen pagina toegevoegd
    'co2_quiz.php', // CO2-quiz pagina toegevoegd
    'eco_travel_recommendations.php', // Eco-reisaanbevelingen pagina toegevoegd
    'weekly_challenges.php', // Wekelijkse uitdagingen pagina toegevoegd
    'events_workshops.php' // Evenementen & workshops pagina toegevoegd
];
$is_ecoligo_home_active = in_array($current_page, $ecoligo_home_pages);
// --- End: Added for active sidebar state ---

// Haal notificaties op
function fetchNotifications($conn, $userId) {
    $stmt = $conn->prepare("SELECT notification_id, message, notification_type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
    return $notifications;
}

// Functie om recente antwoorden op berichten van de gebruiker in de communitychat op te halen
function fetchUserMessageReplies($conn, $userId, $limit = 5) {
    $replies = [];
    $sql = "SELECT
                cm.message_id as reply_message_id,
                cm.message_content as reply_content,
                cm.timestamp as reply_timestamp,
                cm.message_type as reply_message_type,
                u_replier.user_id as replier_user_id,
                u_replier.first_name as replier_first_name,
                u_replier.last_name as replier_last_name,
                u_replier.profile_picture_url as replier_profile_picture
            FROM
                community_messages cm
            JOIN
                users u_replier ON cm.user_id = u_replier.user_id
            JOIN
                community_messages pm ON cm.parent_message_id = pm.message_id
            WHERE
                pm.user_id = ?  -- The current session user_id (author of the original message)
                AND cm.user_id != ? -- The replier is not the current user
                AND cm.is_deleted = 0
                AND pm.is_deleted = 0
            ORDER BY
                cm.timestamp DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iii", $userId, $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $replies[] = $row;
        }
        $stmt->close();
    }
    return $replies;
}

$notifications = fetchNotifications($conn, $user_id);
$message_replies = fetchUserMessageReplies($conn, $user_id); // Haal antwoorden op

function getNotificationIcon($type) {
    switch ($type) {
        case 'profile_update':
            return 'fa fa-user';
        case 'travel_update':
            return 'fa fa-plane';
        default:
            return 'fa fa-info-circle';
    }
}

function getNotificationType($type) {
    switch ($type) {
        case 'profile_update':
            return 'primary';
        case 'travel_update':
            return 'success';
        default:
            return 'default';
    }
}

// Definieer Snelle Acties indien nog niet gedefinieerd in een meer globale scope
if (!isset($quick_actions)) {
    $quick_actions = [
        ['icon' => 'fas fa-user', 'text' => 'My Profile', 'link' => $asset_prefix . 'profile.php', 'bg_color' => 'bg-primary'],
        ['icon' => 'fas fa-comments', 'text' => 'Community', 'link' => $asset_prefix . 'community_chat.php', 'bg_color' => 'bg-success'],
        ['icon' => 'fas fa-leaf', 'text' => 'CO2 Quiz', 'link' => $asset_prefix . 'co2_quiz.php', 'bg_color' => 'bg-info'],
        ['icon' => 'fas fa-question-circle', 'text' => 'Help', 'link' => $asset_prefix . 'help.php', 'bg_color' => 'bg-warning'],
        ['icon' => 'fas fa-trophy', 'text' => 'Leaderboard', 'link' => $asset_prefix . 'leaderboard.php', 'bg_color' => 'bg-danger'],
        ['icon' => 'fas fa-gift', 'text' => 'Rewards', 'link' => $asset_prefix . 'rewards.php', 'bg_color' => 'bg-secondary'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Kaiadmin - Bootstrap 5 Admin Dashboard</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="<?php echo $asset_prefix; ?>assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/webfont/webfont.min.js"></script>
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
          urls: ["<?php echo $asset_prefix; ?>assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo $asset_prefix; ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $asset_prefix; ?>assets/css/plugins.min.css" />
    <link rel="stylesheet" href="<?php echo $asset_prefix; ?>assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="<?php echo $asset_prefix; ?>assets/css/demo.css" />

    <?php
    // Voeg paginaspecifieke CSS-bestanden toe indien gedefinieerd
    if (isset($page_specific_css) && is_array($page_specific_css)) {
        foreach ($page_specific_css as $css_file_url) {
            // Controleer of de URL absoluut is, zo niet, pas dan het prefix aan
            if (strpos($css_file_url, 'http://') === 0 || strpos($css_file_url, 'https://') === 0 || strpos($css_file_url, '//') === 0) {
                echo '<link rel="stylesheet" href="' . htmlspecialchars($css_file_url) . '" />' . "\n";
            } else {
                echo '<link rel="stylesheet" href="' . $asset_prefix . htmlspecialchars($css_file_url) . '" />' . "\n";
            }
        }
    }
    ?>
  </head>
  <body>
    <div class="wrapper">
      <div class="sidebar sidebar-style-2" data-background-color="dark">
        <div class="sidebar-logo">
          <div class="logo-header" data-background-color="dark">
            <a href="<?php echo $asset_prefix; ?>index.php" class="logo">
              <img
                src="<?php echo $asset_prefix; ?>assets/img/kaiadmin/favicon.png"
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
                  class="<?php if (!$is_ecoligo_home_active) echo 'collapsed'; ?>"
                  aria-expanded="<?php echo $is_ecoligo_home_active ? 'true' : 'false'; ?>"
                >
                  <i class="fas fa-home"></i>
                  <p>Ecoligo Home</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse <?php if ($is_ecoligo_home_active) echo 'show'; ?>" id="dashboard">
                  <ul class="nav nav-collapse">
                    <li class="<?php if ($current_page == 'home.php') echo 'active'; ?>">
                      <a href="home.php">
                        <span class="sub-item">Home</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'profile.php' || $current_page == 'followers_list.php' || $current_page == 'following_list.php' || $current_page == 'follow_profile_details.php') echo 'active'; ?>">
                      <a href="profile.php">
                        <span class="sub-item">Profile</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'compensation_projects.php') echo 'active'; ?>">
                      <a href="compensation_projects.php">
                        <span class="sub-item">Compensation Projects</span>
                      </a>
<<<<<<< Updated upstream
=======
                    </li>
                    <li class="<?php if ($current_page == 'community_chat.php') echo 'active'; ?>">
                      <a href="community_chat.php">
                        <span class="sub-item">Community Chat</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'search_users.php') echo 'active'; ?>">
                      <a href="search_users.php">
                        <span class="sub-item">Search Users</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'leaderboard.php') echo 'active'; ?>">
                      <a href="leaderboard.php">
                        <span class="sub-item">Leaderboards</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'rewards.php') echo 'active'; ?>">
                      <a href="rewards.php">
                        <span class="sub-item">Rewards</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'co2_quiz.php') echo 'active'; ?>">
                      <a href="co2_quiz.php">
                        <span class="sub-item">CO2 Quiz</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'eco_travel_recommendations.php') echo 'active'; ?>">
                      <a href="eco_travel_recommendations.php">
                        <span class="sub-item">Eco Travel Tips</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'weekly_challenges.php') echo 'active'; ?>">
                      <a href="weekly_challenges.php">
                        <span class="sub-item">Weekly Challenges</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'events_workshops.php') echo 'active'; ?>">
                      <a href="events_workshops.php">
                        <span class="sub-item">Events & Workshops</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'betalingsgeschiedenis.php') echo 'active'; ?>">
                      <a href="betalingsgeschiedenis.php">
                        <span class="sub-item">Payment History</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'compensation_data.php') echo 'active'; ?>">
                      <a href="compensation_data.php">
                        <span class="sub-item">Compensation Data</span>
                      </a>
                    </li>
                    <li class="<?php if ($current_page == 'help.php') echo 'active'; ?>">
>>>>>>> Stashed changes
                      <a href="help.php">
                        <span class="sub-item">Help</span>
                      </a>
                    </li>     
                    <li class="<?php if ($current_page == 'notifications.php' || $current_page == 'all_notifications.php') echo 'active'; ?>">
                      <a href="notifications.php">
                        <span class="sub-item">Notifications</span>
                      </a>
<<<<<<< Updated upstream
                      <a href="logout.php">
                        <span class="sub-item">Logout</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Components</h4>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#base">
                  <i class="fas fa-layer-group"></i>
                  <p>Base</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="base">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="components/avatars.php">
                        <span class="sub-item">Avatars</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/buttons.php">
                        <span class="sub-item">Buttons</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/gridsystem.php">
                        <span class="sub-item">Grid System</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/panels.php">
                        <span class="sub-item">Panels</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/notifications.php">
                        <span class="sub-item">Notifications</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/sweetalert.php">
                        <span class="sub-item">Sweet Alert</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/font-awesome-icons.php">
                        <span class="sub-item">Font Awesome Icons</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/simple-line-icons.php">
                        <span class="sub-item">Simple Line Icons</span>
                      </a>
                    </li>
                    <li>
                      <a href="components/typography.php">
                        <span class="sub-item">Typography</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#sidebarLayouts">
                  <i class="fas fa-th-list"></i>
                  <p>Sidebar Layouts</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="sidebarLayouts">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="sidebar-style-2.php">
                        <span class="sub-item">Sidebar Style 2</span>
                      </a>
                    </li>
                    <li>
                      <a href="icon-menu.php">
                        <span class="sub-item">Icon Menu</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#forms">
                  <i class="fas fa-pen-square"></i>
                  <p>Forms</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="forms">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="forms/forms.php">
                        <span class="sub-item">Basic Form</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#tables">
                  <i class="fas fa-table"></i>
                  <p>Tables</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="tables">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="tables/tables.php">
                        <span class="sub-item">Basic Table</span>
                      </a>
                    </li>
                    <li>
                      <a href="tables/datatables.php">
                        <span class="sub-item">Datatables</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#maps">
                  <i class="fas fa-map-marker-alt"></i>
                  <p>Maps</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="maps">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="maps/googlemaps.php">
                        <span class="sub-item">Google Maps</span>
                      </a>
                    </li>
                    <li>
                      <a href="maps/jsvectormap.php">
                        <span class="sub-item">Jsvectormap</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#charts">
                  <i class="far fa-chart-bar"></i>
                  <p>Charts</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="charts">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="charts/charts.php">
                        <span class="sub-item">Chart Js</span>
                      </a>
                    </li>
                    <li>
                      <a href="charts/sparkline.php">
                        <span class="sub-item">Sparkline</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a href="widgets.php">
                  <i class="fas fa-desktop"></i>
                  <p>Widgets</p>
                  <span class="badge badge-success">4</span>
                </a>
              </li>
              <li class="nav-item">
                <a href="../../documentation/index.php">
                  <i class="fas fa-file"></i>
                  <p>Documentation</p>
                  <span class="badge badge-secondary">1</span>
                </a>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#submenu">
                  <i class="fas fa-bars"></i>
                  <p>Menu Levels</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="submenu">
                  <ul class="nav nav-collapse">
                    <li>
                      <a data-bs-toggle="collapse" href="#subnav1">
                        <span class="sub-item">Level 1</span>
                        <span class="caret"></span>
                      </a>
                      <div class="collapse" id="subnav1">
                        <ul class="nav nav-collapse subnav">
                          <li>
                            <a href="#">
                              <span class="sub-item">Level 2</span>
                            </a>
                          </li>
                          <li>
                            <a href="#">
                              <span class="sub-item">Level 2</span>
                            </a>
                          </li>
                        </ul>
                      </div>
                    </li>
                    <li>
                      <a data-bs-toggle="collapse" href="#subnav2">
                        <span class="sub-item">Level 1</span>
                        <span class="caret"></span>
                      </a>
                      <div class="collapse" id="subnav2">
                        <ul class="nav nav-collapse subnav">
                          <li>
                            <a href="#">
                              <span class="sub-item">Level 2</span>
                            </a>
                          </li>
                        </ul>
                      </div>
                    </li>
                    <li>
                      <a href="#">
                        <span class="sub-item">Level 1</span>
=======
                    </li>
                    <li class="<?php if ($current_page == 'abonnement.php') echo 'active'; ?>">
                      <a href="abonnement.php">
                        <span class="sub-item">Subscriptions</span>
>>>>>>> Stashed changes
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
              <a href="<?php echo $asset_prefix; ?>index.php" class="logo">
                <img
                  src="<?php echo $asset_prefix; ?>assets/img/kaiadmin/logo_light.svg"
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
                <div class="input-group">
                  <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                  <input
                    type="text"
                    placeholder="Search ..."
                    class="form-control"
                  />
                </div>
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li
                  class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none"
                >
                  <a
                    class="nav-link dropdown-toggle"
                    data-bs-toggle="dropdown"
                    href="#"
                    role="button"
                    aria-expanded="false"
                    aria-haspopup="true"
                  >
                    <i class="fa fa-search"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                      <div class="input-group">
                        <input
                          type="text"
                          placeholder="Search ..."
                          class="form-control"
                        />
                      </div>
                    </form>
                  </ul>
                </li>
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
                        Recent Replies to Your Messages
                        <a href="<?php echo $asset_prefix; ?>community_chat.php" class="small">View All in Chat</a>
                      </div>
                    </li>
                    <li>
                      <div class="message-notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <?php if (empty($message_replies)): ?>
                            <div class="text-center p-3">
                              <small class="text-muted">No new replies to your messages.</small>
                            </div>
                          <?php else: ?>
                            <?php foreach ($message_replies as $reply): ?>
                              <a href="<?php echo $asset_prefix; ?>community_chat.php#message-item-<?php echo $reply['reply_message_id']; ?>">
                                <div class="notif-img">
                                  <img
                                    src="<?php
                                        $replier_pic = trim((string)$reply['replier_profile_picture']);
                                        if (empty($replier_pic) || $replier_pic === 'assets/img/kaiadmin/default.jpg') {
                                            echo htmlspecialchars($asset_prefix . 'assets/img/kaiadmin/default.jpg');
                                        } else {
                                            echo htmlspecialchars($asset_prefix . $replier_pic);
                                        }
                                    ?>"
                                    alt="Img Profile"
                                  />
                                </div>
                                <div class="notif-content">
                                  <span class="subject"><?php echo htmlspecialchars($reply['replier_first_name'] . ' ' . $reply['replier_last_name']); ?></span>
                                  <span class="block">
                                    <?php 
                                      if ($reply['reply_message_type'] === 'image') {
                                        echo htmlspecialchars(empty(trim($reply['reply_content'])) ? '[Sent an image]' : '[Image] ' . substr(trim($reply['reply_content']), 0, 30) . '...');
                                      } else {
                                        echo htmlspecialchars(substr(trim($reply['reply_content']), 0, 40)) . (strlen(trim($reply['reply_content'])) > 40 ? '...' : '');
                                      }
                                    ?>
                                  </span>
                                  <span class="time"><?php echo date('M d, h:i A', strtotime($reply['reply_timestamp'])); ?></span>
                                </div>
                              </a>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="<?php echo $asset_prefix; ?>community_chat.php"
                        >Go to Community Chat<i class="fa fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="<?php echo $asset_prefix; ?>home.php"
                    id="notifDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i href="<?php echo $asset_prefix; ?>home.php" class="fa fa-bell"></i>
                    <span 
                    class="notification">
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
                          <?php foreach ($notifications as $notif): ?>
                            <a href="#">
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
                      <a class="see-all" href="<?php echo $asset_prefix; ?>notifications.php">See all notifications
                        <i class="fa fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <i class="fas fa-layer-group"></i>
                  </a>
                  <div class="dropdown-menu quick-actions animated fadeIn">
                    <div class="quick-actions-header">
                      <span class="title mb-1">Quick Actions</span>
                      <span class="subtitle op-7">Shortcuts</span>
                    </div>
                    <div class="quick-actions-scroll scrollbar-outer">
                      <div class="quick-actions-items">
                        <div class="row m-0">
                          <?php foreach ($quick_actions as $action): ?>
                            <a class="col-6 col-md-4 p-0" href="<?php echo htmlspecialchars($action['link']); ?>">
                              <div class="quick-actions-item">
                                <div class="avatar-item <?php echo htmlspecialchars($action['bg_color']); ?> rounded-circle">
                                  <i class="<?php echo htmlspecialchars($action['icon']); ?>"></i>
                                </div>
                                <span class="text"><?php echo htmlspecialchars($action['text']); ?></span>
                              </div>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <?php
                    if (!isset($_SESSION['user_id'])) {
                        $profile_pic_display = htmlspecialchars($asset_prefix . 'assets/img/kaiadmin/default.jpg');
                        $first_name_display = 'Guest';
                        $email_display = 'guest@example.com';
                        $last_name_display = '';
                    } else {
                        if (!isset($_SESSION['profile_picture_url']) || !isset($_SESSION['first_name']) || !isset($_SESSION['email'])) {
                            $user_id_for_sidebar = $_SESSION['user_id'];
                            $sql_sidebar_user = "SELECT first_name, last_name, email, profile_picture_url FROM users WHERE user_id = ?";
                            $stmt_sidebar_user = $conn->prepare($sql_sidebar_user);
                            if ($stmt_sidebar_user) {
                                $stmt_sidebar_user->bind_param("i", $user_id_for_sidebar);
                                $stmt_sidebar_user->execute();
                                $stmt_sidebar_user->bind_result($sidebar_first_name, $sidebar_last_name, $sidebar_email, $sidebar_profile_pic_db);
                                if ($stmt_sidebar_user->fetch()) {
                                    $_SESSION['first_name'] = $sidebar_first_name;
                                    $_SESSION['last_name'] = $sidebar_last_name;
                                    $_SESSION['email'] = $sidebar_email;
                                    $_SESSION['profile_picture_url'] = trim((string)$sidebar_profile_pic_db);
                                }
                                $stmt_sidebar_user->close();
                            }
                        }
                        
                        $current_profile_pic_path = $_SESSION['profile_picture_url'] ?? '';
                        if (empty($current_profile_pic_path) || $current_profile_pic_path === 'assets/img/kaiadmin/default.jpg') {
                            $profile_pic_display = htmlspecialchars($asset_prefix . 'assets/img/kaiadmin/default.jpg');
                        } else {
                            $profile_pic_display = htmlspecialchars($asset_prefix . $current_profile_pic_path);
                        }
                        $first_name_display = htmlspecialchars($_SESSION['first_name'] ?? 'User');
                        $email_display = htmlspecialchars($_SESSION['email'] ?? 'user@example.com');
                        $last_name_display = htmlspecialchars($_SESSION['last_name'] ?? '');
                    }
                    ?>
                    <div class="avatar-sm">
                      <img
                        src="<?php echo $profile_pic_display; ?>"
                        alt="Profile Picture"
                        class="avatar-img rounded-circle"
                      />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Hi,</span>
                      <span class="fw-bold"><?php echo $first_name_display; ?></span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img
                              src="<?php echo $profile_pic_display; ?>"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4><?php echo $first_name_display . ' ' . $last_name_display; ?></h4>
                            <p class="text-muted"><?php echo $email_display; ?></p>
                            <a
                              href="<?php echo $asset_prefix; ?>profile.php"
                              class="btn btn-xs btn-secondary btn-sm"
                              >View Profile</a
                            >
                          </div>
                        </div>
                      </li>
                        <li>
                          <div class="dropdown-divider"></div>
                          <?php if (isset($_SESSION['user_id'])): ?>
                          <a class="dropdown-item" href="<?php echo $asset_prefix; ?>profile.php">My Profile</a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item" href="<?php echo $asset_prefix; ?>logout.php">Logout</a>
                          <?php else: ?>
                          <a class="dropdown-item" href="<?php echo $asset_prefix; ?>login.php">Login</a>
                          <?php endif; ?>
                        </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>
    <!--   Core JS Files   -->
    <script src="<?php echo $asset_prefix; ?>assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="<?php echo $asset_prefix; ?>assets/js/core/popper.min.js"></script>
    <script src="<?php echo $asset_prefix; ?>assets/js/core/bootstrap.min.js"></script>

    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/chart.js/chart.min.js"></script>

    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/chart-circle/circles.min.js"></script>

    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/datatables/datatables.min.js"></script>

    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="<?php echo $asset_prefix; ?>assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="<?php echo $asset_prefix; ?>assets/js/kaiadmin.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <?php /* Commenting out or removing these demo scripts should stop the persistent notification */ ?>
    <!-- <script src="<?php echo $asset_prefix; ?>assets/js/setting-demo.js"></script> -->
    <!-- <script src="<?php echo $asset_prefix; ?>assets/js/demo.js"></script> -->
    <script>
      $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#177dff",
        fillColor: "rgba(23, 125, 255, 0.14)",
      });

      $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#f3545d",
        fillColor: "rgba(243, 84, 93, .14)",
      });

      $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#ffa534",
        fillColor: "rgba(255, 165, 52, .14)",
      });
    </script>
  </body>
</html>
