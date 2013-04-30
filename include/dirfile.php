<?php
class dirfile {
	protected $_target_path;
	protected $_include_file;
	protected $_image_ext = array('gif', 'jpg', 'jpge', 'png');

	public function __construct($target_path) {
		$this->_target_path  = $this->_fixPath($target_path); // 目标路径, 要访问的路径
	}

	/**
	 *
	 * 得到目录
	 *
	 */
	public function getDirs($path = '') {
		if(!$path) $path = $this->_target_path;
		
		$image_dirs = array();
		$dir = new DirectoryIterator($path);

		foreach ($dir as $file) {
			if (!$file->isDot() && $file->isDir()) {
				$this_dir['name'] = $file->getFilename(); // 转码
 
				$this_dir['child'] = $this->getDirs($path. DIRECTORY_SEPARATOR. $this_dir['name']);
				
				$image_dirs[] = $this_dir;
		   }
		}
		return $image_dirs;
	}

	public function getFiles($path = '') {
		if(!$path) $path = $this->_target_path;

		$image_files = array();
		$dir = new DirectoryIterator($path);

		foreach ($dir as $file) {
			if (!$file->isDot() && !$file->isDir()) {
				// $file_name = iconv("GB2312", "UTF-8", $file->getFilename()); // 转码
				$file_name = $file->getFilename();
				$image_files[] = array('name' => $file_name,
										'size' => $file->getSize(),
									);
		   }
		}
		return $image_files;
	}

	public function getDirsAndFiles($path = '') {
		if(!$path) $path = $this->_target_path;

		$dir = new DirectoryIterator($path);
		$dirs_files = array();

		foreach ($dir as $file) {
			if (!$file->isDot()) {
					
				// $file_name = iconv("GB2312", "UTF-8", $file->getFilename()); // 转码
				$file_name = $file->getFilename();
				// dir
				if($file->isDir()) {
					$dirs_files['dirs'][] = array('name' => $file_name
										);
				// file
				} else {
					$dirs_files['files'][] = array('name' => $file_name,
											'size' => $file->getSize(),
											'type' => $this->_getFileType($file_name),
										);
				}
		   }
		}
		return $dirs_files;
	}

	/**
	 *
	 * 上一路径
	 *
	 */
	public function getUpPath($path) { // a/b
		$path = str_replace('\\', '/', $path);
		if(!$path || $path == '/') return false;
		$path = trim($path, '/');
		$pos = strrpos($path, '/');
		return substr($path, 0, $pos);
	}

	protected function _getHref($href, $name) {
		if(!$this->_href_template) return '';
		$href_template = str_replace('{}', ltrim($href, '/'), $this->_href_template);

		return '<a href="'. $href_template. '" target="folderframe" title="'. $href. '">'. $name. '</a>';
	}

	protected function _isImage($image_name, $ext = '') { // abc.gif
		if(!$ext) {
			if(!$image_name) return false;
			$dot = strpos($image_name, '.');
			if(!$dot) return;
			$ext = substr($image_name, $dot + 1);
		}
		if(in_array(strtolower($ext), $this->_image_ext)) {
			return true;
		}
		return false;
	}

	protected function _fixCurrentPath($current_path) {
		$current_path = trim($current_path, '/');
		$path = explode('/', $current_path);
		if(!$path[0]) return false;
		return $path;
	}

	protected function _getFileType($file_name) {
		if(!$file_name) return false;

		$dot = strpos($file_name, '.');
		if(!$dot) return;
		$ext = substr($file_name, $dot + 1);

		// check if it is image
		if($this->_isImage($file_name, $ext)) return 'image';

		return strtolower($ext);
	}

	protected function _fixPath($path) {
		if(!$path) return false;
		$path = rtrim($path, '/\\');
		if(DIRECTORY_SEPARATOR == '/') {
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		} else {
			$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		}
		return $path;
		// return  iconv("UTF-8", "GB2312", $path); // 转码
	}
	
	// Returns true if $string is valid UTF-8 and false otherwise.
	function _isUtf8($string) {

	// From http://w3.org/International/questions/qa-forms-utf-8.html
	return preg_match('%^(?:
		[\x09\x0A\x0D\x20-\x7E] # ASCII
		| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
		| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
		| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
		| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
		| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
		)*$%xs', $string);

	} // function is_utf8
}
