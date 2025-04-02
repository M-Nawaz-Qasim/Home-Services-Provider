<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login-Signup/login.html");
    exit();
}

include '../PHP/db_connect.php';
$customer_id = $_SESSION['user_id'];

// Fetch Customer's Bookings
$query = "SELECT b.id, s.service_name, u.full_name AS provider_name, b.booking_date, b.booking_status 
          FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          JOIN users u ON b.provider_id = u.id 
          WHERE b.customer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch Customer's Feedback and Provider Responses
$feedback_query = "SELECT f.rating, f.review, f.response, s.service_name, u.full_name AS provider_name 
                   FROM feedback f
                   JOIN bookings b ON f.booking_id = b.id
                   JOIN services s ON b.service_id = s.id
                   JOIN users u ON f.provider_id = u.id
                   WHERE f.customer_id = ?";
$stmt_feedback = $conn->prepare($feedback_query);
$stmt_feedback->bind_param("i", $customer_id);
$stmt_feedback->execute();
$feedback_result = $stmt_feedback->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
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
        }


        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }


        table {
            width: 100%;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        thead {
            background-color: var(--dark-blue);
            color: var(--white);
        }


        .btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: var(--white);
            text-decoration: none;
        }

        .btn-cancel {
            background-color: #e74c3c;
        }

        .btn-feedback {
            background-color: #2ecc71;
        }

        .disabled {
            color: gray;
            font-weight: bold;
        }
    </style>
</head>

<body>


    <div class="sidebar">
        <h2>Customer Panel</h2>
        <ul>
            <li><a href="customer_dashboard.php">My Bookings</a></li>
            <li><a href="../PHP/logout.php" class="logout">Logout</a></li>
        </ul>
    </div>


    <div class="main">
        <div class="header">
            <h1>Welcome, <?= $_SESSION['full_name']; ?></h1>
        </div>

        <h2>My Bookings</h2>

        <p>
            <?php
            if (isset($_SESSION['success'])) {
                echo '<span class="success-message">' . $_SESSION['success'] . '</span>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<span class="error-message">' . $_SESSION['error'] . '</span>';
                unset($_SESSION['error']);
            }
            ?>
        </p>

        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Provider</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['service_name']; ?></td>
                        <td><?= $row['provider_name']; ?></td>
                        <td><?= $row['booking_date']; ?></td>
                        <td><?= ucfirst($row['booking_status']); ?></td>
                        <td>
                            <?php if ($row['booking_status'] == 'pending'): ?>
                                <form action="../PHP/cancel_booking.php" method="POST">
                                    <input type="hidden" name="booking_id" value="<?= $row['id']; ?>">
                                    <button type="submit" class="btn btn-cancel">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="disabled">Cannot Cancel</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['booking_status'] == 'completed'): ?>
                                <a href="../PHP/submit_feedback.php?booking_id=<?= $row['id']; ?>" class="btn btn-feedback">Give Feedback</a>
                            <?php else: ?>
                                <span class="disabled">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>My Feedbacks</h2>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Provider</th>
                    <th>Rating</th>
                    <th>Your Review</th>
                    <th>Provider's Response</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $feedback_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['service_name']; ?></td>
                        <td><?= $row['provider_name']; ?></td>
                        <td><?= $row['rating']; ?> / 5</td>
                        <td><?= $row['review']; ?></td>
                        <td>
                            <?= !empty($row['response']) ? $row['response'] : '<span style="color:gray;">No response yet</span>'; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>


</body>

</html>