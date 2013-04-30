<div class="msg <?php echo $this->msg['status']; ?>" >
<?php echo $this->msg['msg']; ?>
</div>

<form id="edit" method="post" action="index.php?action=edit&wiki=<?php echo $this->config['wiki']; ?>">
编辑: <strong><?php echo $this->config['wiki']. ' : ';?></strong>
<br />
<textarea name="wiki">
<?php echo $this->wiki_content; ?>
</textarea>
<br />
<button type="submit" name="edit" value="edit"/>提交</button>
<button type="submit" name="pre_view" value="pre_view"/>预览</button>
</form>
<?php if($_POST['pre_view']) {?>
预览: 
<div id="pre_view">
<?php echo $this->pre_view_content; ?>
</div>
<?php }?>
