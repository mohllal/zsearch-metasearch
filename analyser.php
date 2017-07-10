<?php
echo '<h1>Analyser</h1>';

class analyser
{
    // Properties
    private $queries = array();

    private $goldStandardResults = array();
    private $goldStandardDocsRetrieved = 100;

    private $googleResults = array();
    private $googlePrecision = array();
    private $googleAveragePrecisions = array();
    private $googleDocsRetrieved = 0;
    private $googleRelevantResults = 0;
    private $googleRelevantResultsAtNum = 0;
    private $googleAP = 0;

    private $bingResults = array();
    private $bingPrecision = array();
    private $bingAveragePrecisions = array();
    private $bingDocsRetrieved = 0;
    private $bingRelevantResults = 0;
    private $bingRelevantResultsAtNum = 0;
    private $bingAP = 0;

    private $aggregatedResults = array();
    private $aggregatedPrecision = array();
    private $aggregatedAveragePrecisions = array();
    private $aggregatedDocsRetrieved = 0;
    private $aggregatedRelevantResults = 0;
    private $aggregatedRelevantResultsAtNum = 0;
    private $aggregatedAP = 0;

    // Member functions

    // Load queries
    public function loadQueries($filename, $numQueries)
    {
        $sh = fopen(dirname(__FILE__).'/metrics/'.$filename, 'r') or die("Couldn\'t open file, sorry");

        for($i=0;$i<$numQueries;$i++)
        {
            $line=fgets($sh);
            array_push($this->queries, $line);
        }
    }

    // Return queries
    public function returnQueries($i)
    {
        return $this->queries[$i];
    }

    // Display queries
    public function displayQueries()
    {
        foreach($this->queries as $item)
            echo '</br>'.$item;
    }

    public function loadArray($filename, $engine, $numResults)
    {
        $resultSet =  $engine.'Results';

        // Open the file
        $sh = fopen(dirname(__FILE__).'/metrics/'.$filename, 'r') or die("Couldn\'t open file, sorry");

        for($i=0;$i<$numResults;$i++)
        {
            $line=fgets($sh);

            if($engine == "goldStandard")
            {
                // Remove numbers
                for($j = 0;$j < strlen($line);$j++)
                {
                    if($line[$j] == ' ') break;
                }

                $line = substr($line, $j+1);
                $line = $this->cleanLine($line);
            }

            array_push($this->$resultSet, trim($line));
        }
    }

    // Clean link
    public function cleanLine($line) {
        $line = strtolower(strip_tags(trim($line)));

        if (substr($line,-1,1) == '/')
            $line = substr($line,0,strlen($line) - 1);

        return $line;
    }

    // Display array
    public function displayArray($array)
    {
        foreach($this->$array as $item)
            echo '</br>'.$item;
    }

    public function analyse($standard, $engine, $precision, $start, $end)
    {
        // Name result sets and counters
        $resultSet = $engine.'Results';
        $resultSetPrecision = $engine.'RelevantResults';
        $resultSetPrecisionAtNum = $engine.'RelevantResultsAtNum';
        $resultSelection = $this->$resultSet;
        $engineDocsRetrieved = $engine.'DocsRetrieved';
        $engineAveragePrecisions = $engine.'AveragePrecisions';
        $engineAP = $engine.'AP';

        // standard set
        unset($standardSet);

        $standardSet = array_slice($this->$standard, $start, 100);

        // Set set's counters to zero
        $this->$resultSetPrecision = 0;
        $this->$resultSetPrecisionAtNum = 0;
        $this->$engineDocsRetrieved = 0;
        $this->$engineAP =0;
        $qap = 0;
        $rank = 0;

        // Identify Relevant Documents
        for($i=$start;$i<$end;$i++)
        {
            $rank++;
            if(strlen($resultSelection[$i]) > 2)
                $this->$engineDocsRetrieved++;

            if(in_array($resultSelection[$i], $standardSet))
            {
                $this->$resultSetPrecision++;
                $qap += $this->$resultSetPrecision/$rank;
            }
        }

        $this->$engineAP = $qap/100;
        array_push($this->$engineAveragePrecisions, $this->$engineAP);

        // Identify top 10 relevant documents
        $temp = array_slice($this->$standard, $start, $precision);

        for($i=$start;$i<$end;$i++)
        {
            if ($i >= ($start + $precision)) break;

            if(in_array($resultSelection[$i], $temp))
            {
                $this->$resultSetPrecisionAtNum++;
            }
        }
    }

