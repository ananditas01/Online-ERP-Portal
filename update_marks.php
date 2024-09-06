<?php

header('Content-Type: application/json');
require "connection.php"; 
require_once "Twilio/autoload.php";
use Twilio\Rest\Client;

$response = [];

if (isset($_POST['student_id']) && isset($_POST['semester']) && isset($_POST['marks'])) {
    $student_id = $_POST['student_id'];
    $semester = $_POST['semester'];
    $marks = json_decode($_POST['marks'], true);

    $query = "SELECT * FROM students WHERE id='$student_id'";
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $name = $row['name'];
            $mobile = $row['mobile']; 

            if (!preg_match('/^[0-9]{10}$/', $mobile)) {
                $response['error'] = 'Invalid phone number format. Expected 10 digits without country code.';
                echo json_encode($response);
                exit;
            }

            $mobile = '+91' . $mobile;

            if ($semester == 6) {
                $compilerDesign = $marks['sub1'];
                $softwareEngineering = $marks['sub2'];
                $computerNetworks = $marks['sub3'];
                $fullStackWebDevelopment = $marks['sub4'];
                $careerSkills = $marks['sub5'];
                $elective = $marks['sub6'];
                $compilerDesignLab = $marks['sub7'];
                $softwareEngineeringLab = $marks['sub8'];
                $fullStackWebDevelopmentLab = $marks['sub9'];
                $cgpa = $marks['cgpa'];

                $sql = "UPDATE Sem6 SET 
                            CompilerDesign=?, 
                            SoftwareEngineering=?, 
                            ComputerNetworks=?, 
                            FullStackWebDevelopment=?, 
                            CareerSkills=?, 
                            Elective=?, 
                            CompilerDesignLab=?, 
                            SoftwareEngineeringLab=?, 
                            FullStackWebDevelopmentLab=?, 
                            Cgpa=? 
                        WHERE id=?";

                $stmt = $con->prepare($sql);
                $stmt->bind_param("ssssssssssi", $compilerDesign, $softwareEngineering, $computerNetworks, $fullStackWebDevelopment, $careerSkills, $elective, $compilerDesignLab, $softwareEngineeringLab, $fullStackWebDevelopmentLab, $cgpa, $student_id);

                if ($stmt->execute()) {
                    $response['success'] = 'Marks updated successfully';

                    $account_sid = 'your_sid';
                    $auth_token = 'your_token';   
                    $twilio_number = 'xxxxxxxxxxx';    
                    $client = new Client($account_sid, $auth_token);

                    try {
                        $message = $client->messages->create(
                            $mobile, 
                            [
                                'from' => $twilio_number, 
                                'body' => "Dear Parent, Your ward's result for Semester ". $semester." is out on ERP portal. Your ward has scored a Cgpa of ".$cgpa ." . Kindly access the portal to view your result."
                            ]
                        );

                        $response['sms_response'] = 'SMS sent successfully: ' . $message->sid;
                    } catch (Exception $e) {
                        $response['sms_error'] = 'SMS Error: ' . $e->getMessage();
                    }
                } else {
                    $response['error'] = 'Error updating marks: ' . $stmt->error;
                }
            } else {
                $response['error'] = 'Invalid semester';
            }
        } else {
            $response['error'] = 'Student not found';
        }
    } else {
        $response['error'] = 'Error fetching student data: ' . mysqli_error($con);
    }

    $con->close();
} else {
    $response['error'] = 'Invalid input';
}

echo json_encode($response);

?>
