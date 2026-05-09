<?php
$_sbCurrentPage = basename($_SERVER['PHP_SELF']);
$activePage = $activePage ?? '';

$_sbShopPages   = ['afficherProduit.php','ajouterProduit.php','modifierProduit.php','supprimerProduit.php','afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php','supprimerCategorie.php','afficherAvis.php','supprimerAvis.php','afficherReclamations.php'];
$_sbEventPages  = ['listEvenements.php','addEvenement.php','updateEvenement.php','deleteEvenement.php','listParticipations.php','addParticipation.php','updateParticipation.php','searchParticipations.php','listCommentaires.php','listPromoCodes.php'];
$_sbMealPages   = ['meal_bo_index.php','meals_admin.php','plans_admin.php'];
$_sbRecipePages = ['index.php','repas.php','search_repas.php','recette.php','statistiques.php','utilisateurs.php','aliments_durables.php','contenu_nutritionnel.php','add_repas.php','edit_repas.php','add_recette.php','edit_recette.php','view_recette.php','ingredients.php'];

$_sbSection = '';
if (in_array($_sbCurrentPage, $_sbShopPages))   $_sbSection = 'shop';
if (in_array($_sbCurrentPage, $_sbEventPages))  $_sbSection = 'events';
if (in_array($_sbCurrentPage, $_sbMealPages))   $_sbSection = 'meal';
if (in_array($_sbCurrentPage, $_sbRecipePages)) $_sbSection = 'recipes';
?>
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  /* Force unified sidebar */
  .admin-shell { display: flex !important; min-height: 100vh !important; }
  .bo-layout   { display: flex !important; min-height: 100vh !important; }
  .main-area, .bo-main-content { flex: 1 !important; overflow-y: auto !important; }

  #unified-sidebar {
    width: 170px !important;
    min-width: 170px !important;
    min-height: 100vh !important;
    height: 100vh !important;
    background: #1a1f2e !important;
    display: flex !important;
    flex-direction: column !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
    flex-shrink: 0 !important;
    overflow-y: auto !important;
    font-family: 'Raleway', sans-serif !important;
  }
  #unified-sidebar * { font-family: 'Raleway', sans-serif !important; box-sizing: border-box !important; }

  #unified-sidebar .usb-logo {
    padding: 22px 18px 16px !important;
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
  }
  #unified-sidebar .usb-brand {
    font-size: 1.1rem !important; color: #fff !important; line-height: 1 !important;
    letter-spacing: 3px !important; text-transform: uppercase !important;
  }
  #unified-sidebar .usb-light { font-weight: 300 !important; }
  #unified-sidebar .usb-bold  { font-weight: 700 !important; color: #e74c3c !important; }
  #unified-sidebar .usb-sub {
    font-size: 0.6rem !important; color: #aaa !important; letter-spacing: 4px !important;
    text-transform: uppercase !important; margin-top: 4px !important; font-weight: 300 !important;
    display: block !important;
  }

  #unified-sidebar .usb-nav { flex: 1 !important; padding: 12px 0 !important; }
  #unified-sidebar .usb-group { border-bottom: 1px solid rgba(255,255,255,0.05) !important; }

  #unified-sidebar .usb-header {
    display: flex !important; align-items: center !important; justify-content: space-between !important;
    padding: 10px 18px !important;
    color: #ccc !important;
    font-size: 0.72rem !important; font-weight: 700 !important;
    letter-spacing: 1.5px !important; text-transform: uppercase !important;
    cursor: pointer !important;
    border-left: 3px solid transparent !important;
    transition: all 0.2s !important;
    user-select: none !important;
    background: transparent !important;
    text-decoration: none !important;
  }
  #unified-sidebar .usb-header:hover { background: rgba(231,76,60,0.1) !important; color: #fff !important; border-left-color: #e74c3c !important; }
  #unified-sidebar .usb-header.open  { color: #e74c3c !important; border-left-color: #e74c3c !important; background: rgba(231,76,60,0.08) !important; }
  #unified-sidebar .usb-left { display: flex !important; align-items: center !important; gap: 10px !important; }
  #unified-sidebar .usb-chevron { font-size: 0.65rem !important; transition: transform 0.25s !important; display: inline-block !important; }
  #unified-sidebar .usb-header.open .usb-chevron { transform: rotate(180deg) !important; }

  #unified-sidebar .usb-body { display: none !important; background: rgba(0,0,0,0.15) !important; }
  #unified-sidebar .usb-body.open { display: block !important; }
  #unified-sidebar .usb-body a {
    display: flex !important; align-items: center !important; gap: 8px !important;
    padding: 8px 18px 8px 36px !important;
    color: #aaa !important; text-decoration: none !important;
    font-size: 0.72rem !important; font-weight: 300 !important;
    letter-spacing: 0.5px !important;
    transition: all 0.2s !important;
    border-left: 3px solid transparent !important;
    background: transparent !important;
  }
  #unified-sidebar .usb-body a:hover { background: rgba(231,76,60,0.1) !important; color: #fff !important; border-left-color: #e74c3c !important; }
  #unified-sidebar .usb-body a.active { background: #e74c3c !important; color: #fff !important; border-left-color: #e74c3c !important; font-weight: 700 !important; }

  #unified-sidebar .usb-front {
    padding: 14px 18px !important;
    border-top: 1px solid rgba(255,255,255,0.08) !important;
  }
  #unified-sidebar .usb-front a {
    display: flex !important; align-items: center !important; gap: 8px !important;
    color: #ccc !important; text-decoration: none !important;
    font-size: 0.72rem !important; font-weight: 300 !important;
    background: transparent !important;
  }
  #unified-sidebar .usb-front a:hover { color: #fff !important; }

  /* Override bo-sidebar styles from backoffice-meals.css */
  .bo-sidebar { display: none !important; }
