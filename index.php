<?php session_start();
$_SESSION['result_op'] = (isset($_SESSION['result_op']) ? $_SESSION['result_op'] : 'agg');
$_SESSION['clust_op'] = (isset($_SESSION['clust_op']) ? $_SESSION['clust_op'] : 'tf');

$_SESSION['results'] = (isset($_SESSION['results']) ? $_SESSION['results'] : 10);
$_SESSION['queryEx'] = (isset($_SESSION['queryEx']) ? $_SESSION['queryEx'] : 'off');
$_SESSION['thesaurus'] = (isset($_SESSION['thesaurus']) ? $_SESSION['thesaurus'] : 'thesaurus_roget_short.txt');
$_SESSION['stemmer'] = (isset($_SESSION['stemmer']) ? $_SESSION['stemmer'] : 'off');
$_SESSION['type'] = (isset($_SESSION['type']) ? $_SESSION['type'] : 'web');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Z-Search - Metasearch Engine</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="static/css/bootstrap.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Audiowide' rel='stylesheet' type='text/css'>
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }
    </style>
    <link href="static/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://twitter.github.io/bootstrap/assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://twitter.github.io/bootstrap/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://twitter.github.io/bootstrap/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="http://twitter.github.io/bootstrap/assets/ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="static/img/favicon.ico">
</head>

<body>
<?php include 'navbar.php'; ?>

<div class="container">

    <!-- Search Area -->
    <div class="hero-unit">
        <center>
            <img src="static/img/logo.png"></br>
            <form method="get" action="./search.php">
                <input style="font-weight:bold;" type="text" name="q" size = "10" required />
                <input type="hidden" name="offset" value = "1" required /></br>
                <input type="submit" value="Search" class="btn btn-primary btn-large" style="margin-top:20px;"/>

                </br>
                <hr>
                <div style = "width: 160px; text-align:left;">
                    <input name="result_op" type="radio" value="agg" <?php echo ($_SESSION['result_op']== 'agg') ?  'checked' : ''; ?> /> Aggregated</br>
                    <input name="result_op" type="radio" value="nonAgg" <?php echo ($_SESSION['result_op']== 'nonAgg') ?  'checked' : ''; ?> /> Non-Aggregated</br>
                    <input name="result_op" type="radio" value="clustered" <?php echo ($_SESSION['result_op']== 'clustered') ?  'checked' : ''; ?> /> Clustered</br>
                </div>
                </br>
                <hr>
                <div style = "width: 160px; text-align:left;">
                    <input name="type" type="radio" value="web" <?php echo ($_SESSION['type']== 'web') ?  'checked' : ''; ?> /> Web</br>
                    <input name="type" type="radio" value="image" <?php echo ($_SESSION['type']== 'image') ?  'checked' : ''; ?> /> Image</br>
                </div>
                </br>

            </form>
        </center>
    </div>

    <hr>

    <footer>
        <p>© Z-Search 2017</p>
    </footer>

</div> <!-- /container -->

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="static/js/jquery.js"></script>
<script src="static/js/bootstrap-transition.js"></script>
<script src="static/js/bootstrap-alert.js"></script>
<script src="static/js/bootstrap-modal.js"></script>
<script src="static/js/bootstrap-dropdown.js"></script>
<script src="static/js/bootstrap-scrollspy.js"></script>
<script src="static/js/bootstrap-tab.js"></script>
<script src="static/js/bootstrap-tooltip.js"></script>
<script src="static/js/bootstrap-popover.js"></script>
<script src="static/js/bootstrap-button.js"></script>
<script src="static/js/bootstrap-collapse.js"></script>
<script src="static/js/bootstrap-carousel.js"></script>
<script src="static/js/bootstrap-typeahead.js"></script>

</body></html>