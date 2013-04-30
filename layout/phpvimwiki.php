<!DOCTYPE html>
<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="content-type">
<title><?php echo $this->title; ?></title>
<link rel="stylesheet" type="text/css" href="public/css/style.css" />
</head>
<body>
<div id="header">
	<?php include '_header.php'; ?>
</div>
<div id="main">
	<?php echo $this->view_content; ?>
</div>
<div id="footer">
	<?php include '_footer.php'; ?>
</div>
</body>
</html>
