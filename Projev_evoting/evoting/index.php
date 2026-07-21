<?php
session_start();
if(isset($_SESSION['level'])) {
    if($_SESSION['level'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: siswa/index.php");
    }
} else {
    header("Location: auth/login.php");
}
?>