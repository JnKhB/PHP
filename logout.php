<?php
function logout() {
    unset($_SESSION["user"]);
  }
session_start();
logout(); 
header("Location:main.php");
?>