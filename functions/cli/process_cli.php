<?php

require '../../vendor/autoload.php';
require 'crawler_cli.php';
require_once '../ProgressBar/Manager.php';
require_once '../ProgressBar/Registry.php';

use Carbon\Carbon;

set_time_limit(0); // Unlimited

$postUrl = '';

while (filter_var($postUrl, FILTER_VALIDATE_URL) === false) {
    echo 'Enter the URL to be scanned. Format for URL - http://www.domain.com/ or http://domain.com/: ';

    $handle = fopen ("php://stdin","r");
    $postUrl = trim(fgets($handle));
}

// Add http://www. to the url - http://www.domain.com/...
$sourceUrl = parse_url($postUrl);
$sourceUrl = preg_replace('#^www\.(.+\.)#i', '$1', $sourceUrl['host'] . $sourceUrl['path']);
$sourceUrl = 'http://www.' . $sourceUrl;

$crawler = new WebCrawlerCLI;

// URL to crawl
$crawler->setURL($sourceUrl);

// Only receive content of files with content-type "text/html"
$crawler->addContentTypeReceiveRule("#text/html#");

// Ignore links to ..., dont even request ...
$crawler->addURLFilterRule("#\.(jpg|jpeg|gif|png|css|js|ico|pdf|xml|doc|docx|mp3|wav|m4a)$# i");

// Store and send cookie-data like a browser does
$crawler->enableCookieHandling(true);

// Enable local caching instead of memory for huge sources. Uses SQLITE as DB
$crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);

// Traffic Limit
//$crawler->setTrafficLimit(200 * 1024);

/*// Important for resumable scripts/processes!
$crawler->enableResumption();

// At the firts start of the script retreive the crawler-ID
// and store it
// (in a temporary file in this example)
$crawlerid_filename = dirname(__DIR__) . '/tmp/crawler_id.tmp';

if (!file_exists($crawlerid_filename))
{
    $crawler_ID = $crawler->getCrawlerId();
    file_put_contents($crawlerid_filename, $crawler_ID);
}
// If the script was restarted again (after it was aborted),
// read the crawler-ID and pass it to the resume() method.
else
{
    $crawler_ID = file_get_contents($crawlerid_filename);
    $crawler->resume($crawler_ID);
}*/

// Thats enough, now here we go
$crawler->go();

// At the end, after the process is finished, we print a short
// report (see method getProcessReport() for more information)
$report = $crawler->getProcessReport();

if (PHP_SAPI == "cli") $lb = "\n";
else $lb = "<br />";

echo "Summary:".$lb;
echo "Links followed: ".$report->links_followed.$lb;
echo "Documents received: ".$report->files_received.$lb;
echo "Bytes received: ".$report->bytes_received." bytes".$lb;
echo "Process runtime: ".$report->process_runtime." seconds".$lb;

// Delete the stored crawler-ID after the process is finished
// completely and successfully.
//if (file_exists($crawlerid_filename)) unlink($crawlerid_filename);

// Generating the .txt file
echo $lb . $lb . "Parsing the HTML from found URLs:" . $lb;

$time = -microtime(true);

$pdo = connectPDO();

echo "Parsing..." . $lb;

$query = $pdo->prepare('SELECT * FROM `url_to_domain` WHERE `domain_id` = :domain_id');
$query->execute(array(':domain_id' => $crawler->domain_id));
$counter = $query->rowCount();

$progressBar = new \ProgressBar\Manager(0, $counter);

$filename = preg_replace('#^www\.(.+\.)#i', '$1', parse_url($sourceUrl, PHP_URL_HOST)) . '-' . Carbon::today()->toDateString() . '-' . rand(0, 999);
$file = fopen(dirname(dirname(__DIR__)) . '/files/' . $filename . '.txt', 'w') or die('Unable to open file!');

while($query_rows = $query->fetch()) {
    // Progress bar
    $progressBar->advance();

    // Write file
    $text = "- URL " . $query_rows['url'] . ":\n";
    $text .= parseText($query_rows['url']) . "\n\n";
    fwrite($file, $text);
}

fclose($file);

// Reconnect to database
$pdo = null;
$pdo = connectPDO();

$query = $pdo->prepare('UPDATE `domains` SET `filename` = :filename WHERE `id` = :id');
$query->execute(array(':filename' => 'files/' . $filename . '.txt', ':id' => $crawler->domain_id));

// After we are finished delete old domain and its URLs
$query = $pdo->prepare('DELETE FROM `domains` WHERE `id` = :domain_id');
$query->execute(array(':domain_id' => $crawler->old_domain_id));

$time += microtime(true);

echo "HTML parsing runtime: " . $time . " seconds" . $lb;
echo "Found URLs are successfully stored into the database and a .txt file with text was generated in - " . 'files/' . $filename . '.txt.' . $lb;
echo "To download the .txt file with data please proceed to index." . $lb;
