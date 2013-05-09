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
        if(!$_POST['domain']) {
			$this->error('注册域名不能为空');
            return;
        }
        $_IMC['domain'] = $_POST['domain'];
        if(!$_POST['apikey']) {
			$this->error('ApiKey不能为空');
            return;
        }
        $_IMC['apikey'] = $_POST['apikey'];
        if(!$_POST['host'] || !$_POST['port']) {
			$this->error('IM服务器和端口不能为空');
            return;
        }
        $_IMC['host'] = $_POST['host'];
        $_IMC['port'] = $_POST['port'];
        $_IMC['local'] = $_POST['local'];
        $_IMC['emot'] = $_POST['emot'];
        $_IMC['opacity'] = $_POST['opacity'];
        $_IMC['show_realname'] = $_POST['show_realname'] == 'true' ? true : false; 
        $_IMC['disable_room'] = $_POST['disable_room'] == 'true' ? true : false;	
        $_IMC['disable_chatlink'] = $_POST['disable_chatlink'] == 'true' ? true : false;	
        $_IMC['disable_menu'] = $_POST['disable_menu'] == 'true' ? true : false;
        $this->writeConfig($_IMC);
        $this->success('设置成功');
	}

	public function writeConfig($cfg) {
		$data = '<?php $_IMC=array(); $_IMC= ' . var_export($cfg, true) . ';';
		$file = fopen(SITE_PATH. '/addons/plugin/Webim/webim/config.php', "wb");
		fwrite($file, $data);  
		@fclose($file);
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
