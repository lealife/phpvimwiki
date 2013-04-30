<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta content="text/html; charset=UTF-8" http-equiv="content-type">
<link rel="stylesheet" type="text/css" href="public/css/style.css" />
<head>
<title>phpvimwiki test</title>
</head>
<body>
<?php
error_reporting(E_ALL & ~E_NOTICE);
$wiki = <<<WIKI
= phpvimwiki =
这是一个用php来解析vimwiki语法的测试页面

== 代码 ==
%toc
{{{
<?php
	echo 'hello, phpvimwiki.';
	echo 'hello, life.';
?>
<script>
	alert('life');
</script>
}}}

`这是一个小代码`
`http://lealife.com`
`代码都会忽略html标签和其它的wiki语法 = h1 = *life* <pre> alert(0) </pre> <b>life</b>`

== 列表 ==
* Fruit
	* apple
	* banana
		# banana1
		# nabana2
* Ball
	# basketball
	# volleyball

# 序1 
	# one
		# two
			# three
# 序2

== 字体 ==
*粗体* life

_*斜体*_ life

~~删除线~~  you

~~*删除线加租*~~ you

== 表格 ==
=== 有表头 ===
| head1  | head2  | head3  | head4  | head5  |
|--------+--------+--------+--------+--------|
| value1 | value2 | value3 | value4 | value5 |
| value1 | value2 | value3 | value4 | value5 |
| value1 | value2 | value3 | value4 | value5 |
| value1 | value2 | value3 | value4 | value5 |
|a|a|c|a|a|

=== 无表头 ===
| head1  | head2  | head3  | head4  | head5  |
| value1 | value2 | value3 | value4 | value5 |
| value1 | value2 | value3 | value4 | value5 |

== 链接 ==
* [[abclife]]
* [[pages/pages_table|pages tables]]
* [[pages/pages_mod|功能模块]]
* [[pages/bug|BUG]]
* [[pages/pages_class|pages_class]]

* http://lealife.com hello life
* [http://lealife.com life's blog] [http://google.com google] hello life

`http://lealife.com/public/image/me.gif`
http://lealife.com/public/image/me.gif

`[[http://lealife.com/public/image/me.gif]]`
[[http://lealife.com/public/image/me.gif]]
== end ==
=== end ===
==== end ====
WIKI;
include 'include/phpvimwiki.php';
$phpvimwiki = new phpvimwiki(array('wiki_content' => $wiki));
$html = $phpvimwiki->html();
print_r($html);
