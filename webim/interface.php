<?php
/*
*   首先感谢@海虾提供的webim的一些文件，有了这些文件才能做集成到TS
*   更改了Class的Conn，在原有类下无法获取到SQL的参数，无法进行query的查询
*   简单的用了mysqli。支持高版本的sql来完成信息的读取
*   简化了极个别地方的写法，不过有些函数不知道用来干嘛的，以后在看
*   通过2个表来存放聊天记录以及设置，若不建立表同样可以完成聊天，不过无法得到留言
*   2012年3月19日  韭菜饺子。
*   $聊天api入口$
*/
define('WEBIM_PRODUCT_NAME', 'thinksns');
define('SITE_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/'/* getcwd() */);
#require_once(SITE_PATH. '/core/core.php');
$data=require '../../../../config/config.inc.php';
$siteUrl = "http://" . dirname(dirname(dirname(dirname(dirname($_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"])))));
$db = @new mysqli($data['DB_HOST'], $data['DB_USER'], $data['DB_PWD'] , $data['DB_NAME']) or die("连接失败");
$db->query("SET NAMES utf8");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'common.php');
$cookie = '3623161/assd';
$loginID = base64_decode($cookie);
$_SERVER['loginID'] = $loginID;


@include_once( 'config.php' );

$_IMC['dbuser'] = $data['DB_USER'];
$_IMC['dbpassword'] =$data['DB_PWD'];
$_IMC['dbname'] = $data['DB_NAME'];
$_IMC['dbhost'] = $data['DB_HOST'];
$_IMC['dbtable_prefix'] = $data['DB_PREFIX'];
$_IMC['dbcharset'] = $data['DB_CHARSET'];

if (!isset($loginID)) {
    $im_is_login = false;
} else {
    $im_is_login = true;
    webim_set_user();
}

function convertUidToPath($uid) {
	$md5 = md5($uid);
	$sc = '/'.substr($md5, 0, 2).'/'.substr($md5, 2, 2).'/'.substr($md5, 4, 2);
	return $sc;
}

function profile_url($id) {
    global $siteUrl;
    return $siteUrl . "/index.php?app=public&mod=Profile&act=index&uid=" . $id;
}

function webim_set_user() {
    global $_SGLOBAL, $imuser, $im_is_admin,$db, $siteUrl;

    //$space = getspace( $_SGLOBAL['supe_uid'] );
    //http://github.com/webim/webim-for-uchome/issues/#issue/10
    //	$query = $_SGLOBAL['db']->query( "SELECT uid, username, name
    //		FROM ".tname('space')."
    //		WHERE uid = ".$_SGLOBAL['supe_uid'] );
	session_start();
    $uid = $_SESSION['mid'];
	//var_dump($uid);
    $query = $db->query("SELECT uid, uname FROM ts_user WHERE uid = $uid");
	$space = mysqli_fetch_array($query);
  
    $imuser->uid = $space['uid'];
    $imuser->id = $space['uname'];
    $imuser->nick = $space['uname'];
    #FIXME: pic path?
    $imuser->pic_url = $siteUrl . '/data/upload/avatar/'. convertUidToPath($imuser->uid). '/original_50_50.jpg';
    if ($space['uname']) {
        $imuser->default_pic_url = $siteUrl . '/addons/theme/stv1/_static/image/noavatar/middle.jpg';
    } else {
        $imuser->default_pic_url = $siteUrl . '/addons/theme/stv1/_static/image/noavatar/middle.jpg';
    }

    $imuser->show = webim_gp('show') ? webim_gp('show') : "available";
    $imuser->url = profile_url($imuser->uid);
    complete_status(array($imuser));
    if ($space['admin_level'] != 0) {
        $im_is_admin = true;
    } else {
        $im_is_admin = false;
    }
}

function webim_get_online_buddies() {
    global $friend_groups, $imuser, $_SGLOBAL,$db,$_IMC, $siteUrl;
    $list = array();
	//FIXME: 需要?
	session_start();
	//thinksns的UID通常存放于session的UID，没有做Cookie的特别处理
    $uid = $_SESSION['mid'];
	//重新载入db，这里使用db由于没有统一的外壳加载，所以必须用global来获取db的SQL参数
	//Notice: 雙向follow用戶為好友關係
    $query = $db->query("SELECT * FROM ts_user u INNER JOIN (SELECT t1.fid FROM ts_user_follow AS t1, ts_user_follow AS t2 WHERE t1.uid =$uid AND t2.fid =$uid AND t1.fid = t2.uid GROUP BY t1.fid) f ON u.uid = f.fid");
     while ($value = $query->fetch_array()) {
     
        $list[] = (object) array(
                    "uid" => $value['uid'],
                    "id" => $value['uname'],
                    "nick" => $value['uname'],
                    "group" => $group, //$friend_groups[$value['gid']],
                    "url" => profile_url($value['uid']),
                    'default_pic_url' => $siteUrl . '/addons/theme/stv1/_static/image/noavatar/middle.jpg',
                    "pic_url" =>  $siteUrl . '/data/upload/avatar/'. convertUidToPath($value['uid']). '/original_50_50.jpg',
        );
    }
	
	$guanfanguid=array();
	
	$query = $db->query("SELECT * FROM ts_user where uid in (".$_IMC['admin_uid'].")");
	 while ($value = $query->fetch_array()) {
		    $guanfanguid[]=(object) array(
                     "uid" => $value['uid'],
                    "id" => $value['uname'],
                    "nick" => $value['uname'],
                    "group" => $_IMC['admin_groupname'], //$friend_groups[$value['gid']],
                    "url" => profile_url($value['uid']),
                    'default_pic_url' => $siteUrl . '/addons/theme/stv1/_static/image/noavatar/middle.jpg',
                    "pic_url" =>  $siteUrl . '/data/upload/avatar/'. convertUidToPath($value['uid']). '/original_50_50.jpg',
					);
		 }
	$return=array_merge($list,$guanfanguid);	 
    complete_status($return);
    return $return;
}

/**
 * Get buddy list from given ids
 * $ids:
 *
 * Example:
 * 	buddy('admin,webim,test');
 *
 */
function webim_get_buddies($names, $uids = null) {
    global $_SGLOBAL, $imuser, $friend_groups,$db,$_IMC, $siteUrl;
    $where_name = "";
    $where_uid = "";
    if (!$names and !$uids)
        return array();
    if ($names) {
        $names = "'" . implode("','", explode(",", $names)) . "'";
        $where_name = "m.uname IN ($names)";
    }
    if ($uids) {
        $where_uid = "m.uid IN ($uids)";
    }
    $where_sql = $where_name && $where_uid ? "($where_name OR $where_uid)" : ($where_name ? $where_name : $where_uid);
    $list = array();
  
	  session_start();
      $uid = $_SESSION['mid'];
	 
	 $query = $db->query("SELECT m.uid, m.uname, f.fid FROM ts_user m LEFT OUTER JOIN  ts_user_follow f ON f.uid = $uid AND m.uid = f.uid WHERE m.uid <> $uid AND $where_sql");

    while ($value = $query->fetch_array()) {

        if (empty($value['fid'])) {
         
            $group = "stranger";
        } else {
            $group = "friend";
        
        }
        
        $list[] = (object) array(
                    'uid' => $value['uid'],
                    'id' => $value['uname'],
                    'nick' => $value['uname'],
                    'status' => '',
                    'status_time' => '',
                    'url' => profile_url($value['uid']),
                    'group' => $group,
                    'default_pic_url' => $siteUrl . '/addons/theme/stv1/_static/image/noavatar/middle.jpg',
                    "pic_url" =>  $siteUrl . '/data/upload/avatar/'. convertUidToPath($value['uid']). '/original_50_50.jpg',
                );
    }
    $guanfanguid=array();
	$query = $db->query("SELECT * FROM ts_user where uid in (".$_IMC['admin_uid'].")");
	 while ($value = $query->fetch_array()) {
		    $guanfanguid[]=(object) array(
                    'uid' => $value['uid'],
                    'id' => $value['uname'],
                    'nick' => "<font color='red'>".$value['uname']."</font>",
                    'status' => '',
                    'status_time' => '',
                    'url' => profile_url($value['uid']),
                    'group' => $_IMC['admin_groupname'],
                    'default_pic_url' => $siteUrl . '/addons/theme/stv1/_static/image/noavatar/middle.jpg',
                    "pic_url" =>  $siteUrl . '/data/upload/avatar/'. convertUidToPath($value['uid']). '/original_50_50.jpg',
            );
		 }
	$return=array_merge($list,$guanfanguid);	 
    complete_status($return);
    return $return;
}


function webim_get_rooms($ids=null) {
    global $_SGLOBAL, $imuser, $site_url,$db, $siteUrl;
    $rooms = array();
    
    /*
	session_start();
    $uid = $_SESSION['mid'];
    $query = $db->query("select g.id,g.name,g.logo,g.membercount from ts_group g,ts_group_member m where g.status!=0 and m.gid = g.id and m.uid = $uid");

    while ($value = $query->fetch_array()) {
        $tagid = $value['id'];
        $id = $tagid;
        $tagname = $value['name'];
        $pic = empty($value['logo']) || $value['logo'] == 'default.gif' ? '/apps/group/Tpl/default/Public/images/group_pic.gif' : '/data/uploads/' . $value['logo'];
        if (strtolower(substr($pic, 0, 4)) != "http") {
            $pic = $site_url . $pic;
        }

        $rooms[$id] = (object) array('id' => $id,
                    'nick' => $tagname,
                    'pic_url' => $pic,
                    'status' => '',
                    'status_time' => '',
                    'all_count' => $value['membercount'],
                    'url' => $site_url . '/index.php?app=group&mod=Group&act=index&gid=' . $tagid,
                    'count' => $value['membercount']);
    }
    */
    return $rooms;
}

#FIXME: 3.0 tables changed
function get_userinfo($uid) {
	global $_SGLOBAL, $site_url, $imuser,$db;
    $query = $db->query("SELECT count(*) FROM ts_notify WHERE is_read =0 AND receive = $uid");
    $temp = mysqli_fetch_array($query);
    $notifyCount = $temp[0];
    $query = $db->query("SELECT count(*) FROM ts_message WHERE is_read =0 AND to_uid = $uid");
    $temp = mysqli_fetch_array($query);
    $messageCount = $temp[0];
    $query = $db->query("SELECT atme FROM ts_user_count WHERE uid = $uid");
     $temp = mysqli_fetch_array($query);
    $atCount = $temp[0];
    $query =$db->query("SELECT comment FROM ts_user_count WHERE uid = $uid");
    $temp = mysqli_fetch_array($query);
    $commentCount = $temp[0];
    $query = $db->query("SELECT count(*) comment FROM ts_myop_myinvite WHERE is_read =0 AND touid = $uid");
    $temp = mysqli_fetch_array($query);
    $appsMessageCount = $temp[0];
    return array('notifyCount' => $notifyCount, 'messageCount' => $messageCount, 'atCount' => $atCount, 'commentCount' => $commentCount, 'appsMessageCount' => $appsMessageCount);
}

function webim_get_notifications() {
    global $_SGLOBAL, $site_url, $imuser,$db, $siteUrl;
   
   $uid = $_SESSION['mid'];
   #FIXME: 
   $member = array(); #get_userinfo($uid); 
  
    if ($member['notifyCount']) {
        $pmlist[] = array("text" => ('您有<strong>' . $member["notifyCount"] . '</strong> 个系统消息'), "link" => $siteUrl . "/index.php?app=home&mod=message&act=notify");
    }
    if ($member['messageCount']) {
        $pmlist[] = array("text" => ('您有<strong>' . $member["messageCount"] . '</strong> 个站内短消息'), "link" => $siteUrl . "/index.php?app=home&mod=message&act=index");
   }
    if ($member['atCount']) {
        $pmlist[] = array("text" => ('您有<strong>' . $member["atCount"] . '</strong> 个好友@了你'), "link" => $siteUrl . "/index.php?app=home&mod=user&act=atme");
    }
    if ($member['commentCount']) {
       $pmlist[] = array("text" => ('您有<strong>' . $member["commentCount"] . '</strong> 评论'), "link" => $siteUrl . "/index.php?app=home&mod=user&act=comments");
    }
  if ($member['appsMessageCount']) {
       $pmlist[] = array("text" => ('您有<strong>' . $member["appsMessageCount"] . '</strong> 应用消息'), "link" => $siteUrl . "/index.php?app=home&mod=message&act=appmessage");
   }
    
    return $pmlist;
}

function webim_get_menu() {
    global $_SCONFIG, $_SGLOBAL, $site_url,$db, $siteUrl;
	session_start();
	$uid=$_SESSION['mid'];
	$value=$v=$vs=array();
	$query = $db->query("SELECT * FROM ts_app where status=1");
	while ($value = $query->fetch_array()) {
		$v[]= array("title" => $value['app_alias'], "icon" => $value['icon_url'], "link" => $siteUrl . "/index.php?app=".$value['app_name']);
	}
    return $v;
}


function complete_status($members) {
      global $_SGLOBAL,$db;
    if (!empty($members)) {
        $num = count($members);
        $ids = array();
        $ob = array();
        for ($i = 0; $i < $num; $i++) {
            $m = $members[$i];
            $id = $m->uid;
            if ($id) {
                $ids[] = $id;
                $ob[$id] = $m;
            }
        }
        $ids = implode(",", $ids);
       $query=$db->query( "SELECT * FROM ts_user WHERE uid IN ($ids)");
       while ($value = $query->fetch_array()) {
		   $value['location']=empty($value['location'])?"未知地区":$value['location'];
           $ob[$value['uid']]->status = $value['location'];
      }
        
    }
    return $members;
}

function nick($sp) {
    global $_IMC;
    return (!$_IMC['show_realname'] || empty($sp['name'])) ? $sp['username'] : $sp['name'];
}

function to_utf8($s) {
    global $_SC;
    if (strtoupper($_SC['charset']) == 'UTF-8') {
        return $s;
    } else {
        if (function_exists('iconv')) {
            return iconv($_SC['charset'], 'utf-8', $s);
        } else {
            require_once 'class_chinese.php';
            $chs = new Chinese($_SC['charset'], 'utf-8');
            return $chs->Convert($s);
        }
    }
}

function from_utf8($s) {
    global $_SC;
    if (strtoupper($_SC['charset']) == 'UTF-8') {
        return $s;
    } else {
        if (function_exists('iconv')) {
            return iconv('utf-8', $_SC['charset'], $s);
        } else {
            require_once 'class_chinese.php';
            $chs = new Chinese('utf-8', $_SC['charset']);
            return $chs->Convert($s);
        }
    }
}

