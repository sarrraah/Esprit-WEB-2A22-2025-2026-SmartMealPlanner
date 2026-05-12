<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$controller = new UserController();

$allUsers = $controller->index();
$users = $allUsers;

if (isset($_GET['filter']) && $_GET['filter'] === 'pending') {
    $users = array_filter($users, function ($u) {
        return strtolower(trim($u['statut'] ?? '')) === 'pending';
    });
}

$search = trim($_GET['search'] ?? '');
$sort = trim($_GET['sort'] ?? '');

if ($search !== '') {
    $users = array_filter($users, function ($u) use ($search) {
        $searchLower = strtolower($search);

        $fullName = strtolower(trim(($u['nom'] ?? '') . ' ' . ($u['prenom'] ?? '')));
        $email = strtolower((string)($u['email'] ?? ''));
        $role = strtolower((string)($u['role'] ?? ''));
        $status = strtolower((string)($u['statut'] ?? ''));
        $gender = strtolower((string)($u['sexe'] ?? ''));
        $id = strtolower((string)($u['id'] ?? ''));

        return strpos($fullName, $searchLower) !== false
            || strpos($email, $searchLower) !== false
            || strpos($role, $searchLower) !== false
            || strpos($status, $searchLower) !== false
            || strpos($gender, $searchLower) !== false
            || strpos($id, $searchLower) !== false;
    });
}

if ($sort !== '') {
    usort($users, function ($a, $b) use ($sort) {
        switch ($sort) {
            case 'name_asc':
                $valueA = strtolower(trim(($a['nom'] ?? '') . ' ' . ($a['prenom'] ?? '')));
                $valueB = strtolower(trim(($b['nom'] ?? '') . ' ' . ($b['prenom'] ?? '')));
                return strcmp($valueA, $valueB);

            case 'name_desc':
                $valueA = strtolower(trim(($a['nom'] ?? '') . ' ' . ($a['prenom'] ?? '')));
                $valueB = strtolower(trim(($b['nom'] ?? '') . ' ' . ($b['prenom'] ?? '')));
                return strcmp($valueB, $valueA);

            case 'id_asc':
                return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);

            case 'id_desc':
                return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);

            case 'role_asc':
                $valueA = strtolower(trim((string)($a['role'] ?? '')));
                $valueB = strtolower(trim((string)($b['role'] ?? '')));
                return strcmp($valueA, $valueB);

            case 'status_asc':
                $valueA = strtolower(trim((string)($a['statut'] ?? '')));
                $valueB = strtolower(trim((string)($b['statut'] ?? '')));
                return strcmp($valueA, $valueB);

            default:
                return 0;
        }
    });
}

$totalUsers = count($allUsers);
$tableUsersCount = count($users);

$clientCount = 0;
$coachCount = 0;
$nutritionistCount = 0;
$adminCount = 0;

$activeCount = 0;
$pendingCount = 0;
$bannedCount = 0;
$deactivatedCount = 0;

$maleCount = 0;
$femaleCount = 0;
$otherGenderCount = 0;

