<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- <script src="JavaScript/inspect-prevent.js"></script> -->
    <link rel="stylesheet" href="css/day-night.css">
    <link rel="stylesheet" href="css/slider.css">

    <script>
        // Apply dark mode before page loads to prevent flickering
        (function () {
            if (localStorage.getItem("theme") === "dark") {
                document.body.classList.add("dark-mode");
            }
        })();
    </script>

    <style>
        /* Hide content until JavaScript applies the correct theme */
        body {
            visibility: hidden;
        }
        body.loaded {
            visibility: visible;
        }
    </style>
</head>

<body>

    <!-- Theme Toggle Switch -->
    <div class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-sun"></i>
        <div class="toggle-slider"></div>
        <i class="fas fa-moon" style="margin-right: 4px;"></i>
    </div>

    <script>
        // Function to toggle theme
        function toggleTheme() {
            let body = document.body;

            body.classList.toggle("dark-mode");

            if (body.classList.contains("dark-mode")) {
                localStorage.setItem("theme", "dark");
            } else {
                localStorage.setItem("theme", "light");
            }
        }

        // Load the saved theme
        window.onload = function () {
            let savedTheme = localStorage.getItem("theme");
            if (savedTheme === "dark") {
                document.body.classList.add("dark-mode");
            }
        }

        // Prevent flickering on page load
        document.addEventListener("DOMContentLoaded", function () {
            document.body.classList.add("loaded");
        });
    </script>

</body>

</html>
