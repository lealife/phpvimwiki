<?php
/**
 * php's vimwiki
 *
 * @author life
 *
 */
class phpvimwiki {
	public $html = '';
	public $wiki_a;
	protected $_code_start; // 代码
	protected $_p_start; // 段落
	protected $_table_start; // 表格
	protected $_table_html;
	protected $_heading_seq; // 标题前面的序号
	protected $_heading;
	protected $_no;
	protected $_meet_more; // 是否遇见了 more_mark
	protected $_more_mark = '--more--';
	// 占位符
	protected $_title;
	protected $_nav_title;
	protected $_has_nav;
	protected $_has_title;
	protected $_nav_token = '<!--nav-->';

	protected $_image_ext = array('jpg', 'jpeg', 'png', 'gif');

	// 当前wiki路径 php/life/a
	protected $_wiki_path; 

	// 该wiki的上一层路径 比如 php/life/a 表示 php/life/a.wiki, 上层路径是 php/life
	// 作用: 创建链接时有用
	protected $_pre_wiki_path; 

	protected $_image_path = 'public/image';

	// 代码暂存, 免得处理 array()
	protected $_pre; // array('<?php....', );
	protected $_code;
	protected $_pre_toc; // array('<!--pre0-->', '<!--pre1-->')
	protected $_code_toc; // array('<!--code0-->, '<!--code1-->')

	/**
	 *
	 * @param array $config = array(
	 *		'wiki_content' => ,
	 *		'wiki_path' => ,
	 *		'more_mark' => ,
	 *	);
	 *
	 */
	function __construct($config) {
		if($config['more_mark']) {
			$this->_more_mark = $config['more_mark'];
		}
		if($config['image_path']) {
			$this->_image_path = $config['image_path'];
		}

		$this->setWikiPath($config['wiki_path']);
		$this->_init($config['wiki_content']);
	}

