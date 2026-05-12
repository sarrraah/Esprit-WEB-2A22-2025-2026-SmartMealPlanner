<?php
require_once __DIR__ . '/../model/UserModel.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return $this->userModel->getAllUsers();
    }

    public function store($data)
    {
        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        return $this->userModel->addUser($data);
    }

    public function show($id)
    {
        return $this->userModel->getUserById($id);
    }

    public function update($id, $data)
    {
        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        return $this->userModel->updateUser($id, $data);
    }

    public function delete($id)
    {
        return $this->userModel->deleteUser($id);
    }

    public function findByEmail($email)
    {
        return $this->userModel->getUserByEmail($email);
    }

    public function login($email, $password)
    {
        $user = $this->userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }

        return false;
    }
    public function saveRememberToken($userId, $hashedToken, $expires)
    {
        $sql = "UPDATE user 
            SET remember_token = :remember_token,
                remember_expires = :remember_expires
            WHERE id = :id";

        $db = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([
            'remember_token' => $hashedToken,
            'remember_expires' => $expires,
            'id' => $userId
        ]);
    }
    public function getUserByRememberToken($token)
    {
        $hashedToken = hash('sha256', $token);

        $sql = "SELECT * FROM user 
            WHERE remember_token = :remember_token
            AND remember_expires > NOW()
            AND statut = 'active'
            LIMIT 1";

        $db = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([
            'remember_token' => $hashedToken
        ]);

        return $query->fetch();
    }
    public function clearRememberToken($userId)
    {
        $sql = "UPDATE user 
            SET remember_token = NULL,
                remember_expires = NULL
            WHERE id = :id";

        $db = config::getConnexion();

        $query = $db->prepare($sql);
        $query->execute([
            'id' => $userId
        ]);
    }

    /**
     * Get users with filtering and sorting
     * @param array $filters ['search' => string, 'sort' => string, 'status' => string]
     * @return array
     */
    public function getFilteredUsers($filters = [])
    {
        $users = $this->userModel->getAllUsers();

        // Filter by status
        if (!empty($filters['status']) && $filters['status'] === 'pending') {
            $users = array_filter($users, function ($u) {
                return strtolower(trim($u['statut'] ?? '')) === 'pending';
            });
        }

        // Search filter
        if (!empty($filters['search'])) {
            $searchLower = strtolower(trim($filters['search']));
            $users = array_filter($users, function ($u) use ($searchLower) {
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

        // Sort
        if (!empty($filters['sort'])) {
            $users = $this->sortUsers($users, $filters['sort']);
        }

        return array_values($users);
    }

    /**
     * Sort users array
     * @param array $users
     * @param string $sortType
     * @return array
     */
    private function sortUsers($users, $sortType)
    {
        usort($users, function ($a, $b) use ($sortType) {
            switch ($sortType) {
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

        return $users;
    }

    /**
     * Get user statistics
     * @return array
     */
    public function getUserStatistics()
    {
        $users = $this->userModel->getAllUsers();
        
        $stats = [
            'total' => count($users),
            'roles' => [
                'client' => 0,
                'coach' => 0,
                'nutritionist' => 0,
                'admin' => 0
            ],
            'statuses' => [
                'active' => 0,
                'pending' => 0,
                'banned' => 0,
                'deactivated' => 0
            ],
            'genders' => [
                'male' => 0,
                'female' => 0,
                'other' => 0
            ]
        ];

        foreach ($users as $user) {
            $role = strtolower(trim($user['role'] ?? ''));
            $status = strtolower(trim($user['statut'] ?? ''));
            $gender = strtolower(trim($user['sexe'] ?? ''));

            // Count roles
            if (isset($stats['roles'][$role])) {
                $stats['roles'][$role]++;
            }

            // Count statuses
            if (isset($stats['statuses'][$status])) {
                $stats['statuses'][$status]++;
            }

            // Count genders
            if ($gender === 'male') {
                $stats['genders']['male']++;
            } elseif ($gender === 'female') {
                $stats['genders']['female']++;
            } else {
                $stats['genders']['other']++;
            }
        }

        return $stats;
    }
}
