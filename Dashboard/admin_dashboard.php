<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../Login-Signup/login.html");
    exit();
}

include '../PHP/db_connect.php';

// Fetch system stats
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE user_type = 'customer') AS total_customers,
        (SELECT COUNT(*) FROM users WHERE user_type = 'service_provider') AS total_providers,
        (SELECT COUNT(*) FROM bookings) AS total_bookings
";
$stats_result = $conn->query($stats_query)->fetch_assoc();

// Fetch pending providers
$providers_query = "SELECT id, full_name, email FROM users WHERE user_type = 'service_provider' AND approval_status = 'pending'";
$providers_result = $conn->query($providers_query);

// Handle provider approval
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve'])) {
    $id = intval($_POST['user_id']);
    $status = $_POST['approve'];

    if (in_array($status, ['approved', 'rejected'])) {
        $stmt = $conn->prepare("UPDATE users SET approval_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Service provider successfully " . ucfirst($status) . "!";
        } else {
            $_SESSION['error'] = "Error updating provider status!";
        }
        header("Location: admin_dashboard.php");
        exit();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');


        :root {
            --gold: #EFC75E;
            --dark-blue: #1E2744;
            --white: #FFFFFF;
            --gray: #2C3E50;
            --light-gray: #f4f6f9;
            --green: #2ecc71;
            --red: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Inter", sans-serif;
        }

        body {
            display: flex;
            background-color: var(--light-gray);
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
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        .header {
            background-color: var(--white);
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 22px;
            font-weight: 600;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
            justify-content: center;
        }

        .card {
            background: linear-gradient(135deg, var(--gold), var(--dark-blue));
            color: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            flex: 1 1 250px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
        }

        .statistics {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }

        .stat-box {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex: 1 1 300px;
        }

        .stat-box h4 {
            font-size: 18px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .stat-box p {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark-blue);
        }

 
        table {
            width: 100%;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
            margin-top: 20px;
            overflow: hidden;
        }

        table th,
        table td {
            padding: 18px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
        }

        thead {
            background: linear-gradient(135deg, var(--dark-blue), var(--gold));
            color: var(--white);
        }

        .btn {
            padding: 12px 18px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 15px;
            font-weight: 600;
            color: var(--white);
            transition: 0.3s;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
        }

        .btn-approve {
            background-color: var(--green);
        }

        .btn-reject {
            background-color: var(--red);
        }

        .btn:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

  
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .stats {
                flex-direction: column;
                align-items: center;
            }

            table {
                overflow-x: auto;
                display: block;
            }

            table th,
            table td {
                padding: 12px;
                font-size: 14px;
            }

            .toggle-btn {
                display: block;
            }

            .sidebar {
                width: 0;
                overflow: hidden;
            }

            .sidebar.active {
                width: 200px;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="../PHP/generate_reports.php">Generate Reports</a></li>
            <li><a href="../PHP/logout.php" class="logout">Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <div class="header">Welcome, Admin</div>

        <h2>System Statistics</h2>
        <div class="stats">
            <div class="card">
                <h3>Total Customers</h3>
                <p><?= $stats_result['total_customers']; ?></p>
            </div>
            <div class="card">
                <h3>Total Service Providers</h3>
                <p><?= $stats_result['total_providers']; ?></p>
            </div>
            <div class="card">
                <h3>Total Bookings</h3>
                <p><?= $stats_result['total_bookings']; ?></p>
            </div>
        </div>

        <h2>Pending Service Provider Approvals</h2>
        <?php if ($providers_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $providers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= $row['full_name']; ?></td>
                            <td><?= $row['email']; ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                    <button type="submit" name="approve" value="approved" class="btn btn-approve">Approve</button>
                                    <button type="submit" name="approve" value="rejected" class="btn btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending service providers.</p>
        <?php endif; ?>
    </div>
</body>

</html>