<?php

require '../vendor/autoload.php';
require 'crawler.php';

use Carbon\Carbon;

set_time_limit(0); // Unlimited

$postURLs = explode("\r\n", $_POST['url']);

if (! is_array($postURLs) || ! array_filter($postURLs)) die('No URLs were found.');

$time = -microtime(true);

$filename = Carbon::today()->toDateString() . '-' . rand(0, 999);
$file     = fopen(dirname(__DIR__) . '/files/' . $filename . '.txt', 'w') or die('Unable to open file!');

foreach ($postURLs as $index => $postUrl) {
    // Whether is last loop
    $last = ($index === count($postURLs) - 1);

    // Add http(s)://www. to the url - http(s)://www.domain.com/...
    $sourceParsed   = parse_url($postUrl);
    $sourceProtocol = $sourceParsed['scheme'];
    $sourceHost     = $sourceParsed['host'];
    $sourcePath     = $sourceParsed['path'];
    $sourceUrl      = preg_replace('#^www\.(.+\.)#i', '$1', $sourceHost . $sourcePath);
    $sourceUrl      = (!empty($sourceProtocol) ? $sourceProtocol : "http") . "://www.{$sourceUrl}";

    // Parse the text and
    $text  = "{$postUrl}:\n";
    $text .= parseText($postUrl);

    if (! $last) $text .= "\n\n";

    // Write to file
    fwrite($file, $text);
}

fclose($file);

$fileUrl = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$fileUrl = dirname(dirname($fileUrl)) . "/files/" . $filename . ".txt";

$time += microtime(true);

echo "Runtime: {$time} seconds <br />";
echo "<a href=\"{$fileUrl}\" target=\"_blank\">Download</a>";
