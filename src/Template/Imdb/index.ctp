<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Debugger;
use Cake\Validation\Validation;

?>

<!-- Intro Section -->
<section id="intro" class="intro-section">
    <div class="text-vertical-center">
    <div class="container">
        <div class="row">
         <div class="col-lg-12">
            <!-- <div class="row">
                 <div class="col-lg-12">
                     <a class="btn btn-default page-scroll" href="#about">Click Me to Scroll Down!</a>
                 </div>
             </div>-->
             <div class="row top-buffer ">
                 <input type="text" class="search form-control"  style="width:235px;" placeholder="Search" ng-model="searchOne">
                 <button type="submit" ng-model="s1" class="btn btn-default" ng-click="filmSelect(searchOne, 'one')" ng-disabled="!searchOne">Search</button>
             </div>
             <div>
                 <select class="btn btn-dark btn-lg" style="width:235px;" ng-disabled="!filmOne" ng-model="selectedOne"
                         ng-options="film.title for film in filmOne track by film.id">
                     <option value=''>Select</option>
                 </select>
             </div>
             <!--</div>-->
             <div class="row top-buffer">
                 <a href="#graph" ng-disabled="!selectedOne" class="btn btn-success btn-lg" ng-click="compare($selectedOne)">See Degrees!</a>
             </div>

        </div>
    </div>
        </div>

</section>

<!-- Services Section -->
<section id="graph" class="services-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1>Graph Section</h1>
            </div>
        </div>
    </div>
</section>