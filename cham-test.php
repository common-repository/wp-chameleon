<?php
include("cham-rewrite.php");

$buffer = "{one|two|three|four|five}";
$vars = array();

echo chameleon_rewrite($vars,$buffer); 
?>