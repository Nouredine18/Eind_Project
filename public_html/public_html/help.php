<?php
include('connect.php');
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();




if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT last_login FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($last_login);
    $stmt->fetch();
    $stmt->close();

<<<<<<< Updated upstream
    // Controleert als de gebruiker langer dan 6 dagen geleden is ingelogd of als de persoon zijn pc niet heeft aangeraakt op een interval van 6 dagen.
=======
>>>>>>> Stashed changes
    $six_days_ago = strtotime('-6 days');
    if (strtotime($last_login) < $six_days_ago) {
      
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    }
  }
}
<<<<<<< Updated upstream
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title>Help Sectie</title>
	<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
	<link rel="icon" href="../assets/img/kaiadmin/favicon.ico" type="image/x-icon"/>

	<!-- Fonts and icons -->
	<script src="../assets/js/plugin/webfont/webfont.min.js"></script>
	<script>
		WebFont.load({
			google: {"families":["Public Sans:300,400,500,600,700"]},
			custom: {"families":["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['../assets/css/fonts.min.css']},
			active: function() {
				sessionStorage.fonts = true;
			}
		});
	</script>

	<!-- CSS Files -->
	<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="../assets/css/plugins.min.css">
	<link rel="stylesheet" href="../assets/css/kaiadmin.min.css">

	<!-- CSS Just for demo purpose, don't include it in your project -->
	<link rel="stylesheet" href="../assets/css/demo.css">
</head>
<body>
<?php
  include('side_bar_template.php')
?>
			<div class="container">
			<?php
				// Dummy data voor veelgestelde vragen
				$faqs = [
					["question" => "Hoe kan ik mijn profiel bewerken?", "answer" => "Ga naar je profielinstellingen en klik op bewerken."],
					["question" => "Hoe wijzig ik mijn wachtwoord?", "answer" => "Klik op 'Wachtwoord vergeten' op de inlogpagina."],
					["question" => "Wat moet ik doen als ik een foutmelding krijg?", "answer" => "Neem contact op met de klantenservice via de contactpagina."]
				];

				// Zoekfunctie (optioneel)
				$search = isset($_GET['search']) ? $_GET['search'] : '';
				$filtered_faqs = [];

				if ($search) {
					foreach ($faqs as $faq) {
						if (stripos($faq['question'], $search) !== false) {
							$filtered_faqs[] = $faq;
						}
					}
				} else {
					$filtered_faqs = $faqs;
				}
				?>
					<h1>Veelgestelde Vragen</h1>
					<form action="help.php" method="get">
						<input type="text" name="search" placeholder="Zoek een vraag..." value="<?php echo htmlspecialchars($search); ?>">
						<button type="submit">Zoeken</button>
					</form>

					<?php if (empty($filtered_faqs)): ?>
						<p>Geen resultaten gevonden voor "<?php echo htmlspecialchars($search); ?>"</p>
					<?php else: ?>
						<ul>
							<?php foreach ($filtered_faqs as $faq): ?>
								<li>
									<h3><?php echo htmlspecialchars($faq['question']); ?></h3>
									<p><?php echo htmlspecialchars($faq['answer']); ?></p>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
			</div>
			
			<footer class="footer">
				<div class="container-fluid d-flex justify-content-between">
					<nav class="pull-left">
						<ul class="nav">
							<li class="nav-item">
								<a class="nav-link" href="http://www.themekita.com">
									ThemeKita
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#"> Help </a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#"> Licenses </a>
							</li>
						</ul>
					</nav>
					<div class="copyright">
						2024, made with <i class="fa fa-heart heart text-danger"></i> by
						<a href="http://www.themekita.com">ThemeKita</a>
					</div>
					<div>
						Distributed by
						<a target="_blank" href="https://themewagon.com/">ThemeWagon</a>.
					</div>
				</div>
			</footer>
		</div>
		
		<!-- Custom template | don't include it in your project! -->
		<div class="custom-template">
			<div class="title">Settings</div>
			<div class="custom-content">
				<div class="switcher">
					<div class="switch-block">
						<h4>Logo Header</h4>
						<div class="btnSwitch">
							<button type="button" class=" selected changeLogoHeaderColor" data-color="dark"></button>
							<button type="button" class="selected changeLogoHeaderColor" data-color="blue"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="purple"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="light-blue"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="green"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="orange"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="red"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="white"></button>
							<br/>
							<button type="button" class="changeLogoHeaderColor" data-color="dark2"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="blue2"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="purple2"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="light-blue2"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="green2"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="orange2"></button>
							<button type="button" class="changeLogoHeaderColor" data-color="red2"></button>
						</div>
					</div>
					<div class="switch-block">
						<h4>Navbar Header</h4>
						<div class="btnSwitch">
							<button type="button" class="changeTopBarColor" data-color="dark"></button>
							<button type="button" class="changeTopBarColor" data-color="blue"></button>
							<button type="button" class="changeTopBarColor" data-color="purple"></button>
							<button type="button" class="changeTopBarColor" data-color="light-blue"></button>
							<button type="button" class="changeTopBarColor" data-color="green"></button>
							<button type="button" class="changeTopBarColor" data-color="orange"></button>
							<button type="button" class="changeTopBarColor" data-color="red"></button>
							<button type="button" class="changeTopBarColor" data-color="white"></button>
							<br/>
							<button type="button" class="changeTopBarColor" data-color="dark2"></button>
							<button type="button" class="selected changeTopBarColor" data-color="blue2"></button>
							<button type="button" class="changeTopBarColor" data-color="purple2"></button>
							<button type="button" class="changeTopBarColor" data-color="light-blue2"></button>
							<button type="button" class="changeTopBarColor" data-color="green2"></button>
							<button type="button" class="changeTopBarColor" data-color="orange2"></button>
							<button type="button" class="changeTopBarColor" data-color="red2"></button>
						</div>
					</div>
					<div class="switch-block">
						<h4>Sidebar</h4>
						<div class="btnSwitch">
							<button type="button" class="selected changeSideBarColor" data-color="white"></button>
							<button type="button" class="changeSideBarColor" data-color="dark"></button>
							<button type="button" class="changeSideBarColor" data-color="dark2"></button>
						</div>
					</div>
				</div>
			</div>
			<div class="custom-toggle">
				<i class="icon-settings"></i>
			</div>
		</div>
		<!-- End Custom template -->
	</div>
	<!--   Core JS Files   -->
	<script src="../assets/js/core/jquery-3.7.1.min.js"></script>
	<script src="../assets/js/core/popper.min.js"></script>
	<script src="../assets/js/core/bootstrap.min.js"></script>
	
	<!-- jQuery Scrollbar -->
	<script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
	<!-- Moment JS -->
	<script src="../assets/js/plugin/moment/moment.min.js"></script>

	<!-- Chart JS -->
	<script src="../assets/js/plugin/chart.js/chart.min.js"></script>

	<!-- jQuery Sparkline -->
	<script src="../assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

	<!-- Chart Circle -->
	<script src="../assets/js/plugin/chart-circle/circles.min.js"></script>

	<!-- Datatables -->
	<script src="../assets/js/plugin/datatables/datatables.min.js"></script>

	<!-- Bootstrap Notify -->
	<script src="../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

	<!-- jQuery Vector Maps -->
	<script src="../assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
	<script src="../assets/js/plugin/jsvectormap/world.js"></script>

	<!-- Sweet Alert -->
	<script src="../assets/js/plugin/sweetalert/sweetalert.min.js"></script>

	<!-- Kaiadmin JS -->
	<script src="../assets/js/kaiadmin.min.js"></script>

	<!-- Kaiadmin DEMO methods, don't include it in your project! -->
	<script src="../assets/js/setting-demo2.js"></script>
</body>
</html>
=======

// Voorbeeld data voor FAQ sectie
$faqs = [
    ["question" => "Hoe kan ik mijn profiel bewerken?", "answer" => "Ga naar je profielpagina. Daar vind je opties om je informatie te bewerken."],
    ["question" => "Hoe wijzig ik mijn wachtwoord?", "answer" => "Je kunt je wachtwoord wijzigen in de profielinstellingen. Zoek naar een 'Wachtwoord Wijzigen' optie."],
    ["question" => "Wat moet ik doen als ik een foutmelding krijg?", "answer" => "Noteer de foutmelding en de stappen die je hebt ondernomen. Je kunt contact opnemen met support via de 'Contact' pagina indien beschikbaar, of controleer of de FAQ vergelijkbare problemen behandelt."],
    ["question" => "Hoe werkt CO2-compensatie?", "answer" => "CO2-compensatie houdt in dat geïnvesteerd wordt in projecten die de uitstoot van broeikasgassen verminderen of verwijderen, zoals herbebossing, hernieuwbare energie, of energie-efficiëntieprogramma's. Jouw bijdragen helpen deze geverifieerde projecten te financieren om je eigen koolstofvoetafdruk te compenseren."]
];

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtered_faqs = [];

if (!empty($search_term)) {
    foreach ($faqs as $faq) {
        if (stripos($faq['question'], $search_term) !== false || stripos($faq['answer'], $search_term) !== false) {
            $filtered_faqs[] = $faq;
        }
    }
} else {
    $filtered_faqs = $faqs;
}

include('side_bar_template.php');
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Help & Support</h3>
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
                <a href="help.php">Help</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Frequently Asked Questions (FAQ)</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="help.php" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search questions..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>

                    <?php if (empty($filtered_faqs)): ?>
                        <p class="text-center text-muted">No results found for "<?php echo htmlspecialchars($search_term); ?>"</p>
                    <?php else: ?>
                        <div class="accordion accordion-secondary" id="faqAccordion">
                            <?php foreach ($filtered_faqs as $index => $faq): ?>
                                <div class="card">
                                    <div class="card-header" id="heading<?php echo $index; ?>">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-start <?php if ($index > 0) echo 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo ($index == 0) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                                <?php echo htmlspecialchars($faq['question']); ?>
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapse<?php echo $index; ?>" class="collapse <?php if ($index == 0) echo 'show'; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
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

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Contact Support</h4>
                </div>
                <div class="card-body">
                    <p>If you can't find an answer to your question in the FAQ, please feel free to contact us.</p>
                    <p><strong>Email:</strong> support@ecoligocollective.com</p>
                    <p><strong>Phone:</strong> +1-234-567-8900 (Mon-Fri, 9am-5pm)</p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
// side_bar_template.php include sluit de </div> voor main-panel,
// dan </div> voor wrapper, en dan </body> en </html>.
?>
>>>>>>> Stashed changes
