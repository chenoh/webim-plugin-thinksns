<?php
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {

	public function index() {
        $im_config_file = SITE_PATH . '/webim/config.php';
        include ($im_config_file);
        if ($_POST) {
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = 'IM - 全局配置 ';
            $data[] = $_IMC;
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
            $new = array_merge($_IMC, $_POST);
            $markup = "<?php\n\$_IMC = " . var_export($new, true) . ";\n";
            file_put_contents($im_config_file, $markup);
            $this->success('保存成功');
        } else {
            $this->assign('config', $_IMC);
            $this->display();
        }
    }

    public function IMThemes() {
        $im_config_file = SITE_PATH . '/webim/config.php';
        include ($im_config_file);
        if ($_GET['app'])
            unset($_GET['app']);
        if ($_GET) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = 'IM - 皮肤 ';
            $data[] = $_IMC['theme'];
            $data[] = $_GET;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
            $new = array_merge($_IMC, $_GET);
            $markup = "<?php\n\$_IMC = " . var_export($new, true) . ";\n";
            file_put_contents($im_config_file, $markup);
            $this->assign('jumpUrl', U('admin/Operation/IMThemes'));
            $this->success('保存成功');
        } else {
            $currentURL = 'index.php?app=' . APP_NAME . '&mod=' . MODULE_NAME . '&act=' . ACTION_NAME;
            $theme = $_IMC['theme'];
            $path = dirname($im_config_file) . '/static/themes';
            $files = $this->scan_subdir($path);
            $pathURL = SITE_URL . '/apps/webim/static/themes';
            $html = '<h3 id="header-title">主题选择</h3><ul id="themes" style="width:800px;margin:20px auto">';
            foreach ($files as $k => $v) {
                $t_path = $path . '/' . $v;
                if (is_dir($t_path) && is_file($t_path . '/' . "jquery.ui.theme.css")) {
                    $cur = $v == $theme ? " style='background:#F6F6F6;width:120px;display:inline-block;margin-right:25px;'" : "";
                    $url = $currentURL . '&theme=' . $v . '#' . $v;
                    $html .= "<li$cur id='$v' style='width:120px;display:inline-block;margin-right:25px;margin-bottom:25px'><h4><a do='$v' href='$url'>$v</a></h4><p><a do='$v' href='$url'><img width=100 height=134 src='$pathURL/images/$v.png' alt='$v' title='$v'/></a></p></li>";
                }
            }
            $html .= '</ul>';
            $this->assign('html', $html);
            $this->display();
        }
    }

    function scan_subdir($dir) {
        $d = dir($dir . "/");

        $dn = array();
        while (false !== ( $f = $d->read() )) {
            if (is_dir($dir . "/" . $f) && $f != '.' && $f != '..')
                $dn[] = $f;
        }
        $d->close();
        return $dn;
    }
}
