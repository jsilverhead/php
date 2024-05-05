<?php

require '../config/connection.php';

$name = $_POST['name'];
$price = $_POST['price'];
$description = $_POST['description'];
$id = $_POST['id'];

mysqli_query($connection, "UPDATE `houses` SET `name` = '$name', `price` = '$price', `description` = '$description' WHERE `houses`.`id` = $id");

header('Location: /');