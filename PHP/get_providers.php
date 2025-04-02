<?php
include 'db_connect.php';

$service_name = $_GET['service_name'] ?? null;

if (!$service_name) {
    echo json_encode([]);
    exit();
}

// Fetch the service ID based on the name
$service_query = $conn->prepare("SELECT id FROM services WHERE service_name = ?");
$service_query->bind_param("s", $service_name);
$service_query->execute();
$service_result = $service_query->get_result();

if ($service_result->num_rows == 0) {
    echo json_encode([]);
    exit();
}

$service = $service_result->fetch_assoc();
$service_id = $service['id'];

// Fetch available providers for the selected service
$providers_query = $conn->prepare("SELECT id, full_name FROM users WHERE user_type = 'service_provider' AND service_id = ? AND approval_status = 'approved'");
$providers_query->bind_param("i", $service_id);
$providers_query->execute();
$providers_result = $providers_query->get_result();

$providers = [];
while ($row = $providers_result->fetch_assoc()) {
    $providers[] = $row;
}

echo json_encode($providers);
?>
