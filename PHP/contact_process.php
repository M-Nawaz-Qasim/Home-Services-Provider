<?php
include 'db_connect.php';

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['number']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Insert data into database
    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

    if ($stmt->execute()) {
        // Send confirmation email
        $to = $email;
        $subject = "Thank You for Contacting Us";
        $headers = "From: example@gmail.com\r\n";
        $headers .= "Reply-To: example@gmail.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $email_content = "<h2>Hi $name,</h2>";
        $email_content .= "<p>Thank you for reaching out to us. We have received your message and will get back to you soon.</p>";
        $email_content .= "<p><strong>Your Message:</strong><br>$message</p>";
        $email_content .= "<p>Best Regards,<br>AI Home Care Team</p>";

        mail($to, $subject, $email_content, $headers);

        echo "<script>alert('Your message has been sent successfully!'); window.location.href='../Main/contact-us.html';</script>";
    } else {
        echo "<script>alert('Error! Please try again later.'); window.location.href='../Main/contact-us.html';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
