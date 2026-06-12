<?php

if (!isset($navBase)) {
    $navBase = '';
}

if (!isset($activeNav)) {
    $activeNav = '';
}

if (!isset($conn)) {
    require_once __DIR__ . '/db.php';
}

$user_id = $_SESSION['user']['User_id'] ?? 0;

$profile = $user_id ? profile($conn, (int) $user_id) : null;

?>

<nav class="admin-navbar">

    </pre>
    <!-- ================= LEFT ================= -->
    <div class="admin-nav-left">

        <a href="<?= htmlspecialchars($navBase) ?>committee.php">

            <i class="bi bi-code-slash"></i>

        </a>

    </div>

    <!-- ================= CENTER ================= -->
    <div class="admin-nav-center">

        <a href="<?= htmlspecialchars($navBase) ?>Module2/manageClubCommittee.php"
            class="<?= $activeNav === 'club' ? 'active-admin-nav' : '' ?>">

            <i class="bi bi-suit-club-fill"></i>
            Club Management

        </a>

        <a href="<?= htmlspecialchars($navBase) ?>Module3/clubEvents.php"
            class="<?= $activeNav === 'events' ? 'active-admin-nav' : '' ?>">

            <i class="bi bi-ticket-perforated"></i>
            Club Events
        </a>

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

                            <?= htmlspecialchars(
                                $profile['FullName'] ?? 'Committee Member'
                            ); ?>

                        </h4>
                        <p>
                            Club Committee Members
                        </p>
                    </div>


                </div>

                <!-- PROFILE -->
                <a href="<?= htmlspecialchars($navBase) ?>Module1/profile.php">

                    <i class="bi bi-person-circle"></i>
                    View Profile

                </a>

                <!-- LOGOUT -->
                <a href="<?= htmlspecialchars($navBase) ?>Module1/login.php"
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

    /* ================= CLOSE OUTSIDE ================= */

    window.addEventListener(
        "click",
        function(e) {

            let container =
                document.querySelector(
                    ".admin-profile-dropdown"
                );

            if (!container.contains(e.target)) {

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
        }
    );
</script>