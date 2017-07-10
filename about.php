<?php
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
    </style>
    <link href="static/css/bootstrap-responsive.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Audiowide' rel='stylesheet' type='text/css'>
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
    <!-- Lower Area -->
    <div>

        <h3>Features</h3>
        <hr>
        <h4>General Features</h4>
        <p>Z-Search is a search engine that takes your query and returns a list of documents intended to satisfy your information needs.
            It is of the metasearch class, which means that it uses three underlying search engines and creates a list of the best results from all three.
            The idea is that the results returned would be better than any single search engine on its own. Z-Search is intended to have the following general features:</p>
        <ol>
            <li>Be useful to the end user</li>
            <li>Be intuitive and easy to use</li>
            <li>Be fast and efficient</li>
        </ol>
        <hr>
        <h4>Search Features</h4>
        <p>To use Z-Search just enter your query and hit the search button.
            </br>For advanced users you have the option for viewing the results as:</p>
        <ol>
            <li>Aggregated</li>
            <li>Non-Aggregated</li>
            <li>Clustered</li>
        </ol>
        <p>For a detailed description of these display features and other complex search features see the help section <a href = "help.php#resultTypes" >here.</a></p>
        <hr>

        <h4>Technical Features</h4>
        <p>Z-Search has a number of features of interest to software developers:</p>
        <ol>
            <li>Responsive Web Design for the User Interface</li>
            <li>Object-oriented code</li>
            <li>Languages: PHP, HTML5 & CSS3</li>
            <li>Clustering feature: Term frequency and a new experimental technique called Binaclustering</li>
        </ol>
        <hr>
    </div><!-- End of Lower Area -->

    <hr>
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