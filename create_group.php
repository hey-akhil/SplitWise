<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = trim($_POST['group_name']);
    $description = trim($_POST['description']);
    $invite_code = substr(md5(uniqid()), 0, 6); // Generate a 6-character invite code
    $selected_friends = $_POST['friends'] ?? [];

    if (!empty($group_name)) {
        // Insert the group into the database
        $stmt = $conn->prepare("INSERT INTO groups (name, description, invite_code, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $group_name, $description, $invite_code, $user_id);

        if ($stmt->execute()) {
            $group_id = $stmt->insert_id;

            // Add the creator to the group
            $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $group_id, $user_id);
            $stmt->execute();

            // Add selected friends to the group
            foreach ($selected_friends as $friend_name) {
                if (!empty($friend_name)) {
                    $stmt = $conn->prepare("SELECT id FROM splitwise_user WHERE name = ?");
                    $stmt->bind_param("s", $friend_name);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($friend_id);
                        $stmt->fetch();
                    } else {
                        // Create a new user with a blank email & password
                        $stmt = $conn->prepare("INSERT INTO splitwise_user (name, email, password) VALUES (?, '', '')");
                        $stmt->bind_param("s", $friend_name);
                        $stmt->execute();
                        $friend_id = $stmt->insert_id;
                    }

                    // Insert the friend into the group
                    $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $group_id, $friend_id);
                    $stmt->execute();
                }
            }

            // Show success message with SweetAlert & redirect after 2s
            $message = "<script>
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 2000);
                Swal.fire({
                    title: 'Success!',
                    text: 'Group created successfully!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            </script>";
        } else {
            $message = "<div class='alert alert-danger'>Error creating group!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Group name cannot be empty!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert Library -->

    <style>
        .autocomplete-list {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            max-height: 150px;
            overflow-y: auto;
            width: 100%;
            border-radius: 5px;
            display: none;
        }

        .autocomplete-list div {
            padding: 8px;
            cursor: pointer;
        }

        .autocomplete-list div:hover {
            background-color: #f0f0f0;
        }
    </style>

    <script>
        $(document).ready(function () {
            $(document).on('click', function (event) {
                if (!$(event.target).closest('.friend-input').length) {
                    $('.autocomplete-list').hide();
                }
            });
        });

        function addFriendField() {
            let container = document.getElementById('friends-container');
            let div = document.createElement('div');
            div.classList.add('input-group', 'mb-2', 'position-relative');

            div.innerHTML = `
                <input type="text" name="friends[]" class="form-control friend-input" placeholder="Friend's Name" required onkeyup="fetchSuggestions(this)">
                <div class="autocomplete-list"></div>
                <button type="button" class="btn btn-danger" onclick="removeFriendField(this)">&#x2715;</button>
                <button type="button" class="btn btn-primary" onclick="addFriendField()">&#x2795;</button>
            `;

            container.appendChild(div);
            updateButtons();
        }

        function removeFriendField(button) {
            button.parentElement.remove();
            updateButtons();
        }

        function updateButtons() {
            let fields = document.querySelectorAll("#friends-container .input-group");
            fields.forEach((field, index) => {
                let buttons = field.querySelectorAll("button");
                buttons[1].style.display = index === fields.length - 1 ? "inline-block" : "none"; // Show + only on last field
            });
        }

        function fetchSuggestions(inputField) {
            let query = inputField.value.trim();
            let listContainer = inputField.nextElementSibling;

            if (query.length < 1) {
                listContainer.innerHTML = "";
                listContainer.style.display = "none";
                return;
            }

            // Get already selected friends
            let selectedFriends = [];
            document.querySelectorAll("input[name='friends[]']").forEach(input => {
                if (input.value.trim() !== "") {
                    selectedFriends.push(input.value.trim());
                }
            });

            $.ajax({
                url: 'fetch_users.php',
                method: 'POST',
                data: {
                    search: query,
                    selectedFriends: JSON.stringify(selectedFriends) // Send as JSON string
                },
                success: function (response) {
                    if (response.trim() !== "") {
                        listContainer.innerHTML = response;
                        listContainer.style.display = "block";
                        adjustDropdownPosition(inputField, listContainer);
                    } else {
                        listContainer.innerHTML = "";
                        listContainer.style.display = "none";
                    }
                }
            });
        }

        function adjustDropdownPosition(inputField, listContainer) {
            let rect = inputField.getBoundingClientRect();
            let parentRect = inputField.closest('.input-group').getBoundingClientRect();

            listContainer.style.top = (rect.bottom - parentRect.top) + "px";
            listContainer.style.left = "0";
            listContainer.style.width = rect.width + "px";
            listContainer.style.position = "absolute";
            listContainer.style.zIndex = "1000";
        }

        function selectSuggestion(name, inputField) {
            inputField.value = name;
            inputField.nextElementSibling.innerHTML = "";
            inputField.nextElementSibling.style.display = "none";
        }
    </script>

</head>

<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'day-night-toggler.php'; ?>

    <div id="content" class="content closed">
        <h2 class="text-left mb-4">Create a Group</h2>

        <?php echo $message; ?>

        <form method="post" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label>Group Name</label>
                <input type="text" name="group_name" class="form-control" required>
            </div>
            <!-- <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div> -->

            <h5 class="mb-3">Add Friends:</h5>
            <div id="friends-container">
                <div class="input-group mb-2 position-relative">
                    <input type="text" name="friends[]" class="form-control friend-input" placeholder="Friend's Name"
                        required onkeyup="fetchSuggestions(this)">
                    <div class="autocomplete-list"></div>
                    <button type="button" class="btn btn-primary" onclick="addFriendField()">&#x2795;</button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 mt-3">Create Group</button>
        </form>
    </div>
</body>

</html>
