<?php

$servername = "server";
$username = "username";
$password = "password";
$dbname = "db";

$conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_error) {

    $responseData["databaseError"] = "<i class='fa fa-times' aria-hidden='true'></i> There has been a database error. Please try again.";

  }

?>