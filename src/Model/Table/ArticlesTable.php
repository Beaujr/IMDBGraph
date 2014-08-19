<?php
/**
 * Created by PhpStorm.
 * User: beaurudder
 * Date: 2014-08-09
 * Time: 5:52 PM
 */

namespace App\Model\Table;

use Cake\ORM\Table;



class ArticlesTable extends Table {
    public function initialize(array $config) {
        $this->addBehavior('Timestamp');
    }

}