</style>

<div id="unified-sidebar">
  <div class="usb-logo">
    <div class="usb-brand"><span class="usb-light">Smart</span><span class="usb-bold">Meal</span></div>
    <span class="usb-sub">Admin</span>
  </div>

  <div class="usb-nav">

    <!-- SHOP -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'shop' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-shop"></i> Shop</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'shop' ? 'open' : '' ?>">
        <a href="afficherProduit.php" class="<?= in_array($_sbCurrentPage, ['afficherProduit.php','ajouterProduit.php','modifierProduit.php','supprimerProduit.php']) ? 'active' : '' ?>">
          <i class="bi bi-box-seam"></i> Products
        </a>
        <a href="afficherCategorie.php" class="<?= in_array($_sbCurrentPage, ['afficherCategorie.php','ajouterCategorie.php','modifierCategorie.php','supprimerCategorie.php']) ? 'active' : '' ?>">
          <i class="bi bi-tags"></i> Categories
        </a>
        <a href="afficherAvis.php" class="<?= in_array($_sbCurrentPage, ['afficherAvis.php','supprimerAvis.php']) ? 'active' : '' ?>">
          <i class="bi bi-chat-square-text"></i> Reviews
        </a>
        <a href="afficherReclamations.php" class="<?= $_sbCurrentPage === 'afficherReclamations.php' ? 'active' : '' ?>">
          <i class="bi bi-megaphone"></i> Complaints
        </a>
      </div>
    </div>

    <!-- EVENTS -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'events' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-calendar-event"></i> Events</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'events' ? 'open' : '' ?>">
        <a href="listEvenements.php" class="<?= in_array($_sbCurrentPage, ['listEvenements.php','addEvenement.php','updateEvenement.php','deleteEvenement.php']) ? 'active' : '' ?>">
          <i class="bi bi-calendar3"></i> Events
        </a>
        <a href="listParticipations.php" class="<?= in_array($_sbCurrentPage, ['listParticipations.php','addParticipation.php','updateParticipation.php','searchParticipations.php']) ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Participations
        </a>
        <a href="listCommentaires.php" class="<?= $_sbCurrentPage === 'listCommentaires.php' ? 'active' : '' ?>">
          <i class="bi bi-chat-dots"></i> Comments
        </a>
        <a href="listPromoCodes.php" class="<?= $_sbCurrentPage === 'listPromoCodes.php' ? 'active' : '' ?>">
          <i class="bi bi-ticket-perforated"></i> Discount Codes
        </a>
      </div>
    </div>

    <!-- MEAL PLANNING -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'meal' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-journal-text"></i> Meal Planning</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'meal' ? 'open' : '' ?>">
        <a href="meal_bo_index.php" class="<?= $_sbCurrentPage === 'meal_bo_index.php' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="meals_admin.php" class="<?= $_sbCurrentPage === 'meals_admin.php' ? 'active' : '' ?>">
          <i class="bi bi-egg-fried"></i> Meals
        </a>
        <a href="plans_admin.php" class="<?= $_sbCurrentPage === 'plans_admin.php' ? 'active' : '' ?>">
          <i class="bi bi-calendar-check"></i> My Plans
        </a>
      </div>
    </div>

    <!-- RECIPES -->
    <div class="usb-group">
      <div class="usb-header <?= $_sbSection === 'recipes' ? 'open' : '' ?>" onclick="usbToggle(this)">
        <span class="usb-left"><i class="bi bi-book"></i> Recipes</span>
        <i class="bi bi-chevron-down usb-chevron"></i>
      </div>
      <div class="usb-body <?= $_sbSection === 'recipes' ? 'open' : '' ?>">
        <a href="index.php" class="<?= $_sbCurrentPage === 'index.php' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="repas.php" class="<?= in_array($_sbCurrentPage, ['repas.php','add_repas.php','edit_repas.php']) ? 'active' : '' ?>">
          <i class="bi bi-bowl-hot"></i> Gestion des Repas
        </a>
        <a href="search_repas.php" class="<?= $_sbCurrentPage === 'search_repas.php' ? 'active' : '' ?>">
          <i class="bi bi-search"></i> Repas par Recette
        </a>
        <a href="recette.php" class="<?= in_array($_sbCurrentPage, ['recette.php','add_recette.php','edit_recette.php','view_recette.php']) ? 'active' : '' ?>">
          <i class="bi bi-journal-richtext"></i> Gestion des Recettes
        </a>
        <a href="statistiques.php" class="<?= $_sbCurrentPage === 'statistiques.php' ? 'active' : '' ?>">
          <i class="bi bi-bar-chart-line"></i> Statistiques
        </a>
        <a href="utilisateurs.php" class="<?= $_sbCurrentPage === 'utilisateurs.php' ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Utilisateurs
        </a>
        <a href="aliments_durables.php" class="<?= $_sbCurrentPage === 'aliments_durables.php' ? 'active' : '' ?>">
          <i class="bi bi-leaf"></i> Aliments Durables
        </a>
        <a href="contenu_nutritionnel.php" class="<?= $_sbCurrentPage === 'contenu_nutritionnel.php' ? 'active' : '' ?>">
          <i class="bi bi-heart-pulse"></i> Contenu Nutritionnel
        </a>
      </div>
    </div>

  </div>

  <div class="usb-front">
    <a href="../index.php">
      <i class="bi bi-arrow-left-circle"></i> Front Office
    </a>
  </div>
</div>

<script>
function usbToggle(header) {
  var body = header.nextElementSibling;
  var isOpen = header.classList.contains('open');
  document.querySelectorAll('#unified-sidebar .usb-header').forEach(function(h) {
    h.classList.remove('open');
    if (h.nextElementSibling) h.nextElementSibling.classList.remove('open');
  });
  if (!isOpen && body) {
    header.classList.add('open');
    body.classList.add('open');
  }
}
</script>
