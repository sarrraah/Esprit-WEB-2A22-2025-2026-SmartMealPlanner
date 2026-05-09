<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Meal Planner - Admin</title>
  <link href="../assets/template/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/template/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    /* Reset template fonts completely */
    body, h1, h2, h3, h4, h5, h6, p, span, a, div, td, th, input, select, textarea, button {
      font-family: 'Raleway', sans-serif !important;
    }

    body { background: #f4f6f9; display: flex; min-height: 100vh; }

    /* ── SIDEBAR ── */
    #sidebar {
      width: 170px;
      min-height: 100vh;
      background: #1a1f2e;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0;
      z-index: 100;
    }

    #sidebar .logo {
      padding: 22px 18px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    #sidebar .logo .brand {
      font-family: 'Raleway', sans-serif;
      font-size: 1.1rem;
      color: #fff;
      line-height: 1;
      letter-spacing: 3px;
      text-transform: uppercase;
    }
    #sidebar .logo .brand .light { font-weight: 300; }
    #sidebar .logo .brand .bold  { font-weight: 700; color: #e74c3c; }
    #sidebar .logo .sub {
      font-size: 0.6rem;
      color: #aaa;
      letter-spacing: 4px;
      text-transform: uppercase;
      margin-top: 4px;
      font-weight: 300;
      font-family: 'Raleway', sans-serif;
    }

    #sidebar nav { flex: 1; padding: 12px 0; }
    #sidebar nav .nav-section {
      font-size: 0.55rem;
      color: #666;
      letter-spacing: 2px;
      text-transform: uppercase;
      font-weight: 700;
      padding: 12px 18px 4px;
    }
    #sidebar nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 18px;
      color: #ccc;
      text-decoration: none;
      font-size: 0.75rem;
      font-weight: 300;
      letter-spacing: 0.5px;
      transition: all 0.2s;
      border-left: 3px solid transparent;
    }
    #sidebar nav a:hover { background: rgba(231,76,60,0.1); color: #fff; border-left-color: #e74c3c; }
    #sidebar nav a.active { background: #e74c3c; color: #fff; border-left-color: #e74c3c; font-weight: 700; }
    #sidebar nav a i { font-size: 0.95rem; }

    #sidebar .front-btn {
      padding: 14px 18px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }
    #sidebar .front-btn a {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #ccc;
      text-decoration: none;
      font-size: 0.72rem;
      font-weight: 300;
      letter-spacing: 0.5px;
    }
    #sidebar .front-btn a:hover { color: #fff; }

    /* ── MAIN CONTENT ── */
    #main-content {
      margin-left: 170px;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ── TOP BAR ── */
    #topbar {
      background: #fff;
      border-bottom: 2px solid #e74c3c;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 99;
    }
    #topbar .page-title {
      font-family: 'Raleway', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      color: #2d2d2d;
      letter-spacing: 3px;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    #topbar .top-actions { display: flex; gap: 8px; }
    #topbar .btn-add {
      background: #e74c3c;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 6px 14px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    #topbar .btn-add:hover { background: #c0392b; color: #fff; }
    #topbar .btn-add-outline {
      background: #fff;
      color: #333;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 6px 14px;
      font-size: 0.75rem;
      font-weight: 300;
      letter-spacing: 0.5px;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    #topbar .btn-add-outline:hover { background: #f5f5f5; color: #333; }

    /* ── PAGE BODY ── */
    .page-body { padding: 24px; flex: 1; }

    /* ── CARDS ── */
    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .stat-card .stat-label { font-size: 0.7rem; color: #999; margin-bottom: 4px; font-weight: 300; letter-spacing: 1px; text-transform: uppercase; }
    .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; color: #2d2d2d; }
    .stat-card .stat-icon {
      width: 42px; height: 42px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
      margin-bottom: 10px;
    }

    .content-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .content-card .card-header-title {
      font-family: 'Raleway', sans-serif;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #2d2d2d;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 16px;
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<div id="sidebar">
  <div class="logo">
    <div class="brand">
      <span class="light">Smart</span><span class="bold">Meal</span>
    </div>
    <div class="sub">Admin</div>
  </div>
  <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

  <!-- Determine active section -->
  <?php
  $shopPages    = ['afficherProduit.php','ajouterProduit.php','modifierProduit.php','supprimerProduit.php','afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php','supprimerCategorie.php','afficherAvis.php','supprimerAvis.php','afficherReclamations.php'];
  $eventPages   = ['listEvenements.php','addEvenement.php','updateEvenement.php','deleteEvenement.php','listParticipations.php','addParticipation.php','updateParticipation.php','searchParticipations.php','listCommentaires.php'];
  $mealPages    = ['afficherMeal.php','ajouterMeal.php','modifierMeal.php'];
  $recipePages  = ['afficherRecette.php','ajouterRecette.php','modifierRecette.php'];

  $activeSection = '';
  if (in_array($currentPage, $shopPages))   $activeSection = 'shop';
  if (in_array($currentPage, $eventPages))  $activeSection = 'events';
  if (in_array($currentPage, $mealPages))   $activeSection = 'meal';
  if (in_array($currentPage, $recipePages)) $activeSection = 'recipes';
  ?>

  <style>
    /* Accordion nav */
    .nav-group { border-bottom: 1px solid rgba(255,255,255,0.05); }
    .nav-group-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 18px;
      color: #ccc;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      cursor: pointer;
      transition: all 0.2s;
      border-left: 3px solid transparent;
      user-select: none;
    }
    .nav-group-header:hover { background: rgba(231,76,60,0.1); color: #fff; border-left-color: #e74c3c; }
    .nav-group-header.open  { color: #e74c3c; border-left-color: #e74c3c; background: rgba(231,76,60,0.08); }
    .nav-group-header .nav-group-left { display: flex; align-items: center; gap: 10px; }
    .nav-group-header i.chevron { font-size: 0.65rem; transition: transform 0.25s; }
    .nav-group-header.open i.chevron { transform: rotate(180deg); }

    .nav-group-body { display: none; background: rgba(0,0,0,0.15); }
    .nav-group-body.open { display: block; }
    .nav-group-body a {
      display: flex; align-items: center; gap: 8px;
      padding: 8px 18px 8px 36px;
      color: #aaa;
      text-decoration: none;
      font-size: 0.72rem;
      font-weight: 300;
      letter-spacing: 0.5px;
      transition: all 0.2s;
      border-left: 3px solid transparent;
    }
    .nav-group-body a:hover { background: rgba(231,76,60,0.1); color: #fff; border-left-color: #e74c3c; }
    .nav-group-body a.active { background: #e74c3c; color: #fff; border-left-color: #e74c3c; font-weight: 700; }
  </style>

  <nav>
    <!-- SHOP -->
    <div class="nav-group">
      <div class="nav-group-header <?= $activeSection === 'shop' ? 'open' : '' ?>" onclick="toggleNav(this)">
        <span class="nav-group-left"><i class="bi bi-shop"></i> Shop</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-group-body <?= $activeSection === 'shop' ? 'open' : '' ?>">
        <a href="afficherProduit.php" class="<?= in_array($currentPage, ['afficherProduit.php','ajouterProduit.php','modifierProduit.php','supprimerProduit.php']) ? 'active' : '' ?>">
          <i class="bi bi-box-seam"></i> Products
        </a>
        <a href="afficherCategorie.php" class="<?= in_array($currentPage, ['afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php','supprimerCategorie.php']) ? 'active' : '' ?>">
          <i class="bi bi-tags"></i> Categories
        </a>
        <a href="afficherAvis.php" class="<?= in_array($currentPage, ['afficherAvis.php','supprimerAvis.php']) ? 'active' : '' ?>">
          <i class="bi bi-chat-square-text"></i> Reviews
        </a>
        <a href="afficherReclamations.php" class="<?= $currentPage === 'afficherReclamations.php' ? 'active' : '' ?>">
          <i class="bi bi-megaphone"></i> Complaints
        </a>
      </div>
    </div>

    <!-- EVENTS -->
    <div class="nav-group">
      <div class="nav-group-header <?= $activeSection === 'events' ? 'open' : '' ?>" onclick="toggleNav(this)">
        <span class="nav-group-left"><i class="bi bi-calendar-event"></i> Events</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
      <div class="nav-group-body <?= $activeSection === 'events' ? 'open' : '' ?>">
        <a href="listEvenements.php" class="<?= in_array($currentPage, ['listEvenements.php','addEvenement.php','updateEvenement.php','deleteEvenement.php']) ? 'active' : '' ?>">
          <i class="bi bi-calendar3"></i> Events
        </a>
        <a href="listParticipations.php" class="<?= in_array($currentPage, ['listParticipations.php','addParticipation.php','updateParticipation.php','searchParticipations.php']) ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Participations
        </a>
        <a href="listCommentaires.php" class="<?= $currentPage === 'listCommentaires.php' ? 'active' : '' ?>">
          <i class="bi bi-chat-dots"></i> Comments
        </a>
        <a href="listPromoCodes.php" class="<?= $currentPage === 'listPromoCodes.php' ? 'active' : '' ?>">
          <i class="bi bi-ticket-perforated"></i> Discount Codes
        </a>
      </div>
    </div>

    <!-- MEAL PLANNING -->
    <div class="nav-group">
      <div class="nav-group-header" style="cursor:default;opacity:0.6;">
        <span class="nav-group-left"><i class="bi bi-journal-text"></i> Meal Planning</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
    </div>

    <!-- RECIPES -->
    <div class="nav-group">
      <div class="nav-group-header" style="cursor:default;opacity:0.6;">
        <span class="nav-group-left"><i class="bi bi-book"></i> Recipes</span>
        <i class="bi bi-chevron-down chevron"></i>
      </div>
    </div>
  </nav>

  <script>
  function toggleNav(header) {
    var body = header.nextElementSibling;
    var isOpen = header.classList.contains('open');
    // Close all
    document.querySelectorAll('.nav-group-header').forEach(function(h) {
      h.classList.remove('open');
      if (h.nextElementSibling) h.nextElementSibling.classList.remove('open');
    });
    // Open clicked if it was closed
    if (!isOpen && body) {
      header.classList.add('open');
      body.classList.add('open');
    }
  }
  </script>
  <div class="front-btn">
    <a href="../index.php">
      <i class="bi bi-arrow-left-circle"></i> Front Office
    </a>
  </div>
</div>

<!-- MAIN CONTENT -->
<div id="main-content">

<!-- TOP BAR -->
<div id="topbar">
  <div class="page-title">
    <i class="bi bi-speedometer2 text-danger"></i>
    <?php
    $titles = [
      'afficherProduit.php'   => 'Products',
      'ajouterProduit.php'    => 'Add Product',
      'modifierProduit.php'   => 'Edit Product',
      'supprimerProduit.php'  => 'Delete Product',
      'afficherCategorie.php' => 'Categories',
      'ajouterCategorie.php'  => 'Add Category',
      'modifierCategorie.php' => 'Edit Category',
      'supprimerCategorie.php'=> 'Delete Category',
      'afficherReclamations.php' => 'Complaints',
      'afficherAvis.php'         => 'Reviews',
      'supprimerAvis.php'        => 'Delete Review',
    ];
    echo $titles[$currentPage] ?? 'Dashboard';
    ?>
  </div>
  <div class="top-actions">
    <?php if (in_array($currentPage, ['afficherProduit.php','ajouterProduit.php','modifierProduit.php'])): ?>
      <a href="ajouterProduit.php" class="btn-add"><i class="bi bi-plus-lg"></i> New Product</a>
    <?php endif; ?>
    <?php if (in_array($currentPage, ['afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php'])): ?>
      <a href="ajouterCategorie.php" class="btn-add"><i class="bi bi-plus-lg"></i> New Category</a>
    <?php endif; ?>
  </div>
</div>

<div class="page-body">
