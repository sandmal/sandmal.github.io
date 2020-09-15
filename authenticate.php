<?php

session_start();
//connection info
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'root';
$DATABASE_NAME = 'phplogin';

//Connect with info above
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
  //If error occurred, stop session and display error message
  exit('Failed to connect to MySQL' . mysqli_connect_error());
}

//Check if data from login form was submitted
//issett() will check if the data exists.

if (!isset($_POST['username'], $_POST['password'])) {
  //could not get data that should have been sent.
  exit('Please fill both the username and passoword fields!');
}

//Prepare SQL, perparing the SQL statement will prevent SQL injection.
if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
  // Bind parameters (s = string, i = int, b = blob, etc), username = string so use "s"
  $stmt->bind_param('s', $_POST['username']);
  $stmt->execute();
  //Store result to check if the account exists in the database
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $password);
    $stmt->fetch();
    //Account exists in database -> Verify password;
    //Remember to use password_hash in your registration file to store the hashed passwords.
    if (password_verify($_POST['password'], $password)) {
      //verification success! User has logged in successfully!
      //create session se we know the user is logged in, they basically act like cookies but remember the data on the server.
      session_regenerate_id();
      $_SESSION['loggedin'] = TRUE;
      $_SESSION['name'] = $_POST['username'];
      $_SESSION['id'] = $id;
      header('Location: home.php');
    } else {
      //incorrect password
      echo 'Incorrect username and/or password!';
    }
  } else {
    //incorrect username
    echo 'Incorrect username and/or password!';
  }
  $stmt->close();
}