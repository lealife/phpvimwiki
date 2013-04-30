<?php
/**
 *
 * controller 页面控制器
 *
 * 每个controller都继承它, 比如home
 *
 */
class controller {
	public $view_content;
	protected $_view;
	protected $_layout = 'phpvimwiki';

	/**
	 *
	 * 执行page::action();
	 * 渲染视图
	 *
	 */
	public function fetch($page, $action) {
		$page_file = "page/{$page}.php";
		if(file_exists($page_file)) {
			include $page_file;
			$page_obj = new $page();
		} else {
			// 执行默认home::actionIndex();
			include 'page/home.php';
			$page_obj = new home();
			$action = 'index';
		}

		$method_name = 'action'. ucwords($action);
		if(!method_exists($page_obj, $method_name)) {
			$method_name = 'actionIndex';
		}

		call_user_func(array($page_obj, $method_name));

		// 渲染视图
		$page_obj->_renderView();
	}

	/**
	 * 
	 * 默认action
	 *
	 */
	public function actionIndex() {

	}

	/**
	 *
	 * 渲染视图
	 *
	 */
	protected function _renderView() {
		// view
		if($this->_view) {
			ob_start();
			include "view/$this->_view.php";
			$this->view_content = ob_get_clean();
		}

		// layout
		require "layout/{$this->_layout}.php";
	}

	/**
	 *
	 * 是否已登录
	 *
	 */
	public function isLogin() {
		return $_SESSION['username'];
	}

	public function getXSSkey() {
		return $_SESSION['xss_key'] = md5('life'. time());
	}

	/**
	 *
	 * 是否是跨站攻击?
	 *
	 */
	protected function isXSS() {
		return $_POST['xss_key'] != $_SESSION['xss_key'];
	}

	/**
	 * 
	 * 跳转
	 *
	 */
	protected function _redirect($href = '') {
		if(!$href) {
			header("Location: {$_SERVER['HTTP_REFERER']}");
		}
	}

}
