<?php

require_once __DIR__ . '/../vendor/autoload.php';

// $csv = new \Tacho\Csv('csv/Abibok.csv');
$csv = new \Tacho\Csv('csv/Vairuotojai1-17.csv');

$data = $csv->readCsvData();

echo '<pre>';
print_r($data);
