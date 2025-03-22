<?php

require_once 'PolarBear.php';
require_once 'Seal.php';

// Example usage:
$polarBear1 = new PolarBear("Nanuk", 5, 450, "Arctic Circle");
$seal1 = new Seal("Sammy", 100, "ice floe");
$seal2 = new Seal("Sally", 80, "ice floe");

$polarBear1->displayStats();
$seal1->displayStats();

$polarBear1->hunt($seal1);

$polarBear1->displayStats();
$seal1->displayStats();
$polarBear1->hunt($seal1);
$polarBear1->hunt($seal2);

?>