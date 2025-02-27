<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$invite_code = $_GET['code'] ?? '';

if ($invite_code == '') {
    die("Invalid invite link.");
}

// Find group by invite code
$stmt = $conn->prepare("SELECT id FROM groups WHERE invite_code = ?");
$stmt->bind_param("s", $invite_code);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

if ($group) {
    $group_id = $group['id'];

    // Check if the user is already in the group
    $stmt = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Add user to the group
        $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $group_id, $user_id);
        if ($stmt->execute()) {
            header("Location: group_expenses.php?group_id=$group_id");
            exit();
        } else {
            die("Error joining the group.");
        }
    } else {
        // User is already a member, just redirect
        header("Location: group_expenses.php?group_id=$group_id");
        exit();
    }
} else {
    die("Invalid invite code.");
}
?>
