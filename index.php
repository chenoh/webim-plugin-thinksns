<?php

/**
 * WebIM-for-ThinkSNS插件入口文件
 *
 * @author ery lee <ery.lee at gmail.com>
 * @copyright   (C) 2014 NexTalk.IM
 * @license     http://nextalk.im/license
 * @lastmodify  2014-04-23
 * @version 5.4
 */ 

if(phpversion() < '5.3.10') { exit('PHP version should be > 5.3.10'); }

/**
 * Env
 */
require 'env.php';

/**
 * -------------------------
 * integrated with thinksns
 * -------------------------
 */

//NOTICE: Have to redefine SITE_URL.
define('__ROOT__', chop($_SERVER['PHP_SELF'], '/addons/plugin/Webim/index.php'));
defined('SITE_PATH') or define('SITE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
require_once (SITE_PATH . '/core/core.php');

define('WEBIM_URL', SITE_URL . '/addons/plugin/Webim');

/**
 * Configuration
 */
$IMC = model('Xdata')->get('hook_webim_plugin:config');

if(!$IMC or count($IMC) == 0) { $IMC = require('config.php'); }

if( !$IMC['isopen'] ) exit('WebIM Not Opened');

$IMC['dbuser'] = C('DB_USER');
$IMC['dbpassword'] = C('DB_PWD');
$IMC['dbname'] = C('DB_NAME');
$IMC['dbhost'] = C('DB_HOST');
$IMC['dbprefix'] =  C('DB_PREFIX') . 'webim_';

function WEBIM_PATH() { return WEBIM_URL; }

function WEBIM_IMAGE($img) { return WEBIM_PATH() . "/static/images/{$img}"; }

/**
 * -----------------------
 * end
 * -----------------------
 */

if($IMC['debug']) {
    define(WEBIM_DEBUG, true);
} else {
    define(WEBIM_DEBUG, false);
}

// Modify error reporting levels to exclude PHP notices
if( WEBIM_DEBUG ) {
	error_reporting( -1 );
} else {
	error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT );
}

if( !$IMC['isopen'] ) exit();

define('WEBIM_ROOT', dirname(__FILE__));

define('WEBIM_SRC', WEBIM_ROOT . '/src');

/**
 *
 * WebIM Libraries
 *
 * https://github.com/webim/webim-php
 *
 */
require WEBIM_ROOT.'/vendor/autoload.php';

/**
 * Model
 */
require WEBIM_SRC . '/Model.php';

/**
 * Base Plugin
 */
require WEBIM_SRC . '/Plugin.php';

/**
 * Router
 */
require WEBIM_SRC . '/Router.php';

/**
 * WebIM APP
 */
require WEBIM_SRC . '/App.php';

/**
 * WebIM Plugin for ThinkSNS
 */
require WEBIM_ROOT . '/ThinkSNS_Plugin.php';

\WebIM\App::run( new \WebIM\ThinkSNS_Plugin() );


