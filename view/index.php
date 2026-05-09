<!DOCTYPE html>
<html lang="en">
<?php
require_once '../config.php';

$showDeactivatedModal = isset($_GET['deactivated']) && $_GET['deactivated'] == '1';
$deactivatedUserId = $_GET['id'] ?? '';
$reactivationSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reactivate_user_id'])) {
  $reactivateUserId = $_POST['reactivate_user_id'];
  try {
    $pdo = config::getConnexion();
    $sqlUpdate = "UPDATE user SET statut = :statut WHERE id = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute(['statut' => 'active', 'id' => $reactivateUserId]);
    $reactivationSuccess = true;
    $showDeactivatedModal = false;
  } catch (Exception $e) {
    $reactivationSuccess = false;
  }
}

// Session user info
if (session_status() === PHP_SESSION_NONE) session_start();
$loggedInUserId = $_SESSION['user_id'] ?? '';
$loggedInNom = '';
$loggedInPrenom = '';
$loggedInPicture = 'default.png';

if ($loggedInUserId !== '') {
  try {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("SELECT nom, prenom, profile_picture FROM user WHERE id = :id");
    $stmt->execute(['id' => $loggedInUserId]);
    $user = $stmt->fetch();
    if ($user) {
      $loggedInNom     = $user['nom'];
      $loggedInPrenom  = $user['prenom'];
      $loggedInPicture = $user['profile_picture'] ?? 'default.png';
    }
  } catch (Exception $e) {}
}
?>

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SmartMeal Planner</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logo-smp.jpg" alt="SmartMealPlanner" style="height:45px;width:auto;">
        <span class="ms-2 fw-bold" style="font-size:1.3rem;color:#2d2d2d;letter-spacing:0;">SmartMealPlanner</span>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="front/interfaceevent.php">Events</a></li>
          <li><a href="front/produits.php">Shop</a></li>
          <li><a href="front/Meals.php">Meals</a></li>
          <li><a href="front/Plans.php">My Plan</a></li>
          <li><a href="front/repas.php">Recipes</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <div class="d-flex align-items-center gap-2">
        <?php if ($loggedInUserId !== ''): ?>
          <a href="front/profile.php" class="btn-book-a-table text-center d-flex flex-column align-items-center">
            <img src="assets/img/profiles/<?= htmlspecialchars($loggedInPicture) ?>"
              alt="Profile"
              style="width:40px;height:40px;border-radius:50%;object-fit:cover;margin-bottom:5px;">
            <strong style="font-size:12px;">
              <?= htmlspecialchars(trim($loggedInNom . ' ' . $loggedInPrenom) ?: 'User') ?>
            </strong>
          </a>
          <a class="btn-book-a-table" href="front/logout.php">Logout</a>
        <?php else: ?>
          <a class="btn-book-a-table" href="front/signup.php">Sign Up</a>
          <a class="btn-book-a-table" href="front/signin.php">Sign In</a>
        <?php endif; ?>
      </div>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section light-background">
      <div class="container">
        <div class="row gy-4 justify-content-center justify-content-lg-between">
          <div class="col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-center">
            <h1 data-aos="fade-up">Enjoy Your Healthy<br>Delicious Food</h1>
            <p data-aos="fade-up" data-aos-delay="100">We are team of talented designers making websites with Bootstrap</p>
            <div class="d-flex" data-aos="fade-up" data-aos-delay="200">
              <a href="front/produits.php" class="btn-get-started">Shop Now</a>
              <a href="front/interfaceevent.php" class="glightbox btn-watch-video d-flex align-items-center ms-3">
                <i class="bi bi-calendar-event"></i><span class="ms-2">View Events</span>
              </a>
            </div>
          </div>
          <div class="col-lg-5 order-1 order-lg-2 hero-img" data-aos="zoom-out">
            <img src="assets/img/hero-img.png" class="img-fluid animated" alt="">
          </div>
        </div>
      </div>
    </section><!-- /Hero Section -->

  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container">
      <div class="row gy-3">
        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-geo-alt icon"></i>
          <div class="address">
            <h4>Address</h4>
            <p>Esprit Ghazela</p>
            <p>Ariana, Tunisie</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-telephone icon"></i>
          <div>
            <h4>Contact</h4>
            <p>
              <strong>Phone:</strong> <span>50 547 135</span><br>
              <strong>Email:</strong> <span>smartmealplanner@gmail.com</span><br>
            </p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-link-45deg icon"></i>
          <div>
            <h4>Liens utiles</h4>
            <ul style="list-style:none;padding:0;margin:0;">
              <li><a href="#hero" style="color:#ce1212;">Accueil</a></li>
              <li><a href="front/produits.php" style="color:#ce1212;">Produits</a></li>
              <li><a href="front/categories.php" style="color:#ce1212;">Catégories</a></li>
              <li><a href="front/repas.php" style="color:#ce1212;">Recettes & Repas</a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <h4>Follow Us</h4>
          <div class="social-links d-flex">
            <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div>
    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <!-- Deactivated Modal -->
  <div class="modal fade" id="deactivatedModal" tabindex="-1" aria-labelledby="deactivatedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:22px;overflow:hidden;border:none;box-shadow:0 20px 50px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background:linear-gradient(135deg,#fff 0%,#fff5f5 100%);border-bottom:1px solid #f3d7d7;">
          <h5 class="modal-title fw-bold" id="deactivatedModalLabel" style="color:#ce1212;">Account Deactivated</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4 px-4">
          <div style="width:82px;height:82px;margin:0 auto 18px;border-radius:50%;background:rgba(206,18,18,0.10);display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-person-x-fill" style="font-size:34px;color:#ce1212;"></i>
          </div>
          <h4 style="font-weight:700;color:#212529;margin-bottom:10px;">Your account has been deactivated</h4>
          <p style="color:#6c757d;font-size:15px;margin-bottom:0;">Would you like to reactivate your account and continue?</p>
        </div>
        <div class="modal-footer d-flex justify-content-center gap-2 border-0 pb-4">
          <a href="index.php" class="btn btn-light px-4 py-2" style="border-radius:999px;border:1px solid #ddd;">Cancel</a>
          <form method="POST" action="" style="display:inline;">
            <input type="hidden" name="reactivate_user_id" value="<?= htmlspecialchars((string)$deactivatedUserId) ?>">
            <button type="submit" class="btn px-4 py-2" style="border-radius:999px;background:#ce1212;color:#fff;border:none;">Reactivate Account</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php if ($showDeactivatedModal): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('deactivatedModal')).show();
      });
    </script>
  <?php endif; ?>

  <!-- Reactivated Modal -->
  <div class="modal fade" id="reactivatedModal" tabindex="-1" aria-labelledby="reactivatedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:22px;overflow:hidden;border:none;box-shadow:0 20px 50px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background:linear-gradient(135deg,#fff 0%,#f4fff7 100%);border-bottom:1px solid #d8f0df;">
          <h5 class="modal-title fw-bold" id="reactivatedModalLabel" style="color:#198754;">Account Reactivated</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4 px-4">
          <div style="width:82px;height:82px;margin:0 auto 18px;border-radius:50%;background:rgba(25,135,84,0.10);display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-check-circle-fill" style="font-size:34px;color:#198754;"></i>
          </div>
          <h4 style="font-weight:700;color:#212529;margin-bottom:10px;">Your account has been reactivated</h4>
          <p style="color:#6c757d;font-size:15px;margin-bottom:0;">You can now sign in again whenever you want.</p>
        </div>
        <div class="modal-footer d-flex justify-content-center border-0 pb-4">
          <button type="button" class="btn btn-success px-4 py-2" data-bs-dismiss="modal" style="border-radius:999px;">Okay</button>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
