<?php
session_start();
include 'db_connect.php';

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Login Required!',
                text: 'You must log in to book a service.',
                icon: 'warning',
                confirmButtonText: 'Login Now'
            }).then(() => {
                window.location.href = '../Login-Signup/login.html';
            });
        });
    </script>";
    exit();
}

$customer_id = $_SESSION['user_id'];
$service_name = $_POST['service_name'] ?? null;
$provider_id = $_POST['service_provider'] ?? null;
$booking_date = $_POST['date'] ?? null;
$address = $_POST['address'] ?? null;
$additional_info = $_POST['additional_info'] ?? null;
$amount = 100.00; 

if (!$service_name || !$provider_id || !$booking_date || !$address) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Missing Information!',
                text: 'Please fill all required fields before proceeding.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '../Main/services.html';
            });
        });
    </script>";
    exit();
}


$service_query = $conn->prepare("SELECT id FROM services WHERE service_name = ?");
$service_query->bind_param("s", $service_name);
$service_query->execute();
$service_result = $service_query->get_result();

if ($service_result->num_rows == 0) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Invalid Selection!',
                text: 'The selected service is not available.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '../Main/services.html';
            });
        });
    </script>";
    exit();
}

$service = $service_result->fetch_assoc();
$service_id = $service['id'];

$conn->begin_transaction();

try {
    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (customer_id, service_id, provider_id, booking_date, address, additional_info, payment_status, booking_status) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending')");
    $stmt->bind_param("iiisss", $customer_id, $service_id, $provider_id, $booking_date, $address, $additional_info);
    $stmt->execute();
    $booking_id = $stmt->insert_id;

    // Insert payment (Cash on Delivery only)
    $stmt = $conn->prepare("INSERT INTO payments (booking_id, customer_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'cash_on_delivery', 'pending')");
    $stmt->bind_param("iid", $booking_id, $customer_id, $amount);
    $stmt->execute();

    $conn->commit();

    // Success message using SweetAlert2
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Booking Confirmed!',
                text: 'Your booking has been successfully placed. Payment will be collected after service completion.',
                icon: 'success',
                confirmButtonText: 'Go to Dashboard'
            }).then(() => {
                window.location.href = '../Dashboard/customer_dashboard.php';
            });
        });
    </script>";
} catch (Exception $e) {
    $conn->rollback();


    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Booking Failed!',
                text: 'An error occurred while processing your request. Please try again later.',
                icon: 'error',
                confirmButtonText: 'Retry'
            }).then(() => {
                window.location.href = '../Main/services.html';
            });
        });
    </script>";
}

$conn->close();
exit();
?>
