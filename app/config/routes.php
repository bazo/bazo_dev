<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$routes = array();
$routes[] = new Route('index.php', array(
    'module' => 'Front',
    'presenter' => 'HomePage',
    'action' => 'homepage',
	), Route::ONE_WAY);
$routes[] = new Route('admin/static-content/edit/<item>', array(
	'module' => 'Admin',
	'presenter' => 'StaticContent',
	'action' => 'edit',
	'item' => NULL
));
$routes[] = new Route('admin/preview/<action>', array(
	'module' => 'Front',
	'presenter' => 'Preview',
	'action' => 'default'
));
$routes[] = new Route('admin/<presenter>/<action>/<id>', array(
	'module' => 'Admin',
	'presenter' => 'Dashboard',
	'action' => 'default',
	'id' => NULL
));
$routes[] = new Route('', array(
	'module' => 'Front',
	'presenter' => 'HomePage',
	'action' => 'homepage',
));
$routes[] = new Route('rss/<category>', array(
	'presenter' => 'Rss',
	'module' => 'Front',
	'action' => 'rss',
	'category' => null
));
$routes[] = new Route('search', array(
	'presenter' => 'Search',
	'module' => 'Front',
	'action' => 'emptyQuery'
));
$routes[] = new Route('search/<query>', array(
	'presenter' => 'Search',
	'module' => 'Front',
	'action' => 'search',
	'query' => null
));
$routes[] = new Route('<category>/', array(
	'module' => 'Front',
	'presenter' => 'Page',
	'action' => 'categoryView',
));
$routes[] = new Route('<category>/<page>/', array(
	'module' => 'Front',
	'presenter' => 'Page',
	'action' => 'pageView',
	'page' => null
));
?>
