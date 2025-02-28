<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['group_id'])) {
    $user_id = intval($_POST['user_id']);
    $group_id = intval($_POST['group_id']);

    // Check if the user is already in the group
    $stmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // Add the user to the group
        $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $group_id, $user_id);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error adding member.";
        }
    } else {
        echo "User is already in the group!";
    }
} else {
    echo "Invalid request.";
}
?>
