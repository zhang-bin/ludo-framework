<?php
namespace Ludo\Routing;

class Router {
    public static function parse() {
        $ctrl = 'Index';
        $act = 'index';

        if (PHP_SAPI != 'cli') {
            $pathInfo = str_replace('.html', '', $_SERVER['PATH_INFO']);
        } else {
            $pathInfo = $_SERVER['argv'][1];
        }

        if (!empty($pathInfo)) {
            $pathInfo = explode('/', trim($pathInfo, '/'));

            //==Ctrl
            !empty($pathInfo[0]) && $ctrl = ucfirst($pathInfo[0]);

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