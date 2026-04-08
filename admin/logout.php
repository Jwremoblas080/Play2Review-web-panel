<?php
    session_start();

    unset(
        $_SESSION['logged'],
        $_SESSION['user_id'],
        $_SESSION['firstName'],
        $_SESSION['middleName'],
        $_SESSION['lastName'],
        $_SESSION['fullname'],       
        $_SESSION['username'],
        $_SESSION['password'],
        $_SESSION['Profile_image']       
    );

    session_destroy();
    header('Location: index.php');
    exit();
?>