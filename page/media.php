<?php
/**
 * Media 主页 controller
 *
 * 图片管理, 删除, 添加
 *
 */
class media extends controller {
	public $config;
	public $title = 'Media Manage';
	public $dirsFiles;
	public $image_path;
	public $cur_path; // 基于image_path下的 a/b
	public $up_path; // 基于image_path下, 上一层

	function __construct() {
		session_start();
		global $config;
		$this->config = &$config;
		$this->image_path = $this->config['image_path'];

		include 'include/dirfile.php';
	}

	/**
	 *
	 * 得到quick view
	 *
	 */
	public function actionIndex() {
		$this->_view = "media_index";
		$this->cur_path = $_GET['path'] ? $_GET['path'] : '';
		$dirfile = new dirfile($this->image_path .'/'. $this->cur_path);
	
		if($this->cur_path) {
			$this->up_path = $dirfile->getUpPath($this->cur_path);
		}

		$this->dirsFiles = $dirfile->getDirsAndFiles();
	}

	/**
	 *
	 * 上传
	 *
	 */
	function actionUpload() {
		$this->_view = "media_upload";
		$file = $_FILES['file'];

		if(!$file) {
			$this->msg = array(
				'status' => 'error',
				'msg' => '没有文件',
			);
			return;
		}
		if($file['error']) {
			$this->msg = array(
				'status' => 'error',
				'msg' => '上传失败',
			);
			return;
		}

		// 处理上传
		$this->cur_path = $_POST['path'];
		if($this->cur_path) {
			$des = $this->image_path. '/'. $this->cur_path. '/'. $file['name'];
		} else {
			$des = $this->image_path. '/'. $file['name'];
		}

		if(move_uploaded_file($file['tmp_name'], $des)) {
			$this->msg = array(
				'status' => 'success',
				'msg' => '上传成功',
			);
		} else {
			$this->msg = array(
				'status' => 'error',
				'msg' => '移动失败',
			);
		}
	}

	/**
	 *
	 * 删除文件
	 *
	 */
	public function actionDelete() {
		$this->_view = "media_upload";
		$this->cur_path = $_GET['path'];
		$filename = $_GET['filename'];

		$file = $this->image_path. '/'. $this->cur_path. '/'. $filename;
		if(file_exists($file) && unlink($file)) {
			$this->msg = array(
				'status' => 'success',
				'msg' => '删除成功',
			);
		} else {
			$this->msg = array(
				'status' => 'error',
				'msg' => '删除失败',
			);
		}
	}
}
