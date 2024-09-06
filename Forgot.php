<?php
require_once "Twilio/autoload.php"; 
use Twilio\Rest\Client;

$sid = 'ACbf8a66e7a249f642362aeb262641e097';
$token = 'be2c9d8cb23eabe2a5b2ea6ed218407e';
$twilio_number = '+14159925039'; 

$client = new Client($sid, $token);

function sendSMS($to, $message, $client, $twilio_number) {
    try {
        $message = $client->messages->create(
            $to,
            array(
                'from' => $twilio_number,
                'body' => $message
            )
        );

        return 'SMS sent successfully: ' . $message->sid;
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

function generateRandomString() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    $length = 8; 

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    require "connection.php"; 

    $id = $_POST['id'];
    $id = mysqli_real_escape_string($con, $id);

    $query = "SELECT mobile FROM students WHERE id='$id'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (isset($row['mobile'])) {
            $mobile = $row['mobile'];

            $to = '+91' . $mobile;

            $password = generateRandomString();

            $update_query = "UPDATE students SET password='$password' WHERE id='$id'";
            mysqli_query($con, $update_query);

            $message = 'Your new password is: ' . $password;
            $sms_response = sendSMS($to, $message, $client, $twilio_number);

            echo '<div id="response">' . $sms_response . '</div>';
            header("Location: StudentLogin.php");
        } else {
            echo "Error: Mobile number not found for this ID.";
        }
    } else {
        echo "Error: Student ID not found or mobile number not retrieved.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSMPS</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div id="welcome">Login</div>
    <div class="nav">
        <a href="Welcome.html">Back</a>
        <h3 style="margin-left:420px;color:red;background-color:white;width:350px;padding:2px;">NOTE: Your New Password will be sent to your registered <b><u>Mobile</u></b></h3>
    </div>
    <div class="container" style="margin-left: 100px;">
        <form method="post">
            <br><br><br><label for="StudentId">Student ID: </label>
            <input type="text" id="StudentId" name="id" placeholder="Enter Student Id"><br>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
