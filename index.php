<?php

require ('functions/db.php');

$pdo = connectPDO();

$query = $pdo->query('SELECT * FROM `domains`');
$query->setFetchMode(PDO::FETCH_ASSOC);
$records = array();

while($query_rows = $query->fetch()) {
    $queryIn = $pdo->prepare('SELECT * FROM `url_to_domain` WHERE `domain_id` = :domain_id');
    $queryIn->execute(['domain_id' => $query_rows['id']]);
    $urls = $queryIn->rowCount();

    array_push($records, array(
        'id' => $query_rows['id'],
        'domain' => $query_rows['domain'],
        'urls' => $urls
    ));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Web Pages Parser</title>

    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css" type="text/css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/dt/dt-1.10.11/datatables.min.css"/>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

<header>
    <div class="container">
        <h1>
            Web Pages Parser
        </h1>
    </div>
</header>

<section id="main">
    <div class="container">
        <div class="well">

            <div class="alert alert-warning" role="alert">
                Attention! Do not close the page, browser or stop the script after pressing the submit button!
            </div>

            <form action="functions/process.php" method="post" class="form-inline">
                <div class="form-group" style="margin-right: 10px;">
                    <label for="url">URL</label>
                    <input type="text" name="url" id="url" class="form-control" placeholder="http://www.domain.com/">
                </div>

                <div class="radio-inline">
                    <label for="one-file">
                        <input type="radio" name="result-type" id="one-file" value="single" checked>
                        Download all in one file
                    </label>
                </div>

                <!--<div class="radio-inline">
                    <label for="mutiple-files">
                        <input type="radio" name="result-type" id="mutiple-files" value="multiple">
                        Download multiple files separated by URL
                    </label>
                </div>-->

                <div class="form-group" style="margin-left: 10px;">
                    <input type="submit" class="btn btn-primary">
                </div>

                <!--<div class="form-group" style="margin-left: 10px;">
                    <a class="btn btn-success disabled" id="download-file">Download .txt</a>
                </div>-->
            </form>
        </div>

        <h2 style="margin-top: 25px;">Download .txt for previously scanned pages</h2>

        <div class="well">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Domain</th>
                    <th>URL count</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $record) { ?>
                    <tr>
                        <td>
                            <?= $record['domain'] ?>
                        </td>
                        <td>
                            <?= $record['urls'] ?>
                        </td>
                        <td width="1">
                            <button class="btn btn-success" onclick="downloadUrlsHtml('<?= $record['domain'] ?>', '<?= $record['id'] ?>')">Collect and download .txt</button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div id="loader"><img src="assets/img/loader.gif" /></div>

<!-- jQuery -->
<script src="assets/vendor/jquery/jquery-1.11.2.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- DataTables JavaScript -->
<script type="text/javascript" src="https://cdn.datatables.net/t/dt/dt-1.10.11/datatables.min.js"></script>

<!-- Custom JavaScript -->
<script src="assets/js/script.js"></script>

<script>
    $(document).ready(function() {
        $('table').DataTable();
    } );
</script>

</body>

</html>
