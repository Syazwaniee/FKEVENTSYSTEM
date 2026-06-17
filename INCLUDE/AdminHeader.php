<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentAdminPage = basename($_SERVER['PHP_SELF'] ?? '');
$clubNavPages = [
    'clubManagement.php',
    '../Module2/addClub.php',
    'editClub.php',
    'assignClubCommittee.php',
    'editAssignClubCommittee.php',
];
$isClubNavActive = in_array($currentAdminPage, $clubNavPages, true);

$dashboardNavPages = ['dashboard.php', 'eventDashboard.php'];
$isDashboardNavActive = in_array($currentAdminPage, $dashboardNavPages, true);
$dashboardPageUrl = '../Module1/dashboard.php';
$eventDashboardUrl = '../Module3/eventDashboard.php';

$reportsNavPages = [
    'index.php',
    'manage-event-attendance.php',
    'participation-history.php',
    'top-student-ranking.php',
    'report-overview.php',
    'participation-dashboard.php',
];
$isReportsNavActive = in_array($currentAdminPage, $reportsNavPages, true);
$reportsPageUrl = '../Module4/index.php';
?>

<nav class="admin-navbar">

    <!-- ================= LEFT ================= -->
    <div class="admin-nav-left">

        <a href="admin.php">

            <i class="bi bi-code-slash"></i>

        </a>

    </div>

    <!-- ================= CENTER ================= -->
    <div class="admin-nav-center">

        <div class="admin-nav-dropdown"
            id="dashboardNavDropdown">

            <span class="admin-nav-dropdown-toggle"
                onclick="toggleDashboardNavDropdown(event)">

                <i class="bi bi-grid"></i>
                Dashboard
                <i class="bi bi-chevron-down admin-nav-chevron"
                    id="dashboardNavChevron"></i>

            </span>

            <div class="admin-nav-dropdown-menu">

                <a href="<?= htmlspecialchars($dashboardPageUrl) ?>#user-summary">

                    <i class="bi bi-people"></i>
                    Users

                </a>

                <a href="<?= htmlspecialchars($dashboardPageUrl) ?>#club-activities">

                    <i class="bi bi-suit-club-fill"></i>
                    Clubs

                </a>

                <a href="<?= htmlspecialchars($eventDashboardUrl) ?>"
                    class="<?= $currentAdminPage === 'eventDashboard.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-calendar-event"></i>
                    Events
                </a>

                <a href="../Module4/participation-dashboard.php"
                    class="<?= $currentAdminPage === 'participation-dashboard.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-bar-chart-line"></i>
                    Analytics

                </a>

            </div>

        </div>

        <a href="../Module1/userManagement.php">

            <i class="bi bi-people"></i>
            User Management

        </a>

        <div class="admin-nav-dropdown"
            id="clubNavDropdown">

            <span class="admin-nav-dropdown-toggle"
                onclick="toggleClubNavDropdown(event)">

                <i class="bi bi-suit-club-fill"></i>
                Club
                <i class="bi bi-chevron-down admin-nav-chevron"
                    id="clubNavChevron"></i>

            </span>

            <div class="admin-nav-dropdown-menu">

                <a href="../Module2/clubManagement.php"
                    class="<?= $currentAdminPage === 'clubManagement.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-list-ul"></i>
                    Club Management

                </a>

                <a href="../Module2/assignClubCommittee.php"
                    class="<?= $currentAdminPage === 'assignClubCommittee.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-person-badge"></i>
                    Assign Club Committee

                </a>

            </div>

        </div>

        <div class="admin-nav-dropdown"
            id="reportsNavDropdown">

            <span class="admin-nav-dropdown-toggle"
                onclick="toggleReportsNavDropdown(event)">

                <i class="bi bi-file-earmark-text"></i>
                Reports
                <i class="bi bi-chevron-down admin-nav-chevron"
                    id="reportsNavChevron"></i>

            </span>

            <div class="admin-nav-dropdown-menu">

                <a href="../Module4/index.php"
                    class="<?= $currentAdminPage === 'index.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-grid"></i>
                    Dashboard

                </a>

                <!-- <a href="../Module4/manage-event-attendance.php"
                    class="<?= $currentAdminPage === 'manage-event-attendance.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-journal-check"></i>
                    Attendance

                </a> -->

                <a href="../Module4/participation-history.php"
                    class="<?= $currentAdminPage === 'participation-history.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-clock-history"></i>
                    Participation History

                </a>

                <a href="../Module4/top-student-ranking.php"
                    class="<?= $currentAdminPage === 'top-student-ranking.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-award"></i>
                    Ranking

                </a>

                <a href="../Module4/report-overview.php"
                    class="<?= $currentAdminPage === 'report-overview.php' ? 'active-sub-nav' : '' ?>">

                    <i class="bi bi-graph-up"></i>
                    Reports

                </a>


            </div>

        </div>

    </div>

    <!-- ================= RIGHT ================= -->
    <div class="admin-nav-right">

        <!-- NOTIFICATION -->
        <div class="notification-icon">

            <i class="bi bi-bell"></i>

        </div>

        <!-- PROFILE -->
        <div class="admin-profile-dropdown">

            <!-- BUTTON -->
            <div class="admin-profile-btn"
                onclick="toggleAdminDropdown()">

                <!-- PROFILE IMAGE -->
                <div class="admin-profile-circle">

                    <i class="bi bi-person-fill"></i>

                </div>

                <!-- ARROW -->
                <i class="bi bi-chevron-down admin-arrow"
                    id="adminArrow"></i>

            </div>

            <!-- ================= DROPDOWN ================= -->
            <div class="admin-dropdown-menu"
                id="adminDropdownMenu">

                <!-- TOP -->
                <div class="admin-dropdown-top">

                    <div class="admin-dropdown-profile">

                        <i class="bi bi-person-fill"></i>

                    </div>

                    <div>

                        <h4>

                            <?php

                            if (isset($_SESSION['user']['FullName'])) {
                                echo htmlspecialchars(
                                    $_SESSION['user']['FullName']
                                );
                            } else {
                                echo 'Admin';
                            }

                            ?>

                        </h4>

                        <p>
                            Administrator
                        </p>

                    </div>

                </div>

                <!-- PROFILE -->
                <a href="/FKEVENTSYSTEM/Module1/profile.php">
                    <i class="bi bi-person-circle"></i>
                    View Profile
                </a>

                <a href="/FKEVENTSYSTEM/Module1/login.php"
                    class="admin-logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    Sign Out
                </a>

            </div>

        </div>

    </div>

