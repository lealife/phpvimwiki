<?php if(!$this->isLogin()) {?>
<h1>Login</h1>
<form method="post" action="index.php?action=login">
	<?php echo $this->msg; ?>
	<br />
	<input type="text" name="username"/>
	<input type="password" name="password"/>
	<input type="hidden" name="xss_key" value="<?php echo $this->getXSSKey(); ?>"/>
	<button type="submit" name="submit" value="login"/>登录</button>
</form>
<?php } else {?>
Hello, <?php echo $_SESSION['username']; ?>.
<br />
<a href="index.php?action=logout">Logout</a>
<?php }?>
