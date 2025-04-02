<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login-Signup/login.html");
    exit();
}

include 'db_connect.php';

$booking_id = intval($_GET['booking_id']);
$customer_id = $_SESSION['user_id'];

// Fetch provider ID for the booking
$query = "SELECT provider_id FROM bookings WHERE id = ? AND booking_status = 'completed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    $_SESSION['error'] = "Invalid booking!";
    header("Location: ../Dashboard/customer_dashboard.php");
    exit();
}

$provider_id = $row['provider_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 90%;
            max-width: 600px;
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            font-size: 28px;
            color: var(--dark-blue);
            margin-bottom: 20px;
        }


        .error-msg {
            color: red;
            font-weight: 600;
            margin-bottom: 10px;
        }


        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
            margin-bottom: 10px;
        }

        textarea {
            height: 120px;
        }

        button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: var(--gold);
            color: var(--dark-blue);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        button:hover {
            background-color: var(--dark-blue);
            color: var(--white);
        }


        .back-link {
            display: block;
            margin-top: 15px;
            color: var(--gray);
            font-weight: bold;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Submit Feedback</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error-msg"><?= $_SESSION['error'];
                                    unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <form action="save_feedback.php" method="POST">
            <input type="hidden" name="booking_id" value="<?= $booking_id; ?>">
            <input type="hidden" name="provider_id" value="<?= $provider_id; ?>">

            <label>Rating (1-5):</label>
            <select name="rating" required>
                <option value="" disabled selected>Select Rating</option>
                <option value="1">1 - Very Bad</option>
                <option value="2">2 - Bad</option>
                <option value="3">3 - Average</option>
                <option value="4">4 - Good</option>
                <option value="5">5 - Excellent</option>
            </select>

            <label>Review:</label>
            <textarea name="review" placeholder="Write your review here..." required></textarea>

            <button type="submit">Submit Feedback</button>
        </form>

        <a href="../Dashboard/customer_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

</body>

</html>