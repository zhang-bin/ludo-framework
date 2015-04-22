<?php
namespace Ludo\Routing;

class Router
{
    public static function parse($pathInfo)
    {
        $ctrl = 'Index';
        $act = 'index';
        if (!empty($pathInfo)) {
            $pathInfo = explode('/', trim($pathInfo, '/'));

            //==Ctrl
            !empty($pathInfo[0]) && $ctrl = ucfirst($pathInfo[0]);

            //==如果ctrl是aaa_bbb的格式，那么会取最后一个下划线后面的字母作为ctrl
            if (false !== ($pos = strrpos($ctrl, '_'))) {
                $ctrl = ucfirst(substr($ctrl, $pos+1));
            }

            //==Act
            if (!empty($pathInfo[1])) {
                if (is_numeric($pathInfo[1])) {
                    $_REQUEST['id'] = $_GET['id'] = $pathInfo[1];
                    $act = 'index';
                } else {
                    $act = $pathInfo[1];
                }
            }

            //==id
            $cnt = count($pathInfo);
            if ($cnt < 2) return array($ctrl, $act); //if pathinfo only one param which is ctrl, just return back;

            $paramStart = 2;
            if ($cnt % 2 != 0) {
                $_REQUEST['id'] = $_GET['id'] = $pathInfo[2];
                $paramStart = 3;
            }
            //==other Variables
            for ($i = $paramStart; $i < $cnt; $i+=2) {
                $_REQUEST[$pathInfo[$i]] = $_GET[$pathInfo[$i]] = $pathInfo[$i+1];
            }
        }
        return array($ctrl, $act);
    }
}
