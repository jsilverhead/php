<?php
require "config/connection.php";

$getId = $_GET['id'];
$houses = mysqli_query($connection, "SELECT * FROM `houses` WHERE `id` = $getId");
$houses = mysqli_fetch_assoc($houses);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>House update</title>
</head>

<body>
	<form action="actions/update.php" method="post">
		<input type="hidden" name="id" value="<?= $houses['id'] ?>">
		<label>Name</label>
		<input type="text" name="name" value="<?= $houses['name'] ?>">
		<br>
		<label>Price</label>
		<input type="number" name="price" value="<?= $houses['price'] ?>">
		<br>
		<label>Description</label>
		<textarea name="description"><?= $houses['description'] ?></textarea>
		<br>
		<button type="submit">Update house</button>
	</form>
</body>

</html>