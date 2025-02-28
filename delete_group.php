<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_id'])) {
    $group_id = intval($_POST['group_id']);

    // Check if the user is the group creator
    $stmt = $conn->prepare("SELECT created_by FROM groups WHERE id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && $result['created_by'] == $user_id) {
        // Delete group members first
        $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();

        // Delete expenses related to the group
        $stmt = $conn->prepare("DELETE FROM expenses WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();

        // Delete the group itself
        $stmt = $conn->prepare("DELETE FROM groups WHERE id = ?");
        $stmt->bind_param("i", $group_id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "unauthorized";
    }
}
?>
