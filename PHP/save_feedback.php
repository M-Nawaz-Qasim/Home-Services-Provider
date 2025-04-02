<?php
session_start();
include 'db_connect.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

$feedbackSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_SESSION['user_id'];
    $booking_id = $_POST['booking_id'];
    $provider_id = $_POST['provider_id'];
    $rating = $_POST['rating'];
    $review = trim($_POST['review']);

    $stmt = $conn->prepare("INSERT INTO feedback (booking_id, customer_id, provider_id, rating, review) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $booking_id, $customer_id, $provider_id, $rating, $review);

    if ($stmt->execute()) {
        $feedbackSuccess = true;
    }

    $stmt->close();
    $conn->close();
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($feedbackSuccess): ?>
        Swal.fire({
            title: "Feedback Submitted!",
            text: "Thank you for your feedback!",
            icon: "success",
            confirmButtonText: "OK"
        }).then(() => {
            window.location.href = "../Dashboard/customer_dashboard.php";
        });
    <?php else: ?>
        Swal.fire({
            title: "Error!",
            text: "Failed to submit feedback. Please try again.",
            icon: "error",
            confirmButtonText: "Retry"
        }).then(() => {
            window.location.href = "../Dashboard/customer_dashboard.php";
        });
    <?php endif; ?>
});
</script>
