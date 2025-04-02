<?php
session_start();
include 'db_connect.php';

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'service_provider') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Access Denied!',
                text: 'You must be logged in as a service provider to access this page.',
                icon: 'warning',
                confirmButtonText: 'Login Now'
            }).then(() => {
                window.location.href = '../Login-Signup/login.html';
            });
        });
    </script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);

    // Update booking status to completed
    $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();

    // Mark payment as completed (since it's COD)
    $stmt = $conn->prepare("UPDATE payments SET payment_status = 'completed' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();

    // Show success message
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Service Completed!',
                text: 'The service has been successfully marked as completed, and payment has been collected.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '../Dashboard/provider_dashboard.php';
            });
        });
    </script>";
    exit();
}
?>