	/**
	 *
	 * 初始化
	 *
	 */
	function _init($wiki = '') {
		if($wiki) {
			if(!is_array($wiki)) {
				// 先处理全局, 把字符串的{{{ }}}, ``代码部分拿出来
				$this->_initPreCode($wiki);
				// 处理链接
				$this->_link($wiki);
				// 处理字体
				$this->_font($wiki);

				$this->wiki_a = explode("\n", $wiki);
			} else {
				$this->wiki_a = $wiki;
			}
		}

		// 1表<h1> 不编号, 编码从<h2>开始!
		$this->_heading_seq = array(2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
		$this->_code_start = false;
		$this->_p_start= false;
		$this->_table_start = false;
		$this->_table_html = '';
		$this->_meet_more = false;
		$this->heading = array();
		$this->_title = '';
		$this->_nav_title = '';
		$this->_has_title = false;
		$this->_has_nav = false;

		$this->_clearList();
	}

	/**
	 *
	 * 先整体处理{{{ }}} 和 ` `
	 * 各自存在$_pre和$_code中
	 * 各占位符存在$_pre_toc, $_code_toc中
	 *
	 */
	function _initPreCode(&$wiki) {
		$this->_stripPre($wiki);
		$this->_stripCode($wiki);
	}

	/**
	 *
	 * 最后把代码还原
	 *
	 */
	protected function _backPreCode(&$html) {
		$html = str_replace($this->_pre_toc, $this->_pre, $html);
		$html = str_replace($this->_code_toc, $this->_code, $html);
	}

	/**
	 *
	 * 从wiki中把`code`拿出放并放入占位符
	 *
	 */
	protected function _fixCode($matches) {
		if($matches[1]) {
			$this->_code[$this->_n] = '<code>'. htmlspecialchars($matches[1]). '</code>'; // 将< > 转义

			$this->_code_toc[$this->_n] = "<!--code". $this->_n. "-->";
			return $this->_code_toc[$this->_n++];
		}
		return '';
	}
	function _stripCode(&$wiki) {
		$this->_n = 0;
		$wiki = preg_replace_callback('/`(.*?)`/s', array($this, '_fixCode'), $wiki);
	}

	/**
	 *
	 * 从wiki中把{{{code}}}拿出放并放入占位符
	 *
	 */
	protected function _fixPre($matches) {
		// 不是空代码
		if($matches[1]) {
			$this->_pre[$this->_n] = '<pre>'. htmlspecialchars($matches[1]). '</pre>'; // 将< > 转义

			$this->_pre_toc[$this->_n] = "<!--pre". $this->_n. "-->";
			return $this->_pre_toc[$this->_n++];
		}
		// 否则去掉{{{}}}
		return '';
	}
	function _stripPre(&$wiki) {
		$this->_n = 0;
		$wiki = preg_replace_callback('/{{{(.*?)}}}/s', array($this, '_fixPre'), $wiki);
	}

	/**
	 * 
	 * 设置wiki 路径
	 * 在处理内链时有用
	 *
	 */
	public function setWikiPath($path) {
		$this->_wiki_path = $path;
		$this->_pre_wiki_path = substr($path, 0, strrpos($path, '/'));
	}

	/**
	 *
	 * 将wiki转换为html
	 * 1. 处理代码
	 * 2. 处理标题
	 * 3. 处理表格
	 * 4. 处理段落, 列表, 字体
	 * 5. 最后处理
	 * 6. 生成导航
	 *
	 * @param array || string || void $wiki_a
	 * @param bool $no_more 为真, 会截断
	 */
	public function html($wiki_a = '', $no_more = false, $no_nav = false) {
		$html = '';
		$h1_text = '';
	
		if($wiki_a) {
			// 初始化
			$this->_init($wiki_a);
		}
		// 加一个空行 更好处理一些.
		$this->wiki_a[] = '';

		$this->_startP($html);

		$cnt = count($this->wiki_a);

		for($this->_no = 0; $this->_no < $cnt; $this->_no++) {
			$line = $this->wiki_a[$this->_no];

			if(!$this->_code_start) {
				$line = trim($line, "\r"); // \r把它去掉!
			}

			// 不能解析成html
			if(substr(ltrim($line), 0, 7) == '%nohtml') {
				return array('content' => '');
			}
			// 处理占位符
			if(!$this->_has_title || !$this->_has_nav) {
				if($this->_placeHolder($html, $line)) continue;
			}

			// 截断
			// 遇见more_mark
			if($line == $this->_more_mark) { // 注意, $this->_more_mark必须要有值, 不然为空, 那么会略过空行
				$this->_meet_more = true;
				// 略过 more mark
				continue;
			}

			if($no_more && $this->_meet_more && !($this->_code_start || $this->_table_start)) {
				$this->_fixEnd($html);
				break;
			}

			// 处理blockquote 2012/7/18
			if(($line[0] == "\t" || substr($line, 0, 4) == "    ") && !$this->_isList($line)) {
				if(!$this->_blockquote_start) {
					$this->_blockquote_start = true;
					$html .= '<blockquote>';
				}
//                $html .= $line;
//                continue; // 还应该去处理link 和 list, font
			} else if($this->_blockquote_start) { // 表明要结束了
				$html .= '</blockquote>';
				$this->_blockquote_start = false;
			}

			// 2. 处理标题
			//
			$matches = array();
			if(preg_match('/(=+)(.+?)(=+) *$/', $line, $matches) && $matches[1] == $matches[3]) {
				// 结束list p
				$this->_fixEnd($html);

				$level = strlen($matches[1]);
				if(!$h1_text && $level == 1) $h1_text = $matches[2];
				$seq = $this->_getHeadingSeq($level); // 得到标题序号
				$text = "$seq {$matches[2]}";
				// 前面有没有空格 有空格则居中
				$class = '';
				if($line[0] == ' ') {
					$class = 'class="justcenter"';
				}
				$html .= "<h$level id=\"toc_$seq\" $class>$text</h$level>";
				$this->_heading[] = array('text' => $text, 'space' => $level, 'type' => 'ul', 'seq' => $seq);

				// 之后也是一个段落的开始
				$this->_startP($html);	

				continue;
			}

			// 3. 处理表格
			//
			if($this->_table($html, $line)) {
				continue;
			}

			// 4. 处理段落
			// 段落已经开始了, 有<p>了
			if($this->_p_start) {
				// 段落结束
				if($this->_isBlank($line)) {
					$this->_fixEnd($html);

					// 之后也是一个段落的开始
					$this->_startP($html);	

				// 是该段落的一行
				} else {
					// 这里处理链接和列表
//                    $this->_link($line); // 处理链接
					$this->_list($line); // 处理列表
//                    $this->_font($line); // 处理字体
					$html .= $line;

					// 不是列表的一部分就添加<br />
					if(!$this->_isBlank($this->wiki_a[$this->_no+1])) {
						if(!$this->_ul_start && !$this->_ol_start) {
							$html .= "<br />";
						}
					}
				}
				continue;
			}
			if($this->_isBlank($line)) {
				$this->_startP($html);
				continue;
			}
		}

		// 把代码还原
		$this->_backPreCode($html);

		// 5. 最后的处理
		$this->_fixEnd($html);
		$this->_clearVoidP($html); // 清除空<p></p>
		
		// 6. 得到导航
		if(!$no_nav && $this->_has_nav) {
			$nav = $this->_getNav();
			if($nav) {
				$nav = '<div id="content_nav" class="toc"><div id="nav_title">'. $this->_nav_title. '</div>'. $nav. '</div>';
				// nav插入到html中
				$html = str_replace($this->_nav_token, $nav, $html);
			}
		}
		// 标题
		if(!$this->_title) {
			$this->_title = $h1_text;
		}

		return array('content' => $html, 'title' => $this->_title, 'has_nav' => $this->_has_nav, 'nav' => $nav, 'h1_text' => $h1_text);
	}

	/**
	 *
	 * 处理占位符, 标题和nav
	 *
	 * @return bool true 如果是占位符, false 则不是
	 *
	 */
	protected function _placeHolder(&$html, $line) {
		$ok = false; // 是否是占位符

		// %toc 导航
		if(!$this->_has_nav) {
			if(substr(ltrim($line), 0, 4) == '%toc') {
				$ok = true;
				$this->_has_nav = true;
				$html .= $this->_nav_token;
				$this->_nav_title = substr($line, 4);
			}
		}
		// <title></title>
		if(!$this->_has_title) {
			if(substr(ltrim($line), 0, 6) == '%title') {
				$ok = true;
				$this->_has_title = true;
				$this->_title = substr($line, 6);
			}
		}
		return $ok;
	}

	/**
	 *
	 * 最后标记的处理
	 * 避免最后没有空行, 不能结束list p
	 *
	 */
	protected function _fixEnd(&$html) {
		$html .= $this->_endList();
		$this->_endP($html);
	}

	/**
	 * 
	 * 截断
	 *
	 */
	protected function _more($line, $no_more) {
	}

	// ---------------
	// table 表格
	//
	// | head1  | head2  | head3  | head4  | head5  |
	// |--------+--------+--------+--------+--------|
	// | value1 | value2 | value3 | value4 | value5 |
	//
	// --------------

	/**
	 *
	 * 表格 如果当前行是表格, 还要判断下一行是否是表格
	 *
	 * @param string $html
	 * @param string $line
	 * @return void
	 *
	 */
	protected function _table(&$html, $line) {
		// 是表格的一行
		$is_end = false;
		$line = trim($line);

		// 判断下一行
		$next_line = $this->wiki_a[$this->_no+1];

		if($this->_isTable($line)) {
			if(!$this->_table_start) {
				$this->_table_start = true;
				$html .= '<table>';

				// 下一行是表格
				if($this->_isTable($next_line)) {
					// 是不是表头?
					if(strpos($next_line, '+') !== false) {
						$html .= $this->_getTr($line, true);
						$this->_no++; // 越过|-----+-----|
						// 下下一行是不是表格
						$is_end = !$this->_isTable($this->wiki_a[$this->_no+1]); 

					} else {
						$html .= $this->_getTr($line, false);
					}
					
				// 下一行不是table了.
				} else {
					$html .= $this->_getTr($line, false);
					$is_end = true;
				}
			// 以前有过的
			} else {
				$is_end = !$this->_isTable($next_line);
				$html .= $this->_getTr($line, false);
			}

			// 下一行不是表格了
			if($is_end) {
				$html .= '</table>';
				$this->_table_start = false;
			}

			return true;
		}

		// 不是表格
		return false;
	}

	/**
	 *
	 * 得到<tr><td>..</td>...</tr>
	 *
	 * @param string $line
	 * @param bool $is_th 是否是<th>
	 * @return string
	 *
	 */
	protected function _getTr($line, $is_th) {
		$line = trim($line, '|');
		$elements = explode('|', $line);
		$tr = '<tr>';
		foreach($elements as $each) {
			// 处理字体与链接
//            $this->_link($each); // 全局已处理
//            $this->_font($each);
			$tr .= $is_th ? "<th>$each</th>" : "<td>$each</td>";
		}
		$tr .= '</tr>';

		return $tr;
	}

	/**
	 *
	 * 某行是否是表格
	 *
	 * @param string $line
	 * @return bool
	 *
	 */
	protected function _isTable($line = '') {
		$line = trim($line);
		return ($line[0] == '|') && ($line[strlen($line)-1] == '|');
	}


	/**
	 * 
	 * 通过标题得到导航
	 *
	 * @return string
	 *
	 */
	protected function _getNav() {
		if(!$this->_heading || count($this->_heading) == 1) return '';
		// 初始化
		$this->_clearList();

		$nav = '';
		foreach((array)$this->_heading as $each) {
			$each['text'] = '<a href="#toc_'. $each['seq']. '">'. $each['text']. '</a>';
			$this->_list($each['text'], $each['space'], $each['type']);
			$nav .= $each['text'];
		}
		$nav .= $this->_endList();

		return $nav;
	}

	/**
	 * 
	 * 开始一个段落
	 *
	 * @return void
	 *
	 */
	protected function _startP(&$html) {
		$this->_p_start = true; 
		$html .= '<p>';
	}

	/**
	 *
	 * 结束一个段落
	 *
	 * @return void
	 *
	 */
	protected function _endP(&$html) {
		if(!$this->_p_start) return;
		$html .= '</p>';
	}

	protected function _clearVoidP(&$html) {
		$html = str_replace("<p></p>", '', $html);
	}

	/**
	 *
	 * 是否为空字符串
	 * @param string $str
	 *
	 * @return bool
	 *
	 */
	protected function _isBlank($str) {
		$str = trim($str);
		if(empty($str)) return true;
		return false;
	}

	/**
	 *
	 * 得到标题序号
	 *	$this->_heading_seq = array(2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
	 *
	 * @param int $level 等级
	 * @return string 1.1.1
	 *
	 */
	protected function _getHeadingSeq($level) {
		if($level <= 1) return;
		// 之前有过这一级的标题
		if($this->_heading_seq[$level]) {
			$this->_heading_seq[$level]++;

			// 把后面的全清0
			foreach($this->_heading_seq as $no => $each) if($no > $level) {
				$this->_heading_seq[$no] = 0;
			}

		// 这是第一个这一级的标题
		} else {
			$this->_heading_seq[$level] = 1;
		}

		$seq = implode($this->_heading_seq, '.'); // 1.1.0.0.0 OR 1.0.1.0.0
		return trim($seq, '.0').'.';
	}

	/**
	 *
	 * 一个链接地址是否是图片链接
	 *
	 */
	protected function _isImageLink($link) {
		$ext = substr($link, strrpos($link, '.') + 1);
		if(in_array($ext, $this->_image_ext)) {
			return true;
		}
	}

	/**
	 *
	 * 得到图片链接地址
	 *
	 */
	protected function _getImageLink($link) {
		// 外链图片
		if(strtolower(substr($link, 0, 4)) == 'http') {
			return $link;
		}
		return $this->_image_path. '/'. $link;
	}

	/**
	 *
	 * 处理内部链接和图片链接 2012/7/19 23:59
	 *
	 * [[a]] <a href="a">a</a>
	 * [[a/b|c]] <a href="a/b">c</a>
	 *
	 * [[a.jpg]] <img scr="" />
	 * [[http://lealife/a.jpg]] <img scr="http://lealife/a.jpg" />
	 * [[http://lealife/a.jpg | 图片链接title]] <a href="http://...a.jpg>图片链接title</a>
	 * [[http://lealife/a.jpg | alt | width:100px; height:100px]] <img alt="alt" src="http://...a.jpg/ style="width:100px;height:100px">
	 * 
	 * @param string $line
	 *
	 */
	protected function _fixLink($matches) {
		$links = explode('|', $matches[1]);
		if(!$links) return '';
		array_map('trim', $links);

		// 是图片链接, 只要第一个元素是图片链接
		if($this->_isImageLink($links[0])) {
			$count = count($links);
			$image_link0 = $this->_getImageLink($links[0]);

			// [[a.jpg | title]] 图片链接
			if($count == 2) {
				// [[big.jpg | thumb.jpg]] 缩略图形式
				if($this->_isImageLink($links[1])) {
					return '<a href="'. $image_link0. '"><img src="'. $this->_getImageLink($links[1]).'" /></a>';
				// [[a.jpg | title]]
				} else {
					return '<a href="'. $image_link0. '">'. $links[1].'</a>';
				}
			// [[.jpg | alt | attr]]
			} else if($count == 3) {
				return '<img src="'. $image_link0.'" alt="'. $links[1].'" style="'. $links[2].'"/>';
			// [[.jpg]]
			} else {
				return '<img src="'. $image_link0. '"/>';
			}
		// a 链接
		} else {
			// [[a]]
			if(!isset($links[1])) {
				$title = $href = $links[0];
			// [[a|b]]
			} else {
				$title = $links[1];
				$href = $links[0];
			}
			return '<a href="index.php?action=view&wiki='. $this->_pre_wiki_path. '/'. $href .'">'. $title. '</a>';
		}
	}
	protected function _link(&$line) {
		// 外链	先做外链, 不然可能二次解析
		$this->_linkOutside($line);

		$line = preg_replace_callback(
			'/\[\[(.+?)\]\]/', 
			array($this, '_fixLink'), $line);
	}

	/**
	 * 处理外链 比如 : 
	 *	http://lealife.com 
	 *		=> Array ( [0] => http://lealife.com [1] => [2] => http [3] => lealife.com [4] => [5] => ) 
	 *	https://lealife.com
	 *		=> Array ( [0] => https://lealife.com [1] => [2] => https [3] => lealife.com [4] => [5] => ) 
	 *	[http://lealife.com life]
	 *		=> Array ( [0] => [http://lealife.com life] [1] => [ [2] => http [3] => life [4] => ] )
	 */
	protected function _fixLinkOutside($matches) {
		$ok = false;
		// [http://lealife.com life]
		if($matches[1] == '[' && $matches[5] == ']') {
			$ok = true;
			$href = $matches[2]. '://'. $matches[3];
			if(trim($matches[4])) $title = $matches[4];
			else $title = $matches[3];
		// 第1, 2种情况
		} else if(!$matches[1] && !$matches[5]) {
			$ok = true;
			// 有可能是http://life.com life, 那么也会把life包含
			$split = explode(' ', $matches[0], 2);
			$href = $title = $split[0];
			if($split[1]) $split[1] = " ". $split[1];
			// 看是不是图片链接 http://life.com/logo.jpg
			if($this->_isImageLink($matches[3])) {
				return '<img src="'. $href. '" />'. $split[1];
			}
		}
		if($ok) {
			return '<a href="'. $href. '" target="_blank">'. $title. '</a>'. $split[1];
		}
		return $matches[0];
	}
	protected function _linkOutside(&$line) {
		$ever_line = $line;
		$line = preg_replace_callback(
			'/(\[*)(https|http):\/\/([0-9a-zA-Z_\.\/\\\-]+) *([^\]\n]*)(\]*)/', 
			array($this, '_fixLinkOutside'), $line);
	}

	// --------------
	// font style
	// --------------

	/**
	 * 
	 * 字体, bold, italic del code
	 *
	 * @param string $line
	 * @return void
	 *
	 */
	protected function _font(&$line) {
		$this->_italic($line);
		$pattern = array(
			// 不能是 (^ .+? ^ ) ^的作用是非后面所有的, 所以要用[^ ]限定范围
			'/\*([^ ].+?[^ ])\*/',  // 这里可能出现 * 列表 *强调*, 前两个合为一个的情况!
			'/~~(.+?)~~/',
		);
		$replace = array(
			"<strong>$1</strong>",
			"<del>$1</del>",
		);
		$line = preg_replace($pattern, $replace, $line);
	}

	/**
	 *
	 * 处理斜体 _a_, *_a_~是的, _a_b(不是), a_a_b(不是)
	 *
	 * @param string $line
	 * @return void
	 *
	 */
	protected function _italic(&$line) {
		$ever_line = $line;
		$line = preg_replace(
			"/([^0-9a-zA-Z\x{4e00}-\x{9fa5}]|^)_(.+?)_([^0-9a-zA-Z\x{4e00}-\x{9fa5}]|$)/u", 
			"$1<i>$2</i>$3", $line);
	}


	// ----------------
	// 列表 ul ol
	// ----------------

	protected $_ul_start = false; // 最高级是否开始
	protected $_ol_start = false; // 最高级是否开始
	protected $_list_level = array(); // 存array(0=>array(space=>3, type=ul));
	protected $_pre_list = 0; // 上一级 

	/**
	 *
	 * 清空list
	 *
	 */
	protected function _clearList() {
		$this->_ul_start = false;
		$this->_ol_start = false;
		$this->_list_level = false;
		$this->_pre_list = 0;
	}

	/**
	 *
	 * 结束list
	 *
	 * @return string
	 *
	 */
	protected function _endList() {
		if(!$this->_ul_start || !$this->_ul_start) {
			return '';
		}
		$end = '';
		while($this->_pre_list-- > 0) {
			if($this->_list_level[$this->_pre_list]['type'] == 'ul') {
				$end = '</ul>'. $end;
			} else {
				$end = '</ol>'. $end;
			}
		}
		if($this->_ul_start) {
			$this->_ul_start = false;
			$end = $end. '</ul>';
		} else if($this->_ol_start) {
			$this->_ol_start = false;
			$end = $end. '</ol>';
		}
		$this->_pre_list = 0;
		$this->_list_level = array(); // life 2012/7/15

		return $end;
	}

	/**
	 * 
	 * 该行是否是list
	 *
	 */
	protected function _isList($line) {
		$line = ltrim($line); // 去除前面的空格
		$pre = substr($line, 0, 2); // *和空格

		// 不是列表, 可能是ul, ol的结束!
		if(!in_array($pre, array('# ', '* ', '- '))) {
			return false;
		}
		return $pre;
	}
	/**
	 *
	 * 得到这一列表的属性 array(space, type)
	 *
	 * @param string $line
	 * @param int $space
	 * @param string $type
	 * @return bool
	 *
	 */
	protected function _getListAttr(&$line, &$space, &$type) {
		// 得到前面的空格
		$space = 0;
		$len = strlen($line);
		for($i = 0; $i < $len; $i++) {
			$ch = $line[$i];
			if($ch == " ") $space += 1;
			else if($ch == "\t") $space += 4;
			else break;
		}

		// 该行不是列表	
		if(!($pre = $this->_isList($line))) {
			$line = $this->_endList(). $line;	
			return false;
		}

		$line = substr(ltrim($line), 2); // 去掉前面的
		// 处理
		$type = ($pre == '* ' || $pre == '- ') ? 'ul' : 'ol';

		return true;
	}

	/**
	 *
	 * 列表操作
	 * 如果 $space, $type不为空, 那么 $line表示不要处理得到space, type
	 *
	 * @param string $line
	 * @param int $space 前面的空格数
	 * @param string $type ul OR ol
	 * @return void
	 *
	 */
	protected function _list(&$line, $space = 0, $type = '') {
		if($type == '') {
			// 得到这一行的属性
			if(!$this->_getListAttr($line, $space, $type)) { // 不是列表
				return;
			}
		}

		// 与上一级space比较
		if(isset($this->_list_level[$this->_pre_list])) {
			$pre_list = $this->_list_level[$this->_pre_list];
			if($pre_list['space'] < $space) { // 该级是前一级的子级
				if($type == 'ul') {
					$line = '<ul><li>'. $line. '</li>';
				} else {
					$line = '<ol><li>'. $line. '</li>';
				}

				// 建这一级, 并设该级为上一级
				$this->_pre_list++;
				$this->_list_level[$this->_pre_list] = array(
					'space' => $space,
					'type' => $type,
				);

			// 这一级是上一级的父(或父的父), 那么结束上一级
			// 这里最难
			} else if($pre_list['space'] > $space) {
				// 结束上一级
				// 向前找到与自己相当的位置
				$pre_end = '';
				while($pre_list['space'] > $space) {
					if($pre_list['type'] == 'ul') {
						$pre_end .= '</ul>';
					} else {
						$pre_end .= '</ol>';
					}
					
					$this->_pre_list--;
					$pre_list = $this->_list_level[$this->_pre_list];
				}
				// OK 找到了一个与自己一样的了
				$line = $pre_end. '<li>'. $line. '</li>';

			// 与上一级是兄弟
			} else {
				$line = '<li>'. $line. '</li>';
			}

		// 第一列表, 第一项
		} else {
			$this->_list_level[$this->_pre_list] = array(
				'space' => $space,
				'type' => $type,
			);
			if($type = 'ul') {
				$this->_ul_start = true;
				$line = '<ul><li>'. $line. '</li>';
			} else {
				$this->_ol_start = true;
				$line = '<ol><li>'. $line. '</li>';
			}
		}
	}

	/**
	 *
	 * debug 
	 *
	 */
	protected function _print_start() {
		$start = array('p_start', 'code_start', 'ul_start', 'ol_start', 'table_start');
		foreach($start as $each) {
			echo $each.': '. $this->{'_'.$each}.'<br />';
		}
	}

	/**
	 * 已弃用
	 * 处理粗体 *a*
	 *
	 * @param string $line
	 * @return void
	 *
	 */
	protected function _bold(&$line) {
		$line = preg_replace(
			'/\*(.+?)\*/', 
			"<strong>$1</strong>", $line);
	}

	/**
	 * 已弃用
	 * 删除线 ~~删除线~~
	 *
	 * @param string $line
	 * @return void
	 *
	 */
	protected function _del(&$line) {
		$ever_line = $line;
		$line = preg_replace(
			'/~~(.+?)~~/',
			"<del>$1</del>", $line);
	}

	/**
	 * 已弃用
	 * 代码
	 * `<php ?>`
	 *
	 * @param string $line
	 * @return void
	 *
	 */
	protected function _code(&$line) {
		$ever_line = $line;
		$line = preg_replace(
			'/`(.+?)`/',
			"<code>$1</code>", $line);
	}
}
