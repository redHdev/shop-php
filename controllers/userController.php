<?php

// Include the db.php file to make use of the $connection variable
require 'includes/db.php';

// Include Composer's autoloader
require 'vendor/autoload.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

use GuzzleHttp\Client;


function signUpfunction($params) {
    global $connection;

    $valid = validationSignUpParams($params);

    if(!$valid["status"]){
        echo json_encode($valid);
        return;
    }

    $name = $params["name"];
    $email = $params["email"];
    $username = $params["username"];
    $password = $params["password"];
    $passwordConfirm = $params["passwordConfirm"];

    $where = "emailAddress = '".$email."'";
    $isExitUser = getUsers($where);

    if($isExitUser){
        echo json_encode(array(
            "status" => false,
            "message" => "This user has already existed"
        ));
        return;
    }

    // Hash the user's password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $stmt = $connection->prepare("INSERT INTO users (emailAddress, name, userName, password, createdAt, verifyStatus, status) VALUES (?, ?, ?, ?, NOW(), 0, 1)"); // assuming verifyStatus is 0 for unverified and status is 1 for active
        $stmt->execute([$email, $name, $username, $hashedPassword]);

        $user = array(
            "emailAddress" => $email,
            "name" => $name,
        );

        $_SESSION['user'] = $user;
        // Send the verification email
        sendVerificationEmail($email);

        echo json_encode(array(
            "status" => true,
            "message" => "User successfully registered! Check your email for verification."
        ));

    } catch(PDOException $e) {
        // Handle potential errors here
        echo json_encode(array(
            "status" => false,
            "message" => "There was an error in the registration process."
        ));
    }



}

function signInfunction($params) {

}

function getUsers($where) {
    global $connection;

    $query = "SELECT * FROM users WHERE ".$where;

    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

function validationSignUpParams($params) {

    $name = $params["name"];
    $email = $params["email"];
    $username = $params["username"];
    $password = $params["password"];
    $passwordConfirm = $params["passwordConfirm"];

    if($name == ""){
        return array(
            "status" => false,
            "message" => "Name field is required."
        );
    }

    if($email == ""){
        return array(
            "status" => false,
            "message" => "Email field is required."
        );
    }

    if(!isValidEmail($email)){
        return array(
            "status" => false,
            "message" => "Email is not valid email."
        );
    }

    if($password == ""){
        return array(
            "status" => false,
            "message" => "Password field is required."
        );
    }

    if($passwordConfirm == ""){
        return array(
            "status" => false,
            "message" => "Confirm Password field is required."
        );
    }

    if($password != $passwordConfirm) {
        return array(
            "status" => false,
            "message" => "Confirm Password is failed."
        );
    }

    return array(
        "status" => true
    );
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sendVerificationEmail($email) {
    global $connection;

    // After successfully inserting the user:
    $verificationCode = rand(100000, 999999); // Generates a random 6-digit number
    $tokenExpiration = date("Y-m-d H:i:s", strtotime("+24 hours")); // Token valid for 24 hours

    // Store the token in the database
    $stmt = $connection->prepare("UPDATE users SET verificationCode = ?, tokenExpiration = ? WHERE emailAddress = ?");
    $stmt->execute([$verificationCode, $tokenExpiration, $email]);

    $client = new Client(['base_uri' => 'https://api.nylas.com/']);

    $headers = [
        'Authorization' => 'Bearer '.$_ENV['NYLAS_ACCESS_TOKEN'], // Replace with your token
        'Content-Type' => 'application/json',
    ];

    $htmlBody = "
                <html>
                <head>
                    <title>Email Verification</title>
                </head>
                <body>
                    <img src='assets/img/shop-logo.png' alt='UK Banker Logo'/>
                    <p>Thank you for registering with us! To verify your email, please enter the following code:</p>
                    <h1>{$verificationCode}</h1>
                    <p>If you didn't request this, please ignore this email.</p>
                    <div><p>Didn't receive the code? <a href='".$_ENV['SITE_URL']."verify/resend?emailAdress=".base64_encode($email)."'>Click here</a> to resend.</p></div>
                </body>
                </html>
            ";

    $payload = [
        'to' => [['email' => $email]],
        'subject' => 'Verify your email',
        'body' => $htmlBody,
        'body_type' => 'html'  // Specify the body type as HTML
    ];

    try {
        $response = $client->post('send', [
            'headers' => $headers,
            'body' => json_encode($payload)
        ]);

        // Return the response if needed
        return json_decode($response->getBody(), true);
    } catch (Exception $e) {
        // Handle the error appropriately
        echo "Error sending email: " . $e->getMessage();
        return false;
    }

}

function verificationWithCode($params) {

    global $connection;

    if(!isset($_SESSION['user'])){
        echo json_encode(array(
            "status" => false,
            "message" => "The session has destroyed."
        ));
        return;
    }

    $verificationCode = $params['first'].$params['second'].$params['third'].$params['fourth'].$params['fifth'].$params['sixth'];
    if( strlen($verificationCode) < 6 ) {
        echo json_encode(array(
            "status" => false,
            "message" => "Verification Code is invalid."
        ));
        return;
    }

    $emailAddress = $_SESSION['user']['emailAddress'];

    $where = "emailAddress = '".$emailAddress."'";
    $currentUserData = getUsers($where);

    $tokenExpiration = $currentUserData["tokenExpiration"];
    $currentTimestamp = new DateTime();

    if($verificationCode != $currentUserData['verificationCode']){
        echo json_encode(array(
            'status' => false,
            "message" => "Verification Code is not matched. Please verify again with new code"
        ));
        return;
    }

    if($currentTimestamp <= $tokenExpiration) {
        echo json_encode(array(
            'status' => false,
            "message" => "Verification Code is not available. Please verify again with new code"
        ));
        return;
    }

    $stmt = $connection->prepare("UPDATE users SET verifyStatus = ? WHERE emailAddress = ?");
    $stmt->execute([1, $emailAddress]);

    echo json_encode(array(
        'status' => true,
        'message' => "Verification succed"
    ));
    return;

    



}