<?php
namespace Ludo\Routing;

use Ludo\Support\ServiceProvider;

abstract class Controller
{
    /**
     * @var String current Ctrl name
     */
    protected $name;

    /**
     * @var \Ludo\View\View
     */
    protected $tpl;

    /**
     * used when you need to specify the http header information. <br>
     * e.g.: when you sent gbk data back to ajax request, it should using header('Content-Type: text/html;charset:GBK') to prevent mash code.<br>
     * another example is using header("Content-Disposition", "attachment;filename=xxxx.zip"); to popup a SaveAS dialog. <br>
     * when using more than one header common, you should use array here.
     *
     * @var String|Array
     */
    public $httpHeader = null;

    public function __construct($name)
    {
        $this->name = $name;
        $this->httpHeader = 'Content-Type:text/html;charset='.PROGRAM_CHARSET;
        $this->tpl 	  = ServiceProvider::getInstance()->getTplHandler();
    }

    public function getCurrentCtrlName()
    {
        return $this->name;
    }

    protected function resetGet()
    {
        $get = $_GET;
        unset($get['pager']);
        $params = http_build_query($get);
        if (!empty($params)) $params .= '&';

        return '?'.$params.'pager=';
    }

    /**
     * @param string $action
     * @return string
     */
    public function beforeAction($action)
    {
        if (!logined()) {
            return gotoLogin();
        }
    }

    /**
     * @param string $action
     * @param array $result
     */
    public function afterAction($action, $result)
    {

    }
}
