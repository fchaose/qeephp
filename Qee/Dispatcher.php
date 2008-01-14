<?php
/////////////////////////////////////////////////////////////////////////////
// ����ļ��� QeePHP ��Ŀ��һ����
//
// Copyright (c) 2005 - 2007 QeePHP.org (www.qeephp.org)
//
// Ҫ�鿴�����İ�Ȩ��Ϣ�������Ϣ����鿴Դ�����и����� COPYRIGHT �ļ���
// ���߷��� http://www.qeephp.org/ �����ϸ��Ϣ��
/////////////////////////////////////////////////////////////////////////////

/**
 * ���� Qee_Dispatcher��
 *
 * @copyright Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
 * @author ��Դ�Ƽ�(www.qeeyuan.com)
 * @package Core
 * @version $Id$
 */

/**
 * Qee_Dispatcher ���� HTTP ���󣬲�ת�������ʵ� Controller ������
 *
 * @package Core
 * @author ��Դ�Ƽ�(www.qeeyuan.com)
 * @version 1.0
 */
class Qee_Dispatcher
{
	// Base URL
	protected $_base = null;
	// ���ʲ�������(��_GET����)
	protected $_params = array();
	// �ṩ���������Ĳ���
	protected $_vars = array();
	
	// �ṩ�û���֤����Ľӿ�ʵ����(Auth_interface)
	protected $_auth;
	// string
	protected $_controller;
	// string
	protected $_action;

	/**
	 * ���캯��
	 *
	 *
	 * @return Dispatcher
	 */
	public function __construct()
	{
		//$this->_auth = & AuthFactory::getAuth();
		$this->_base = Qee::getAppInf('urlBase');
		$url = $_SERVER['REQUEST_URI'];
		$urlMode = Qee::getAppInf('urlMode');
		$data = $this->parseMode($url, $urlMode);

		$this->_controller = $data['controller'];
		$this->_action = $data['action'];
		$this->_params = &$data['params'];
		$this->_vars = &$data['vars'];
	}

	/**
	 * function description
	 * 
	 * @param
	 * @return void
	 */
	public function forward($controller, $action = null, $args = array())
	{
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_vars = $args;
		return $this->dispatch();
	}
	
	/*
	 * �������� 
	 *
	 */
	public function dispatch()
	{
		$data = '';
		$cache_pages_items = Qee::getAppInf('cached_pages_items');
		if(isset($cache_pages_items[$this->_controller.'.'.$this->_action]))
		{
			$lifetime = $cache_pages_items[$this->_controller.'.'.$this->_action];
			$cached_key = sprintf("%s.%s_%s", $this->_controller, $this->_action, @implode("_", $this->_vars ));
			$cache_selotion = Cache::getSolution('cached_pages_options');
			if(($data = $cache_selotion->get($cached_key)) === FALSE )
			{
				$data = $this->execute();
				$cache_selotion->set($cached_key, $data, $lifetime);
			}
		}
		else
		{
			$data = $this->execute();
		}
		echo $data;
		

	}

	/**
	 * function description
	 * 
	 * @param
	 * @return void
	 */
	protected function &execute()
	{
		$controller = ucfirst($this->_controller);
		$action = ucfirst($this->_action);
		
		//���Ͽ�����ǰ�
		$controller = Qee::getAppInf("controllerPrefix") . $controller;
		//$file = Qee::getAppInf("controllerPath") . DS . $controller . ".php";

		Qee::loadClass($controller);
		if(!class_exists($controller)) {
			throw new Exception("����Ŀ����� <span class='err'>$file::$controller</span> �����ڡ�");
		}

		$ctl = new $controller($controller);
		$ctl->setDispatcher($this);
		//$ctl->setParams($this->_params);

		$actionPrefix = Qee::getAppInf('actionMethodPrefix');
        if ($actionPrefix != '') { $actionMethod = $actionPrefix . ucfirst($action); }
		else $actionMethod = $action;

		//ִ��action
		if(method_exists($ctl, $actionPrefix . $action) )
		{
		}
		elseif(method_exists($ctl, '__call'))
		{
			$actionMethod = $action;
		}
		else
		{
			throw new Exception("����Ŀ����� <span class='err'>$file::$controller</span> �����ڶ��� <span class='err'>$actionMethod</span>��");
			
		}

        ob_start();
        ob_implicit_flush(false);

		$out = call_user_func_array(array($ctl, $actionMethod), $this->_vars);
		
        $data = ob_get_contents();
        ob_end_clean();
		return $data;
	}
	
