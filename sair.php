<?php

session_start();
unset($_SESSION['id'], $_SESSION['nome'], $_SESSION['email'], $_SESSION['nivel']);

$_SESSION['msg'] = "Deslogado com sucesso";
header("Location: main.php");
