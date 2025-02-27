<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare and execute the query to fetch the user by email
    $stmt = $conn->prepare("SELECT id, name, email, encrypted_password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // Fetch user data as an associative array

    // Check if user exists and verify the password
    if ($user && password_verify($password, $user['encrypted_password'])) {
        // Store user details in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        // Handle invalid login attempt
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <!-- <link rel="icon" type="image/png" href="images/img.png"> -->
        <link rel="stylesheet" href="css/styles.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <style>
            /* Ensure the body and html cover the full height */
            html,
            body {
                height: 100%;
                margin: 0;
            }

            /* Full height flex container */
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                /* background-color: #f4f4f4; */
            }

            /* Centered form box with responsive width */
            .login-container {
                width: 100%;
                max-width: 400px;
                /* Maximum width for the form */
                padding: 20px;
                /* background-color: white; */
                /* border-radius: 8px; */
                /* box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); */
            }

            .form-control {
                margin-bottom: 15px;
            }

            .btn {
                margin-top: 10px;
            }

            /* Make the link more readable */
            .text-center a {
                color: #007bff;
            }

            .text-danger {
                color: red;
            }
        </style>
    </head>

    <body>
        <!-- <div class="animated-bg"> -->
        <?php include('day-night-toggler.php'); ?>
            <div class="login-container">
                <form method="post" class="bg-none p-4 rounded shadow">
                    <h2 class="text-center">Login</h2>
                    <?php if (isset($error)) {
                        echo "<p class='text-danger text-center'>$error</p>";
                    } ?>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    <p class="text-center mt-2"><a href="register.php">Don't have an account? Register</a></p>
                </form>
            </div>
        </div>
        <script src="JavaScript/main-bg.js"></script>
    </body>
</html>
<!-- Copyright © 2022 Par Solution. All Rights Reserved -->