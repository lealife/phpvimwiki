<?php
error_reporting(~E_ALL & ~E_NOTICE);

$config = require 'config.php';

define('ROOT', dirname(__FILE__));

require './include/phpvimwiki.php';
require './page/controller.php';

// 得到page, action
$action = $_GET['action'];
if(!$action) {
	$page = 'home';
	$action = 'index';
} else if(strpos($action, ':') === false) {
	$page = 'home';
} else {
	$page_action = explode(':', $action);
	$page = $page_action[0];
	$action = $page_action[1];
}
$config['wiki'] = $_GET['wiki']; // 当前要查看的wiki 如 php/index

$controller = new controller();
$controller->fetch($page, $action);

?>
