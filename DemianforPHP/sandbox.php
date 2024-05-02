<?php

set_exception_handler('errorMessage');

function findWord($words)
{
	$wordCount = 0;

	foreach ($words as $word) {
		if (gettype($word) != "string") {
			throw new Exception(" $word Isn't a word!");
		}
		$wordCount++;
	}
	echo "$wordCount \n";
}

try {
	findWord(["It's", "Good", "To", "See", "Ya"]);
	findWord(["Billy", "Jean", "Is", 11, "years"]);
} finally {
	echo "Completed search. \n";
}

function errorMessage(Throwable $exception)
{
	echo "Exception found:" . $exception->getMessage();
}