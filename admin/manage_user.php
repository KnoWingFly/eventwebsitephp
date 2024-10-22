<?php
session_start();
require '../config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

$valid_columns = ['name', 'email', 'role'];
$sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $valid_columns) ? $_GET['sort'] : 'name';
$sort_direction = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'desc' : 'asc';

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_email = isset($_GET['search_email']) ? trim($_GET['search_email']) : '';
$search_role = isset($_GET['search_role']) ? trim($_GET['search_role']) : '';
$search_event = isset($_GET['search_event']) ? trim($_GET['search_event']) : '';

$sql = "
    SELECT u.id, u.name, u.email, u.role, GROUP_CONCAT(e.name SEPARATOR ', ') AS event_names 
    FROM users u
    LEFT JOIN registrations r ON u.id = r.user_id
    LEFT JOIN events e ON r.event_id = e.id
    WHERE 1 = 1
";

$params = [];
if ($search_name) {
    $sql .= " AND u.name LIKE :search_name";
    $params[':search_name'] = "%$search_name%";
}
if ($search_email) {
    $sql .= " AND u.email LIKE :search_email";
    $params[':search_email'] = "%$search_email%";
}
if ($search_role) {
    $sql .= " AND u.role LIKE :search_role";
    $params[':search_role'] = "%$search_role%";
}
if ($search_event) {
    $sql .= " AND e.name LIKE :search_event";
    $params[':search_event'] = "%$search_event%";
}

$sql .= " GROUP BY u.id ORDER BY $sort_column $sort_direction";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userEventsStmt = $pdo->prepare("
    SELECT u.id as user_id, e.name as event_name
    FROM users u
    JOIN registrations r ON u.id = r.user_id
    JOIN events e ON r.event_id = e.id
    WHERE u.id = :user_id
");

$next_direction = $sort_direction === 'asc' ? 'desc' : 'asc';
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-base-300 p-4 sm:p-6 md:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-0 text-white">User Management</h1>
            <a href="../index.php?page=logout" class="btn btn-sm sm:btn-md w-full sm:w-auto hover:bg-red-700 text-white">Logout</a>
        </div>

        <form method="GET" class="mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <input type="text" name="search_name" value="<?= htmlspecialchars($search_name); ?>" class="input input-bordered w-full" placeholder="Search by Name">
                <input type="text" name="search_email" value="<?= htmlspecialchars($search_email); ?>" class="input input-bordered w-full" placeholder="Search by Email">
                <input type="text" name="search_role" value="<?= htmlspecialchars($search_role); ?>" class="input input-bordered w-full" placeholder="Search by Role">
                <input type="text" name="search_event" value="<?= htmlspecialchars($search_event); ?>" class="input input-bordered w-full" placeholder="Search by Event">
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>

            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_column); ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($sort_direction); ?>">
        </form>

        <div class="card bg-base-100 shadow-xl overflow-x-auto">
            <div class="card-body p-0">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>

                            <th class="text-white">
                                <a href="?sort=name&dir=<?= $next_direction ?>&search_name=<?= htmlspecialchars($search_name); ?>&search_email=<?= htmlspecialchars($search_email); ?>&search_role=<?= htmlspecialchars($search_role); ?>&search_event=<?= htmlspecialchars($search_event); ?>" class="hover:underline">Name</a>
                            </th>
                            <th class="text-white">
                                <a href="?sort=email&dir=<?= $next_direction ?>&search_name=<?= htmlspecialchars($search_name); ?>&search_email=<?= htmlspecialchars($search_email); ?>&search_role=<?= htmlspecialchars($search_role); ?>&search_event=<?= htmlspecialchars($search_event); ?>" class="hover:underline">Email</a>
                            </th>
                            <th class="text-white">
                                <a href="?sort=role&dir=<?= $next_direction ?>&search_name=<?= htmlspecialchars($search_name); ?>&search_email=<?= htmlspecialchars($search_email); ?>&search_role=<?= htmlspecialchars($search_role); ?>&search_event=<?= htmlspecialchars($search_event); ?>" class="hover:underline">Role</a>
                            </th>
                            <th class="text-white">Actions</th>
                            <th class="text-white">Registered Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="whitespace-nowrap"><?php echo htmlspecialchars($user['name']); ?></td>
                                <td class="whitespace-nowrap"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="whitespace-nowrap"><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn hover:bg-purple-700 btn-xs sm:btn-sm transition-all duration-300">Edit</a>
                                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn hover:bg-purple-700 btn-xs sm:btn-sm transition-all duration-300" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <?php
                                    $userEventsStmt->execute([':user_id' => $user['id']]);
                                    $userEvents = $userEventsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    if ($userEvents):
                                        foreach ($userEvents as $event): ?>
                                            <span class="block"><?= htmlspecialchars($event['event_name']); ?></span>
                                        <?php endforeach;
                                    else: ?>
                                        <span class="block">No events</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
