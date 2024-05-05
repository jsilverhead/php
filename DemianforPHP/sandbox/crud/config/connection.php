<?php

$connection = mysqli_connect(hostname: 'localhost', username: 'root', password: '', database: 'crud');

if (!$connection) {
	die("Error connect to a database.");
}