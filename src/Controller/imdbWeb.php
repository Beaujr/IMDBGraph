<?php
/**
 * Created by PhpStorm.
 * User: beaurudder
 * Date: 2014-08-09
 * Time: 11:04 PM
 */

namespace App\Controller;
use App\Controller\Imdb;

class imdbWeb {
    private $filmRows = array();
    private $imdbRows = array();
    private $movies = array();

    public function loadFilms($movies){
        $movieName = $movies;
        //$output = $_GET['o']
        $actorStack = array();
        $filmStack = array();
        $this->imdbRows = array();
//FIELD VALUES FOR ACTORS
        $imdbFields = array();
        $fieldVals = array();
        $fieldVals['name'] = 'imdbID';
        array_push($imdbFields,$fieldVals);
        $fieldVals['name'] = 'imdbActor';
        array_push($imdbFields,$fieldVals);
        $fieldVals['name'] = 'imdbFilms';
        array_push($imdbFields,$fieldVals);
        $actorStack['fields'] = $imdbFields;
//FIELD VALUES FOR FILMS
        $imdbFields = array();
        $fieldVals = array();
        $fieldVals['name'] = 'imdbID';
        array_push($imdbFields,$fieldVals);
        $fieldVals['name'] = 'imdbName';
        array_push($imdbFields,$fieldVals);
        $filmStack['fields'] = $imdbFields;
//ROW VALUES FOR FILMS

//START LOADING MOVIE DATA
        $movies = explode(',',$movieName);
        $this->movies = $movies;
        $sizeOfMovies = sizeof($movies);
        for ($j = 0; $j < $sizeOfMovies; $j++){

            $this->getFilmsCast($movies[$j]);
        }
        $filmStack['rows'] = $this->filmRows;
        $actorStack['rows'] = $this->imdbRows;

        $resultSet = array();
        $resultSet['ACTORS'] = $actorStack;
        $resultSet['FILMS'] = $filmStack;
        return $resultSet;
    }

    private function getFilmsCast($movieName){
        $filmData = array();
        $mArr = $this->getMovieInfo($movieName, 'raw');

        array_push($filmData,$mArr['TITLE_ID']);
        array_push($filmData,$mArr['TITLE']);

        array_push($this->filmRows, $filmData);
        foreach ($mArr['CAST'] as $k=>$v){

            $imdbID = array();
            $actorArrayPlace = $this->isActorInArray($k);
            $filmography = array();
            if($actorArrayPlace == -1){
                $filmography = $this->getActorFilmography($k);
                //echo sizeof($filmography);
                array_push($imdbID,$k);
                array_push($imdbID, $mArr['CAST'][$k]);
                array_push($imdbID,$mArr['TITLE_ID']);
                array_push($this->imdbRows, $imdbID);
            } else {
                $this->imdbRows[$actorArrayPlace][2] = $this->imdbRows[$actorArrayPlace][2] . ",".$mArr['TITLE_ID'];
            }
            for($f = 0; $f<sizeof($filmography);$f++){
                if($this->isFilmInArray($filmography[$f]) < 0){

                    //echo $filmography[$f];
                    array_push($this->movies,$filmography[$f]);
                }
            }

        }

        //return $imdbID;

    }
    function isActorInArray($ActorID){

        $size = sizeof($this->imdbRows);
        for($i=0; $i < $size; $i++){
            if(strcmp($this->imdbRows[$i][0], $ActorID) == 0){
                return $i;
            }
        }
        return -1;

    }
    function isFilmInArray($FilmID){

        $size = sizeof($this->filmRows);
        for($i=0; $i < $size; $i++){
            if(strcmp($this->filmRows[$i][0], $FilmID) == 0){
                return $i;
            }
        }
        return -1;

    }

    function geturl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/".rand(3,5).".".rand(0,3)." (Windows NT ".rand(3,5).".".rand(0,2)."; rv:2.0.1) Gecko/20100101 Firefox/".rand(3,5).".0.1");
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    function getActorFilmography($imdbActorId){
        $imdbUrl = "http://www.imdb.com/name/".$imdbActorId."/";
        $arr = array();
        $html = $this-> geturl($imdbUrl);
        $startStripLoc = strrpos($html,"<div id=\"filmography\">");
        $endStripLoc = strrpos($html,"<h2>Message Boards</h2>");
        $condensedHTML = substr($html, $startStripLoc, $endStripLoc);
        $arr2 = array();
        /*preg_match_all('/<a href="\/title\/(.*?)\/".*?>(.*?)<\/a>/ms', $html , $arr2);*/
        preg_match_all('/<a href="\/title\/(.*?)\//ms', $condensedHTML, $arr2);

        //echo sizeOf($arr2[1]);
        /*global $f;
        for($i=0; $i < sizeOf($arr2[1]); $i++){

            fwrite($f, $arr2[1][$i]);
            //echo "\n";
            //echo $arr2[1][$i];
            //echo $arr2[1][3];
        }*/

        return $arr2[1];
    }

    function getMovieInfo($m, $o)
    {
        $movieName = $m;
        $output = strtolower($o);
        if($output != "xml" && $output != "json" && $output != "jsonp" && $output != "raw"){
            $output = "xml"; //Set default to XML
        }

        $i = new Imdb();
        $mArr = array_change_key_case($i->getMovieInfo($movieName), CASE_UPPER);

        if($output == "raw") {
            return $mArr;
        }

        ///////////////[ XML Output ]/////////////////
        if($output == "xml") {
            header("Content-Type: text/xml");
            $doc = new DomDocument('1.0');
            $doc->formatOutput = true;
            $movie = $doc->createElement('MOVIE');
            $movie = $doc->appendChild($movie);
            foreach ($mArr as $k=>$v){
                if(is_array($v)){
                    $node = $doc->createElement($k);
                    $node = $movie->appendChild($node);
                    $c = 0;
                    foreach($v as $a){
                        $c++;
                        $child = $doc->createElement($k . "_");
                        $child = $node->appendChild($child);
                        $child->setAttribute('n', $c);
                        $value = $doc->createTextNode($a);
                        $value = $child->appendChild($value);
                    }
                } else {
                    $node = $doc->createElement($k);
                    $node = $movie->appendChild($node);
                    $value = $doc->createTextNode($v);
                    $value = $node->appendChild($value);
                }
            }
            $xml_string = $doc->saveXML();
            return $xml_string;
        } //End XML Outout

        ///////////////[ JSON Output ]/////////////////
        if($output == "json") {
            header('Content-type: application/json');
            return json_encode($mArr);
        } //End JSON Outout

        ///////////////[ JSONP Output ]/////////////////
        if($output == "jsonp") {
            header('Content-type: application/json');
            echo isset($_GET['callback']) ? $_GET['callback']."(". json_encode($mArr) .")" : json_encode($mArr);
        } //End JSONP Outout
    }
} 