<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'service_provider') {
    header("Location: ../Login-Signup/login.html");
    exit();
}

include 'db_connect.php';
$provider_id = $_SESSION['user_id'];

$query = "SELECT f.id, f.rating, f.review, u.full_name AS customer_name, f.response 
          FROM feedback f 
          JOIN users u ON f.customer_id = u.id 
          WHERE f.provider_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            max-width: 900px;
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


        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table thead {
            background-color: var(--dark-blue);
            color: var(--white);
        }

        table tbody tr:hover {
            background-color: var(--light-gray);
        }


        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        button {
            margin-top: 10px;
            padding: 8px 15px;
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
    </style>
</head>

<body>

    <div class="container">
        <h1>Customer Feedback</h1>

        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Response</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['customer_name']); ?></td>
                        <td>⭐ <?= htmlspecialchars($row['rating']); ?>/5</td>
                        <td><?= htmlspecialchars($row['review']); ?></td>
                        <td><?= $row['response'] ? htmlspecialchars($row['response']) : '<i>No response yet</i>'; ?></td>
                        <td>
                            <form onsubmit="return confirmResponse(event, this)">
                                <input type="hidden" name="feedback_id" value="<?= $row['id']; ?>">
                                <textarea name="response" placeholder="Write a response..." required></textarea>
                                <button type="submit">Respond</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <script>
        function confirmResponse(event, form) {
            event.preventDefault();
            Swal.fire({
                title: "Are you sure?",
                text: "You are about to respond to this feedback.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, submit",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = "respond_feedback.php"; 
                    form.method = "POST";
                    form.submit();
                }
            });
        }
    </script>

</body>

</html>
