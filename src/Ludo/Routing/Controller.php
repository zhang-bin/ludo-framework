<?php

namespace Ludo\Routing;

use Ludo\Support\ServiceProvider;
use Ludo\View\View;


/**
 * Class Controller
 *
 * @package Ludo\Routing
 */
abstract class Controller
{
    /**
     * @var View $tpl view object
     */
    protected View $tpl;

    /**
     * used when you need to specify the http header information. <br>
     * e.g.: when you sent gbk data back to ajax request, it should using header('Content-Type: text/html;charset:GBK') to prevent mash code.<br>
     * another example is using header("Content-Disposition", "attachment;filename=xxxx.zip"); to popup a SaveAS dialog. <br>
     * when using more than one header common, you should use array here.
     *
     * @var ?string $httpHeader
     */
    public ?string $httpHeader = null;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->httpHeader = 'Content-Type:text/html;charset=' . PROGRAM_CHARSET;
        $this->tpl = ServiceProvider::getInstance()->getTplHandler();
    }

    /**
     * Reset $_GET data
     *
     * @return string
     */
    protected function resetGet(): string
    {
        $get = $_GET;
        unset($get['pager']);
        $params = http_build_query($get);
        if (!empty($params)) {
            $params .= '&';
        }

        return '?' . $params . 'pager=';
    }

    /**
     * Called before main handle
     *
     * @param string $action action name
     * @return mixed
     */
    public function beforeAction(string $action)
    {

    }

    /**
     * Called after main handle
     *
     * @param string $action
     * @param array $result
     * @return mixed
     */
    public function afterAction(string $action, array $result)
    {

    }
}
