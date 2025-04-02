<?php
session_start();
include '../PHP/db_connect.php';

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    
    $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Booking Cancelled!',
                    text: 'Your booking has been cancelled successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../Dashboard/customer_dashboard.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Cancellation Failed!',
                    text: 'An error occurred while cancelling your booking. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'Retry'
                }).then(() => {
                    window.location.href = '../Dashboard/customer_dashboard.php';
                });
            });
        </script>";
    }
    exit();
}
?>
