<?php

include '../PHP/db_connect.php';

// Default values for filtering
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$service_filter = $_POST['service_id'] ?? '';
$provider_filter = $_POST['provider_id'] ?? '';
$booking_status = $_POST['booking_status'] ?? '';

// Build query with dynamic filters
$query = "SELECT b.id, s.service_name, u.full_name AS provider_name, b.booking_date, b.booking_status
          FROM bookings b
          JOIN services s ON b.service_id = s.id
          JOIN users u ON b.provider_id = u.id
          WHERE 1";  // Always true, allows easy dynamic filtering

// Apply filters if provided
$params = [];
$types = "";

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND b.booking_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

if (!empty($service_filter)) {
    $query .= " AND b.service_id = ?";
    $params[] = $service_filter;
    $types .= "i";
}

if (!empty($provider_filter)) {
    $query .= " AND b.provider_id = ?";
    $params[] = $provider_filter;
    $types .= "i";
}

if (!empty($booking_status)) {
    $query .= " AND b.booking_status = ?";
    $params[] = $booking_status;
    $types .= "s";
}

$stmt = $conn->prepare($query);

// Bind parameters dynamically
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch Services & Providers for filters
$services = $conn->query("SELECT id, service_name FROM services")->fetch_all(MYSQLI_ASSOC);
$providers = $conn->query("SELECT id, full_name FROM users WHERE user_type = 'service_provider'")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');

        :root {
            --gold: #EFC75E;
            --dark-blue: #1E2744;
            --white: #FFFFFF;
            --gray: #2C3E50;
            --light-gray: #f4f6f9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Inter", sans-serif;
        }

        body {
            background-color: var(--light-gray);
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background-color: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--dark-blue);
            text-align: center;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-form label {
            font-weight: bold;
        }

        .filter-form select,
        .filter-form input {
            padding: 8px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 15px;
            background-color: var(--dark-blue);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--gold);
            color: var(--dark-blue);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        thead {
            background-color: var(--dark-blue);
            color: var(--white);
        }

    </style>
</head>
<body>

    <div class="container">
        <h2>Generate Reports</h2>

        <form method="POST" class="filter-form">
            <label>Start Date:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date); ?>">

            <label>End Date:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date); ?>">

            <label>Service:</label>
            <select name="service_id">
                <option value="">All Services</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id']; ?>" <?= ($service_filter == $service['id']) ? 'selected' : ''; ?>>
                        <?= $service['service_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Service Provider:</label>
            <select name="provider_id">
                <option value="">All Providers</option>
                <?php foreach ($providers as $provider): ?>
                    <option value="<?= $provider['id']; ?>" <?= ($provider_filter == $provider['id']) ? 'selected' : ''; ?>>
                        <?= $provider['full_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Booking Status:</label>
            <select name="booking_status">
                <option value="">All Status</option>
                <option value="pending" <?= ($booking_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?= ($booking_status == 'approved') ? 'selected' : ''; ?>>Approved</option>
                <option value="completed" <?= ($booking_status == 'completed') ? 'selected' : ''; ?>>Completed</option>
            </select>

            <button type="submit" class="btn">Generate Report</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Service</th>
                    <th>Provider</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= $row['service_name']; ?></td>
                            <td><?= $row['provider_name']; ?></td>
                            <td><?= $row['booking_date']; ?></td>
                            <td><?= ucfirst($row['booking_status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <a href="../Dashboard/admin_dashboard.php" class="btn">← Back to Dashboard</a>
    </div>

</body>
</html>
