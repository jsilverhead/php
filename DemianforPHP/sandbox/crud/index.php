<?php

require 'config/connection.php';

// CREATE

// READ

// UPDATE

// DELETE

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<style>
		th {
			background-color: grey;
			padding: 7px;
		}

		td {
			background-color: lightgray;
			padding: 7px;
		}
	</style>
</head>

<body>
	<table>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Price</th>
			<th>Description</th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
		<?php

		$houses = mysqli_query($connection, query: "SELECT * FROM `houses`");

		$houses = mysqli_fetch_all($houses);

		foreach ($houses as $house) {
			?>

			<tr>
				<td><?= $house[0] ?></td>
				<td><?= $house[1] ?></td>
				<td><?= $house[2] ?></td>
				<td><?= $house[3] ?></td>
				<td><a href="viewhouse.php?id=<?= $house[0] ?>">View<a></td>
				<td><a href="updateform.php?id=<?= $house[0] ?>">Update</a></td>
				<td><a href="../actions/delete.php?id=<?= $house[0] ?>">Delete<a></td>
			</tr>

			<?php
		}
		;

		?>

		<form action="actions\create.php" method="post">
			<label>Name</label>
			<input type="text" name="name">
			<br>
			<label>Price</label>
			<input type="number" name="price">
			<br>
			<label>Description</label>
			<textarea name="description"></textarea>
			<br>
			<button type="submit">Add house</button>
		</form>

	</table>
</body>

</html>