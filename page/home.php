<?php
/**
 * Home 主页 controller
 *
 */
class home extends controller {
	public $config;
	public $title;
	public $quick_contents;
	public $wiki_content; // 纯wiki
	public $html_content;
	public $pre_view_content;

	function __construct() {
		session_start();
		global $config;
		$this->config = &$config;
	}

	/**
	 *
	 * 得到quick view
	 *
	 */
	function actionIndex() {
		require_once './include/quickview.php';

		// 得到导航内容
		$quick_view_config = array(
			'wiki_root' => $this->config['wiki_root'],
			'more_mark' => $this->config['more_mark'],
			'quick_view' => $this->config['quick_view']. '.wiki',
			'quick_view_separator' => $this->config['quick_view_separator'],
		);
		$quick = new quickview($quick_view_config);
		$this->quick_contents = $quick->html();

		$this->title = "life";
		$this->_view = 'home_quickview';
	}

	/**
	 *
	 * 显示wiki
	 *
	 */
	function actionView() {
		if(!$this->config['wiki']) {
			$this->actionIndex();
			return;
		}
		// 处理获得的wiki路径
		$this->_fixWikiPath();

		// 查看 OR 建wiki
		// 是否有该wiki, 没有则创建
		$wiki_path = $this->_getWikiPath();
		if(!file_exists($wiki_path)) {
			// 转换成gb2312
			$wiki_path_gbk = iconv('UTF-8', 'GB2312//IGNORE', $wiki_path);
			if(!file_exists($wiki_path_gbk)) {
				$this->actionCreate();
				return;
			// 转成gb2312就有了, 那么干脆把文件重命名为utf8, 下次不要再转了
			} else {
//                echo 'utf8->gbk';
				$wiki_path = $wiki_path_gbk;
				/*
				// 问题: 在win下把文件名改成utf8的显示为乱码
				if(!rename($wiki_path_gbk, $wiki_path)) {
					$this->html_content = '无法浏览!';
					return;
				}
				 */
			}
		}
		// 存在, 解析之, 显示
		$wiki_content = file_get_contents($wiki_path);
		$phpvimwiki_config = array(
			'wiki_content' => $wiki_content,
			'wiki_path' => $this->config['wiki'],
			'more_mark' => $this->config['more_mark'],
			'image_path' => $this->config['image_path'],
		);
		$phpvimwiki = new phpvimwiki($phpvimwiki_config);
		$html = $phpvimwiki->html();

		$this->html_content = '<div id="article">'. $html['content']. '</div>';
		$this->title = $html['title'];

		$this->_view = 'home_view';
	}

	/**
	 * 
	 * 创建建wiki
	 *
	 */
	public function actionCreate() {
		if(!$this->isLogin()) {
			$this->_redirect();
			return;
		}

		$this->_view = 'home_create';
		$this->title = 'Create '. $this->config['wiki'];

		$this->wiki_content = $_POST['wiki'];
		if($_POST['create']) {
			// 判断是否已存在该wiki
			$wiki_path = $this->_getWikiPath();
			if(file_exists($wiki_path)) {
				$this->msg = "The wiki {$this->config['wiki']} is already exists.";
				return;
			}

			// OK 没有则创建之
			// 创建成功则跳转到编辑页面
			if(file_put_contents($wiki_path, $_POST['wiki'])) {
				$this->msg = 'Create Success. Now you can Edit it!';
				$this->actionEdit();
			} else {
				$this->msg = 'Create error!';
			}
		} else if($_POST['pre_view']) {
			$this->_preView();
		}
	}

	/**
	 *
	 * 编辑wiki
	 *
	 */
	public function actionEdit() {
		if(!$this->isLogin()) {
			$this->_redirect();
			return;
		}
		$this->_view = 'home_edit';
		$this->title = 'Edit '. $this->config['wiki'];

		$wiki_path = $this->_getWikiPath();

		// 文件不存在, 跳转到主页
		if(!file_exists($wiki_path)) {
			$this->actionIndex();
			return;
		}
		$this->wiki_content = file_get_contents($wiki_path);

		// 编辑
		if($_POST['edit']) {
			$this->wiki_content = $_POST['wiki'];
			// 覆盖
			if(file_put_contents($wiki_path, $_POST['wiki'])) {
				$this->msg = array(
					'status' => 'success',
					'msg' => 'Edit Success! <a href="index.php?action=view&wiki='. $this->config['wiki'].'">返回</a>',
				);
			} else {
				$this->msg = array(
					'status' => 'error',
					'msg' => 'Edit Error!',
				);
			}
		// 预览
		} else if($_POST['pre_view']) {
			$this->wiki_content = $_POST['wiki'];
			$this->_preView();
		}
	}

	/**
	 * 
	 * 预览, 生成html
	 *
	 */
	protected function _preView() {
		$phpvimwiki_config = array(
			'wiki_content' => $_POST['wiki'],
			'wiki_path' => $this->config['wiki'],
			'more_mark' => $this->config['more_mark'],
		);
		$phpvimwiki = new phpvimwiki($phpvimwiki_config);
		$html = $phpvimwiki->html();
		$this->pre_view_content = '<div id="article">'. $html['content']. '</div>';
	}

	/**
	 * 
	 * 登录
	 *
	 */
	public function actionLogin() {
		$this->_view = 'home_login';
		if($_POST) {
			if(!$this->isXSS() && $this->config['username'] == $_POST['username'] && md5($_POST['password']) == $this->config['password']) {
				$_SESSION['username'] = $this->config['username'];
				$this->msg = "Welcome {$_SESSION['username']}.";
			} else {
				$this->msg = 'Error, Please Try Again.';
			}
		}
	}

	/**
	 *
	 * 注销
	 *
	 */
	public function actionLogout() {
		unset($_SESSION['username']);
		header("Location: {$_SERVER['HTTP_REFERER']}");
	}


	/**
	 *
	 * config['wiki'] = 'php/life'
	 *	= '../path/../../../life'; 不行
	 *
	 */
	protected function _fixWikiPath() {
		while(strpos($this->config['wiki'], '..') !== false) { // 防止再次出现.., 比如... => ..
			$this->config['wiki'] = str_replace('..', '.', $this->config['wiki']);
		}
	}

	/**
	 *
	 * 得到wiki的绝对路径
	 *
	 */
	protected function _getWikiPath() {
		return $this->config['wiki_root']. '/'. $this->config['wiki']. '.wiki';
	}
}
