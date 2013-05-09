<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// Blog数据
	"DROP TABLE IF EXISTS `{$db_prefix}webim_histories`;",
	"DROP TABLE IF EXISTS `{$db_prefix}_settings`;",
);

foreach ($sql as $v)
	M('')->execute($v);