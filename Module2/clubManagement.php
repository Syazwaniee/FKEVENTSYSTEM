<!-- clubManagement.php -->
<?php
require_once '../INCLUDE/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['delete_club'])) {
    $result = deleteClub((int) ($_POST['Club_id'] ?? 0));
    $redirectParams = $_GET;
    $redirectParams['msg'] = $result['message'];
    $redirectParams['msg_type'] = $result['success'] ? 'success' : 'danger';
    header('Location: clubManagement.php?' . http_build_query($redirectParams));
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
        Club Management
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
                    Club Management
                </h1>

                <p>
                    Manage all club information.
                </p>

            </div>

            <a href="../Module2/addClub.php"
                class="add-user-btn">

                <i class="bi bi-plus-lg"></i>
                Add Club

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
                Search clubs
            </h5>

            <form method="GET"
                action="">

                <div class="search-flex">

                    <select class="search-select"
                        name="search_filter">

                        <option value="">
                            Select filter
                        </option>

                        <option value="Club_name"
                            <?= (($_GET['search_filter'] ?? '') === 'Club_name') ? 'selected' : '' ?>>

                            Club name

                        </option>

                        <option value="Advisor_name"
                            <?= (($_GET['search_filter'] ?? '') === 'Advisor_name') ? 'selected' : '' ?>>

                            Advisor name

                        </option>

                    </select>

                    <input type="text"
                        name="search_keyword"
                        class="search-input"
                        placeholder="Enter keyword…"
                        value="<?= htmlspecialchars($_GET['search_keyword'] ?? '') ?>">

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

                <thead>

                    <tr>

                        <th>
                            Club name
                        </th>

                        <th>
                            Description
                        </th>

                        <th>
                            Advisor name
                        </th>

                        <th>
                            Maximum capacity
                        </th>

                        <th>
                            Status
                        </th>

                        <th class="text-center">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>
                    <?php
                    $clubList = getClubsForManagement(
                        $_GET['search_filter'] ?? null,
                        $_GET['search_keyword'] ?? null
                    );

                    if (count($clubList) > 0):
                        foreach ($clubList as $club):
                            ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($club['Club_name']) ?>
                                </td>
                                <td class="text-wrap"
                                    style="max-width: 320px;">
                                    <?= htmlspecialchars($club['Description'] !== '' ? $club['Description'] : '—') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(($club['Advisor_name'] ?? '') !== '' ? $club['Advisor_name'] : '—') ?>
                                </td>
                                <td>
                                    <?php
                                    $cap = $club['maxCapacity'] ?? null;
                                    echo $cap !== null && $cap !== ''
                                        ? htmlspecialchars((string) $cap)
                                        : '—';
                                    ?>
                                </td>
                                <td>

                                    <?php if ((int) $club['Is_active'] === 1): ?>

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
                                    <div class="action-flex justify-content-center">

                                        <a href="../Module2/editClub.php?Club_id=<?= (int) $club['Club_id']; ?>"
                                            class="edit-btn"
                                            title="Edit club">

                                            <i class="bi bi-pencil-square"></i>

                                        </a>

                                        <form method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this club? This cannot be undone.');">
                                            <input type="hidden"
                                                name="delete_club"
                                                value="1">
                                            <input type="hidden"
                                                name="Club_id"
                                                value="<?= (int) $club['Club_id'] ?>">
                                            <button type="submit"
                                                class="delete-btn"
                                                title="Delete club">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                            <?php
                        endforeach;
                    else:
                        ?>
                        <tr>
                            <td colspan="6"
                                class="text-center">
                                No clubs found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>

        </div>

    </div>
</body>

</html>
