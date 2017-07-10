<?php session_start();
$_SESSION['result_op'] = (isset($_GET['result_op']) ? $_GET['result_op'] : $_SESSION['result_op']);
$_SESSION['clust_op'] = (isset($_SESSION['clust_op']) ? $_SESSION['clust_op'] : 'tf');

$_SESSION['results'] = (isset($_SESSION['results']) ? $_SESSION['results'] : 10);
$_SESSION['queryEx'] = (isset($_SESSION['queryEx']) ? $_SESSION['queryEx'] : 'off');
$_SESSION['offset'] = (isset($_GET['offset']) ? $_GET['offset'] : 1);
$_SESSION['query'] = $_GET['q'];
$_SESSION['thesaurus'] = (isset($_SESSION['thesaurus']) ? $_SESSION['thesaurus'] : 'thesaurus_roget.txt');
$_SESSION['type'] = (isset($_GET['type']) ? $_GET['type'] : $_SESSION['type']);
$_SESSION['stemmerAlg'] = (isset($_SESSION['stemmerAlg']) ? $_SESSION['stemmerAlg'] : 'porter');

if(!isset($_SESSION['query'])) header("Location: index.php");
include 'classes.php';
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
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }
        img {
            max-width: 200px;
            max-height: 200px;
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
    <div class="results-unit">


        <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input style="font-weight:normal;" type="text" name="q" size = "50" value = "<?php echo $_GET['q']; ?>" required />
            <input type="submit" value="Search" class="btn btn-primary btn-small" />
        </form>
    </div><!-- End of Search Area -->

    <!-- Results -->
    <div>
        <?php include 'main.php'; ?>
    </div>

    <hr>
    <?php
    if ($_SESSION['result_op'] == "nonAgg"){
        echo'<div>
    <ul class = "pager">
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=1">1</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=11">2</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=21">3</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=31">4</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=41">5</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=51">6</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=61">7</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=71">8</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=81">9</a></li>
    <li><a href ="search.php?q='; echo $_SESSION['query']; echo'&offset=91">10</a></li>
    </ul>
    </div>
    <hr>';
    }
    ?>
    <!-- Footer -->
    <footer>
        <p>© Z-Search 2017</p>
    </footer>

</div> <!-- End of container -->
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

</body>
</html>