<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($group_id == 0) {
    die("Invalid group ID.");
}

// Fetch group details including invite link
$stmt = $conn->prepare("SELECT name, invite_code, created_by FROM groups WHERE id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
$invite_link = "join_group.php?code=" . $group['invite_code'];

// Fetch expenses
$expenses = $conn->prepare("SELECT expenses.*, users.name AS paid_by FROM expenses 
                          JOIN users ON expenses.user_id = users.id 
                          WHERE group_id = ?");
$expenses->bind_param("i", $group_id);
$expenses->execute();
$expense_results = $expenses->get_result();

// Fetch group members count
$stmt = $conn->prepare("SELECT COUNT(*) AS member_count FROM group_members WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$member_count_result = $stmt->get_result()->fetch_assoc();
$member_count = $member_count_result['member_count'];

// Fetch group members list
$stmt = $conn->prepare("SELECT users.id, users.name FROM group_members JOIN users ON group_members.user_id = users.id WHERE group_members.group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$members_result = $stmt->get_result();

// Add new member
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_member'])) {
    $new_member_email = trim($_POST['email']);

    if (!empty($new_member_email)) {
        // Check if the user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $new_member_email);
        $stmt->execute();
        $user_result = $stmt->get_result()->fetch_assoc();

        if ($user_result) {
            $new_member_id = $user_result['id'];

            // Check if the member is already in the group
            $stmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $group_id, $new_member_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                // Add the user to the group
                $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $group_id, $new_member_id);
                if ($stmt->execute()) {
                    echo "<script>alert('Member added successfully!'); window.location.href='group_expenses.php?group_id=$group_id';</script>";
                }
            } else {
                echo "<script>alert('User is already in the group!');</script>";
            }
        } else {
            echo "<script>alert('User not found!');</script>";
        }
    }
}

// Remove a member
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_member'])) {
    $remove_user_id = intval($_POST['user_id']);

    // Prevent removing the group creator
    if ($remove_user_id == $group['created_by']) {
        echo "<script>alert('You cannot remove the group creator.');</script>";
    } else {
        $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $remove_user_id);
        if ($stmt->execute()) {
            echo "<script>alert('Member removed successfully!'); window.location.href='group_expenses.php?group_id=$group_id';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Group Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'day-night-toggler.php'; ?>

    <div id="content" class="content closed">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Group Expenses - <?= htmlspecialchars($group['name']) ?></h2>
            
            <div class="d-flex gap-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fas fa-user-plus"></i>
                </button>

                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#shareLinkModal">
                    <i class="fas fa-share-alt"></i>
                </button>

                <button class="btn btn-info position-relative" data-bs-toggle="modal" data-bs-target="#groupMembersModal">
                    <i class="fas fa-users"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $member_count ?>
                    </span>
                </button>
            </div>
        </div>

        <ul class="list-group mt-3">
            <?php while ($row = $expense_results->fetch_assoc()) { ?>
                <li class="list-group-item"><?= htmlspecialchars($row['description']) ?> - ₹<?= $row['amount'] ?> (Paid by <?= htmlspecialchars($row['paid_by']) ?>)</li>
            <?php } ?>
        </ul>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="email" name="email" class="form-control mb-2" placeholder="Enter Email" required>
                        <button type="submit" name="add_member" class="btn btn-success w-100">Add Member</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Group Link Modal -->
    <div class="modal fade" id="shareLinkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share Group Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="inviteLink" class="form-control mb-2" value="<?= $invite_link ?>" readonly>
                    <button class="btn btn-primary w-100" onclick="copyLink()">Copy Link</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyLink() {
            let link = document.getElementById("inviteLink");
            link.select();
            document.execCommand("copy");
            alert("Invite link copied!");
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
