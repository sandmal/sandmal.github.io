<?php
//connection info
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'root';
$DATABASE_NAME = 'phplogin';

// try and connect using the database connection info

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    //if there is an error with the connection, stop the script and display error.
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

//first we check if the email and code exist...
if(isset($_POST['email'], $_GET['code'])) {
    if($stmt = $con->prepare('SELECT * FROM accounts WHERE email = ? AND activation_code = ?')) {
        $stmt->bind_param('ss', $_POST['email'], $_GET['code']);
        $stmt->execute();
        //store the result so we can check if the account exists in the database.
        $stmt->store_result();
        if($stmt->num_rows > 0) {
            //account exists with the requested email and code
            if($stmt->$con->prepare('UPDATE accounts SET activation_code = ? WHERE email = ? AND activation_code = ?')) {
                //set the new activation_code to 'Activated', this is how we can check if the user has activated their account
                $newcode = 'activated';
                $stmt->bind_param('sss', $newcode, $_GET['email']. $_GET['code']);
                $stmt->execute();
                echo 'Your account is now activated, you can login! <br><a href="index.html">Login</a>';
            }
        } else {
            echo 'Your account is already activated or doesn\'t exist'
        }
    }
}