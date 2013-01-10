#!/usr/bin/env php
<?php
while ($line = fgetcsv(STDIN)) {
	for ($i = 1; $i < count($argv); $i++) {
		$column = $argv[$i];
		$column--;
		echo "{$line[$column]}\t";
	}
	echo "\n";
}