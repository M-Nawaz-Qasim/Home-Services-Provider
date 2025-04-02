<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'home_care';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure SweetAlert2 is loaded
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if the user is admin
    if ($email === 'admin@gmail.com' && $password === 'admin') {
        $_SESSION['user_type'] = 'admin';
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Login Successful!',
                    text: 'Welcome Admin!',
                    icon: 'success',
                    confirmButtonText: 'Proceed'
                }).then(() => {
                    window.location.href = '../Dashboard/admin_dashboard.php';
                });
            });
        </script>";
        exit();
    }

    // Check for regular users
    $stmt = $conn->prepare("SELECT id, full_name, password, user_type, approval_status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $full_name, $hashed_password, $user_type, $approval_status);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        if ($user_type === 'service_provider' && $approval_status !== 'approved') {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Pending Approval!',
                        text: 'Your account is pending admin approval.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '../Login-Signup/login.html';
                    });
                });
            </script>";
            exit();
        }

        $_SESSION['user_id'] = $id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['user_type'] = $user_type;

        // Redirect based on user type
        if ($user_type === 'customer') {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Login Successful!',
                        text: 'Welcome, $full_name!',
                        icon: 'success',
                        confirmButtonText: 'Proceed'
                    }).then(() => {
                        window.location.href = '../Main/Home.html';
                    });
                });
            </script>";
        } elseif ($user_type === 'service_provider') {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Login Successful!',
                        text: 'Welcome, $full_name!',
                        icon: 'success',
                        confirmButtonText: 'Proceed'
                    }).then(() => {
                        window.location.href = '../Dashboard/provider_dashboard.php';
                    });
                });
            </script>";
        }
        exit();
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Login Failed!',
                    text: 'Invalid email or password.',
                    icon: 'error',
                    confirmButtonText: 'Retry'
                }).then(() => {
                    window.location.href = '../Login-Signup/login.html';
                });
            });
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
