<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Try launch that Rocket</title>
</head>

<body>
	<h1>Try Launch That Rocket</h1>
	<br>
	<form action="post">
		<label>Rocket Mass:</label><br>
		<input type="number" name="mass">
		<br>
		<label>Fuel Mass:</label><br>
		<input type="number" name="fuelMass">
		<br>
		<select name="engine">
			<option value="<? engineTypes::nuclear ?>">Ядерный</option>
			<option value="<? engineTypes::chemical ?>">Химический</option>
			<option value="<? engineTypes::electrical ?>">Электрический</option>
		</select><br>
		<button type="submit">Try Launch</button>
	</form>
	<?php
	use rockets\rocket\Rocket;

	require __DIR__ . '\rocket.php';
	require __DIR__ . '\launch.php';
	require __DIR__ . '\enginetypes.php';

	$mass = $_POST['mass'];
	$fuelMass = $_POST['fuelMass'];
	$engine = $_POST['engine'];

	$rocket = new Rocket($engine, $mass, $fuelMass);

	?>
</body>

</html>