<?php

require '../config/connection.php';

$name = $_POST['name'];
$price = $_POST['price'];
$description = $_POST['description'];

echo "Added $name";

mysqli_query($connection, query: "INSERT INTO `houses` (`id`, `name`, `price`, `description`) VALUES (NULL, '$name', '$price', '$description')");

header('Location: /');