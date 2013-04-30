<?php if($this->isLogin()) { ?>
<div id="manage_nav">
<a href="index.php?action=edit&wiki=<?php echo $this->config['quick_view'];?>">Edit</a>
 | <a href="index.php?action=logout">Logout</a>
</div>
<?php } ?>

<div id="quick_view">
<?php echo $this->quick_contents['pre_contents']['content']; ?>
</div>
<?php
$n = 1;
foreach($this->quick_contents['contents'] as $each) {
	echo '<div class="quick">';
	echo '<div class="title"><a href="'. $each['href']. '">'.$each['title']. '</a></div>';
	echo '<div class="content">'. $each['content']. '</div>';
	echo '<a href="'. $each['href'].'">View More...</a>';
	echo '</div>';
	$n++;
}
?>
