<?php
require "config/connection.php";

$getId = $_GET['id'];
$houses = mysqli_query($connection, "SELECT * FROM `houses` WHERE `id` = $getId");
$houses = mysqli_fetch_assoc($houses);

$comments = mysqli_query($connection, "SELECT * FROM `comments` WHERE `house_id` = $getId");
$comments = mysqli_fetch_all($comments);

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= $houses['name'] ?></title>
</head>

<body>
	<h1><?= $houses['name'] ?></h1>
	<h3>Price: <?= $houses['price'] ?></h3>
	<p>About:</p>
	<p><?= $houses['description'] ?></p>

	<h3>Comments:</h3>
	<ul>
		<?php
		foreach ($comments as $comment) {
			?>
			<li><?= $comment[2] ?></li>
			<?php
		}
		?>
	</ul>

	<form method="post" action="actions/addcomment.php?id=<?= $getId ?>">
		<label>Add your feedback</label>
		<textarea name='comment'></textarea>
		<br>
		<button type="submit">Leave feedback</button>
	</form>

</body>

</html>