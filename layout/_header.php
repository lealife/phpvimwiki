<div class="logo">
	<h1><a title="life" href="index.php">life</a></h1>
	<p>只为记录自己的生活 you can make it!</p>
</div>
<ul id="nav">
	<li><a href="index.php">HOME</a></li>
	<li><a href="index.php?action=view&wiki=php/index">PHP</a></li>
	<li><a href="index.php?action=view&wiki=Solarphp/index">Solarphp</a></li>
	<li><a href="index.php?action=view&wiki=C/index">C</a></li>
	<li><a href="index.php?action=view&wiki=Linux/index">Linux</a></li>
	<li><a href="index.php?action=view&wiki=Java/index">Java</a></li>
	<li><a href="index.php?action=view&wiki=Hadoop/index">Hadoop</a></li>
	<li><a href="index.php?action=view&wiki=life/me">About Me</a></li>
	<?php if($this->isLogin()) { ?>
	<li><a href="index.php?action=media:index">Media Manage</a></li>
	<?php } ?>
	<li><a href="index.php?action=login">login</a></li>
</ul>
