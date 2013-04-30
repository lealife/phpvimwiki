<style>
</style>
<?php
//print_r($quick_contents);
?>
<div id="quick_view">
<?php echo $quick_contents['pre_contents']['content']; ?>
</div>
<?php
$n = 1;
foreach($quick_contents['contents'] as $each) {
	echo '<div class="quick">';
	echo '<div class="title"><a href="'. $each['href']. '">'.$each['title']. '</a></div>';
	echo '<div class="content">'. $each['content']. '</div>';
	echo '<a href="'. $each['href'].'">View More...</a>';
	echo '</div>';
	$n++;
}
?>
