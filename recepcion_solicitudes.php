<?php
session_start();

if (!isset($_SESSION['id_usuario'], $_SESSION['rol']) || $_SESSION['rol'] !== 'Tutor') {
    header('Location: dashboard.php');
    exit();
}

header('Location: solicitudes.php');
exit();
