<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_hUserId  = $_SESSION['user_id'] ?? '';
$_hNom     = '';
$_hPrenom  = '';
$_hPicture = 'default.png';
if ($_hUserId !== '') {
  try {
    require_once __DIR__ . '/../../config.php';
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("SELECT nom, prenom, profile_picture FROM user WHERE id = :id");
    $stmt->execute(['id' => $_hUserId]);
    $u = $stmt->fetch();
    if ($u) {
      $_hNom     = $u['nom'];
      $_hPrenom  = $u['prenom'];
      $_hPicture = $u['profile_picture'] ?? 'default.png';
    }
  } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner</title>
  <link href="../assets/template/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/template/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/template/vendor/aos/aos.css" rel="stylesheet">
  <link href="../assets/template/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../assets/template/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="../assets/template/css/main.css" rel="stylesheet">
</head>
<body class="index-page">
<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container position-relative d-flex align-items-center justify-content-between">
    <a href="../index.php" class="logo d-flex align-items-center me-auto me-xl-0" style="gap:6px;">
      <img src="../assets/img/logo-smp.jpg" alt="SmartMealPlanner" style="height:42px;width:auto;">
      <span class="fw-bold" style="font-size:1.25rem;color:#2d2d2d;letter-spacing:0;">SmartMealPlanner</span>
    </a>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="../index.php" <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>Home</a></li>
        <li><a href="interfaceevent.php" <?= $currentPage === 'interfaceevent.php' ? 'class="active"' : '' ?>>Events</a></li>
        <li><a href="produits.php" <?= $currentPage === 'produits.php' ? 'class="active"' : '' ?>>Shop</a></li>
        <li><a href="Meals.php" <?= in_array($currentPage, ['Meals.php','day_plan.php','Plans.php','view_plan.php','create_plan.php']) ? 'class="active"' : '' ?>>Meals</a></li>
        <li><a href="Plans.php" <?= $currentPage === 'Plans.php' ? 'class="active"' : '' ?>>My Plan</a></li>
        <li><a href="repas.php" <?= $currentPage === 'repas.php' ? 'class="active"' : '' ?>>Recipes</a></li>
        <li><a href="#footer">Contact</a></li>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

    <button class="btn-getstarted position-relative ms-2" onclick="ouvrirPanier()" style="border:none;cursor:pointer;<?= $currentPage !== 'produits.php' ? 'display:none!important;' : '' ?>">
      🛒 <span id="panier-badge" style="display:none;position:absolute;top:-6px;right:-6px;background:#fff;color:#c0392b;border-radius:50%;width:18px;height:18px;font-size:11px;font-weight:700;line-height:18px;text-align:center;">0</span>
    </button>
    <button onclick="ouvrirWishlist()" title="My Wishlist"
      style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:#ce1212;position:relative;margin-left:6px;padding:4px 8px;<?= $currentPage !== 'produits.php' ? 'display:none!important;' : '' ?>">
      <i class="bi bi-heart-fill"></i>
      <span id="wishlist-badge" style="display:none;position:absolute;top:-4px;right:-2px;background:#ce1212;color:white;border-radius:50%;width:16px;height:16px;font-size:10px;font-weight:700;line-height:16px;text-align:center;">0</span>
    </button>
    <button onclick="ouvrirReclamation()" title="Submit a Complaint"
      style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:#ce1212;position:relative;margin-left:6px;padding:4px 8px;<?= $currentPage !== 'produits.php' ? 'display:none!important;' : '' ?>">
      <i class="bi bi-megaphone-fill"></i>
    </button>

    <!-- User account -->
    <div class="d-flex align-items-center gap-2 ms-3">
      <?php if ($_hUserId !== ''): ?>
        <a href="profile.php" class="d-flex flex-column align-items-center text-decoration-none" style="line-height:1.1;">
          <img src="../assets/img/profiles/<?= htmlspecialchars($_hPicture) ?>"
               alt="Profile"
               style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
          <small style="font-size:0.7rem;color:#ce1212;font-weight:600;">
            <?= htmlspecialchars(trim($_hNom . ' ' . $_hPrenom) ?: 'User') ?>
          </small>
        </a>
        <a href="logout.php" style="color:#ce1212;font-weight:600;font-size:0.85rem;text-decoration:none;">Logout</a>
      <?php else: ?>
        <a href="signin.php" style="color:#ce1212;font-weight:600;font-size:0.85rem;text-decoration:none;">Sign In</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- ── COMPLAINT MODAL ── -->
<div class="modal fade" id="modalReclamation" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
      <div style="background:linear-gradient(135deg,#ce1212,#ff4444);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
        <div>
          <div style="font-size:1.1rem;font-weight:700;color:white;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-megaphone-fill"></i> Submit a Complaint
          </div>
          <div style="font-size:0.75rem;color:rgba(255,255,255,0.8);margin-top:2px;">We'll review your message as soon as possible</div>
        </div>
        <button type="button" data-bs-dismiss="modal"
          style="background:rgba(255,255,255,0.2);border:none;border-radius:50%;width:30px;height:30px;color:white;font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">
          ✕
        </button>
      </div>
      <div style="padding:24px;">
        <div id="reclamation-success" style="display:none;background:#e8f5e9;border-radius:10px;padding:14px 16px;margin-bottom:16px;text-align:center;">
          <i class="bi bi-check-circle-fill" style="color:#2e7d32;font-size:1.5rem;display:block;margin-bottom:6px;"></i>
          <div style="font-weight:700;color:#2e7d32;font-size:0.9rem;">Complaint submitted!</div>
          <div style="font-size:0.8rem;color:#555;margin-top:4px;">Thank you. We'll get back to you shortly.</div>
        </div>
        <form id="reclamationForm" onsubmit="soumettreReclamation(event)">
          <div style="margin-bottom:14px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Full Name *</label>
            <input type="text" id="rec-nom" required
              style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;font-family:'Inter',sans-serif;"
              placeholder="Your full name">
          </div>
          <div style="margin-bottom:14px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Email *</label>
            <input type="email" id="rec-email" required
              style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;font-family:'Inter',sans-serif;"
              placeholder="your@email.com">
          </div>
          <div style="margin-bottom:14px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Subject *</label>
            <select id="rec-sujet" required
              style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;font-family:'Inter',sans-serif;background:white;cursor:pointer;">
              <option value="">— Select a subject —</option>
              <option value="Delivery issue">Delivery issue</option>
              <option value="Wrong product received">Wrong product received</option>
              <option value="Product quality">Product quality</option>
              <option value="Payment problem">Payment problem</option>
              <option value="Missing item">Missing item</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div style="margin-bottom:20px;">
            <label style="font-size:0.78rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">Message *</label>
            <textarea id="rec-message" required rows="4"
              style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px 14px;font-size:0.85rem;outline:none;resize:none;font-family:'Inter',sans-serif;"
              placeholder="Describe your issue in detail..."></textarea>
          </div>
          <button type="submit"
            style="width:100%;background:#ce1212;color:white;border:none;border-radius:25px;padding:12px;font-size:0.9rem;font-weight:600;cursor:pointer;transition:0.2s;">
            <i class="bi bi-send me-2"></i>Send Complaint
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function ouvrirReclamation() {
  document.getElementById('reclamation-success').style.display = 'none';
  document.getElementById('reclamationForm').style.display = 'block';
  document.getElementById('reclamationForm').reset();
  var modal = bootstrap.Modal.getInstance(document.getElementById('modalReclamation'))
           || new bootstrap.Modal(document.getElementById('modalReclamation'));
  modal.show();
}

function soumettreReclamation(e) {
  e.preventDefault();
  var nom     = document.getElementById('rec-nom').value.trim();
  var email   = document.getElementById('rec-email').value.trim();
  var sujet   = document.getElementById('rec-sujet').value;
  var message = document.getElementById('rec-message').value.trim();
  if (!nom || !email || !sujet || !message) return;

  // Save to localStorage
  var reclamations = JSON.parse(localStorage.getItem('reclamations') || '[]');
  reclamations.unshift({
    id:      Date.now(),
    nom:     nom,
    email:   email,
    sujet:   sujet,
    message: message,
    statut:  'pending',
    date:    new Date().toLocaleString()
  });
  localStorage.setItem('reclamations', JSON.stringify(reclamations));

  // Show success
  document.getElementById('reclamationForm').style.display = 'none';
  document.getElementById('reclamation-success').style.display = 'block';

  // Auto-close after 3s
  setTimeout(function() {
    var modal = bootstrap.Modal.getInstance(document.getElementById('modalReclamation'));
    if (modal) modal.hide();
  }, 3000);
}
</script>
  </div>
</header>
<main class="main">
