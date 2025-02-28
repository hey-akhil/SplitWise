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
                } else {
                    echo "<script>alert('Error adding member: " . $stmt->error . "');</script>";
                }
            } else {
                echo "<script>alert('User is already in the group!');</script>";
            }
        } else {
            echo "<script>alert('User not found!');</script>";
        }
    }
}

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
                } else {
                    echo "<script>alert('Error adding member: " . $stmt->error . "');</script>";
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

                <button class="btn btn-info position-relative" data-bs-toggle="modal"
                    data-bs-target="#groupMembersModal">
                    <i class="fas fa-users"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $member_count ?>
                    </span>
                </button>
            </div>
        </div>

        <ul class="list-group mt-3">
            <?php while ($row = $expense_results->fetch_assoc()) { ?>
                <li class="list-group-item"><?= htmlspecialchars($row['description']) ?> - ₹<?= $row['amount'] ?> (Paid by
                    <?= htmlspecialchars($row['paid_by']) ?>)
                </li>
            <?php } ?>
        </ul>
    </div>

    <!-- 🔹 Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="memberSearch" class="form-control mb-2" placeholder="Enter user name"
                        onkeyup="fetchMembers(this)">
                    <div id="memberSuggestions" class="suggestion-box"></div>
                    <input type="hidden" id="selectedUserId">
                    <button id="addSelectedMember" class="btn btn-success w-100 mt-2" disabled>Add Member</button>
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
    <!-- 🔹 Show Group Members Modal -->
    <div class="modal fade" id="groupMembersModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Group Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <?php while ($member = $members_result->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($member['name']) ?>
                                <?php if ($member['id'] != $group['created_by']) { ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                                        <button type="submit" name="remove_member" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchMembers(inputField) {
            let query = inputField.value.trim();
            if (query.length < 2) return;

            $.ajax({
                url: 'fetch_users.php',
                method: 'POST',
                data: { search: query },
                success: function (response) {
                    $('#memberSuggestions').html(response).show();
                }
            });
        }

        function selectSuggestion(name, id) {
            $('#memberSearch').val(name);
            $('#memberSuggestions').hide();
            $('#selectedUserId').val(id);
            $('#addSelectedMember').prop('disabled', false);
        }

        $('#addSelectedMember').click(function () {
            let userId = $('#selectedUserId').val();
            let groupId = <?= $group_id ?>;
            if (!userId) return;

            $.ajax({
                url: 'add_member.php',
                method: 'POST',
                data: { user_id: userId, group_id: groupId },
                success: function (response) {
                    console.log(response); // Debug Response
                    if (response.trim() === "success") {
                        alert("Member added successfully!");
                        location.reload();
                    } else {
                        alert(response);
                    }
                },
                error: function () {
                    alert("Error processing request.");
                }
            });
        });


        function copyLink() {
            let link = document.getElementById("inviteLink");
            navigator.clipboard.writeText(link.value).then(() => {
                alert("Invite link copied!");
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>