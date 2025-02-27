<?php
include 'config.php';

if (isset($_POST['search'])) {
    $search = '%' . $_POST['search'] . '%';
    $selectedFriends = $_POST['selectedFriends'] ?? [];

    // Convert JSON string to PHP array if needed
    if (!is_array($selectedFriends)) {
        $selectedFriends = json_decode($selectedFriends, true);
    }
    
    // Prepare SQL query
    $stmt = $conn->prepare("SELECT name FROM splitwise_user WHERE name LIKE ? LIMIT 50");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['name'], $selectedFriends)) { // Exclude selected users
            echo "<div onclick=\"selectSuggestion('" . htmlspecialchars($row['name'], ENT_QUOTES) . "', this.parentElement.previousElementSibling)\">" 
                . htmlspecialchars($row['name']) . 
                "</div>";
        }
    }
}
?>
