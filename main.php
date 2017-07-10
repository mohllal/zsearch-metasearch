<?php
require_once('askSearch.php');

// Time query
$time_pre = microtime(true);

// Get the Query from User input
$q = strtolower($_SESSION['query']);

$qX = new query;
$qX->tokenizeQuery($q);

if($_SESSION['stemmer'] == 'on'){
    if($_SESSION['stemmerAlg'] == 'porter')
        $qX->tokenizeQuery(implode(" ",$qX->stem_list($q, 'porter')));
    else
        $qX->tokenizeQuery(implode(" ",$qX->stem_list($q, 'porter2')));
}

if($_SESSION['queryEx'] == 'on')
{
    // Query Preprocess
    $thesaurus1 = new thesaurus;
    $thesaurus1->loadThesaurusFile($_SESSION['thesaurus']);
    //$q = $qX->expandQuery($q, $thesaurus1->returnThesaurus());
    $qX->makeSuggestions($q,$thesaurus1->returnThesaurus());
}

// Process the Query
$q1 = new query;
$query1 = $q1->complexQueryGoogle($q);
$query2 = $q1->complexQueryBing($q);
$query3 = askParser::complexQueryAsk($q);

$offset = $_SESSION['offset'];

// AGG
if($_SESSION['result_op']=='agg')
{
    // Instantiate Aggregator
    $aggregator1 = new aggregator(new resultSet());

    $conn = new mysqli("localhost", "root", "", "z-search");
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $google_sql_query = NULL;
    $bing_sql_query = NULL;
    $ask_sql_query = NULL;

    $google_result = NULL;
    $bing_result = NULL;
    $ask_result = NULL;

    if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
        $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'image' AND result_offset = '$offset'";
        $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'image' AND result_offset = '$offset'";

        $google_result = $conn->query($google_sql_query);
        $bing_result = $conn->query($bing_sql_query);
    }

    else {
        $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'text' AND result_offset = '$offset'";
        $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'text' AND result_offset = '$offset'";
        $ask_sql_query = "SELECT * FROM results WHERE result_keywords = '$query3' AND result_source = 'Ask' AND result_type = 'text' AND result_offset = '$offset'";

        $google_result = $conn->query($google_sql_query);
        $bing_result = $conn->query($bing_sql_query);
        $ask_result = $conn->query($ask_sql_query);
    }

    if ($google_result === FALSE || $bing_result === FALSE || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result === FALSE)) {
        echo "Error: " . $sql_query . "<br>" . $conn->error;
        $conn->close();
    }

    if($google_result->num_rows > 0 || $bing_result->num_rows > 0 || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0)){
        echo  'From database!!';
        $google_result_flag = ($google_result->num_rows > 0)? true: false;
        $bing_result_flag = ($bing_result->num_rows > 0)? true: false;
        $ask_result_flag = (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0)? true: false;

        $google_resultSet = new resultSet();
        // output data of each row
        while($row = $google_result->fetch_assoc()) {
            $google_resultSet->addUrl($row["result_link"]);
            $google_resultSet->addTitle($row["result_title"]);
            $google_resultSet->addSnippet($row["result_snippet"]);
            $google_resultSet->addScore($row["result_score"]);
        }

        $bing_resultSet = new resultSet();
        // output data of each row
        while($row = $bing_result->fetch_assoc()) {
            $bing_resultSet->addUrl($row["result_link"]);
            $bing_resultSet->addTitle($row["result_title"]);
            $bing_resultSet->addSnippet($row["result_snippet"]);
            $bing_resultSet->addScore($row["result_score"]);
        }

        $ask_resultSet = new resultSet();
        if(!(isset($_SESSION['type']) && $_SESSION['type']=='image')){
            // output data of each row
            while($row = $ask_result->fetch_assoc()) {
                $ask_resultSet->addUrl($row["result_link"]);
                $ask_resultSet->addTitle($row["result_title"]);
                $ask_resultSet->addSnippet($row["result_snippet"]);
                $ask_resultSet->addScore($row["result_score"]);
            }
        }
        $conn->close();

        // Send result sets 1,2 & 3 to Data Fusion Function
        $aggregator1->dataFusion($google_result_flag, $bing_result_flag, $ask_result_flag, $google_resultSet, $bing_resultSet, $ask_resultSet, $_SESSION['results']);
    }
    else{
        // Instantiate a new API
        $api1 = new api;
        // Instantiate a new formatter with the 3 result sets as properties
        $formatter1 = new formatter(new resultSet(), new resultSet(), new resultSet());

        // Google Results
        for($i=0;$i<($_SESSION['results']/10);$i++)
        {
            // Get offset
            $offset = 1+($i*10);
            // Call Google API
            $api1->googleApi($query1, $offset);

            // Set Google JSON Data
            $formatter1->setGoogleJson($api1->returnGoogleJsonData(), $api1->returnGoogleJsonResultFlag());
            $formatter1->formatGoogleJson($_SESSION['results'], $i*10, $query1);
        }

        // Bing Results
        for($i=0;$i<($_SESSION['results'] > 50 ? 2 : 1);$i++)
        {
            // Get offset
            $offset = 1+($i*50);
            // Call Bing API
            $api1->bingApi($query2, $_SESSION['results'], $offset);
            // Set BING JSON Data
            $formatter1->setBingJson($api1->returnBingJsonData(), $api1->returnBingJsonResultFlag());
            $formatter1->formatBingJson($_SESSION['results'], $i*50, $query2);
        }

        $arr = NULL;
        // Ask results
        if($_SESSION['type']!='image'){
            $arr=askParser::getAggregatedSearchResults($q ,$_SESSION['results']);
            $formatter1->formatAskResults($arr, $query3, $_SESSION['offset']);
        }

        $askResultFlag = (isset($arr) && count($arr)>0);
        // Send result sets 1,2 & 3 to Data Fusion Function
        $aggregator1->dataFusion($api1->returnGoogleJsonResultFlag(), $api1->returnBingJsonResultFlag(), $askResultFlag, $formatter1->returnResultSet('resultSet1'), $formatter1->returnResultSet('resultSet2'), $formatter1->returnResultSet('resultSet3'), $_SESSION['results']);
    }


    echo '<div class="row">';
    // Print Agg Results
    $aggregator1->printResultSetAgg();

    // Query Timer
    $time_post = microtime(true);
    $exec_time = $time_post - $time_pre;
    echo '</br>Query Time: '.$exec_time;

    // Print Results to file - for metrics
    //$aggregator1->outputResultSetToFile('Aggregated', 'resultSetAgg', $_SESSION['results']);
    //$formatter1->outputResultSetToFile('Google', 'resultSet1', $_SESSION['results']);
    //$formatter1->outputResultSetToFile('Bing', 'resultSet2', $_SESSION['results']);
}

