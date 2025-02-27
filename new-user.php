<?php
session_start();
require 'config.php'; // Ensure database connection

// Redirect to login if not logged in
if (!isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format!";
    } else {
        // Check if email already exists in `splitwise_user`
        $checkEmail = $conn->prepare("SELECT id FROM splitwise_user WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $message = "User already exists!";
        } else {
            // Insert into `splitwise_user` table
            $stmt = $conn->prepare("INSERT INTO splitwise_user (name, email) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $email);

            if ($stmt->execute()) {
                $message = "User added successfully!";
            } else {
                $message = "Error adding user!";
            }

            $stmt->close();
        }
        $checkEmail->close();
    }

    // Redirect back to prevent form resubmission
    echo "<script>
            alert('$message');
            window.location.href='new-user.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <?php include 'day-night-toggler.php'; ?>

    <div id="content" class="content closed">
        <h2>Add New User</h2>
        <form method="POST" action="new-user.php">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add User</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
