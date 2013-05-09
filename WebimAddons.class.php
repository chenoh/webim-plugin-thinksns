<?php
/**
 * WebIM插件
 * @author ery.lee@gmail.com
 * @version 2.0
 * @copyright www.webim20.cn
 */
class WebimAddons extends NormalAddons
{
	protected $version = '2.0';
	protected $author  = '杭州巨鼎信息技術有限公司';
    protected $thanks  = 'isjava';
	protected $site    = 'http://www.webim20.cn';
	protected $info    = '类似新浪微博的WebIM插件';
	protected $pluginName = 'WebIM';
    // 安装时需要执行的sql文件名
	protected $sqlfile = 'install.sql';    
    // ts核心版本号
	protected $tsVersion  = "3.0";                               

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
        $hooks['list'] = array('WebimHooks');
        return $hooks;
    }

    public function adminMenu() {
        $menu = array('config' => '设置',
                      'skin' => '主题',
                      'history' => '清除历史',);
        return $menu;
    }

    public function start() {
        return true;
    }

	/**
	 * 安裝插件，初始化WebIM數據庫表
	 */
	public function install() {     
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}webim_histories` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`send` tinyint(1) DEFAULT NULL,
					`type` varchar(20) DEFAULT NULL,
					`to` varchar(20) DEFAULT NULL,
					`from` varchar(20) DEFAULT NULL,
					`nick` varchar(20) DEFAULT NULL COMMENT 'from nick',
					`body` text,
					`style` varchar(150) DEFAULT NULL,
					`timestamp` double DEFAULT NULL,
					`todel` tinyint(1) NOT NULL DEFAULT '0',
					`fromdel` tinyint(1) NOT NULL DEFAULT '0',
					`created_at` date DEFAULT NULL,
					`updated_at` date DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `todel` (`todel`),
					KEY `fromdel` (`fromdel`),
					KEY `timestamp` (`timestamp`),
					KEY `to` (`to`),
					KEY `from` (`from`),
					KEY `send` (`send`)
				) ENGINE=MyISAM;
				CREATE TABLE IF NOT EXISTS `{$db_prefix}webim_settings` (
					`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
					`uid` mediumint(8) unsigned NOT NULL,
					`web` blob,
					`air` blob,
					`created_at` date DEFAULT NULL,
					`updated_at` date DEFAULT NULL,
					PRIMARY KEY (`id`) 
				) ENGINE=MyISAM;";

		D()->execute($sql);
		return true;
	}

	public function uninstall() {
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}webim_histories`;
				DROP TABLE IF EXISTS `{$db_prefix}webim_settings;`;";
		D()->execute($sql);
		return true;
	}

}
