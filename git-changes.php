<?php

// $changes = shell_exec('git ls-files --modified');
$changes = shell_exec('git diff --cached --name-only');
$changes = explode("\n", $changes);

$change_dir = './changes/';

foreach ($changes as $change) {
	if(empty($change))
		continue;

	echo '> ' . $change;
	echo ' > ';

	$path = explode('/', $change);
	array_pop($path);
	$path = implode('/', $path);
	$path = $change_dir . $path;

	if (!is_dir($path))
		mkdir($path, 0777, true);

	copy($change, $change_dir . $change);

	echo $path;
	echo "\n";
}