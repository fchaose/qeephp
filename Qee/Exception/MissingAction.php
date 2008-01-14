<?php
/////////////////////////////////////////////////////////////////////////////
// 这个文件是 QeePHP 项目的一部分
//
// Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
//
// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
// 或者访问 http://www.qeephp.org/ 获得详细信息。
/////////////////////////////////////////////////////////////////////////////

/**
 * 定义 Qee_Exception_MissingAction 异常
 *
 * @copyright Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
 * @author 起源科技(www.qeeyuan.com)
 * @package Exception
 * @version $Id$
 */

// {{{ includes
//require_once 'Qee/Exception.php';
// }}}

/**
 * Qee_Exception_MissingAction 指示请求的控制器 Action 方法没有找到
 *
 * @package Exception
 * @author 起源科技(www.qeeyuan.com)
 * @version 1.0
 */
class Qee_Exception_MissingAction extends Qee_Exception
{
    /**
     * 控制器的名字
     *
     * @var string
     */
    public $controllerName;

    /**
     * 控制器类名称
     *
     * @var string
     */
    public $controllerClass;

    /**
     * 动作名
     *
     * @var string
     */
    public $actionName;

    /**
     * 动作方法名
     *
     * @var string
     */
    public $actionMethod;

    /**
     * 调用参数
     *
     * @var mixed
     */
    public $arguments;

    /**
     * 控制器的类定义文件
     *
     * @var string
     */
    public $controllerClassFilename;

    /**
     * 构造函数
     *
     * @param string $controllerName
     * @param string $actionName
     * @param mixed $arguments
     * @param string $controllerClass
     * @param string $actionMethod
     */
    function __construct($controllerName, $actionName, $arguments = null, $controllerClass = null, $actionMethod = null)
    {
        parent::__construct(self::t('Controller method "%s::%s()" is missing.', $controllerName, $actionName));
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->arguments = $arguments;
        $this->controllerClass = $controllerClass;
        $this->actionMethod = $actionMethod;
    }
}
