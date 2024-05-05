<?php

require "../config/connection.php";

$getId = $_GET['id'];

$comment = $_POST['comment'];

mysqli_query($connection, "INSERT INTO `comments` (`id`, `house_id`, `body`) VALUES (NULL, '$getId', '$comment')");

header('Location: /viewhouse.php?id=' . $getId);