<?php
class askResult{
    private $title;
    private $abstract;
    private $link;
    private $score;
    private $pageNum;

    public function getScore(){
        return $this->score;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getAbstract(){
        return $this->abstract;
    }

    public function getLink(){
        return $this->link;
    }

    public function getPageNum(){
        return $this->pageNum;
    }

    public function  __construct($title,$link,$abstract,$pageNum,$rank){
        $this->title=$title;
        $this->link=$link;
        $this->abstract=$abstract;
        $this->pageNum=$pageNum;
        $this->score=$rank;
    }
}

class askParser{

    public static function complexQueryAsk($q){
        $q=str_replace(" NOT "," -",$q);
        $q=urlencode("$q");
        return $q;
    }

    public static function getAskSearchResultsByPage($query, $pageNum){
        $objArray= array();
        $url=askParser::formatAskQuery($query,$pageNum);

        $urlContent=file_get_contents($url);

        $formatContentStr=stristr($urlContent,"<div class=\"PartialSearchResults-item\" data-zen=\"true\">");

        $formatContentStr=stristr($formatContentStr,"<script>",true);

        $resultsInHtml=explode("class=\"PartialSearchResults-item\"",$formatContentStr);
        $count=(($pageNum-1)*9);

        foreach($resultsInHtml as $mkey=>$mvalue){
            $res = new askResult(askParser::extract_title($mvalue),askParser::extract_link($mvalue)
                ,askParser::extract_abstract($mvalue),$pageNum,$count);
            if((empty($res->getTitle())||empty($res->getLink())))
                continue;
            //Add res object to the array
            $objArray[]=$res;
            $count++;
        }

        return $objArray;
    }

    public static function getAggregatedSearchResults($query, $resultsNum){
        $objArray= array();
        $i=1;

        while(count($objArray)<=$resultsNum){
            $newArr=askParser::getAskSearchResultsByPage($query,$i);

            //No more results available so break
            if(count($newArr)<1)
                break;

            //Still more pages needs to be loaded to reach the required results number
            if(count($newArr)+count($objArray)<=$resultsNum)
                $objArray=array_merge($objArray,$newArr);

            //Pick certain amount of results to reach the required number from the last page to be loaded
            else{
                $j=count($objArray);
                foreach ($newArr as $key => $value) {
                    if($j++<$resultsNum){
                        $objArray[]=$value;
                    }
                }
                break;
            }
            $i++;
        }

        return $objArray;
    }

    public static function extract_title($htmlStr){
        $title1=stristr(stristr($htmlStr,"class=\"PartialSearchResults-item-title-link result-link\""),"</",true);
        $title1=stristr($title1,">");
        $title1=str_replace(">", "", $title1);

        return $title1;
    }

    public static function extract_abstract($htmlStr){
        $abstract=stristr(stristr($htmlStr,"class=\"PartialSearchResults-item-abstract\""),"</",true);
        $abstract=stristr($abstract,">");
        $abstract=str_replace(">", "", $abstract);

        return $abstract;
    }

    public static function extract_link($htmlStr){
        $link="";
        $link[0]=" ";
        $link=stristr(stristr($htmlStr,"class=\"PartialSearchResults-item-url\""),"</",true);
        $link=stristr($link,">");
        $link=str_replace(">", "", $link);
        $charArr=str_split($link);

        if($charArr[0]=='.'){
            $link=substr($link, 1);
        }

        return "http://".$link;
    }

    public static function formatAskQuery($query, $pageNum){
        $q="http://www.ask.com/web?q=";
        $q.=askParser::complexQueryAsk($query);
        $q.="&qsrc=998&page=";
        $q.=$pageNum;

        return $q;
    }
}
?>