<?php
session_start();
include '../PHP/db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in and is a service provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'service_provider') {
    header("Location: ../Login-Signup/login.html");
    exit();
}

$updateSuccess = false;
$statusMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['booking_id']) && isset($_POST['action'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['action'];

    if (in_array($status, ['approved', 'rejected', 'completed'])) {
        $stmt = $conn->prepare("UPDATE bookings SET booking_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $booking_id);

        if ($stmt->execute()) {
            $updateSuccess = true;
            $statusMessage = "Booking successfully " . ucfirst($status) . "!";
        } else {
            $statusMessage = "Error updating booking!";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($updateSuccess): ?>
        Swal.fire({
            title: "Success!",
            text: "<?= $statusMessage ?>",
            icon: "success",
            confirmButtonText: "OK"
        }).then(() => {
            window.location.href = "../Dashboard/provider_dashboard.php";
        });
    <?php else: ?>
        Swal.fire({
            title: "Error!",
            text: "<?= $statusMessage ?>",
            icon: "error",
            confirmButtonText: "Retry"
        }).then(() => {
            window.location.href = "../Dashboard/provider_dashboard.php";
        });
    <?php endif; ?>
});
</script>
