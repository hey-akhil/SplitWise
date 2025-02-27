<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_start();
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<link rel="stylesheet" href="css/day-night.css">
<?php include 'day-night-toggler.php'; ?>
<nav id="sidebar" class="sidebar closed">
    <div class="sidebar-toggler" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'new-user.php') ? 'active' : '' ?>" href="new-user.php">
                    <i class="fas fa-user-plus"></i>
                    <span>Add User</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'create_group.php') ? 'active' : '' ?>" href="create_group.php">
                    <i class="fas fa-users"></i>
                    <span>Create Group</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'add_expense.php') ? 'active' : '' ?>" href="add_expense.php">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Add Expenses</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="?logout=true">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");
        const content = document.getElementById("content");

        // Get the current page from PHP
        const currentPage = "<?= $current_page ?>";

        // Ensure sidebar state persists without flickering
        if (localStorage.getItem("sidebarState") === "open") {
            sidebar.classList.remove("closed");
            content.classList.remove("closed");
        } else {
            sidebar.classList.add("closed");
            content.classList.add("closed");
        }

        // Function to toggle sidebar
        function toggleSidebar() {
            sidebar.classList.toggle("closed");
            content.classList.toggle("closed");

            // Save state in localStorage
            localStorage.setItem("sidebarState", sidebar.classList.contains("closed") ? "closed" : "open");
        }

        // Assign function to toggler button
        document.querySelector(".sidebar-toggler").addEventListener("click", toggleSidebar);

        // Prevent reload when clicking the current page link
        document.querySelectorAll(".nav-link").forEach(link => {
            link.addEventListener("click", function (event) {
                const clickedPage = this.getAttribute("href");

                // If the clicked link is the same as the current page, prevent reloading
                if (clickedPage === currentPage) {
                    event.preventDefault();
                } else {
                    // Ensure sidebar remains in the same state after page change
                    localStorage.setItem("sidebarState", sidebar.classList.contains("closed") ? "closed" : "open");
                }
            });
        });
    });
</script>