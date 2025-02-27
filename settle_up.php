<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payer_id = $_SESSION['user_id'];
    $payee_id = $_POST['payee_id'];
    $amount = $_POST['amount'];

    $stmt = $conn->prepare("INSERT INTO payments (payer_id, payee_id, amount, status) VALUES (?, ?, ?, 'completed')");
    $stmt->bind_param("iid", $payer_id, $payee_id, $amount);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE expense_shares SET paid_status = 1 WHERE user_id = ? AND amount <= ?");
    $stmt->bind_param("id", $payer_id, $amount);
    $stmt->execute();
    $stmt->close();

    echo "Payment settled!";
}
?>
<form method="post">
    <input type="number" name="payee_id" placeholder="Payee User ID" required>
    <input type="number" name="amount" placeholder="Amount" required>
    <button type="submit">Settle Up</button>
</form>
