<?php
/////////////////////////////////////////////////////////////////////////////
// 这个文件是 QeePHP 项目的一部分
//
// Copyright (c) 2005 - 2007 QeePHP.org (www.qeephp.org)
//
// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
// 或者访问 http://www.qeephp.org/ 获得详细信息。
/////////////////////////////////////////////////////////////////////////////

/**
 * 定义 FLEA_Controller_Action 类
 *
 * @copyright Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
 * @author 廖宇雷 dualface@gmail.com
 * @package Core
 * @version $Id: Action.php 880 2007-06-21 13:59:32Z dualface $
 */

/**
 * FLEA_Controller_Action 实现了一个其它控制器的超类，
 * 为开发者自己的控制器提供了一些方便的成员变量和方法
 *
 * 开发者不一定需要从这个类继承来构造自己的控制器。
 * 但从这个类派生自己的控制器可以获得一些便利性。
 *
 * @package Core
 * @author 廖宇雷 dualface@gmail.com
 * @version 1.0
 */
class FLEA_Controller_Action
{
    /**
     * 当前控制的名字，用于 $this->url() 方法
     *
     * @var string
     */
    protected $_controllerName = null;

    /**
     * 当前使用的调度器的名字
     *
     * @var FLEA_Dispatcher_Auth
     */
    protected $_dispatcher = null;

    /**
     * 要使用的控制器部件
     *
     * @var array
     */
    protected $components = array();

    /**
     * 渲染视图前要调用的 callback 方法
     *
     * @var array
     */
    protected $_renderCallbacks = array();

    /**
     * 构造函数
     *
     * @param string $controllerName
     */
    public function __construct($controllerName)
    {
        $this->_controllerName = $controllerName;

        foreach ((array)$this->components as $componentName) {
            $componentClassName = FLEA::getAppInf('component.' . $componentName);
            FLEA::loadClass($componentClassName);
            $this->{$componentName} = new $componentClassName($this);
        }
    }

    /**
     * 设置控制器名字，由 dispatcher 调用
     *
     * @param string $controllerName
     */
    public function __setController($controllerName)
    {
        $this->_controllerName = $controllerName;
    }

    /**
     * 设置当前控制器使用的调度器对象
     *
     * @param FLEA_Dispatcher_Simple $dispatcher
     */
    public function __setDispatcher($dispatcher)
    {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * 获得当前使用的 Dispatcher
     *
     * @return FLEA_Dispatcher_Auth
     */
    protected function _getDispatcher()
    {
        if (!is_object($this->_dispatcher)) {
            $this->_dispatcher =& FLEA::getSingleton(FLEA::getAppInf('dispatcher'));
        }
        return $this->_dispatcher;
    }

    /**
     * 构造当前控制器的 url 地址
     *
     * @param string $actionName
     * @param array $args
     * @param string $anchor
     *
     * @return string
     */
    protected function _url($actionName = null, $args = null, $anchor = null)
    {
        return url($this->_controllerName, $actionName, $args, $anchor);
    }

    /**
     * 转发请求到另一个控制器方法
     *
     * @param string $controllerName
     * @param string $actionName
     */
    protected function _forward($controllerName = null, $actionName = null)
    {
        $this->_dispatcher->setControllerName($controllerName);
        $this->_dispatcher->setActionName($actionName);
        $this->_dispatcher->dispatching();
    }

    /**
     * 返回视图对象
     *
     * @return object
     */
    protected function _getView()
    {
        $viewClass = FLEA::getAppInf('view');
        if ($viewClass != 'PHP') {
            return FLEA::getSingleton($viewClass);
        } else {
            return false;
        }
    }

    /**
     * 执行指定的视图
     *
     * @param string $__flea_internal_viewName
     * @param array $data
     */
    protected function _executeView($__flea_internal_viewName, $data = null)
    {
        $viewClass = FLEA::getAppInf('view');
        if ($viewClass == 'PHP') {
            if (strtolower(substr($__flea_internal_viewName, -4)) != '.php') {
                $__flea_internal_viewName .= '.php';
            }
            $view = null;
            foreach ((array)$this->_renderCallbacks as $callback) {
                call_user_func_array($callback, array(& $data, & $view));
            }
            if (is_array($data)) { extract($data); }
            include $__flea_internal_viewName;
        } else {
            $view =& $this->_getView();
            foreach ((array)$this->_renderCallbacks as $callback) {
                call_user_func_array($callback, array(& $data, & $view));
            }
            if (is_array($data)) { $view->assign($data); }
            $view->display($__flea_internal_viewName);
        }
    }

    /**
     * 判断 HTTP 请求是否是 POST 方法
     *
     * @return boolean
     */
    protected function _isPOST()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    /**
     * 为指定控件绑定事件，返回浏览器端该事件响应函数的名字
     *
     * @param string $controlName
     * @param string $event
     * @param string $action
     * @param array $attribs
     *
     * @return string
     */
    protected function _registerEvent($controlName, $event, $action, $attribs = null)
    {
        $ajax = FLEA::initAjax();
        return $ajax->registerEvent($controlName, $event, url($this->_controllerName, $action), $attribs);
    }

    /**
     * 注册一个视图渲染 callback 方法
     *
     * @param callback $callback
     */
    protected function _registerRenderCallback($callback)
    {
        $this->_renderCallbacks[] = $callback;
    }
}
