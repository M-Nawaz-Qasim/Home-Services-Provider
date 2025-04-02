<?php

session_start();


$host = 'localhost';
$db = 'home_care';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    $service_id = isset($_POST['service_id']) ? $_POST['service_id'] : NULL;
    $expertise_proof = NULL;
    $approval_status = ($user_type === 'service_provider') ? 'pending' : 'approved'; // Customers are auto-approved

    if ($password !== $confirm_password) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Passwords do not match. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'Retry'
                }).then(() => {
                    window.history.back(); // Go back to the signup page
                });
            });
        </script>";
        exit();
    }

    // Hash the password after validation
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Handle file upload for service providers
    if ($user_type == 'service_provider' && isset($_FILES['expertise_proof'])) {
        $target_dir = "../Uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["expertise_proof"]["name"]);
        if (move_uploaded_file($_FILES["expertise_proof"]["tmp_name"], $target_file)) {
            $expertise_proof = $target_file;
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Upload Failed!',
                        text: 'Error uploading expertise proof. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
            exit();
        }
    }

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (full_name, contact_number, email, password, user_type, service_id, expertise_proof, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $full_name, $contact_number, $email, $hashed_password, $user_type, $service_id, $expertise_proof, $approval_status);

    if ($stmt->execute()) {
        if ($user_type === 'customer') {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Signup Successful!',
                        text: 'Welcome, $full_name! You can now log in.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '../Login-Signup/login.html';
                    });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Signup Successful!',
                        text: 'Your account is pending admin approval.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '../Login-Signup/login.html';
                    });
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Signup failed. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'Retry'
                });
            });
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
