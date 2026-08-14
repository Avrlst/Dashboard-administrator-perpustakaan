<?php
session_start();
require_once 'config/db.php';

if (isset($_POST['login'])){
    $username = mysqli_real_escape_string($connect,$_POST['username']);
    
    $password = mysqli_real_escape_string($connect,$_POST['password']);

    $md5_password = md5($password);

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$md5_password'";
    $result = mysqli_query($connect,$query);

    if (mysqli_num_rows($result) === 1) {
        $user_data = mysqli_fetch_assoc($result);

        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $user_data['id_user'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
        $_SESSION['role'] = $user_data['role'];


        if($user_data['role'] === 'administrator') {
            header("Location: index.php?status=welcome_admin");
        }else{
            header("Location: index.php?status=welcome_petugas");
        }
        exit;
    }else{
        header("Location: login.php?error=invalid");
        exit;
    }
}else{
    header('Location: login.php');
    exit;
}
?>