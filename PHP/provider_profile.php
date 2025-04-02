<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'service_provider') {
    header("Location: ../Login-Signup/login.html");
    exit();
}

include 'db_connect.php';
$provider_id = $_SESSION['user_id'];

// Fetch provider details
$query = "SELECT full_name, email, contact_number, service_id, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
$provider = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $contact_number = $_POST['contact_number'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "../Uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $target_file;
    } else {
        $profile_picture = $provider['profile_picture'];
    }

    $update_query = "UPDATE users SET full_name=?, contact_number=?, profile_picture=? WHERE id=?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $name, $contact_number, $profile_picture, $provider_id);
    $update_stmt->execute();
    header("Location: provider_profile.php?success=Profile updated successfully");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
        .profile-pic {
            display: block;
            margin: 10px auto;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Provider Profile</h2>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green; text-align: center;"> <?= $_GET['success']; ?> </p>
        <?php endif; ?>
        <img src="<?= $provider['profile_picture']  ?>" class="profile-pic">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= $provider['full_name']; ?>" required>
            </div>
            <div class="form-group">
                <label>Email (Cannot be changed)</label>
                <input type="email" value="<?= $provider['email']; ?>" disabled>
            </div>
            <div class="form-group">
                <label>contact_number</label>
                <input type="text" name="contact_number" value="<?= $provider['contact_number']; ?>" required>
            </div>
            <div class="form-group">
                <label>service_id</label>
                <input type="text" value="<?= $provider['service_id']; ?>" disabled>
            </div>
            <div class="form-group">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture">
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
</body>
</html>
