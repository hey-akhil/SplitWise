<?php
include 'config.php';

// // Ensure the connection uses UTF-8
// $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// $conn->exec("SET NAMES 'utf8'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert the plain password and the hashed password into respective columns
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, encrypted_password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $hashed_password]);

        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="register-container">
        <form method="post" class="register-form">
            <h2 class="text-center">Register</h2>
            <?php if (isset($error)) {
                echo "<p class='text-danger text-center'>$error</p>";
            } ?>
            <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
            <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm Password"
                required>
            <button type="submit" class="btn btn-primary w-100">Register</button>
            <p class="text-center mt-3"><a href="index.php">Already registered? Login</a></p>
        </form>
    </div>
</body>

</html>