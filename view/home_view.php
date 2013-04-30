<?php if($this->isLogin()) { ?>
<div id="manage_nav">
<a href="index.php?action=edit&wiki=<?php echo $this->config['wiki'];?>">Edit</a>
 | <a href="index.php?action=logout">Logout</a>
</div>
<?php } ?>

<?php
echo $this->html_content;
?>
