<?php

$host = 'localhost';
$dbname = 'PDO';
$username = 'root';
$password = '';

$getUsers = "SELECT * FROM `users`";

$getSingleUser = "SELECT * FROM `users` WHERE `id` = ? AND `email` = ?";

$userId = 3;
$userEmail = 'musk@x.com';

//$userId = $_GET['id'];

// CONNECT

try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

	var_dump($pdo);

} catch (PDOException $e) {
	echo "Exception found: " . $e->getMessage();
}

// GET users list

/* $statement = $pdo->query($getUsers);

while ($user = $statement->fetch(PDO::FETCH_ASSOC)) {
	var_dump($user);
}

$users = $statement->fetchAll(PDO::FETCH_ASSOC);

var_dump($users); */

// GET single user

/*$statement = $pdo->query($getSingleUser);

$user = $statement->fetch(PDO::FETCH_ASSOC);

var_dump($user);*/

// GET single user PROTECTED from injections

$statement = $pdo->prepare($getSingleUser);
$statement->execute([$userId, $userEmail]);

$user = $statement->fetch(PDO::FETCH_ASSOC);

var_dump($user);

// OR

$getSingleUserWithKey = "SELECT * FROM `users` WHERE `email` = :email";

$statement = $pdo->prepare($getSingleUserWithKey);
$statement->execute([
	'email' => $userEmail
]);

$user = $statement->fetch(PDO::FETCH_ASSOC);

var_dump($user);

// ADD USER

$name = 'Tim Cook';
$email = 'icook@apple.com';
$newPassword = 'exclusive99';

$addUser = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";

$statement = $pdo->prepare($addUser);

$statement->execute([
	'name' => $name,
	'email' => $email,
	'password' => $newPassword
]);

// EDIT USER

$newName = 'Evegnii Maskovich';
$newEmail = 'maskovich@mail.ru';

$editUser = "UPDATE users SET name = :name, email = :email, password = :password WHERE users.id = $userId";

try {
	$statement = $pdo->prepare($editUser);
	$statement->execute([
		'name' => $newName,
		'email' => $newEmail,
		'password' => $newPassword
	]);

	echo "User $userId is updated.\n";
} catch (PDOException $e) {
	echo "Error updating $userId: " . $e->getMessage();
}

// DELETE USER

$deleteUser = "DELETE FROM `users` WHERE `users`.`id` = 10";

try {
	$statement = $pdo->prepare($deleteUser);
	$statement->execute();

	echo "User 10 is deleted";
} catch (PDOException $e) {
	echo "Error deleting the user: " . $e->getMessage();
}