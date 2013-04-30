<div id="media">
	<h1>Media Manage</h1>

	<?php echo $this->cur_path ? $this->cur_path : '/'; ?>
	<form action="index.php?action=media:upload" enctype="multipart/form-data" method="post">
		<input type="hidden" name="path" value="<?php echo $this->cur_path; ?>"/>
		<input type="file" name="file" /> &nbsp;&nbsp;&nbsp;&nbsp;
		<button value="upload" name="upload" type="submit">Upload</button>
	</form>
	<ul>
	<?php
	echo '<li><a class="up_path" href="index.php?action=media:index&path='.$this->up_path.'">..</a></li>';
	if($this->dirsFiles) {
		if($this->dirsFiles['dirs']) {
			foreach($this->dirsFiles['dirs'] as $dir) {
				echo '<li><a href="index.php?action=media:index&path='. $this->cur_path. '/'. $dir['name'].'">'. $dir['name']. '</a></li>';
			}
		}
		
		if($this->dirsFiles['files']) {
			foreach($this->dirsFiles['files'] as $file) {
				echo '<li>';
				echo '<span class="file_info">';
				if($file['type'] == 'image') {
					echo '<img src="public/image/'. $file['name'].'" style="height: 50px"/>';
				}
				echo $file['name'];
				echo '</span>';

				echo '<span class="operation">';
				echo '<a href="index.php?action=media:delete&path='. $this->cur_path.'&filename='.$file['name'].'">Del</a>';
				echo '</span>';

				echo '<span class="wiki_path">';
				if($this->cur_path) {
					$common = $this->cur_path. '/'. $file['name'];
				} else {
					$common = $file['name'];
				}
				if($file['type'] == 'image') {
					echo "[[$common]]";
				} else {
					echo "[http://lealife.com/{$this->image_path}$common {$file['name']}]";
				}
				echo '</span>';
				echo '</li>';
			}
		}
	}
	?>
	</ul>
</div>