	/**
	 * function description
	 * 
	 * @param
	 * @return void
	 */
	public function &parseUrl($url, $base = '')
	{
		$parts = parse_url($url);
		
		if (!$parts || empty($parts['path']))
		{
			return FALSE;
		}
		if (!empty($_SERVER['PATH_INFO']) )
		{
			$parts['pathinfo'] = $_SERVER['PATH_INFO'];
			if ($_SERVER['PATH_INFO'] == '/')
			{
				$base = $parts['path'];
			}
			else
			{
				$pos = strpos($url, $_SERVER['PATH_INFO']);
				if ($pos !== false)
				{
					$base = substr($parts['path'], 0, $pos);
				}
				elseif(isset($_SERVER['SCRIPT_NAME'])) $base = $_SERVER['SCRIPT_NAME'];
			}
		}
		else
		{
			$parts['pathinfo'] = empty($base)?$parts['path']:substr($parts['path'], strlen($base)-1);
		}

		parse_str($parts['query'], $params);
		$parts['params'] = array_merge($_GET, $params);

		$parts['base'] = $base;
		return $parts;
	}

	/*
	 * ����URLģʽ
	 */
	public function &parseMode($url, $urlMode = null)
	{
		$request = $this->parseUrl($url, $this->_base);
		extract($request);
		$this->_base = $base;
		$defaultController = Qee::getAppInf("defaultController");
		$defaultAction = Qee::getAppInf("defaultAction");
		$data = array('controller' => $defaultController, 'action' => $defaultAction);
		switch($urlMode)
		{
			//pathInfoģʽ
			case URL_PATHINFO:
				if(empty($_SERVER['PATH_INFO'])) {
					throw new Exception("_SERVER['PATH_INFO'] not found");
				}
			case URL_REWRITE:
			case URL_ROUTER:
				$parts = explode('/', trim($pathinfo, '/'));
				isset($parts[0]) && !empty($parts[0]) && $data['controller'] =  $parts[0];
				isset($parts[1]) && !empty($parts[1]) && $data['action']	 =  $parts[1];
				$data['vars'] = array_slice($parts, 2);
				break;
			case URL_STANDARD:
			default:
				$ctl = Qee::getAppInf('controllerAccessor');
				$act = Qee::getAppInf('actionAccessor');
				$data['controller'] = !empty($_REQUEST[$ctl]) ? $_REQUEST[$ctl] : $defaultController;
				$data['action']	 = !empty($_REQUEST[$act]) ? $_REQUEST[$act] : $defaultAction;
				if(isset($_GET['_pi'])) $data['vars'] = explode('/', trim($_GET['_pi'], '/'));
				break;
		}
		
		$data['params'] = & $params;

		return $data;
	}

	/*
	 * ָ�ɿ�����
	 */
	public function setController($controller)
	{
		$this->_controller = $controller;
	}

	/*
	 * ָ�ɶ���
	 */
	public function setAction($action)
	{
		$this->_action = $action;
	}

	/*
	 * ȡ�ÿ����������� 
	 */
	public function getController()
	{
		return $this->_controller;

	}

	/*
	 * ȡ�ö��������� 
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * ���ص�ǰʹ�õ���֤�������
	 *
	 * @return Auth_interface
	 */
    function & getAuth()
    {
        return $this->_auth;
    }

	/**
	 * ����Ҫʹ�õ���֤�������
	 *
	 * @param Auth_interface $auth
	 */
    function setAuth($auth)
    {
        $this->_auth =& $auth;
    }

	/**
	 * ���ݿ���������Ϣ����URL
	 * δ���ƣ�Ŀǰֻ֧�� Pathinfo  ��ʽ�� Rewrite����Ҫ������Router�Ĵ���
	 * 
	 * @param
	 * @return void
	 */
	public function url($controllerName = null, $actionName = null, $params = null, $anchor = null)
	{
		$out = $this->_base . '/' . $controllerName . '/' . $actionName;
		!empty($params) && $out .= '/' . (is_array($params)? implode('/', $params) : $params);
		!empty($anchor) && $out .= '#' . $anchor;
		return $out;
	}
}
