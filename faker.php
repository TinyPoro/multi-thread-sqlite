<?php
/**
 * Created by PhpStorm.
 * User: idist
 * Date: 19/06/2018
 * Time: 15:59
 */
include "vendor/autoload.php";

$db = new \IDisT\SQLiteMultiThread\DB(__DIR__ . "/demo.sqlite");

$faker = \Faker\Factory::create();
$now = time();
var_dump($db);
$k =0;

while (true) {
        $sql = "";
        for ($i = 0; $i < 100; $i++) $sql .= "INSERT INTO `users` ('name', 'email', 'phone', 'created_at', 'updated_at') VALUES ('$faker->name', '$faker->email', '$faker->phoneNumber','$now', '$now');";

    try {
        $db->query($sql);
        echo $k++." ---> \n";
    } catch (SQLiteException $e) {
        echo "";
    }
}