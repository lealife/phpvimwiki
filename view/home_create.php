<?php echo $this->msg; ?>
<form id="create" method="post" action="index.php?action=create&wiki=<?php echo $this->config['wiki']; ?>">
<?php echo $this->config['wiki']. ' : ';?>
<br />
<textarea name="wiki" cols="100" rows="25">
<?php echo $this->wiki_content; ?>
</textarea>
<br />
<button type="submit" name="create" value="create"/>提交</button>
<button type="submit" name="pre_view" value="pre_view"/>预览</button>
</form>
<?php if($_POST['pre_view']) {?>
预览: 
<div id="pre_view">
<?php echo $this->pre_view_content; ?>
</div>
<?php }?>
