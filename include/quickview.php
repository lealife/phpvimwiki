<?php
require_once 'phpvimwiki.php';

class quickview extends phpvimwiki {
	protected $_quick_separator; // 区分之前的内容和quick list
	protected $_pre_wiki_a; // 之前的内容 array() 按行
	protected $_quick_path; // 所有文章的路径 array()
	protected $_wiki_root; // wiki根路径

	/**
	 *
	 * @param array $config = array(
	 *		'more_mark' => ,
	 *		'quick_view' => ,
	 *		'quick_view_separator' => ,
	 *		'wiki_root' => ,
	 *	)
	 *
	 */
	function __construct($config) {
		$this->_more_mark = $config['more_mark'];
		$this->_quick_separator = $config['quick_view_separator'];

		$this->_wiki_root = $config['wiki_root'];
		$quick_view_wiki_path = $this->_wiki_root. '/'. $config['quick_view']; // quick view wiki

		$quick_view_wiki = file_get_contents($quick_view_wiki_path); // 得到quick view nav 的内容

		// 得到之前的wiki 和 quick view list
		// 存到 $_pre_wiki_a 和 $_quick_path 中
		$this->_separateQuick($quick_view_wiki); 

		// 得到所有wiki的路径
		$this->_fixQuickPath(); 
	}

	/**
	 *
	 * 得到前面的内容和 quick view contents
	 *
	 */
	public function html() {
		// 前面的内容
		$pre_contents = parent::html($this->_pre_wiki_a);

		// 之后得到quikc view contents
		$quick_contents = array();
		$n = 0;
		foreach($this->_quick_path as $path) {
			$file = $this->_wiki_root .'/'. $path. '.wiki';

			$quick_wiki = file_get_contents($file);
			$this->setWikiPath($path);
			$quick_contents[$n] = parent::html($quick_wiki, true, true); // no_more, no_nav
			// title
			if($quick_contents[$n]['h1_text']) {
				$quick_contents[$n]['title'] = $quick_contents[$n]['h1_text'];
			} else {
				$quick_contents[$n]['title'] = '文章 '. ($n+1);
			}
			$quick_contents[$n]['href'] = 'index.php?action=view&wiki='. $path;
			$n++;
		}

		return array('pre_contents' => $pre_contents, 'contents' => $quick_contents);
	}

	/**
	 *
	 * 分隔 前面的内容与 quick view nav
	 * 存到 $this->_pre_wiki_a 和 $this->_quick_path
	 *
	 * @return void
	 *
	 */
	protected function _separateQuick($wiki) {
		$wiki_a = explode("\n", $wiki);

		foreach($wiki_a as $no => $each) {
			if(trim($each) != $this->_quick_separator) {
				$this->_pre_wiki_a[] = $each;
				unset($wiki_a[$no]);
			} else {
				unset($wiki_a[$no]);
				$this->_quick_path = $wiki_a;
				break;
			}
		}
	}

	/**
	 *
	 * 修正quick path
	 *
	 * @return void
	 *
	 */
	protected function _fixQuickPath() {
		foreach($this->_quick_path as $no => &$each) {
			$each = trim($each);
			$each = ltrim($each, '* '); // 前面的 * 

			if(!$each) {
				unset($this->_quick_path[$no]);
			}
		}
	}
}
