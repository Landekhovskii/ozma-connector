<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

use Landekhovskii\OzmaConnector\OzmaConnector;

$dotenv = new Dotenv();
$dotenv->load(dirname(__DIR__) . "/.env");

$connector = new OzmaConnector('schemaName', 'entityName');
var_dump($connector->select());
