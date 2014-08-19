<?php
/**
 * Created by PhpStorm.
 * User: beaurudder
 * Date: 2014-08-09
 * Time: 5:53 PM
 */

namespace App\Controller;
use Cake\Error\NotFoundException;

class ArticlesController extends AppController {
    public function index() {
        $articles = $this->Articles->find('all');
        $this->set(compact('articles'));
    }
    public function view($id = null) {
        if (!$id) {
            throw new NotFoundException(__('Invalid article'));
        }

        $article = $this->Articles->get($id);

        $this->set(compact('article'));
    }
    public function add() {
        $article = $this->Articles->newEntity($this->request->data);
        if ($this->request->is('post')) {
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        $this->set('article', $article);
    }
}