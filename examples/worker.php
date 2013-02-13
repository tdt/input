<?php

chdir("../");
set_include_path(get_include_path() . PATH_SEPARATOR . "../");
require_once("vendor/autoload.php");

$w = new tdt\input\scheduler\Worker(parse_ini_file("examples/custom/db.ini", false));
$w->execute();

