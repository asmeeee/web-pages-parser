<?php

require '../vendor/autoload.php';
require 'db.php';
require 'helpers.php';

use Carbon\Carbon;

set_time_limit(50000);

$domain = $_POST['domain'];
$domain_id = $_POST['id'];

$pdo = connectPDO();

$query = $pdo->prepare('SELECT * FROM `domains` WHERE `id` = :id');
$query->execute(array(':id' => $domain_id));

while($query_rows = $query->fetch()) {
    if (file_exists(dirname(__DIR__) . '/' . $query_rows['filename']) && $query_rows['filename']) {

        echo json_encode($query_rows['filename']);

    } else {

        $query = $pdo->prepare('UPDATE `domains` SET `filename` = :filename WHERE `id` = :id');
        $query->execute(array(':filename' => null, ':id' => $domain_id));

        $query = $pdo->prepare('SELECT * FROM `url_to_domain` WHERE `domain_id` = :domain_id');
        $query->execute(array(':domain_id' => $domain_id));

        // Write file
        $filename = preg_replace('#^www\.(.+\.)#i', '$1', parse_url($domain, PHP_URL_HOST)) . '-' . Carbon::today()->toDateString() . '-' . rand(0, 999);
        $file = fopen(dirname(__DIR__) . '/files/' . $filename . '.txt', 'w') or die('Unable to open file!');

        while($query_rows = $query->fetch()) {
            // Write file
            $text = "- URL " . $query_rows['url'] . ":\n";
            $text .= parseText($query_rows['url']) . "\n\n";
            fwrite($file, $text);
        }

        fclose($file);

        $query = $pdo->prepare('UPDATE `domains` SET `filename` = :filename WHERE `id` = :id');
        $query->execute(array(':filename' => 'files/' . $filename . '.txt', ':id' => $domain_id));

        echo json_encode('files/' . $filename . '.txt');

    }

    break;
}
