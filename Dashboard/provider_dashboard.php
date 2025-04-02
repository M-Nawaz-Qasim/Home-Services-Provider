<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'service_provider') {
  header("Location: ../Login-Signup/login.html");
  exit();
}

include '../PHP/db_connect.php';
$provider_id = $_SESSION['user_id'];

// Fetch Profile Picture
$profile_query = "SELECT profile_picture FROM users WHERE id = ?";
$profile_stmt = $conn->prepare($profile_query);
$profile_stmt->bind_param("i", $provider_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result()->fetch_assoc();
$profile_picture = $profile_result['profile_picture'] ?? "https://via.placeholder.com/40";

// Fetch Statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM bookings WHERE provider_id = ? AND booking_status = 'completed') AS completed,
        (SELECT COUNT(*) FROM bookings WHERE provider_id = ? AND booking_status = 'pending') AS pending,
        (SELECT COUNT(*) FROM bookings WHERE provider_id = ?) AS total,
        (SELECT COUNT(*) FROM feedback WHERE provider_id = ?) AS feedback_count
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiii", $provider_id, $provider_id, $provider_id, $provider_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result()->fetch_assoc();

// Fetch Bookings
$bookings_query = "
    SELECT b.id, s.service_name, u.full_name AS customer_name, b.booking_date, b.booking_status 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    JOIN users u ON b.customer_id = u.id 
    WHERE b.provider_id = ? 
    ORDER BY b.booking_date DESC
";
$bookings_stmt = $conn->prepare($bookings_query);
$bookings_stmt->bind_param("i", $provider_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Provider Dashboard</title>
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


    .sidebar {
      width: 250px;
      height: 100vh;
      background-color: var(--dark-blue);
      color: var(--white);
      position: fixed;
      padding-top: 20px;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar ul li {
      padding: 15px;
      transition: 0.3s;
    }

    .sidebar ul li:hover {
      background-color: var(--gold);
      cursor: pointer;
    }

    .sidebar ul li a {
      color: var(--white);
      text-decoration: none;
      display: block;
    }

    .logout {
      color: red;
    }


    .main {
      margin-left: 250px;
      padding: 20px;
      background-color: var(--light-gray);
      min-height: 100vh;
    }


    .header {
      background-color: var(--white);
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      overflow: hidden;
      margin-top: 20px;
    }

    thead {
      background-color: var(--dark-blue);
      color: var(--white);
    }

    th,
    td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }




    .btn {
      padding: 8px 12px;
      border: none;
      cursor: pointer;
      border-radius: 5px;
      font-weight: bold;
      transition: 0.3s;
    }

    .btn-approve {
      background-color: #2ecc71;
      color: white;
    }

    .btn-approve:hover {
      background-color: #27ae60;
    }

    .btn-reject {
      background-color: #e74c3c;
      color: white;
    }

    .btn-reject:hover {
      background-color: #c0392b;
    }

    .btn-complete {
      background-color: #3498db;
      color: white;
    }

    .btn-complete:hover {
      background-color: #2980b9;
    }


    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .card {
      background: linear-gradient(135deg, var(--gold), var(--dark-blue));
      color: white;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      font-weight: bold;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease-in-out;
    }

    .card:hover {
      transform: scale(1.05);
    }

    .card h3 {
      margin-bottom: 8px;
      font-size: 18px;
    }

    .card p {
      font-size: 24px;
      font-weight: bold;
    }

    .profile {
      display: flex;
      align-items: center;
      gap: 8px;

    }

    .profile img {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #EFC75E;
    }

    .profile span {
      font-size: 14px;
      font-weight: 600;
      color: #1E2744;

    }
  </style>
</head>

<body>

  <div class="sidebar">
    <h2>Provider Panel</h2>
    <ul>
      <li><a href="provider_dashboard.php">Dashboard</a></li>
      <li><a href="../PHP/provider_profile.php">Profile</a></li>
      <li><a href="../PHP/provider_feedback.php">Feedback</a></li>
      <li><a href="../PHP/logout.php" class="logout">Logout</a></li>
    </ul>
  </div>


  <div class="main">
    <div class="header">
      <h1>Welcome, <?= $_SESSION['full_name']; ?></h1>
      <div class="profile">
        <img src="<?= $profile_picture; ?>" alt="Profile Picture">
        <span><?= $_SESSION['full_name']; ?></span>
      </div>
    </div>


    <div class="stats">
      <div class="card">
        <h3>Total Bookings</h3>
        <p><?= $stats_result['total']; ?></p>
      </div>
      <div class="card">
        <h3>Pending Bookings</h3>
        <p><?= $stats_result['pending']; ?></p>
      </div>
      <div class="card">
        <h3>Completed</h3>
        <p><?= $stats_result['completed']; ?></p>
      </div>
      <div class="card">
        <h3>Feedback</h3>
        <p><?= $stats_result['feedback_count']; ?></p>
      </div>
    </div>


    <h2>Recent Bookings</h2>
    <table>
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Service</th>
          <th>Customer</th>
          <th>Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $bookings_result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['service_name']; ?></td>
            <td><?= $row['customer_name']; ?></td>
            <td><?= $row['booking_date']; ?></td>
            <td><?= ucfirst($row['booking_status']); ?></td>
            <td>
              <?php if ($row['booking_status'] == 'pending'): ?>
                <form action="../PHP/update_booking.php" method="POST">
                  <input type="hidden" name="booking_id" value="<?= $row['id']; ?>">
                  <button type="submit" name="action" value="approved" class="btn btn-approve">Accept</button>
                  <button type="submit" name="action" value="rejected" class="btn btn-reject">Reject</button>
                </form>
              <?php elseif ($row['booking_status'] == 'approved'): ?>
                <form action="../PHP/complete_booking.php" method="POST">
                  <input type="hidden" name="booking_id" value="<?= $row['id']; ?>">
                  <button type="submit" name="action" value="completed" class="btn btn-complete">Complete</button>
                </form>
              <?php else: ?>
                <span class="disabled">N/A</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>

</html>