else if($_SESSION['result_op']=='nonAgg')
{
    $conn = new mysqli("localhost", "root", "", "z-search");
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $google_sql_query = NULL;
    $bing_sql_query = NULL;
    $ask_sql_query = NULL;

    $google_result = NULL;
    $bing_result = NULL;
    $ask_result = NULL;

    if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
        $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'image' AND result_offset = '$offset'";
        $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'image' AND result_offset = '$offset'";

        $google_result = $conn->query($google_sql_query);
        $bing_result = $conn->query($bing_sql_query);
    }
    else {
        $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'text' AND result_offset = '$offset'";
        $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'text' AND result_offset = '$offset'";
        $ask_sql_query = "SELECT * FROM results WHERE result_keywords = '$query3' AND result_source = 'Ask' AND result_type = 'text' AND result_offset = '$offset'";

        $google_result = $conn->query($google_sql_query);
        $bing_result = $conn->query($bing_sql_query);
        $ask_result = $conn->query($ask_sql_query);
    }

    if ($google_result === FALSE || $bing_result === FALSE || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result === FALSE)) {
        echo "Error: in SQL query<br>";
        $conn->close();
    }

    if(($google_result->num_rows > 0 || $bing_result->num_rows > 0 || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0))){
        echo  'From database!!';
        $google_result_flag = ($google_result->num_rows > 0)? true: false;
        $bing_result_flag = ($bing_result->num_rows > 0)? true: false;
        $ask_result_flag = (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0)? true: false;

        $google_resultSet = new resultSet();
        // output data of each row
        while($row = $google_result->fetch_assoc()) {
            $google_resultSet->addUrl($row["result_link"]);
            $google_resultSet->addTitle($row["result_title"]);
            $google_resultSet->addSnippet($row["result_snippet"]);
            $google_resultSet->addScore($row["result_score"]);
        }

        $bing_resultSet = new resultSet();
        // output data of each row
        while($row = $bing_result->fetch_assoc()) {
            $bing_resultSet->addUrl($row["result_link"]);
            $bing_resultSet->addTitle($row["result_title"]);
            $bing_resultSet->addSnippet($row["result_snippet"]);
            $bing_resultSet->addScore($row["result_score"]);
        }

        $ask_resultSet = new resultSet();
        if(!(isset($_SESSION['type']) && $_SESSION['type']=='image')){
            // output data of each row
            while($row = $ask_result->fetch_assoc()) {
                $ask_resultSet->addUrl($row["result_link"]);
                $ask_resultSet->addTitle($row["result_title"]);
                $ask_resultSet->addSnippet($row["result_snippet"]);
                $ask_resultSet->addScore($row["result_score"]);
            }
        }

        $conn->close();

        $formatter1 = new formatter($google_resultSet, $bing_resultSet, $ask_resultSet);

        if(!(isset($_SESSION['type']) && $_SESSION['type']=='image')) {
            echo '<div class="row"><div class="span4"><h2>Google</h2>';
            // Display Google Results to Screen
            $formatter1->printResultSet('resultSet1', $_SESSION['results']);

            echo '</div><div class="span4"><h2>Bing</h2>';
            // Display Bing Results
            $formatter1->printResultSet('resultSet2', $_SESSION['results']);

            echo '</div><div class="span4"><h2>Ask</h2>';
            // Display Ask Results
            //$formatter1->printResultSet('resultSet3', $_SESSION['results']);
            $formatter1->printResultSet('resultSet3', $_SESSION['results']);
            echo '</div></div> <!-- End of Class row -->';
        }

        else{
            echo '<div class="row"><div class="span6"><h2>Google</h2>';
            // Display Google Results to Screen
            $formatter1->printResultSet('resultSet1', $_SESSION['results']);

            echo '</div><div class="span6"><h2>Bing</h2>';
            // Display Bing Results
            $formatter1->printResultSet('resultSet2', $_SESSION['results']);
            echo '</div></div> <!-- End of Class row -->';
        }

    }
    else{
        // Instantiate a new API
        $api1 = new api;
        // Instantiate a new formatter with the 3 result sets as properties
        $formatter1 = new formatter(new resultSet(), new resultSet(), new resultSet());

        // Call Google API
        $api1->googleApi($query1, $_SESSION['offset']);
        // Set Google JSON Data
        $formatter1->setGoogleJson($api1->returnGoogleJsonData(), $api1->returnGoogleJsonResultFlag());
        $formatter1->formatGoogleJson(100, $_SESSION['offset'], $query1);

        // Call Bing API
        $api1->bingApi($query2, 10, $_SESSION['offset']);
        // Set BING JSON Data
        $formatter1->setBingJson($api1->returnBingJsonData(), $api1->returnBingJsonResultFlag());
        $formatter1->formatBingJson(100, $_SESSION['offset'], $query2);

        // Ask Results
        if($_SESSION['type']!='image'){
            $arr=askParser::getAskSearchResultsByPage($q ,intval(($_SESSION['offset']/10)) + 1);
            $formatter1->formatAskResults($arr, $query3, $offset);

            echo '<div class="row"><div class="span4"><h2>Google</h2>';
            // Display Google Results to Screen
            $formatter1->printResultSet('resultSet1', $_SESSION['results']);

            echo '</div><div class="span4"><h2>Bing</h2>';
            // Display Bing Results
            $formatter1->printResultSet('resultSet2', $_SESSION['results']);

            echo '</div><div class="span4"><h2>Ask</h2>';
            // Display Ask Results
            $formatter1->printResultSet('resultSet3', $_SESSION['results']);
            echo '</div></div> <!-- End of Class row -->';
        }  else {

            echo '<div class="row"><div class="span6"><h2>Google</h2>';
            // Display Google Results to Screen
            $formatter1->printResultSet('resultSet1', $_SESSION['results']);

            echo '</div><div class="span6"><h2>Bing</h2>';
            // Display Bing Results
            $formatter1->printResultSet('resultSet2', $_SESSION['results']);
            echo '</div></div> <!-- End of Class row -->';
        }
    }

    // Query Timer
    $time_post = microtime(true);
    $exec_time = $time_post - $time_pre;
    echo '</br>Query Time: '.$exec_time;

}

