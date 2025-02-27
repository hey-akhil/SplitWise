<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$balances = [];

$query = "SELECT user_id, SUM(amount) as total_owed FROM expense_shares WHERE user_id != $user_id GROUP BY user_id";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $balances[$row['user_id']] = -$row['total_owed'];
}

$query = "SELECT expenses.user_id, SUM(expense_shares.amount) as total_paid FROM expenses 
          JOIN expense_shares ON expenses.id = expense_shares.expense_id 
          WHERE expenses.user_id = $user_id GROUP BY expenses.user_id";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    if (isset($balances[$row['user_id']])) {
        $balances[$row['user_id']] += $row['total_paid'];
    } else {
        $balances[$row['user_id']] = $row['total_paid'];
    }
}
?>

<h3>Balance Overview</h3>
<ul>
    <?php
    foreach ($balances as $friend_id => $amount) {
        if ($amount < 0) {
            echo "<li>You owe " . abs($amount) . " to User ID: $friend_id</li>";
        } elseif ($amount > 0) {
            echo "<li>User ID: $friend_id owes you $amount</li>";
        } else {
            echo "<li>You're settled with User ID: $friend_id</li>";
        }
    }
    ?>
</ul>
