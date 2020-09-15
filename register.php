<?php
//connect info
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'root';
$DATABASE_NAME = 'phplogin';

//connect using info above
$con =mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if(mysqli_connect_errno($con)){
    //if there is no connection, stop the script and display error
    exit('Failed to connect to MySQL' . mysqli_connect_error());
}

//basic validation
//check if data is submitted, isset() function will check if the data exist;
if(!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    //could not get the data that should have been submitted
    exit('Please complete the registration form');
}
//check if the submitted registration form values are not empty;
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
    //one or more values are empty.
    exit('PLease complete the registration form');
}


// Email validation
if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    exit('Email is not valid');
}

//Username validation
if(preg_match('/^[A-Za-z0-9]+/', $_POST['username']) == 0) {
    exit('Username is not valid');
}

//password length validation
if(strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
    exit('Password must be between 5 and 20 characters long');
}

if($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
    //bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->store_result();
    //Store the data so we can check if the account exists in the database.
    if($stmt->num_rows > 0) {
        //usename already exists
        echo 'Username exists, please choose another!';
    } else {
        if($stmt->prepare('INSERT INTO accounts (username, password, email, activation_code) VALUES (?, ?, ?, ?)')) {
            //Hash the password using the PHP password_hash function because we dont want to expose the password in the database.
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $uniqid = uniqid();
            $stmt->bind_param('ssss', $_POST['username'], $password, $_POST['email'], $uniqid);
            $stmt->execute();
            
            //send email validation
            $from    = 'noreply@yourdomain.com';
            $subject = 'Account Activation Required';
            $headers = 'From: ' . $from . "\r\n" . 'Reply-to: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
            $activate_link = 'http://yourdomain.com/phplogin/activate.php?email=' . $_POST['email'] . '&code=' . $uniqid;
            $message = '<p>Please click the following link to activate your account: < href="' . $activate_link . '">' . $activate_link . '</a></p>';
            mail($_POST[email], $subject, $message, $headers);
            echo 'Please check your email to activate your account!';
        } else {
            //something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields
            echo 'Could not prepare SQL statement';
        }
    }
    $stmt->close();
} else {
    echo 'Could not prepare statement';
}
$con->close();
?>