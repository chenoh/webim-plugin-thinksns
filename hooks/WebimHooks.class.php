<?php

class WebimHooks extends Hooks
{

    //钩子
    public function public_head($param) {
        //头部钩子，预留接口，否则添加新钩子不会载入钩子，必须重装才有效
    }

    public function public_footer($param) {
        require SITE_PATH. '/addons/plugin/Webim/webim/config.php';
        echo '<script src="'. SITE_URL .'/addons/plugin/Webim/webim/custom.js.php"></script> ';
    }

	public function config(){
		require SITE_PATH. '/addons/plugin/Webim/webim/config.php';
		$this->assign('IMC', $_IMC);
		$this->display('config');
	}

	public function saveConfig() {
        require SITE_PATH. '/addons/plugin/Webim/webim/config.php';
	$theme = $_IMC['theme'];
		if($_POST) {
			$domain=$_POST['domain'];
			$apikey=$_POST['apikey'];
			$host=$_POST['host'];
			$port=$_POST['port'];
			$local=$_POST['local'];
			$emot=$_POST['emot'];
			$opacity=$_POST['opacity'];
			$show_realname=$_POST['show_realname'];	
			$disable_room=$_POST['disable_room'];	
			$disable_chatlink=$_POST['disable_chatlink'];	
			$disable_menu=$_POST['disable_menu'];
       
		if(!$domain || !$apikey || !$host || !$port) {
			$this->error('注册域名、ApiKey、IM服務器和端口不能為空。');
		}else{
			$file = fopen(SITE_PATH. '/addons/plugin/Webim/webim/config.php', "wb");

			$data=<<<EOT
 <?php
CFG= array();
CFG=array('version'=>'3.3.0',
	'enable'=>true,
	'domain'=>'$domain',
	'apikey'=>'$apikey',
	'host'=>'$host',
	'port'=>'$port',
	'theme'=>'$theme',
	'local'=>'$local',
	'show_realname'=>$show_realname,
	'disable_room'=>$disable_room,
	'disable_chatlink'=>$disable_chatlink,
	'enable_shortcut'=>true,
	'emot'=>'default',
	'opacity'=>'$opacity',
	'disable_menu'=>$disable_menu,
	'enable_login'=>false,
	'host_from_domain'=>false,
);
EOT;
            $data = str_replace('CFG', '$_IMC', $data);
		    fwrite($file, $data);  
		    @fclose($file);

		    $this->success('设置成功');
		  }
		}
	}

    public function scanDir( $dir ) {
        $d = dir( $dir."/" );
        $dn = array();
        while ( false !== ( $f = $d->read() ) ) {
            if(is_dir($dir."/".$f) && $f!='.' && $f!='..') $dn[]=$f;
        }
        $d->close();
        return $dn;
    }

	public function skin() {
		require SITE_PATH. '/addons/plugin/Webim/webim/config.php';
        $path = SITE_PATH. '/addons/plugin/Webim/webim/static/themes';
		$theme_url = SITE_URL. '/addons/plugin/Webim/webim/static/themes';

        $files = $this->scanDir($path);
        $themes = array();
        foreach ($files as $k => $v){
            $t_path = $path.'/'.$v;
            if(is_dir($t_path) && is_file($t_path."/jquery.ui.theme.css")) {
                $cur = $v == $_IMC['theme'] ? " class='current'" : "";
				$themes[] = "<li$cur><a href=\"javascript:;\" onclick=\"fChange('{$v}',$(this));\"><img width=100 height=134 src='$theme_url/images/$v.png' alt='$v' title='$v'/></a></li>";
            }
        }
		$this->assign('themes', $themes);
	    $this->display('skin');
	}

	public function writeConfig($cfg) {
		$data = '<?php $_IMC=array(); $_IMC= ' . var_export($cfg, true) . ';';
		$file = fopen(SITE_PATH. '/addons/plugin/Webim/webim/config.php', "wb");
		fwrite($file, $data);  
		@fclose($file);
	}

	public function saveSkin() {
		if($_POST) {
			require SITE_PATH. '/addons/plugin/Webim/webim/config.php';
			$_IMC['theme'] = $_POST['theme'];
			$this->writeConfig($_IMC);
		    $this->success('设置成功, 主题设置为: ' . $_POST['theme']);
		}
	}

	public function history() {
	    $this->display('history');
	}

	public function clearHistory() {
		if($_POST) {
		    switch( $_POST['ago'] ) {
			case 'weekago':
				$ago = 7*24*60*60;break;
			case 'monthago':
				$ago = 30*24*60*60;break;
			case '3monthago':
				$ago = 3*30*24*60*60;break;
			default:
				$ago = 0;
			}
			$ago = ( time() - $ago ) * 1000;
		
			$db_prefix = C('DB_PREFIX');
			$sql = "DELETE FROM `{$db_prefix}webim_histories` WHERE `timestamp` < {$ago}";
		    D()->execute($sql);
		    $this->success('清除成功: ' . $sql);
	    }
	}


}

