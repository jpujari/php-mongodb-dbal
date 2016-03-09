<?php

require_once "vendor/autoload.php";
require_once "MongoDbDbal.php";

use MongoDbDbal\MongoDbDbal as MongoDbDbal;

$db = new MongoDbDbal("localhost",27017, "test");
$db->setCollectionName("users");

$user = array(
       "first_name" => "john",
       "last_name" => "wick"
);

$result = $db->insertOne($user);

var_dump($result);
