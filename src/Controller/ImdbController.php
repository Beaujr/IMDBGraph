<?php
/**
 * Created by PhpStorm.
 * User: beaurudder
 * Date: 2014-08-09
 * Time: 6:37 PM
 */

namespace App\Controller;
use Cake\Error\NotFoundException;
use Cake\Core\Configure;
use Cake\Error;
use Cake\Utility\Inflector;
use App\Controller\imdbWeb;
use JsonSchema\Constraints\Object;

class ImdbController  extends AppController {



    public function index() {
        $path = func_get_args();

        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        $page = $subpage = $title_for_layout = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        if (!empty($path[$count - 1])) {
            $title_for_layout = Inflector::humanize($path[$count - 1]);
        }
        $this->set(compact('page', 'subpage', 'title_for_layout'));

        try {
            $this->render(implode('/', $path));
        } catch (Error\MissingViewException $e) {
            if (Configure::read('debug')) {
                throw $e;
            }
            throw new Error\NotFoundException();
        }
    }
    public function ajax() {

        if (count($this->request->params['pass']) < 2){
            return $this->redirect('/');
        }
        $this->autoRender = false;
        $this->layout = null ;

        $urlParams = $this->request->params['pass'];

        if($urlParams[0] == 'lm'){
            $filmDetails = array();
            $argument = ' -'.$urlParams[0].' ';

            $field = $urlParams[1];
            $python = APP.'python/imdbAccessor.py';
            $json= exec('python '.$python.$argument.$field);

//            $dir = '/Applications/XAMPP/htdocs/template/saintsrow.json';
//            $json = file_get_contents($dir);

            $result = json_decode($json);
            $arrayFilms = array();
            $nodes = array();
            $i = 0;
            foreach($result->Films as $film){
                $node = array();
                $node['label'] = $film->title;
                $node['target'] = $film->id;
                $node['type'] =3;
                $node['slug'] = $film->title;
                $node['value'] = 1;
                $node['name'] = $film->title;
                array_push($nodes, $node);
                $arrayFilms[$film->id] = $i;
                $i++;
            }
            foreach($result->Actors as $actor){
                $history = array();
                foreach($actor->films as $filmography){

                    if(array_key_exists($filmography, $arrayFilms)){
                        array_push($history, $filmography);
                    }
                }
                $historyStr = implode(",",$history);
                $node = array();
                $node['label'] = $actor->name;
                $node['target'] = $historyStr;
                $node['type'] = 1;
                $node['slug'] = $actor->name;
                $node['value'] = count($history);
                $node['name'] = $actor->name;
                $node['id'] = $actor->id;
                array_push($nodes, $node);

            }
            $links = array();
            for($x = 0; $x < count($nodes); $x++){
                $node = $nodes[$x];

                //if(node.target.indexOf(",") > -1){

                //}
                $targetArr = explode(",",$node['target']);

                    for($i = 0 ; $i < count($targetArr); $i++){
                        $link =array();
                        $link['source'] = $x;
                        $link['target'] = $arrayFilms[$targetArr[$i]];
                        array_push($links, $link);
                    }
                };
            $jsonResponse = array();
            $jsonResponse['nodes'] = $nodes;
            $jsonResponse['links'] = $links;
            $this->response->body(json_encode($jsonResponse));
            return $this->response;

        }
        $argument = ' -'.$urlParams[0].' ';
        $field = urlencode($urlParams[1]);
        $python = APP.'python/imdbAccessor.py';

        $result = exec('python '.$python.$argument.$field);
        $this->response->body($result);
        #return json_encode($result);
        return $this->response;
    }
    public function add() {
    }

} 