    public function displayScoreTable($q, $precision)
    {
        echo '<table border="1">
			<tr>
				<th style="width:100px">Query</th>
				<th style="width:100px">Engine</th>
				<th style="width:100px">Precision</th>
				<th style="width:100px">Avg Prec</th>
				<th style="width:100px">P@'.$precision.'</th>
				<th style="width:100px">Recall</th>
				<th style="width:100px">F-measure</th>
			</tr>
			<tr>
				<td rowspan="4" >'.$q.'</td>
				<td>Google</td>';
        //
        echo '<td>'.(round($this->googleRelevantResults/$this->googleDocsRetrieved, 4)).'</td>';
        array_push($this->googlePrecision, ($this->googleRelevantResults/$this->googleDocsRetrieved));
        // AP
        echo '<td>'.(round($this->googleAP, 4)).'</td>';
        echo '<td>'.(round($this->googleRelevantResultsAtNum/$precision, 4)).'</td>';
        echo '<td>'.(round($this->googleRelevantResults/$this->goldStandardDocsRetrieved, 4)).'</td>';
        echo '<td>'.(round((2 * ($this->googleRelevantResults/$this->googleDocsRetrieved) * ($this->googleRelevantResults/$this->goldStandardDocsRetrieved)) / (($this->googleRelevantResults/$this->googleDocsRetrieved) + ($this->googleRelevantResults/$this->goldStandardDocsRetrieved)), 4)).'</td>';

        //
        echo '</tr><tr><td>Bing</td>';
        echo '<td>'.(round($this->bingRelevantResults/$this->bingDocsRetrieved, 4)).'</td>';
        array_push($this->bingPrecision, ($this->bingRelevantResults/$this->bingDocsRetrieved));
        // AP
        echo '<td>'.(round($this->bingAP, 4)).'</td>';
        echo '<td>'.(round($this->bingRelevantResultsAtNum/$precision, 4)).'</td>';
        echo '<td>'.(round($this->bingRelevantResults/$this->goldStandardDocsRetrieved, 4)).'</td>';
        echo '<td>'.(round((2 * ($this->bingRelevantResults/$this->bingDocsRetrieved) * ($this->bingRelevantResults/$this->goldStandardDocsRetrieved)) / (($this->bingRelevantResults/$this->bingDocsRetrieved) + ($this->bingRelevantResults/$this->goldStandardDocsRetrieved)), 4)).'</td>';

        //
        echo '</tr><tr><td>Aggregated</td>';
        echo '<td>'.(round($this->aggregatedRelevantResults/$this->aggregatedDocsRetrieved, 4)).'</td>';
        array_push($this->aggregatedPrecision, ($this->aggregatedRelevantResults/$this->aggregatedDocsRetrieved));
        // AP
        echo '<td>'.(round($this->aggregatedAP, 4)).'</td>';
        echo '<td>'.(round($this->aggregatedRelevantResultsAtNum/$precision, 4)).'</td>';
        echo '<td>'.(round($this->aggregatedRelevantResults/$this->goldStandardDocsRetrieved, 4)).'</td>';
        echo '<td>'.(round((2 * ($this->aggregatedRelevantResults/$this->aggregatedDocsRetrieved) * ($this->aggregatedRelevantResults/$this->goldStandardDocsRetrieved)) / (($this->aggregatedRelevantResults/$this->aggregatedDocsRetrieved) + ($this->aggregatedRelevantResults/$this->goldStandardDocsRetrieved)), 4)).'</td>';
        //
        echo '</tr></table> ';

        echo '<h2>MAPs</h2>Google: '.(round(array_sum($this->googleAveragePrecisions)/count($this->googleAveragePrecisions), 4)).'</br>';
        echo 'Bing: '.(round(array_sum($this->bingAveragePrecisions)/count($this->bingAveragePrecisions), 4)).'</br>';
        echo 'Aggregated: '.(round(array_sum($this->aggregatedAveragePrecisions)/count($this->aggregatedAveragePrecisions), 4)).'</br>';
    }

    // Display precisions
    public function displayAveragePrecisions($engine)
    {
        echo '<h2>Average Precisions: '.$engine.'</h2>';
        $resultSet = $engine.'AveragePrecisions';

        foreach($this->$resultSet as $result)
            echo '</br>'.$result;
    }

} // End of Class

// Initialise analyser
$analyser1 = new analyser;
$numQueries = 50;
$numResults = 5000;
$range = 100;
$precision = 10;

// Load queries
$analyser1->loadQueries('trec2012-queries.txt', 50);
//$analyser1->displayQueries();

// Load Gold Standard
$analyser1->loadArray('relevance_judgments.txt','goldStandard', $numResults);
//$analyser1->displayArray('goldStandardResults');

// Load Google results
$analyser1->loadArray('Google','google', $numResults);

// Load Bing results
$analyser1->loadArray('Bing','bing', $numResults);

// Load Aggregated results
$analyser1->loadArray('Aggregated','aggregated', $numResults);

// Analyse
for($i=0;$i<$numQueries;$i++)
{
    // enumerate query
    echo '<h3>QUERY '.($i+1).'</h3>';

    // Analyse Google results
    $analyser1->analyse('goldStandardResults', 'google', $precision, ($i*$range), (($i+1)*$range));

    // Analyse Bing results
    $analyser1->analyse('goldStandardResults', 'bing', $precision, ($i*$range), (($i+1)*$range));

    // Analyse Aggregated results
    $analyser1->analyse('goldStandardResults', 'aggregated', $precision, ($i*$range), (($i+1)*$range));

    // Display scores
    $analyser1->displayScoreTable($analyser1->returnQueries($i), $precision);
}
?>