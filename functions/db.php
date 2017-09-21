<?php

function connectPDO()
{
    $host   = 'localhost';
    $dbname = 'webpages_parser';
    $user   = 'root';
    $pass   = '';

    try {

        if (PHP_SAPI == "cli") $lb = "\n";
        else $lb = "<br />";

        //echo 'Handling database...' . $lb;
        return new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass);

    } catch(PDOException $e) {

        exit("Unable to connect to database. Error: \n" . $e->getMessage());

    }
}
