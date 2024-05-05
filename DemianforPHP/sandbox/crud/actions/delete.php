<?php

require "../config/connection.php";

$id = $_GET['id'];

mysqli_query($connection, "DELETE FROM `houses` WHERE `houses`.`id` = $id");

header('Location: /');