else if($_SESSION['result_op']=='clustered')
{
    // Limit Results
    $limiter = 100;
    if(($_SESSION['results'] > $limiter) && ($_SESSION['clust_op']=='tf'))
    {
        $_SESSION['results'] = $limiter;
    }

    // ***********************
    // ** TERM FREQ CLUSTERING
    // ***********************
    if($_SESSION['clust_op']=='tf')
    {
        // Instantiate Aggregator
        $aggregator1 = new aggregator(new resultSet());

        $conn = new mysqli("localhost", "root", "", "z-search");
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $google_sql_query = NULL;
        $bing_sql_query = NULL;
        $ask_sql_query = NULL;

        $google_result = NULL;
        $bing_result = NULL;
        $ask_result = NULL;

        if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
            $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'image' AND result_offset = '$offset'";
            $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'image' AND result_offset = '$offset'";

            $google_result = $conn->query($google_sql_query);
            $bing_result = $conn->query($bing_sql_query);
        }

        else {
            $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'text' AND result_offset = '$offset'";
            $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'text' AND result_offset = '$offset'";
            $ask_sql_query = "SELECT * FROM results WHERE result_keywords = '$query3' AND result_source = 'Ask' AND result_type = 'text' AND result_offset = '$offset'";

            $google_result = $conn->query($google_sql_query);
            $bing_result = $conn->query($bing_sql_query);
            $ask_result = $conn->query($ask_sql_query);
        }

        if ($google_result === FALSE || $bing_result === FALSE || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result === FALSE)) {
            echo "Error: " . $sql_query . "<br>" . $conn->error;
            $conn->close();
        }

        if($google_result->num_rows > 0 || $bing_result->num_rows > 0 || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0)){
            echo  'From database!!';
            $google_result_flag = ($google_result->num_rows > 0)? true: false;
            $bing_result_flag = ($bing_result->num_rows > 0)? true: false;
            $ask_result_flag = (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0)? true: false;

            $google_resultSet = new resultSet();
            // output data of each row
            while($row = $google_result->fetch_assoc()) {
                $google_resultSet->addUrl($row["result_link"]);
                $google_resultSet->addTitle($row["result_title"]);
                $google_resultSet->addSnippet($row["result_snippet"]);
                $google_resultSet->addScore($row["result_score"]);
            }

            $bing_resultSet = new resultSet();
            // output data of each row
            while($row = $bing_result->fetch_assoc()) {
                $bing_resultSet->addUrl($row["result_link"]);
                $bing_resultSet->addTitle($row["result_title"]);
                $bing_resultSet->addSnippet($row["result_snippet"]);
                $bing_resultSet->addScore($row["result_score"]);
            }

            $ask_resultSet = new resultSet();
            if(!(isset($_SESSION['type']) && $_SESSION['type']=='image')){
                // output data of each row
                while($row = $ask_result->fetch_assoc()) {
                    $ask_resultSet->addUrl($row["result_link"]);
                    $ask_resultSet->addTitle($row["result_title"]);
                    $ask_resultSet->addSnippet($row["result_snippet"]);
                    $ask_resultSet->addScore($row["result_score"]);
                }
            }
            $conn->close();

            // Send result sets 1,2 & 3 to Data Fusion Function
            $aggregator1->dataFusion($google_result_flag, $bing_result_flag, $ask_result_flag, $google_resultSet, $bing_resultSet, $ask_resultSet, $_SESSION['results']);
        }

        else{
            // Instantiate a new API
            $api1 = new api;
            // Instantiate a new formatter with the 3 result sets as properties
            $formatter1 = new formatter(new resultSet(), new resultSet(), new resultSet());

            // Google Results
            for($i=0;$i<($_SESSION['results']/10);$i++)
            {
                // Get offset
                $offset = 1+($i*10);
                // Call Google API
                $api1->googleApi($query1, $offset);

                // Set Google JSON Data
                $formatter1->setGoogleJson($api1->returnGoogleJsonData(), $api1->returnGoogleJsonResultFlag());
                $formatter1->formatGoogleJson($_SESSION['results'], $i*10, $query1);
            }

            // Bing Results
            for($i=0;$i<($_SESSION['results'] > 50 ? 2 : 1);$i++)
            {
                // Get offset
                $offset = 1+($i*50);
                // Call Bing API
                $api1->bingApi($query2, $_SESSION['results'], $offset);
                // Set BING JSON Data
                $formatter1->setBingJson($api1->returnBingJsonData(), $api1->returnBingJsonResultFlag());
                $formatter1->formatBingJson($_SESSION['results'], $i*50, $query2);
            }

            $arr = NULL;
            // Ask Results
            if($_SESSION['type']!='image'){
                $arr=askParser::getAggregatedSearchResults($q ,$_SESSION['results']);
                $formatter1->formatAskResults($arr, $query3, $offset);
            }
            $askResultFlag = (isset($arr) && count($arr)>0);

            // Send result sets 1,2 & 3 to Data Fusion Function
            $aggregator1->dataFusion($api1->returnGoogleJsonResultFlag(), $api1->returnBingJsonResultFlag(), $askResultFlag, $formatter1->returnResultSet('resultSet1'), $formatter1->returnResultSet('resultSet2'), $formatter1->returnResultSet('resultSet3'),$_SESSION['results']);
        }

        // Instantiate Cluster Object
        $cluster1 = new cluster;

        // Instantiate Stopword Dictionary
        $stopwordDictionary = new dictionary('stop-words-english1.txt');
        //$stopwordDictionary->loadStopwordFile();
        // Add Query to Stopwords
        $stopwordDictionary->addQueryToStopwords($cluster1->tokeniseString($q));
        $stopwords = $stopwordDictionary->returnStopwords();

        // Can use Title or Snippets for Clusters - Using Snippets

        //$aggTitles = $aggregator1->returnResultSetAggTitles();
        // Get array of snippets in list
        //$aggSnippets = $aggregator1->returnResultSetAggSnippets();

        // Find the cluster terms of interest
        // But don't include stopwords
        //$cluster1->findTerms($aggTitles, $stopwords);
        $cluster1->findTerms($aggregator1->returnResultSetAggSnippets(), $stopwords);
        // Count Term Freq
        //$cluster1->countTermFrequency($aggTitles);
        $cluster1->countTermFrequency($aggregator1->returnResultSetAggSnippets());
        // Set most frequet terms
        $cluster1->setMostFrequentTerms(10);
        //$cluster1->stopwordRemoval($stopwords);

        echo '<div class="row"><div class="span3"><h2>F-Clusters</h2>';
        // Print Clustered Terms
        $cluster1->displayMostFrequentTerms($q);
        // $cluster1->displayMostFrequentBinTerms($q);
        echo '</div><div class="span5"><h2>Results</h2>';
        // Print Cluser Term Results
        $aggregator1->printResultSetAggCluster(isset($_GET['term'])?$_GET['term']:$_GET['term']='');
        // end of DIV
        echo '</div></div> <!-- End of Class row -->';

        // Query Timer
        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;
        echo '</br>Query Time: '.$exec_time;
    }
    // ******************
    // ** BINA CLUSTERING
    // ******************
    else if($_SESSION['clust_op']=='b') // ** BINA CLUSTERING
    {
        // Instantiate Aggregator
        $aggregator1 = new aggregator(new resultSet());

        $conn = new mysqli("localhost", "root", "", "z-search");
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $google_sql_query = NULL;
        $bing_sql_query = NULL;
        $ask_sql_query = NULL;

        $google_result = NULL;
        $bing_result = NULL;
        $ask_result = NULL;

        if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
            $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'image' AND result_offset = '$offset'";
            $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'image' AND result_offset = '$offset'";

            $google_result = $conn->query($google_sql_query);
            $bing_result = $conn->query($bing_sql_query);
        }

        else {
            $google_sql_query = "SELECT * FROM results WHERE result_keywords = '$query1' AND result_source = 'Google' AND result_type = 'text' AND result_offset = '$offset'";
            $bing_sql_query = "SELECT * FROM results WHERE result_keywords = '$query2' AND result_source = 'Bing' AND result_type = 'text' AND result_offset = '$offset'";
            $ask_sql_query = "SELECT * FROM results WHERE result_keywords = '$query3' AND result_source = 'Ask' AND result_type = 'text' AND result_offset = '$offset'";

            $google_result = $conn->query($google_sql_query);
            $bing_result = $conn->query($bing_sql_query);
            $ask_result = $conn->query($ask_sql_query);
        }

        if ($google_result === FALSE || $bing_result === FALSE || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result === FALSE)) {
            echo "Error: " . $sql_query . "<br>" . $conn->error;
            $conn->close();
        }

        if($google_result->num_rows > 0 || $bing_result->num_rows > 0 || (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0)){
            echo  'From database!!';
            $google_result_flag = ($google_result->num_rows > 0)? true: false;
            $bing_result_flag = ($bing_result->num_rows > 0)? true: false;
            $ask_result_flag = (!(isset($_SESSION['type']) && $_SESSION['type']=='image') && $ask_result->num_rows > 0)? true: false;

            $google_resultSet = new resultSet();
            // output data of each row
            while($row = $google_result->fetch_assoc()) {
                $google_resultSet->addUrl($row["result_link"]);
                $google_resultSet->addTitle($row["result_title"]);
                $google_resultSet->addSnippet($row["result_snippet"]);
                $google_resultSet->addScore($row["result_score"]);
            }

            $bing_resultSet = new resultSet();
            // output data of each row
            while($row = $bing_result->fetch_assoc()) {
                $bing_resultSet->addUrl($row["result_link"]);
                $bing_resultSet->addTitle($row["result_title"]);
                $bing_resultSet->addSnippet($row["result_snippet"]);
                $bing_resultSet->addScore($row["result_score"]);
            }

            $ask_resultSet = new resultSet();
            if(!(isset($_SESSION['type']) && $_SESSION['type']=='image')){
                // output data of each row
                while($row = $ask_result->fetch_assoc()) {
                    $ask_resultSet->addUrl($row["result_link"]);
                    $ask_resultSet->addTitle($row["result_title"]);
                    $ask_resultSet->addSnippet($row["result_snippet"]);
                    $ask_resultSet->addScore($row["result_score"]);
                }
            }
            $conn->close();

            // Send result sets 1,2 & 3 to Data Fusion Function
            $aggregator1->dataFusion($google_result_flag, $bing_result_flag, $ask_result_flag, $google_resultSet, $bing_resultSet, $ask_resultSet, $_SESSION['results']);
        }
        else{
            // Instantiate a new API
            $api1 = new api;
            // Instantiate a new formatter with the 3 result sets as properties
            $formatter1 = new formatter(new resultSet(), new resultSet(), new resultSet());

            // Google Results
            for($i=0;$i<($_SESSION['results']/10);$i++)
            {
                // Get offset
                $offset = 1+($i*10);
                // Call Google API
                $api1->googleApi($query1, $offset);

                // Set Google JSON Data
                $formatter1->setGoogleJson($api1->returnGoogleJsonData(), $api1->returnGoogleJsonResultFlag());
                $formatter1->formatGoogleJson($_SESSION['results'], $i*10, $query1);
            }

            // Bing Results
            for($i=0;$i<($_SESSION['results'] > 50 ? 2 : 1);$i++)
            {
                // Get offset
                $offset = 1+($i*50);
                // Call Bing API
                $api1->bingApi($query2, $_SESSION['results'], $offset);
                // Set BING JSON Data
                $formatter1->setBingJson($api1->returnBingJsonData(), $api1->returnBingJsonResultFlag());
                $formatter1->formatBingJson($_SESSION['results'], $i*50, $query2);
            }

            $arr = NULL;
            // Ask Results
            if($_SESSION['type']!='image') {
                // Set ASK Results Data
                $arr = askParser::getAggregatedSearchResults($q, $_SESSION['results']);
                $formatter1->formatAskResults($arr, $query3, $offset);
            }

            $askResultFlag = (isset($arr) && count($arr)>0);
            // Send result sets 1,2 & 3 to Data Fusion Function
            $aggregator1->dataFusion($api1->returnGoogleJsonResultFlag(), $api1->returnBingJsonResultFlag(), $askResultFlag,$formatter1->returnResultSet('resultSet1'), $formatter1->returnResultSet('resultSet2'), $formatter1->returnResultSet('resultSet3'),$_SESSION['results']);
        }

        // Instantiate Cluster Object
        $cluster1 = new cluster;

        // Instantiate Stopword Dictionary
        $stopwordDictionary = new dictionary('stop-words-english2-short.txt');
        //$stopwordDictionary->loadStopwordFile();
        // Add Query to Stopwords
        $stopwordDictionary->addQueryToStopwords($cluster1->tokeniseString($q));
        $stopwords = $stopwordDictionary->returnStopwords();

        // Find the cluster terms of interest
        $cluster1->findBinaTerms($aggregator1->returnResultSetAggSnippets());
        // Count Term Freq
        //$cluster1->countTermFrequency($aggTitles);
        //$cluster1->countTermFrequency($aggregator1->returnResultSetAggSnippets());
        // Set most frequet terms
        //$cluster1->setMostFrequentTerms(10);
        //$cluster1->stopwordRemoval($stopwords);
        //echo $cluster1->countClusteredTerms();

        //$cluster1->displayClusteredTerms();
        $cluster1->setDocumentBinatures($aggregator1->returnResultSetAggSnippets());
        //$cluster1->printBinatures();
        //$cluster1->printBinatureSums();
        //echo $aggSnippets[0];
        $ticks = 3;
        $cluster1->setBindroids($ticks);
        $cluster1->binBinatures($ticks);

        $cluster1->setBinTerms(($ticks));

        echo '<div class="row"><div class="span3"><h2>Bina-clusters</h2>';
        // Print Clustered Terms
        //$cluster1->displayMostFrequentTerms($q);
        // $cluster1->displayMostFrequentBinTerms($q);
        $cluster1->displayBinTerms($q);

        echo '</div><div class="span5"><h2>Results</h2>';
        // Print Cluser Term Results
        //$aggregator1->printResultSetAggCluster(isset($_GET['term'])?$_GET['term']:$_GET['term']='');
        //pass binTerm and binatureSums of each result
        $aggregator1->printResultSetAggBinCluster((isset($_GET['binTerm'])?$_GET['binTerm']:$_GET['binTerm']=''), $cluster1->returnBins(), $cluster1->returnBinatureSums());

        // end of DIV

        // Helper
        //echo '</div><div class="span3"><h2>Helper</h2>';

        // end of DIV
        echo '</div></div> <!-- End of Class row -->';

        // Query Timer
        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;
        echo '</br>Query Time: '.$exec_time;
    }
}
?>