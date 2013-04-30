<?php
$config = array(
	'root' => dirname(__FILE__),
	'wiki_dir' => 'wiki', // wiki 根路径
	// 各个wiki的目录 相对对root路径
	'wiki_path' => array(
		'php' => 'php',
		'Solarphp' => 'Solarphp',
		'Linux' => 'Linux',
	),
	'image_path' => 'public/image',
	'username' => 'phpvimwiki',
	'password' => 'b488b02d354faffb95b5a9c9c3f076cd', // phpvimwiki
);
$config['wiki_root'] = $config['root']. '/'. $config['wiki_dir'];

// 内容简要, 在首页上显示, 
// 哪个wiki写内容简要的路径
$config['quick_view'] = 'php/quick_view';
$config['quick_view_separator'] = '<!-- quick view -->';
$config['more_mark'] = '-- more --';

return $config;
