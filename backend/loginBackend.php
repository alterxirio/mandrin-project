<?php

session_destroy();

include("../config/config.php");
// session_start();

$username = $_POST["username"];
$password = $_POST["password"];

// cari user information berdasarkan input user
$sql = mysqli_query($con, "SELECT * FROM users WHERE angkagiliran = '$username'");
$match = mysqli_fetch_array($sql);

// matching user input dgn username dlm database
$username_db = $match["angkagiliran"];
$password_db = $match["password"];



if (isset($_POST["username"]) && !empty($_POST["username"])) {
    if (isset($_POST["password"]) && !empty($_POST["password"])) {

        echo $username;
        echo $username_db;
        echo $password;
        echo $password_db;
        
        if ($username == $username_db && $password == $password_db) {
            // $_SESSION['id_user'] = $match["id_user"];
            session_start();
            $_SESSION['name'] = $match["nama"];
            $_SESSION['id'] = $match["id"];
            $_SESSION['role'] = $match["role"];
            // $_SESSION['email'] = $match["email"];
            // $_SESSION['name'] = $match["name"];
            header("location:../frontend/main.php");
        } else {
            $_SESSION['error'] = "Username or password invalid";
            header("location:../index.php");
            echo '1';
        }
    } else {
        header("location:../index.php");
        echo '2';

    }
} else {
    header("location:../index.php");
    echo '3';

}



?>
<a href=""></a> 