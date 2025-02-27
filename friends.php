<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["friend_id"])) {
    $friend_id = $_POST["friend_id"];
    $conn->query("INSERT INTO friendships (user_id1, user_id2, status) VALUES ($user_id, $friend_id, 'pending')");
    echo "Friend request sent!";
}

$friends = $conn->query("SELECT users.id, users.name FROM users JOIN friendships ON users.id = friendships.user_id2 WHERE friendships.user_id1 = $user_id AND friendships.status = 'accepted'");

$requests = $conn->query("SELECT users.id, users.name FROM users JOIN friendships ON users.id = friendships.user_id1 WHERE friendships.user_id2 = $user_id AND friendships.status = 'pending'");
?>

<h3>Send Friend Request</h3>
<form method="post">
    <select name="friend_id">
        <?php
        $users = $conn->query("SELECT id, name FROM users WHERE id != $user_id");
        while ($row = $users->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select>
    <button type="submit">Add Friend</button>
</form>

<h3>Friend Requests</h3>
<ul>
    <?php while ($row = $requests->fetch_assoc()) { ?>
        <li>
            <?= $row['name'] ?>
            <a href="accept_friend.php?friend_id=<?= $row['id'] ?>">Accept</a>
        </li>
    <?php } ?>
</ul>

<h3>Friends List</h3>
<ul>
    <?php while ($row = $friends->fetch_assoc()) { ?>
        <li><?= $row['name'] ?></li>
    <?php } ?>
</ul>
