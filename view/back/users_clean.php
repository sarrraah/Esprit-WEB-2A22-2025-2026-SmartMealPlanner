<?php
/**
 * Users Management View - Clean MVC Implementation
 * This view only handles presentation - all business logic is in UserController
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle "Remember Me" functionality
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $rememberController = new UserController();
    $rememberUser = $rememberController->getUserByRememberToken($_COOKIE['remember_token']);

    if ($rememberUser) {
        $_SESSION['user_id'] = $rememberUser['id'];
        $_SESSION['nom'] = $rememberUser['nom'];
        $_SESSION['prenom'] = $rememberUser['prenom'];
        $_SESSION['email'] = $rememberUser['email'];
        $_SESSION['user_role'] = $rememberUser['role'];
        $_SESSION['role'] = $rememberUser['role'];
        $_SESSION['statut'] = $rememberUser['statut'];
    } else {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}

require_once 'admin_auth.php';

// Initialize controller
$controller = new UserController();

// Get filters from request
$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'sort' => trim($_GET['sort'] ?? ''),
    'status' => isset($_GET['filter']) && $_GET['filter'] === 'pending' ? 'pending' : ''
];

// Get data from controller (business logic handled there)
$users = $controller->getFilteredUsers($filters);
$stats = $controller->getUserStatistics();

// Extract statistics for easy access in view
$totalUsers = $stats['total'];
$tableUsersCount = count($users);
$clientCount = $stats['roles']['client'];
$coachCount = $stats['roles']['coach'];
$nutritionistCount = $stats['roles']['nutritionist'];
$adminCount = $stats['roles']['admin'];
$activeCount = $stats['statuses']['active'];
$pendingCount = $stats['statuses']['pending'];
$bannedCount = $stats['statuses']['banned'];
$deactivatedCount = $stats['statuses']['deactivated'];
$maleCount = $stats['genders']['male'];
$femaleCount = $stats['genders']['female'];
$otherGenderCount = $stats['genders']['other'];
?>
<?php
$pageTitle = 'Users Management - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
  <div class="admin-topbar">
    <h5><i class="bi bi-people me-2" style="color:var(--accent)"></i>Users Management</h5>
  </div>
  <div class="admin-content">

        <div class="topbar">
            <div>
                <h1 data-i18n="pageTitle">Users Management</h1>
                <p data-i18n="pageSubtitle">
                    Manage accounts, review professional requests, and monitor user activity through a clean and modern admin workspace.
                </p>
            </div>

            <div class="topbar-actions">
                <div class="mini-tools">
                    <button type="button" id="themeToggle" class="mini-btn" title="Dark / Light">
                        <i class="bi bi-moon-stars"></i>
                    </button>

                    <button type="button" id="langEnBtn" class="mini-btn active">
                        EN
                    </button>

                    <button type="button" id="langFrBtn" class="mini-btn">
                        FR
                    </button>
                </div>

                <a href="pending_requests.php" class="top-btn">
                    <span data-i18n="pendingRequests">Pending Requests</span>
                    <span class="pending-count"><?= $pendingCount ?></span>
                </a>

                <a href="add_user.php" class="top-btn" data-i18n="addUser">
                    + Add User
                </a>
            </div>
        </div>

        <div class="analytics-panel">

            <div class="analytics-card total-card">
                <div>
                    <div class="total-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <span data-i18n="totalUsers">Total Users</span>
                    <strong><?= $totalUsers ?></strong>
                </div>

                <p data-i18n="totalUsersDesc">
                    Global count of all registered accounts currently stored in the platform database.
                </p>
            </div>

            <div class="analytics-card chart-card">
                <h3 data-i18n="roleDistribution">Role Distribution</h3>
                <p data-i18n="roleDistributionDesc">Clients, coaches, nutritionists, and admins.</p>
                <div class="chart-box">
                    <canvas id="roleChart"></canvas>
                </div>
            </div>

            <div class="analytics-card chart-card">
                <h3 data-i18n="statusOverview">Status Overview</h3>
                <p data-i18n="statusOverviewDesc">Active, pending, banned, and deactivated users.</p>
                <div class="chart-box">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="analytics-card chart-card">
                <h3 data-i18n="genderDistribution">Gender Distribution</h3>
                <p data-i18n="genderDistributionDesc">Registered user gender repartition.</p>
                <div class="chart-box">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

        </div>

        <div class="main-card">
            <div class="card-head">
                <div>
                    <h2 data-i18n="userDirectory">User Directory</h2>
                    <p data-i18n="userDirectoryDesc">View all existing accounts, their role, status, and profile details.</p>
                </div>

                <div class="users-count-pill">
                    <?= $tableUsersCount ?> <span data-i18n="usersCount">user(s)</span>
                </div>
            </div>

            <div class="toolbar">
                <div class="toolbar-form">
                    <div class="toolbar-group">
                        <label for="search" data-i18n="search">Search</label>
                        <input
                            type="text"
                            id="search"
                            class="toolbar-input"
                            data-placeholder-en="Search by ID, name, email, role, status, or gender"
                            data-placeholder-fr="Rechercher par ID, nom, email, rôle, statut ou genre"
                            placeholder="Search by ID, name, email, role, status, or gender"
                            value="<?= htmlspecialchars($filters['search']) ?>">
                    </div>

                    <div class="toolbar-group">
                        <label for="sort" data-i18n="sortBy">Sort By</label>
                        <select id="sort" class="toolbar-select">
                            <option value="" <?= $filters['sort'] === '' ? 'selected' : '' ?> data-i18n="defaultOrder">Default Order</option>
                            <option value="id_asc" <?= $filters['sort'] === 'id_asc' ? 'selected' : '' ?> data-i18n="idLowHigh">ID: Low to High</option>
                            <option value="id_desc" <?= $filters['sort'] === 'id_desc' ? 'selected' : '' ?> data-i18n="idHighLow">ID: High to Low</option>
                            <option value="name_asc" <?= $filters['sort'] === 'name_asc' ? 'selected' : '' ?> data-i18n="nameAZ">Name: A to Z</option>
                            <option value="name_desc" <?= $filters['sort'] === 'name_desc' ? 'selected' : '' ?> data-i18n="nameZA">Name: Z to A</option>
                            <option value="role_asc" <?= $filters['sort'] === 'role_asc' ? 'selected' : '' ?> data-i18n="roleAZ">Role: A to Z</option>
                            <option value="status_asc" <?= $filters['sort'] === 'status_asc' ? 'selected' : '' ?> data-i18n="statusAZ">Status: A to Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-scroll-area">
                <div class="table-wrap" id="usersTableWrap">
                    <table>
                        <thead>
                            <tr>
                                <th data-i18n="id">ID</th>
                                <th data-i18n="lastName">Last Name</th>
                                <th data-i18n="firstName">First Name</th>
                                <th data-i18n="dob">Date of Birth</th>
                                <th data-i18n="email">Email</th>
                                <th data-i18n="role">Role</th>
                                <th data-i18n="status">Status</th>
                                <th data-i18n="gender">Gender</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $index => $user): ?>
                                    <?php
                                    // Presentation logic only - formatting for display
                                    $role = strtolower(trim((string)($user['role'] ?? '')));
                                    $status = strtolower(trim((string)($user['statut'] ?? '')));
                                    $genderRaw = trim((string)($user['sexe'] ?? ''));

                                    $roleClass = match($role) {
                                        'client' => 'badge-role-client',
                                        'coach' => 'badge-role-coach',
                                        'nutritionist' => 'badge-role-nutritionist',
                                        'admin' => 'badge-role-admin',
                                        default => 'badge-role-default'
                                    };

                                    $statusClass = match($status) {
                                        'active' => 'badge-status-active',
                                        'banned' => 'badge-status-banned',
                                        'deactivated' => 'badge-status-deactivated',
                                        'pending' => 'badge-status-pending',
                                        default => 'badge-status-default'
                                    };

                                    $normalizedGender = strtolower(trim($genderRaw));
                                    $genderDisplay = match($normalizedGender) {
                                        'female' => 'Female',
                                        'male' => 'Male',
                                        default => $genderRaw !== '' ? $genderRaw : '—'
                                    };
                                    ?>

                                    <tr
                                        class="user-row"
                                        data-index="<?= $index ?>"
                                        data-id="<?= htmlspecialchars((string)$user['id']) ?>"
                                        data-name="<?= htmlspecialchars(strtolower(trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? '')))) ?>"
                                        data-email="<?= htmlspecialchars(strtolower((string)($user['email'] ?? ''))) ?>"
                                        data-role="<?= htmlspecialchars(strtolower((string)($user['role'] ?? ''))) ?>"
                                        data-status="<?= htmlspecialchars(strtolower((string)($user['statut'] ?? ''))) ?>"
                                        data-gender="<?= htmlspecialchars(strtolower($genderDisplay)) ?>">

                                        <td>
                                            <span class="id-pill">
                                                #<?= htmlspecialchars((string)($user['id'] ?? '')) ?>
                                            </span>
                                        </td>

                                        <td class="name-cell" title="<?= htmlspecialchars((string)($user['nom'] ?? '—')) ?>">
                                            <?= htmlspecialchars((string)($user['nom'] ?? '—')) ?>
                                        </td>

                                        <td class="name-cell" title="<?= htmlspecialchars((string)($user['prenom'] ?? '—')) ?>">
                                            <?= htmlspecialchars((string)($user['prenom'] ?? '—')) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars((string)($user['date_naissance'] ?? '—')) ?>
                                        </td>

                                        <td class="email-cell" title="<?= htmlspecialchars((string)($user['email'] ?? '—')) ?>">
                                            <?= htmlspecialchars((string)($user['email'] ?? '—')) ?>
                                        </td>

                                        <td>
                                            <span class="badge <?= $roleClass ?> dynamic-role" data-value="<?= htmlspecialchars($role) ?>">
                                                <?= htmlspecialchars($role !== '' ? ucfirst($role) : '—') ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if ($status === 'pending'): ?>
                                                <a href="review_user_request.php?id=<?= urlencode((string)$user['id']) ?>" class="badge <?= $statusClass ?> status-link dynamic-status" data-value="<?= htmlspecialchars($status) ?>">
                                                    <?= htmlspecialchars(ucfirst($status)) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge <?= $statusClass ?> dynamic-status" data-value="<?= htmlspecialchars($status) ?>">
                                                    <?= htmlspecialchars($status !== '' ? ucfirst($status) : '—') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td style="position: relative; padding-right: 44px;">
                                            <span class="gender-pill dynamic-gender" data-value="<?= htmlspecialchars($normalizedGender) ?>">
                                                <?= htmlspecialchars($genderDisplay) ?>
                                            </span>

                                            <button
                                                type="button"
                                                class="row-delete-btn"
                                                data-id="<?= htmlspecialchars((string)$user['id']) ?>"
                                                data-name="<?= htmlspecialchars((string)($user['nom'] ?? '') . ' ' . (string)($user['prenom'] ?? '')) ?>">
                                                <i class="bi bi-dash-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state" data-i18n="noUsers">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-arrows">
                    <button type="button" class="table-arrow-btn" id="scrollLeftBtn" title="Previous users">
                        <i class="bi bi-arrow-left"></i>
                    </button>

                    <button type="button" class="table-arrow-btn" id="scrollRightBtn" title="Next users">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal-backdrop">
        <div class="modal-card">
            <h3 data-i18n="deleteModalTitle">Delete User</h3>
            <p id="deleteModalText">Are you sure you want to delete this user?</p>

            <form method="POST" action="delete_user.php">
                <input type="hidden" name="id" id="modalDeleteUserId">

                <div class="modal-actions">
                    <button type="button" class="modal-btn-secondary" id="cancelDeleteBtn" data-i18n="no">
                        No
                    </button>

                    <button type="submit" class="modal-btn-danger" data-i18n="yesDelete">
                        Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Include JavaScript for client-side interactions -->
    <script src="partials/users.js"></script>
    <script>
        // Pass PHP data to JavaScript
        const userData = {
            clientCount: <?= $clientCount ?>,
            coachCount: <?= $coachCount ?>,
            nutritionistCount: <?= $nutritionistCount ?>,
            adminCount: <?= $adminCount ?>,
            activeCount: <?= $activeCount ?>,
            pendingCount: <?= $pendingCount ?>,
            bannedCount: <?= $bannedCount ?>,
            deactivatedCount: <?= $deactivatedCount ?>,
            maleCount: <?= $maleCount ?>,
            femaleCount: <?= $femaleCount ?>,
            otherGenderCount: <?= $otherGenderCount ?>
        };
    </script>
</div>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
