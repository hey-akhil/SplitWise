<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($group_id == 0) {
    die("Invalid group ID.");
}

// Fetch group members
$stmt = $conn->prepare("SELECT users.id, users.name FROM users 
                         JOIN group_members ON users.id = group_members.user_id 
                         WHERE group_members.group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$members = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $selected_members = $_POST['members'] ?? [];

    if ($amount <= 0) {
        die("Amount must be greater than zero.");
    }

    if (empty($selected_members)) {
        die("Select at least one member to split the expense.");
    }

    // Insert expense
    $stmt = $conn->prepare("INSERT INTO expenses (group_id, user_id, description, amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $group_id, $user_id, $description, $amount);

    if ($stmt->execute()) {
        $expense_id = $stmt->insert_id;
        $split_amount = $amount / count($selected_members);

        // Insert expense shares
        $stmt = $conn->prepare("INSERT INTO expense_shares (expense_id, user_id, amount) VALUES (?, ?, ?)");
        foreach ($selected_members as $member_id) {
            $member_id = intval($member_id);
            $stmt->bind_param("iid", $expense_id, $member_id, $split_amount);
            $stmt->execute();
        }

        header("Location: group_expenses.php?group_id=$group_id");
        exit();
    } else {
        die("Error adding expense: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Expense</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Add Expense to Group</h2>
    <form method="post">
        <input type="text" name="description" placeholder="Expense Description" required>
        <input type="number" name="amount" placeholder="Amount" step="0.01" required>
        <h3>Split with:</h3>
        <?php while ($member = $members->fetch_assoc()) { ?>
            <label>
                <input type="checkbox" name="members[]" value="<?= $member['id'] ?>" checked>
                <?= htmlspecialchars($member['name']) ?>
            </label><br>
        <?php } ?>
        <button type="submit">Add Expense</button>
    </form>
</body>
</html>
