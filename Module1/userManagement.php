<!-- userManagement.php -->
<?php
require_once '../INCLUDE/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['delete_user'])) {
    $result = deleteUser((int) ($_POST['User_id'] ?? 0));
    $redirectParams = $_GET;
    $redirectParams['msg'] = $result['message'];
    $redirectParams['msg_type'] = $result['success'] ? 'success' : 'danger';
    header('Location: userManagement.php?' . http_build_query($redirectParams));
    exit;
}

$flashMessage = $_GET['msg'] ?? null;
$flashType = in_array($_GET['msg_type'] ?? '', ['success', 'danger'], true)
    ? $_GET['msg_type']
    : 'danger';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        User Management
    </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../CSS/UserManagement.css">

    <link rel="stylesheet" href="../CSS/style.css">

    <link rel="stylesheet" href="../CSS/adminHeader.css">

</head>

<body>

    <!-- HEADER -->
    <?php include '../INCLUDE/AdminHeader.php'; ?>

    <div class="user-container">

        <div class="top-flex">

            <!-- TITLE -->
            <div>

                <h1>
                    User Management
                </h1>

                <p>
                    Manage all student, committee,
                    and administrator accounts.
                </p>

            </div>

            <a href="../Module1/addUser.php"
                class="add-user-btn">

                <i class="bi bi-plus-lg"></i>
                Add User

            </a>

        </div>

        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= htmlspecialchars($flashType) ?> mb-3"
                role="alert">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <div class="search-box">

            <h5>
                Search User
            </h5>

            <form method="GET">

                <div class="search-flex">

                    <!-- FILTER -->
                    <select
                        class="search-select"
                        name="search_filter">

                        <option value="">
                            Select Filter
                        </option>

                        <option value="FullName">
                            Full Name
                        </option>

                        <option value="User_id">
                            User ID
                        </option>

                        <option value="Phone_num">
                            Phone Number
                        </option>

                        <option value="Student_id">
                            Matrix / Staff ID
                        </option>

                    </select>
                    <!-- INPUT -->
                    <input type="text"
                        name="search_keyword"
                        class="search-input"
                        placeholder="Enter keyword...">

                    <!-- BUTTON -->
                    <button type="submit"
                        class="search-btn">

                        <i class="bi bi-search"></i>
                        Search

                    </button>

                </div>

            </form>

        </div>

        <!-- ================= TABLE ================= -->
        <div class="table-box">

            <table class="table custom-table align-middle">

                <!-- HEADER -->
                <thead>

                    <tr>

                        <th>
                            User ID
                        </th>

                        <th>
                            Full Name
                        </th>

                        <th>
                            Phone Number
                        </th>

                        <th>
                            Matrics / Staff ID
                        </th>

                        <th>
                            Role
                        </th>

                        <th>
                            Status
                        </th>

                        <th class="text-center">
                            Action
                        </th>

                    </tr>

                </thead>

                <!-- BODY -->
                <tbody>
                    <?php
                    $search_filter =
                        $_GET['search_filter'] ?? null;

                    $search_keyword =
                        $_GET['search_keyword'] ?? null;

                    // If search exists
                    if (!empty($search_keyword)) {
                        $userList = search(
                            $search_filter,
                            $search_keyword
                        );
                    } else {
                        $userList = getUserList();
                    }

                    if ($userList && $userList->num_rows > 0):
                        while ($user = $userList->fetch_assoc()):
                    ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($user['User_id']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['FullName']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['Phone_num']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['Student_id']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td>

                                    <?php if ($user['Is_active'] == 1): ?>

                                        <span class="status-active">

                                            <span class="status-dot active-dot"></span>

                                            Active

                                        </span>

                                    <?php else: ?>

                                        <span class="status-inactive">

                                            <span class="status-dot inactive-dot"></span>

                                            Inactive

                                        </span>

                                    <?php endif; ?>

                                </td>
                                <td>
                                    <div class="action-flex">
                                        <a href="../Module1/edit.php?User_id=<?= $user['User_id']; ?>"
                                            class="edit-btn">

                                            <i class="bi bi-pencil-square"></i>

                                        </a>

                                        <form method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                            <input type="hidden"
                                                name="delete_user"
                                                value="1">
                                            <input type="hidden"
                                                name="User_id"
                                                value="<?= (int) $user['User_id'] ?>">
                                            <button type="submit"
                                                class="delete-btn"
                                                title="Delete user">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>


            </table>

        </div>

    </div>
</body>

</html>