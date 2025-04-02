<?php
session_start();
include 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$responseSuccess = false; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $provider_id = $_SESSION['user_id'];
    $feedback_id = $_POST['feedback_id'];
    $response = trim($_POST['response']);

    $stmt = $conn->prepare("UPDATE feedback SET response = ? WHERE id = ? AND provider_id = ?");
    $stmt->bind_param("sii", $response, $feedback_id, $provider_id);

    if ($stmt->execute()) {
        $responseSuccess = true;
    }

    $stmt->close();
    $conn->close();
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($responseSuccess): ?>
        Swal.fire({
            title: "Response Submitted!",
            text: "Your feedback response has been successfully recorded.",
            icon: "success",
            confirmButtonText: "OK"
        }).then(() => {
            window.location.href = "provider_feedback.php";
        });
    <?php else: ?>
        Swal.fire({
            title: "Error!",
            text: "Failed to submit your response. Please try again.",
            icon: "error",
            confirmButtonText: "Retry"
        }).then(() => {
            window.location.href = "provider_feedback.php";
        });
    <?php endif; ?>
});
</script>