foreach ($allUsers as $u) {
    $role = strtolower(trim($u['role'] ?? ''));
    $status = strtolower(trim($u['statut'] ?? ''));
    $gender = strtolower(trim($u['sexe'] ?? ''));

    if ($role === 'client') {
        $clientCount++;
    } elseif ($role === 'coach') {
        $coachCount++;
    } elseif ($role === 'nutritionist') {
        $nutritionistCount++;
    } elseif ($role === 'admin') {
        $adminCount++;
    }

    if ($status === 'active') {
        $activeCount++;
    } elseif ($status === 'pending') {
        $pendingCount++;
    } elseif ($status === 'banned') {
        $bannedCount++;
    } elseif ($status === 'deactivated') {
        $deactivatedCount++;
    }

    if ($gender === 'male') {
        $maleCount++;
    } elseif ($gender === 'female') {
        $femaleCount++;
    } else {
        $otherGenderCount++;
    }
}
?>
<?php
$pageTitle = 'Users Management - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  .admin-content { padding: 24px; background: #f4f6fb; min-height: calc(100vh - 60px); }

  /* Topbar */
  .topbar { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; margin-bottom: 24px; flex-wrap: wrap; }
  .topbar h1 { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 6px; }
  .topbar p  { font-size: 14px; color: #6b7280; line-height: 1.6; max-width: 600px; }
  .topbar-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

  .mini-tools { display: flex; gap: 6px; }
  .mini-btn {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 7px 11px; font-size: 13px; font-weight: 600; cursor: pointer;
    color: #374151; transition: .2s;
  }
  .mini-btn:hover, .mini-btn.active { background: #ce1212; color: #fff; border-color: #ce1212; }

  .top-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: #ce1212; color: #fff; border-radius: 10px;
    padding: 9px 16px; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: .2s; white-space: nowrap;
  }
  .top-btn:hover { background: #b51010; color: #fff; }
  .pending-count {
    background: #fff; color: #ce1212; border-radius: 999px;
    padding: 2px 8px; font-size: 12px; font-weight: 700;
  }

  /* Analytics */
  .analytics-panel { display: grid; grid-template-columns: 1fr repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
  .analytics-card {
    background: #fff; border-radius: 18px; padding: 20px;
    border: 1px solid #e5e7eb; box-shadow: 0 4px 14px rgba(0,0,0,.04);
  }
  .total-card { display: flex; flex-direction: column; justify-content: space-between; }
  .total-card > div { display: flex; flex-direction: column; gap: 8px; }
  .total-icon {
    width: 48px; height: 48px; border-radius: 14px;
    background: rgba(206,18,18,.1); color: #ce1212;
    display: flex; align-items: center; justify-content: center; font-size: 22px;
  }
  .total-card span { font-size: 13px; color: #6b7280; font-weight: 600; }
  .total-card strong { font-size: 42px; font-weight: 800; color: #111827; line-height: 1; }
  .total-card p { font-size: 12px; color: #9ca3af; line-height: 1.6; margin-top: 12px; }
  .chart-card h3 { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 4px; }
  .chart-card p  { font-size: 12px; color: #6b7280; margin-bottom: 12px; }
  .chart-box { height: 160px; position: relative; }

  /* Main card */
  .main-card {
    background: #fff; border-radius: 18px; padding: 22px;
    border: 1px solid #e5e7eb; box-shadow: 0 4px 14px rgba(0,0,0,.04);
  }
  .card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
  .card-head h2 { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 4px; }
  .card-head p  { font-size: 13px; color: #6b7280; }
  .users-count-pill {
    background: #fef2f2; color: #ce1212; border: 1px solid #fecaca;
    border-radius: 999px; padding: 6px 14px; font-size: 13px; font-weight: 700; white-space: nowrap;
  }

  /* Toolbar */
  .toolbar { margin-bottom: 18px; }
  .toolbar-form { display: flex; gap: 14px; flex-wrap: wrap; }
  .toolbar-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 200px; }
  .toolbar-group label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
  .toolbar-input, .toolbar-select {
    border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px;
    font-size: 14px; color: #111827; outline: none; transition: .2s; background: #fff;
  }
  .toolbar-input:focus, .toolbar-select:focus { border-color: #ce1212; box-shadow: 0 0 0 3px rgba(206,18,18,.1); }

  /* Table */
  .table-scroll-area { overflow: hidden; }
  .table-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; min-width: 700px; }
  thead tr { background: #f9fafb; border-bottom: 2px solid #e5e7eb; }
  th { padding: 12px 14px; font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; text-align: left; white-space: nowrap; }
  td { padding: 13px 14px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  .user-row { cursor: pointer; transition: background .15s; }
  .user-row:hover { background: #fef9f9; }
  .name-cell { max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .email-cell { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .id-pill { background: #f3f4f6; color: #374151; border-radius: 6px; padding: 3px 8px; font-size: 12px; font-weight: 700; }
  .empty-state { text-align: center; padding: 30px; color: #9ca3af; font-style: italic; }

  /* Badges */
  .badge { display: inline-block; padding: 5px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: capitalize; }
  .badge-role-client       { background: #eff6ff; color: #1d4ed8; }
  .badge-role-coach        { background: #ecfdf5; color: #15803d; }
  .badge-role-nutritionist { background: #f5f3ff; color: #7c3aed; }
  .badge-role-admin        { background: #fef2f2; color: #ce1212; }
  .badge-role-default      { background: #f3f4f6; color: #374151; }
  .badge-status-active      { background: #ecfdf5; color: #15803d; }
  .badge-status-banned      { background: #fef2f2; color: #ce1212; }
  .badge-status-deactivated { background: #f3f4f6; color: #6b7280; }
  .badge-status-pending     { background: #fffbeb; color: #b45309; }
  .badge-status-default     { background: #f3f4f6; color: #374151; }
  .status-link { text-decoration: none; cursor: pointer; }
  .status-link:hover { opacity: .8; }
  .gender-pill { font-size: 13px; color: #374151; }

  /* Delete button */
  .row-delete-btn {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: #dc2626; cursor: pointer;
    font-size: 16px; padding: 4px; border-radius: 6px; transition: .2s;
    opacity: 0;
  }
  .user-row:hover .row-delete-btn { opacity: 1; }

  /* Pagination arrows */
  .table-arrows { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; }
  .table-arrow-btn {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 7px 14px; cursor: pointer; font-size: 14px; color: #374151; transition: .2s;
  }
  .table-arrow-btn:hover:not(:disabled) { background: #ce1212; color: #fff; border-color: #ce1212; }
  .table-arrow-btn:disabled { opacity: .4; cursor: not-allowed; }

  /* Modal */
  .modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 9999;
    align-items: center; justify-content: center;
  }
  .modal-backdrop.show { display: flex; }
  .modal-card {
    background: #fff; border-radius: 18px; padding: 28px 24px;
    max-width: 420px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,.15);
  }
  .modal-card h3 { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 10px; }
  .modal-card p  { font-size: 14px; color: #6b7280; margin-bottom: 22px; }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
  .modal-btn-secondary {
    background: #f3f4f6; color: #374151; border: none; border-radius: 10px;
    padding: 10px 20px; font-weight: 600; cursor: pointer; transition: .2s;
  }
  .modal-btn-secondary:hover { background: #e5e7eb; }
  .modal-btn-danger {
    background: #ce1212; color: #fff; border: none; border-radius: 10px;
    padding: 10px 20px; font-weight: 600; cursor: pointer; transition: .2s;
  }
  .modal-btn-danger:hover { background: #b51010; }

  /* Dark mode */
  body.dark-mode { background: #0f172a; color: #e5e7eb; }
  body.dark-mode .admin-content { background: #0f172a; }
  body.dark-mode .analytics-card,
  body.dark-mode .main-card { background: #1e293b; border-color: #334155; }
  body.dark-mode th { background: #1e293b; color: #94a3b8; }
  body.dark-mode td { color: #cbd5e1; border-color: #1e293b; }
  body.dark-mode .user-row:hover { background: #1e293b; }
  body.dark-mode .toolbar-input,
  body.dark-mode .toolbar-select { background: #1e293b; border-color: #334155; color: #e5e7eb; }
  body.dark-mode .modal-card { background: #1e293b; }
  body.dark-mode .modal-card h3 { color: #f1f5f9; }
  body.dark-mode .topbar h1 { color: #f1f5f9; }
  body.dark-mode .total-card strong { color: #f1f5f9; }
  body.dark-mode .id-pill { background: #334155; color: #cbd5e1; }
  body.dark-mode .mini-btn { background: #1e293b; border-color: #334155; color: #cbd5e1; }
  body.dark-mode .table-arrow-btn { background: #1e293b; border-color: #334155; color: #cbd5e1; }

  @media (max-width: 1100px) { .analytics-panel { grid-template-columns: 1fr 1fr; } }
  @media (max-width: 700px)  { .analytics-panel { grid-template-columns: 1fr; } .topbar { flex-direction: column; } }
</style>

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
                            placeholder="Search by ID, name, email, role, status, or gender">
                    </div>

                    <div class="toolbar-group">
                        <label for="sort" data-i18n="sortBy">Sort By</label>
                        <select id="sort" class="toolbar-select">
                            <option value="" data-i18n="defaultOrder">Default Order</option>
                            <option value="id_asc" data-i18n="idLowHigh">ID: Low to High</option>
                            <option value="id_desc" data-i18n="idHighLow">ID: High to Low</option>
                            <option value="name_asc" data-i18n="nameAZ">Name: A to Z</option>
                            <option value="name_desc" data-i18n="nameZA">Name: Z to A</option>
                            <option value="role_asc" data-i18n="roleAZ">Role: A to Z</option>
                            <option value="status_asc" data-i18n="statusAZ">Status: A to Z</option>
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
                                    $role = strtolower(trim((string)($user['role'] ?? '')));
                                    $status = strtolower(trim((string)($user['statut'] ?? '')));
                                    $genderRaw = trim((string)($user['sexe'] ?? ''));

                                    $roleClass = 'badge-role-default';

                                    if ($role === 'client') {
                                        $roleClass = 'badge-role-client';
                                    } elseif ($role === 'coach') {
                                        $roleClass = 'badge-role-coach';
                                    } elseif ($role === 'nutritionist') {
                                        $roleClass = 'badge-role-nutritionist';
                                    } elseif ($role === 'admin') {
                                        $roleClass = 'badge-role-admin';
                                    }

                                    $statusClass = 'badge-status-default';

                                    if ($status === 'active') {
                                        $statusClass = 'badge-status-active';
                                    } elseif ($status === 'banned') {
                                        $statusClass = 'badge-status-banned';
                                    } elseif ($status === 'deactivated') {
                                        $statusClass = 'badge-status-deactivated';
                                    } elseif ($status === 'pending') {
                                        $statusClass = 'badge-status-pending';
                                    }

                                    $normalizedGender = strtolower(trim($genderRaw));

                                    if ($normalizedGender === 'female') {
                                        $genderDisplay = 'Female';
                                    } elseif ($normalizedGender === 'male') {
                                        $genderDisplay = 'Male';
                                    } else {
                                        $genderDisplay = $genderRaw !== '' ? $genderRaw : '—';
                                    }
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

    <script>
        let currentLang = localStorage.getItem('usersLang') || 'en';

        const tbody = document.querySelector('tbody');
        const rows = Array.from(document.querySelectorAll('.user-row'));
        const rowsPerPage = 5;
        let currentPage = 0;
        let currentRows = rows;

        const deleteModal = document.getElementById('deleteModal');
        const modalDeleteUserId = document.getElementById('modalDeleteUserId');
        const deleteModalText = document.getElementById('deleteModalText');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

        const searchInput = document.getElementById('search');
        const sortSelect = document.getElementById('sort');

        const usersTableWrap = document.getElementById('usersTableWrap');
        const scrollLeftBtn = document.getElementById('scrollLeftBtn');
        const scrollRightBtn = document.getElementById('scrollRightBtn');

        const themeToggle = document.getElementById('themeToggle');
        const langEnBtn = document.getElementById('langEnBtn');
        const langFrBtn = document.getElementById('langFrBtn');

        const translations = {
            en: {
                pageTitle: 'Users Management',
                pageSubtitle: 'Manage accounts, review professional requests, and monitor user activity through a clean and modern admin workspace.',
                pendingRequests: 'Pending Requests',
                addUser: '+ Add User',
                totalUsers: 'Total Users',
                totalUsersDesc: 'Global count of all registered accounts currently stored in the platform database.',
                roleDistribution: 'Role Distribution',
                roleDistributionDesc: 'Clients, coaches, nutritionists, and admins.',
                statusOverview: 'Status Overview',
                statusOverviewDesc: 'Active, pending, banned, and deactivated users.',
                genderDistribution: 'Gender Distribution',
                genderDistributionDesc: 'Registered user gender repartition.',
                userDirectory: 'User Directory',
                userDirectoryDesc: 'View all existing accounts, their role, status, and profile details.',
                usersCount: 'user(s)',
                search: 'Search',
                sortBy: 'Sort By',
                defaultOrder: 'Default Order',
                idLowHigh: 'ID: Low to High',
                idHighLow: 'ID: High to Low',
                nameAZ: 'Name: A to Z',
                nameZA: 'Name: Z to A',
                roleAZ: 'Role: A to Z',
                statusAZ: 'Status: A to Z',
                id: 'ID',
                lastName: 'Last Name',
                firstName: 'First Name',
                dob: 'Date of Birth',
                email: 'Email',
                role: 'Role',
                status: 'Status',
                gender: 'Gender',
                noUsers: 'No users found.',
                deleteModalTitle: 'Delete User',
                deleteConfirm: 'Are you sure you want to delete',
                deleteConfirmDefault: 'Are you sure you want to delete this user?',
                no: 'No',
                yesDelete: 'Yes, Delete',
                clients: 'Clients',
                coaches: 'Coaches',
                nutritionists: 'Nutritionists',
                admins: 'Admins',
                active: 'Active',
                pending: 'Pending',
                banned: 'Banned',
                deactivated: 'Deactivated',
                male: 'Male',
                female: 'Female',
                other: 'Other',
                client: 'Client',
                coach: 'Coach',
                nutritionist: 'Nutritionist',
                admin: 'Admin'
            },
            fr: {
                pageTitle: 'Gestion des Utilisateurs',
                pageSubtitle: 'Gérez les comptes, vérifiez les demandes professionnelles et suivez l’activité des utilisateurs dans un espace admin moderne.',
                pendingRequests: 'Demandes en attente',
                addUser: '+ Ajouter',
                totalUsers: 'Total Utilisateurs',
                totalUsersDesc: 'Nombre global de tous les comptes enregistrés actuellement dans la base de données.',
                roleDistribution: 'Répartition des rôles',
                roleDistributionDesc: 'Clients, coachs, nutritionnistes et admins.',
                statusOverview: 'Aperçu des statuts',
                statusOverviewDesc: 'Utilisateurs actifs, en attente, bannis et désactivés.',
                genderDistribution: 'Répartition du genre',
                genderDistributionDesc: 'Répartition des utilisateurs inscrits selon le genre.',
                userDirectory: 'Liste des utilisateurs',
                userDirectoryDesc: 'Consultez les comptes, leurs rôles, statuts et informations principales.',
                usersCount: 'utilisateur(s)',
                search: 'Recherche',
                sortBy: 'Trier par',
                defaultOrder: 'Ordre par défaut',
                idLowHigh: 'ID : croissant',
                idHighLow: 'ID : décroissant',
                nameAZ: 'Nom : A à Z',
                nameZA: 'Nom : Z à A',
                roleAZ: 'Rôle : A à Z',
                statusAZ: 'Statut : A à Z',
                id: 'ID',
                lastName: 'Nom',
                firstName: 'Prénom',
                dob: 'Date de naissance',
                email: 'Email',
                role: 'Rôle',
                status: 'Statut',
                gender: 'Genre',
                noUsers: 'Aucun utilisateur trouvé.',
                deleteModalTitle: 'Supprimer utilisateur',
                deleteConfirm: 'Voulez-vous vraiment supprimer',
                deleteConfirmDefault: 'Voulez-vous vraiment supprimer cet utilisateur ?',
                no: 'Non',
                yesDelete: 'Oui, supprimer',
                clients: 'Clients',
                coaches: 'Coachs',
                nutritionists: 'Nutritionnistes',
                admins: 'Admins',
                active: 'Actif',
                pending: 'En attente',
                banned: 'Banni',
                deactivated: 'Désactivé',
                male: 'Homme',
                female: 'Femme',
                other: 'Autre',
                client: 'Client',
                coach: 'Coach',
                nutritionist: 'Nutritionniste',
                admin: 'Admin'
            }
        };



        function openDeleteModal(userId, userName) {
            modalDeleteUserId.value = userId;

            if (currentLang === 'fr') {
                deleteModalText.textContent = translations.fr.deleteConfirm + ' ' + userName + ' ?';
            } else {
                deleteModalText.textContent = translations.en.deleteConfirm + ' ' + userName + '?';
            }

            deleteModal.classList.add('show');
        }

        function closeDeleteModal() {
            deleteModal.classList.remove('show');
        }

        function getFilteredRows() {
            const searchValue = searchInput.value.trim().toLowerCase();
            const sortValue = sortSelect.value;

            let filteredRows = rows.filter(row => {
                const combined = (
                    (row.dataset.id || '') + ' ' +
                    (row.dataset.name || '') + ' ' +
                    (row.dataset.email || '') + ' ' +
                    (row.dataset.role || '') + ' ' +
                    (row.dataset.status || '') + ' ' +
                    (row.dataset.gender || '')
                ).toLowerCase();

                return combined.includes(searchValue);
            });

            filteredRows.sort((a, b) => {
                if (sortValue === 'id_asc') return Number(a.dataset.id) - Number(b.dataset.id);
                if (sortValue === 'id_desc') return Number(b.dataset.id) - Number(a.dataset.id);
                if (sortValue === 'name_asc') return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                if (sortValue === 'name_desc') return (b.dataset.name || '').localeCompare(a.dataset.name || '');
                if (sortValue === 'role_asc') return (a.dataset.role || '').localeCompare(b.dataset.role || '');
                if (sortValue === 'status_asc') return (a.dataset.status || '').localeCompare(b.dataset.status || '');

                return 0;
            });

            return filteredRows;
        }

        function renderRows(rowsToShow) {
            tbody.innerHTML = '';
            currentRows = rowsToShow;

            if (rowsToShow.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state" data-i18n="noUsers">${translations[currentLang].noUsers}</td>
                    </tr>
                `;
                return;
            }

            const maxPage = Math.max(Math.ceil(rowsToShow.length / rowsPerPage) - 1, 0);

            if (currentPage > maxPage) {
                currentPage = maxPage;
            }

            const start = currentPage * rowsPerPage;
            const end = start + rowsPerPage;
            const pageRows = rowsToShow.slice(start, end);

            pageRows.forEach(row => {
                row.style.display = '';
                tbody.appendChild(row);
            });

            if (scrollLeftBtn) {
                scrollLeftBtn.disabled = currentPage === 0;
            }

            if (scrollRightBtn) {
                scrollRightBtn.disabled = currentPage >= maxPage;
            }

            translateDynamicBadges();
        }

        function applyFilters() {
            currentPage = 0;
            renderRows(getFilteredRows());
        }

        function applyLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('usersLang', lang);

            document.documentElement.lang = lang;

            langEnBtn.classList.toggle('active', lang === 'en');
            langFrBtn.classList.toggle('active', lang === 'fr');

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.dataset.i18n;

                if (translations[lang][key]) {
                    el.textContent = translations[lang][key];
                }
            });

            if (searchInput) {
                searchInput.placeholder = lang === 'fr' ?
                    searchInput.dataset.placeholderFr :
                    searchInput.dataset.placeholderEn;
            }

            translateDynamicBadges();
            updateChartsLanguage();
        }

        function translateDynamicBadges() {
            document.querySelectorAll('.dynamic-role').forEach(el => {
                const value = (el.dataset.value || '').toLowerCase();
                el.textContent = translations[currentLang][value] || '—';
            });

            document.querySelectorAll('.dynamic-status').forEach(el => {
                const value = (el.dataset.value || '').toLowerCase();
                el.textContent = translations[currentLang][value] || '—';
            });

            document.querySelectorAll('.dynamic-gender').forEach(el => {
                const value = (el.dataset.value || '').toLowerCase();
                el.textContent = translations[currentLang][value] || el.textContent;
            });
        }

        function applyTheme(theme) {
            const isDark = theme === 'dark';

            document.body.classList.toggle('dark-mode', isDark);
            localStorage.setItem('usersTheme', theme);

            themeToggle.innerHTML = isDark ?
                '<i class="bi bi-sun"></i>' :
                '<i class="bi bi-moon-stars"></i>';

            updateChartTheme();
        }

        searchInput.addEventListener('input', applyFilters);
        sortSelect.addEventListener('change', applyFilters);

        cancelDeleteBtn.addEventListener('click', function() {
            closeDeleteModal();
        });

        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });

        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.row-delete-btn');

            if (!deleteBtn) {
                return;
            }

            e.stopPropagation();

            const userId = deleteBtn.dataset.id;
            const userName = deleteBtn.dataset.name || 'this user';

            openDeleteModal(userId, userName);
        });

        scrollRightBtn.addEventListener('click', function() {
            const maxPage = Math.ceil(currentRows.length / rowsPerPage) - 1;

            if (currentPage < maxPage) {
                currentPage++;
                renderRows(currentRows);
            }
        });

        scrollLeftBtn.addEventListener('click', function() {
            if (currentPage > 0) {
                currentPage--;
                renderRows(currentRows);
            }
        });

        themeToggle.addEventListener('click', function() {
            const newTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            applyTheme(newTheme);
        });

        langEnBtn.addEventListener('click', function() {
            applyLanguage('en');
        });

        langFrBtn.addEventListener('click', function() {
            applyLanguage('fr');
        });

        const chartColors = [
            '#dc2626',
            '#f97316',
            '#7c3aed',
            '#2563eb',
            '#16a34a',
            '#6b7280'
        ];

        const roleChart = new Chart(document.getElementById('roleChart'), {
            type: 'doughnut',
            data: {
                labels: [
                    translations[currentLang].clients,
                    translations[currentLang].coaches,
                    translations[currentLang].nutritionists,
                    translations[currentLang].admins
                ],
                datasets: [{
                    data: [
                        <?= $clientCount ?>,
                        <?= $coachCount ?>,
                        <?= $nutritionistCount ?>,
                        <?= $adminCount ?>
                    ],
                    backgroundColor: chartColors,
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                animation: {
                    duration: 1100,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });

        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: [
                    translations[currentLang].active,
                    translations[currentLang].pending,
                    translations[currentLang].banned,
                    translations[currentLang].deactivated
                ],
                datasets: [{
                    data: [
                        <?= $activeCount ?>,
                        <?= $pendingCount ?>,
                        <?= $bannedCount ?>,
                        <?= $deactivatedCount ?>
                    ],
                    backgroundColor: [
                        '#16a34a',
                        '#f59e0b',
                        '#dc2626',
                        '#6b7280'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1100,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });

        const genderChart = new Chart(document.getElementById('genderChart'), {
            type: 'bar',
            data: {
                labels: [
                    translations[currentLang].male,
                    translations[currentLang].female,
                    translations[currentLang].other
                ],
                datasets: [{
                    label: 'Users',
                    data: [
                        <?= $maleCount ?>,
                        <?= $femaleCount ?>,
                        <?= $otherGenderCount ?>
                    ],
                    backgroundColor: [
                        '#2563eb',
                        '#be123c',
                        '#6b7280'
                    ],
                    borderRadius: 14,
                    barThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1100,
                    easing: 'easeOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(107, 114, 128, 0.12)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        function updateChartsLanguage() {
            roleChart.data.labels = [
                translations[currentLang].clients,
                translations[currentLang].coaches,
                translations[currentLang].nutritionists,
                translations[currentLang].admins
            ];

            statusChart.data.labels = [
                translations[currentLang].active,
                translations[currentLang].pending,
                translations[currentLang].banned,
                translations[currentLang].deactivated
            ];

            genderChart.data.labels = [
                translations[currentLang].male,
                translations[currentLang].female,
                translations[currentLang].other
            ];

            roleChart.update();
            statusChart.update();
            genderChart.update();
        }

        function updateChartTheme() {
            const isDark = document.body.classList.contains('dark-mode');
            const textColor = isDark ? '#e5e7eb' : '#374151';
            const gridColor = isDark ? 'rgba(229, 231, 235, 0.12)' : 'rgba(107, 114, 128, 0.12)';
            const borderColor = isDark ? '#111827' : '#ffffff';

            [roleChart, statusChart].forEach(chart => {
                chart.data.datasets[0].borderColor = borderColor;
                chart.options.plugins.legend.labels.color = textColor;
                chart.update();
            });

            genderChart.options.scales.x.ticks.color = textColor;
            genderChart.options.scales.y.ticks.color = textColor;
            genderChart.options.scales.y.grid.color = gridColor;
            genderChart.update();
        }

        renderRows(rows);
        applyLanguage(currentLang);
        applyTheme(localStorage.getItem('usersTheme') || 'light');
        rows.forEach(row => {
            row.addEventListener('dblclick', function(e) {
                if (e.target.closest('.status-link')) {
                    return;
                }

                if (e.target.closest('.row-delete-btn')) {
                    return;
                }

                const userId = this.dataset.id;

                if (userId) {
                    window.location.href = 'edit_user.php?id=' + encodeURIComponent(userId);
                }
            });
        });
    </script>

</body>

</html>