</nav>

<!-- ================= SCRIPT ================= -->

<script>
    function toggleAdminDropdown() {

        let dropdown =
            document.getElementById(
                "adminDropdownMenu"
            );

        let arrow =
            document.getElementById(
                "adminArrow"
            );

        dropdown.classList.toggle(
            "show-admin-dropdown"
        );

        arrow.classList.toggle(
            "rotate-admin-arrow"
        );
    }

    function toggleClubNavDropdown(event) {

        if (event) {
            event.stopPropagation();
        }

        document
            .getElementById("clubNavDropdown")
            .classList.toggle("is-open");
    }

    function toggleDashboardNavDropdown(event) {

        if (event) {
            event.stopPropagation();
        }

        document
            .getElementById("dashboardNavDropdown")
            .classList.toggle("is-open");
    }

    function toggleReportsNavDropdown(event) {

        if (event) {
            event.stopPropagation();
        }

        document
            .getElementById("reportsNavDropdown")
            .classList.toggle("is-open");
    }

    /* ================= CLOSE OUTSIDE ================= */

    window.addEventListener(
        "click",
        function(e) {

            let profileContainer =
                document.querySelector(
                    ".admin-profile-dropdown"
                );

            if (
                profileContainer &&
                !profileContainer.contains(e.target)
            ) {

                document
                    .getElementById(
                        "adminDropdownMenu"
                    )
                    .classList.remove(
                        "show-admin-dropdown"
                    );

                document
                    .getElementById(
                        "adminArrow"
                    )
                    .classList.remove(
                        "rotate-admin-arrow"
                    );
            }

            let clubDropdown =
                document.getElementById(
                    "clubNavDropdown"
                );

            if (
                clubDropdown &&
                !clubDropdown.contains(e.target)
            ) {
                clubDropdown.classList.remove("is-open");
            }

            let dashboardDropdown =
                document.getElementById(
                    "dashboardNavDropdown"
                );

            if (
                dashboardDropdown &&
                !dashboardDropdown.contains(e.target)
            ) {
                dashboardDropdown.classList.remove("is-open");
            }

            let reportsDropdown =
                document.getElementById(
                    "reportsNavDropdown"
                );

            if (
                reportsDropdown &&
                !reportsDropdown.contains(e.target)
            ) {
                reportsDropdown.classList.remove("is-open");
            }
        }
    );
</script>