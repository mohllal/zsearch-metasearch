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
        <h3>Getting Started</h3>
        <ol>
            <li>Navigate to the homepage by clicking the link "Z-Search" in the top left corner</li>
            <li>Enter your search term in the input box</li>
            <li>Hit the search button</li>
            <li>If in doubt, check out the screen below!</li>
        </ol>
        <hr>
        <figure>
            <img src="static/img/screen_01.png" alt="Screen shot of the homepage" />
            <figcaption>Fig. 1</figcaption>
        </figure>
        <hr>

        <h3>What complex search features are available?</h3>
        <p>Z-Search supports the use of Boolean Searches in the form of "AND", "OR" and "NOT". To search for documents with cats and dogs just type "cats AND dogs" not including the double quote marks. Of course, this is also the default query and is equivalent to a search for "cats dogs". To search for articles with cats or dogs, but not both, use "cats OR dogs". Finally, to search for articles with cats but not dogs enter "cats NOT dogs". To search for a specific sequence of words you may surround your query by the double quotation marks: "cats and dogs" including quotes.</p>
        <hr>

        <h3>Can't click the options in the menu?</h3>
        <p>Your Javascript may be disabled! Don't worry, you can enable it in your browser options or simply click below to get at e same options:</p>
        <ol>
            <li><a href="search_options.php">Search Options</a></li>
            <li><a href="metrics.php">Performance Metrics</a></li>
        </ol>
        <hr>
        <a name="resultTypes" ></a>
        <h3>What's the story with Search Options in the main menu?</h3>
        </br>
        <h4>What's the difference between Aggregated, Non-Aggregated and Clustered Search Types?</h4>
        <p>This Search Engine collects results from Google, Bing, and Ask.</p>
        <ul>
            <li>If you want to see the results merged into a single, optimised list, choose Aggregated</li>
            <li>If you want to see those results as returned from each Search individually choose Non-Aggregated</li>
            <li>If you want to see the results merged into one list with categories choose Clustered</li>
        </ul>
        <hr>
        <figure>
            <img src="static/img/screen_02.png" alt="Screen shot of the homepage" />
            <figcaption>Fig. 2</figcaption>
        </figure>
        <hr>

        <h4>What's Search Depth - Selecting the number of results to return?</h4>
        <p>Most people just look at the top ten results! If you want more in your aggregated list, increase this number. The more you select, the longer it takes to get them, so if you select the maximum of 100 results, it will take a few seconds! Does not apply to Non-Aggregated Results which are paginated.</p>

        <hr>

        <h4>What's Clustering Type?</h4>
        <ul>
            <li>Term Frequency Clustering Displays Results in category folders and documents (results) can be in more than one category. This is the recommended type.</li>
            <li>Binaclustering is an experiment in clustering, four unique categories are created and documents will only appear in one cluster. This type is for demonstration purposes and is not ready for ordinary users so you can safely ignore it.</li>
        </ul>
        <hr>

        <h4>What's Query Expansion?</h4>
        <p>If you select this option some words will be automtically added to your search. This is an experimental feature might give you back some interesting results, but sometimes it can return no results if the input search engine cannot handle it! If you are feeling adventurous, give it a try! Use the Shorter Roget's Thesaurus - the default option.</p>
        <hr>

        <h4>What's an Expansion Stemmer?</h4>
        <p>If you select Query Expansion, this option some words will include words in their plural form, otherwise it won't. This feature might might not always work - it depends on the word.</p>
        <hr>

        <h3>Can't get the form to submit or save any options?</h3>
        <p>Don't forget to click the blue button at the bottom of the page!</p>

        <hr>

        <h3>What are metrics?</h3>
        <p>This page displays the results of tests that show how each of the search Engines has performed! The higher the mark, the better the performance! TREC Queries from 2012 are used.</p>

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