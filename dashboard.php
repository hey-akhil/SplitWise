<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user groups
$query = "SELECT g.*, g.created_by FROM groups g 
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .delete-group {
            background: transparent;
            color: #999;
            border: none;
            font-size: 1.2rem;
        }

        .delete-group:hover {
            color: red;
        }
    </style>
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
                    <div class="col-md-4 group-card-container" data-group-id="<?= $row['id'] ?>">
                        <div class="card group-card shadow-sm position-relative">
                            <!-- Delete Button (Only if user is group creator) -->
                            <?php if ($row['created_by'] == $user_id) { ?>
                                <button class="delete-group position-absolute top-0 end-0 m-2" data-group-id="<?= $row['id'] ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php } ?>

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

    <!-- jQuery for AJAX -->
    <script>
        $(document).ready(function() {
            $(".delete-group").click(function() {
                let groupId = $(this).data("group-id");
                let cardContainer = $(this).closest(".group-card-container");

                if (confirm("Are you sure you want to delete this group?")) {
                    $.ajax({
                        url: "delete_group.php",
                        method: "POST",
                        data: { group_id: groupId },
                        success: function(response) {
                            if (response.trim() === "success") {
                                cardContainer.fadeOut(300, function() { $(this).remove(); });
                            } else {
                                alert("Error deleting group.");
                            }
                        }
                    });
                }
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
