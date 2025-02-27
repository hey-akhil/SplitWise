<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user groups
$query = "SELECT g.* FROM groups g 
          JOIN group_members gm ON g.id = gm.group_id 
          WHERE gm.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$groups = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <?php include 'day-night-toggler.php'; ?>

    <div id="content" class="content closed">
        <h2 class="text-center">Your Groups</h2>

        <div class="row">
            <?php if ($groups->num_rows > 0) { ?>
                <?php while ($row = $groups->fetch_assoc()) { ?>
                    <div class="col-md-4">
                        <div class="card group-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                                <a href="group_expenses.php?group_id=<?= $row['id'] ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12 text-center">
                    <p class="text-muted">You have not joined any groups yet.</p>
                    <a href="create_group.php" class="btn btn-success">Create New Group</a>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
