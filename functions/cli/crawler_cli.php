<?php

require '../PHPCrawl/libs/PHPCrawler.class.php';
require '../helpers.php';
require '../db.php';

// Extend the class and override the handleDocumentInfo()-method
class WebCrawlerCLI extends PHPCrawler
{
    private $pdo;
    private $index = 0;

    public $old_domain_id;
    public $domain_id;

    function handleDocumentInfo($DocInfo)
    {
        $this->index++;

        // Database connection
        $this->pdo = connectPDO();

        // Just detect linebreak for output ("\n" in CLI-mode, otherwise "<br>").
        if (PHP_SAPI == "cli") $lb = "\n";
        else $lb = "<br />";

        // Print the URL and the HTTP-status-Code
        echo "Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")".$lb;

        // Print the refering URL
        echo "Referer-page: ".$DocInfo->referer_url.$lb;

        // Print the memory usage atm
        //echo "Memory usage: " . memory_get_usage() . $lb;

        // Print if the content of the document was be recieved or not
        if ($DocInfo->received == true)
            echo "Content received: ".$DocInfo->bytes_received." bytes".$lb;
        else
            echo "Content not received".$lb;

        // Now you should do something with the content of the actual
        // received page or file ($DocInfo->source), we skip it in this example

        // Write to Database
        // Check for we need only first loop
        if ($this->index === 1) {
            // Check if domain exists to replace
            $query = $this->pdo->query('SELECT * FROM `domains`');
            $query->setFetchMode(PDO::FETCH_ASSOC);

            while($query_rows = $query->fetch()) {
                if ($query_rows['domain'] === $this->starting_url . '/') {
                    $this->old_domain_id = $query_rows['id'];
                    break;
                }
            }

            // Add new domain record
            $query = $this->pdo->prepare('INSERT INTO `domains` (`domain`) VALUES (:domain)');
            $query->execute(['domain' => $this->starting_url . '/']);

            $this->domain_id = $this->pdo->lastInsertId();
        }

        if ($DocInfo->http_status_code != '404' && $DocInfo->received == true) {
            // Insert URLs to database
            $query = $this->pdo->prepare('INSERT INTO `url_to_domain` (`domain_id`, `url`) VALUES (:domain_id, :url)');
            $query->execute(['domain_id' => $this->domain_id, 'url' => $DocInfo->url]);
        }

        echo $lb;

        // Cleaning
        $this->pdo = null;
        flush();
    }
}