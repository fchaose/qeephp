<?PHP
/////////////////////////////////////////////////////////////////////////////
// ����ļ��� QeePHP ��Ŀ��һ����
//
// Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
//
// Ҫ�鿴�����İ�Ȩ��Ϣ�������Ϣ����鿴Դ�����и����� COPYRIGHT �ļ���
// ���߷��� http://www.qeephp.org/ �����ϸ��Ϣ��
/////////////////////////////////////////////////////////////////////////////

/**
 * ���� QEE ��ͻ�������������ʼ�� QeePHP ���л���
 *
 * ���ڴ󲿷� QeePHP ���������Ҫ��Ԥ�ȳ�ʼ�� QeePHP ������
 * ��Ӧ�ó�����ֻ��Ҫͨ�� require('QEE.php') ������ļ���
 * ������� QeePHP ���л����ĳ�ʼ��������
 *
 * @copyright Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
 * @author ��Դ�Ƽ�(www.qeeyuan.com)
 * @package Core
 * @version $Id$
 */


/**
 * ���빫��������
 */
//require_once 'FLEA/Functions.php';

//include_once 'Qee/Container.php';

/**
 * ����һЩ���õĳ���
 */

// ���� QeePHP �汾�ų����� QeePHP ����·��
define('QEE_VERSION', '2.0.1 alpha');

// ��д�� DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);

// ��׼ URL ģʽ
define('URL_STANDARD',  1);

// PATHINFO ģʽ
define('URL_PATHINFO',  2);

// URL ��дģʽ
define('URL_REWRITE',   3);

// URL ·��ģʽ�� ��ʵ��
define('URL_ROUTER',    4);


/**#@+
 * ���� RBAC ������ɫ����
 */
// RBAC_EVERYONE ��ʾ�κ��û������ܸ��û��Ƿ���н�ɫ��Ϣ��
define('RBAC_EVERYONE',     -1);

// RBAC_HAS_ROLE ��ʾ�����κν�ɫ���û�
define('RBAC_HAS_ROLE',     -2);

// RBAC_NO_ROLE ��ʾ�������κν�ɫ���û�
define('RBAC_NO_ROLE',      -3);

// RBAC_NULL ��ʾ������û��ֵ
define('RBAC_NULL',         null);
/**#@-*/


/**
 * ��ʼ�� QeePHP ���
 */
if (DEBUG_MODE) {
    //error_reporting(E_ALL & E_STRICT);
} else {
    //error_reporting(0);
}


/**
 * Qee ���ṩ�� QeePHP ��ܵĻ�������
 *
 * ��������з������Ǿ�̬������
 *
 * @package Core
 * @author ��Դ�Ƽ�(www.qeeyuan.com)
 * @version 1.0
 */
abstract class Qee
{
    /**
     * Ӧ�ó�������
     *
     * @var array
     */
    private static $APP_INF = array();

    /**
     * ����Ӧ�ó�������
     *
     * @param string $configFilename �����ļ���
     */
    static function loadAppInf($configFilename)
    {
        $config = self::loadFile($configFilename);
        self::setAppInf($config);
    }

    /**
     * ȡ��ָ�����ֵ�����ֵ
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    static function getAppInf($key, $default = null)
    {
		if(is_null($key)) return self::$APP_INF;
		if(isset(self::$APP_INF[$key])) return self::$APP_INF[$key];
		$arr = explode(".", $key);
		//�����÷�ʽ���ҹ�������
		$pt  = &self::$APP_INF;
		while($arr)
		{
			if(!is_array($pt)) return $default;
			$key = array_shift($arr);
			$pt = &$pt[$key];
		}
		if (null === $pt) return $default;
		return $pt;
    }

    /**
     * ���ָ�����ֵ�����ֵ�е���Ŀ��Ҫ������ñ���������
     *
     * @param string $option
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    static function getAppInfValue($option, $key, $default = null)
    {
        return isset(self::$APP_INF[$option][$key]) ? self::$APP_INF[$option][$key] : $default;
    }

    /**
     * �޸�����ֵ
     *
     * @param string $option
     * @param mixed $data
     */
    static function setAppInf($option, $data = null)
    {
        if (is_array($option)) {
            self::$APP_INF = array_merge(self::$APP_INF, $option);
        } else {
            self::$APP_INF[$option] = $data;
        }
    }

    /**
     * ����ע���
     *
     * @var array
     */
    private static $OBJECTS = array();

    /**
     * ������·��
     *
     * @var array
     */
    private static $CLASS_PATH = array();


    /**
     * ����ָ����Ķ����ļ����������ʧ���׳��쳣
     *
     * example:
     * <code>
     * Qee_Container::loadClass('Qee_Db_TableDataGateway');
     * </code>
     *
     * �ڲ����ඨ���ļ�ʱ���������еġ�_���ᱻ�滻ΪĿ¼�ָ�����
     * �Ӷ�ȷ�������ƺ��ඨ���ļ���ӳ���ϵ�����磺Qee_Db_TableDataGateway �Ķ����ļ�Ϊ
     * Qee/Db/TableDataGateway.php����
     *
     * loadClass() �����ȳ��Դӿ�����ָ��������·���в�����Ķ����ļ���
     * ����·�������� Qee_Container::import() ��ӣ�����ͨ�� $dirs �����ṩ��
     *
     * ���û��ָ�� $dirs ����������·������ô loadClass() ��ͨ�� PHP ��
     * include_path �����������ļ���
     *
     * @param string $className Ҫ�����������
     * @param string|array $dirs ��ѡ������·��
     */
    static function loadClass($className, $dirs = null)
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return;
        }

        if (null === $dirs) {
            $dirs = self::$CLASS_PATH;
        } else {
            if (!is_array($dirs)) {
                $dirs = explode(PATH_SEPARATOR, $dirs);
            }
            $dirs = array_merge($dirs, self::$CLASS_PATH);
        }

        $filename = str_replace('_', DIRECTORY_SEPARATOR, $className);
        if ($filename != $className) {
            $dirname = dirname($filename);
            foreach ($dirs as $offset => $dir) {
                if ($dir == '.') {
                    $dirs[$offset] = $dirname;
                } else {
                    $dir = rtrim($dir, '\\/');
                    $dirs[$offset] = $dir . DIRECTORY_SEPARATOR . $dirname;
                }
            }
            $filename = basename($filename) . '.php';
        } else {
            $filename .= '.php';
        }

        self::loadFile($filename, true, $dirs);

        if ( !(class_exists($className, false) || interface_exists($className, false)) ) {
            //require_once 'Qee/Exception/ExpectedClass.php';
            throw new Qee_Exception_ExpectedClass($className, $filename);
        }
    }

    /**
     * ����ָ�������Ψһʵ�������ָ�����޷�����򲻴��ڣ����׳��쳣
     *
     * example:
     * <code>
     * $service = Qee_Container::getSingleton('Service_Products');
     * </code>
     *
     * @param string $classname Ҫ��ȡ�Ķ����������
     *
     * @return object
     */
    static function getSingleton($classname)
    {
        if (isset(self::$OBJECTS[$classname])) {
            return self::registry($classname);
        }
        self::loadClass($classname);
        return self::register(new $classname(), $classname);
    }

	/**
	 * ���ض�����ע��һ�������Ա��Ժ��� registry() ����ȡ�ظö���
	 * ���ָ�������Ѿ���ʹ�ã����׳��쳣
	 *
	 * example:
	 * <code>
	 * // ע��һ������
	 * Container::register(new MyObject(), 'my_obejct');
	 * .....
	 * // �Ժ�ȡ������
	 * $obj = Qee::registry('my_object');
	 * </code>
	 *
	 * Container �ṩһ������ע��������߿��Խ�һ���������ض�����ע�ᵽ���С�
	 *
	 * ��û���ṩ $name ����ʱ�����Զ������������Ϊע������
	 *
	 * �� $persistent ����Ϊ true ʱ�����󽫱�����־ô洢��������һ��ִ�нű�ʱ��
	 * ����ͨ�� Container::registry() ȡ������־ô洢���Ķ����������¹������
	 * ����������ԣ������߿��Խ�һЩ��Ҫ��������ʱ��Ķ������־ô洢����
	 * �Ӷ�����ÿһ��ִ�нű���ȥ�������������
	 *
	 * ʹ����һ�ֳ־û��洢�������������Ӧ�ó������� objectPersistentProvier ������
	 * ������ָ��һ���ṩ�־û�����Ķ�������
	 *
	 * example:
	 * <code>
	 * if (!Container::isRegister('ApplicationObject')) {
	 * 		Container::loadClass('Application');
	 * 		Container::register(new Application(), 'ApplicationObject', true);
	 * }
	 * $app = Container::registry('ApplicationObject');
	 * </code>
	 *
	 * @param object $obj Ҫע��Ķ���
	 * @param string $name ע�������
	 * @param boolean $persistent �Ƿ񽫶������־û��洢��
	 */
    static function register($obj, $name = null, $persistent = false)
    {
        if (is_null($name)) {
            $name = get_class($obj);
        }
        if (!isset(self::$OBJECTS[$name])) {
            self::$OBJECTS[$name] = $obj;
			return self::$OBJECTS[$name];
        }

        //require_once 'Qee/Exception/DuplicateEntry.php';
        throw new Exception_DuplicateEntry('Qee_Container::register($name)', $name);
    }

	/**
	 * ȡ��ָ�����ֵĶ���ʵ�������ָ�����ֵĶ��󲻴������׳��쳣
	 *
	 * ʹ��ʾ���ο� Container::register()��
	 *
	 * @param string $name ע����
	 *
	 * @return object
	 */
    static function registry($name)
    {
        if (isset(self::$OBJECTS[$name])) {
            return self::$OBJECTS[$name];
        }

        //require_once 'Qee/Exception/NonExistentEntry.php';
        throw new Exception_NonExistentEntry('Qee_Container::registry($name)', $name);
    }

    /**
     * ���ָ�����ֵĶ����Ƿ��Ѿ�ע��
     *
     * ʹ��ʾ���ο� Qee_Container::register()��
     *
     * @param string $name ע����
     *
     * @return boolean
     */
    static function isRegistered($name)
    {
        return isset(self::$OBJECTS[$name]);
    }


    /**
     * ����ָ�����ļ�
     *
     * $filename ����������һ��������չ���������ļ�����
     * loadFile() �����ȴ� $dirs ����ָ����·���в����ļ���
     * �Ҳ���ʱ�ٴ� PHP �� include_path ����·���в����ļ���
     *
     * $once ����ָʾͬһ���ļ��Ƿ�ֻ����һ�Ρ�
     *
     * example:
     * <code>
     * Qee_Container::loadFile('Table/Products.php');
     * </code>
     *
     * @param string $filename Ҫ������ļ���
     * @param boolean $once ͬһ���ļ��Ƿ�ֻ����һ��
     * @param array $dirs ����Ŀ¼
     *
     * @return mixed
     */
    static function loadFile($filename, $once = false, $dirs = null)
    {
        //if (preg_match('/[^a-z0-9\-_.]/i', $filename)) {
        //    throw new Qee_Exception(Qee_Exception::t('Security check: Illegal character in filename: %s.', $filename));
       // }

        if (is_null($dirs)) {
            $dirs = array();
        } else if (is_string($dirs)) {
            $dirs = explode(PATH_SEPARATOR, $dirs);
        }

        foreach ($dirs as $dir) {
            $path = rtrim($dir, '\\/') . DIRECTORY_SEPARATOR . $filename;
            if (@self::isReadable($path)) {
                return $once ? include_once $path : include $path;
            }
        }

        // include ���� include_path ��Ѱ���ļ�
        if (@self::isReadable($filename)) {
            return $once ? include_once $filename : include $filename;
        }
    }

    /**
     * �����ļ�����·��
     *
     * ��ʹ�� loadClass() ʱ����ͨ�� import() ָ��������·�������ඨ���ļ���
     *
     * �� loadClass('Service_Products') ʱ������������ӳ��������ඨ���ļ��Ѿ�������Ŀ¼��
     * ��Service_Products ӳ��Ϊ Service/Products.php����
     * ����ֻ�ܽ� Service ��Ŀ¼����Ŀ¼��ӵ�����·����������ֱ�ӽ� Service Ŀ¼��ӵ�����·����
     *
     * example:
     * <code>
     * // ����Ҫ������ļ�����·��Ϊ /www/app/Service/Products.php
     * Qee_Container::import('/www/app');
     * Qee::loadClass('Service_Products');
     * </code>
     *
     * @param string $dir
     */
    static function import($dir)
    {
        if (!array_search($dir, self::$CLASS_PATH)) {
            self::$CLASS_PATH[] = $dir;
        }
    }

    /**
     * ���ָ���ļ��Ƿ�ɶ�
     *
     * ����������� PHP ������·���в����ļ���
     *
     * �÷������� Zend Framework �е� Zend_Loader::isReadable()��
     *
     * @param string $filename
     *
     * @return boolean
     */
    public static function isReadable($filename)
    {
        if (@is_readable($filename)) { return true; }

        $path = get_include_path();
        $dirs = explode(PATH_SEPARATOR, $path);

        foreach ($dirs as $dir) {
            if ('.' == $dir) { continue; }
            if (@is_readable($dir . DIRECTORY_SEPARATOR . $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * QeePHP Ӧ�ó��� MVC ģʽ���
     */
    static function runMVC()
    {
        self::init();
		$dispatcherName = self::getAppInf('dispatcher');
		$dispatcher = self::getSingleton($dispatcherName);
		//var_dump($dispatcher);
		
		return $dispatcher->dispatch();
    }

    /**
     * ׼�����л���
     */
    static function init()
    {
        static $firstTime = true;

        // �����ظ����� self::init()
        if (!$firstTime) { return; }
        $firstTime = false;

		/**
		 * �Զ����ض���
		 */
		spl_autoload_register(array('Qee', 'loadClass'));
		/**
		 * �����쳣��������
		 */
		set_exception_handler(array('Qee', 'printError'));
        /**
         * ������־�����ṩ����
         */
        if (self::getAppInf('logEnabled') && self::getAppInf('logProvider')) {
            self::loadClass(self::getAppInf('logProvider'));
        }
        if (!function_exists('log_message')) {
            // ���û��ָ����־�����ṩ���򣬾Ͷ���һ���յ� log_message() ����
            eval('function log_message() {}');
        }

        // ���� magic_quotes
        if (get_magic_quotes_gpc()) {
            $in = array(& $_GET, & $_POST, & $_COOKIE, & $_REQUEST);
            while (list($k,$v) = each($in)) {
                foreach ($v as $key => $val) {
                    if (!is_array($val)) {
                        $in[$k][$key] = stripslashes($val);
                        continue;
                    }
                    $in[] =& $in[$k][$key];
                }
            }
            unset($in);
        }
        set_magic_quotes_runtime(0);

        // ���� URL ģʽ���ã������Ƿ�Ҫ���� URL ����������
        //if (self::getAppInf('urlMode') != URL_STANDARD) {
        //    require 'Qee/Filter/Uri.php';
        //}

        // ���� requestFilters
        foreach ((array)self::getAppInf('requestFilters') as $file) {
            self::loadFile($file);
        }

        // ���� autoLoad
        foreach ((array)self::getAppInf('autoLoad') as $file) {
            self::loadFile($file);
        }

        // ����ָ���� session �����ṩ����
        //if (self::getAppInf('sessionProvider')) {
        //    self::getSingleton(self::getAppInf('sessionProvider'));
        //}
        // �Զ����� session �Ự
        //if (self::getAppInf('autoSessionStart')) {
        //    session_start();
        //}

        // ���� I18N ��صĳ���
        define('RESPONSE_CHARSET', self::getAppInf('responseCharset'));
        define('DATABASE_CHARSET', self::getAppInf('databaseCharset'));

        // ����Ƿ����ö�����֧��
        if (self::getAppInf('multiLanguageSupport')) {
            self::loadClass(self::getAppInf('languageSupportProvider'));
        }
        if (!function_exists('_T')) {
            eval('function _T() {}');
        }

        // �Զ��������ͷ��Ϣ
        if (self::getAppInf('autoResponseHeader')) {
            header('Content-Type: text/html; charset=' . self::getAppInf('responseCharset'));
        }
    }
	
	/**
	 * ���Ժʹ�������ص�ȫ�ֺ���
	 */

	/**
	 * QeePHP Ĭ�ϵ��쳣��������
	 *
	 * @package Core
	 *
	 * @param Exception $ex
	 */
	function printError(Exception $ex)
	{
	    if (!self::getAppInf('displayErrors')) { exit; }
	    if (self::getAppInf('friendlyErrorsMessage')) {
	        $language = self::getAppInf('defaultLanguage');
	        $language = preg_replace('/[^a-z0-9\-_]+/i', '', $language);

	        $exclass = strtoupper(get_class($ex));
	        $template = "Qee/_Errors/{$language}/{$exclass}.php";
	        if (!file_exists($template)) {
	            $template = "Qee/_Errors/{$language}/QEE_EXCEPTION.php";
	            if (!file_exists($template)) {
	                $template = "Qee/_Errors/default/QEE_EXCEPTION.php";
	            }
	        }
	        include $template;
	    } else {
	        Qee_Exception::printEx($ex);
	    }
	    exit;
	}

}




// ��������·��
Qee::import(dirname(__FILE__));
Qee::import(dirname(__FILE__).'/Qee');


/**
 * ���� url
 *
 * ���� url ��Ҫ�ṩ�������������������ƺͿ����������������ʡ��������������������һ����
 * �� url() ������ʹ��Ӧ�ó��������е�ȷ����Ĭ�Ͽ������ƺ�Ĭ�Ͽ�������������
 *
 * url() �����Ӧ�ó������� urlMode ���ɲ�ͬ�� URL ��ַ��
 * - URL_STANDARD - ��׼ģʽ��Ĭ�ϣ������� index.php?url=Login&action=Reject&id=1
 * - URL_PATHINFO - PATHINFO ģʽ������ index.php/Login/Reject/id/1
 * - URL_REWRITE  - URL ��дģʽ������ /Login/Reject/id/1
 *
 * ���ɵ� url ��ַ����Ҫ������Ӧ�ó������õ�Ӱ�죺
 *   - controllerAccessor
 *   - defaultController
 *   - actionAccessor
 *   - defaultAction
 *   - urlMode
 *   - urlLowerChar
 *
 * �÷���
 * <code>
 * $url = url('Login', 'checkUser');
 * // $url ����Ϊ ?controller=Login&action=checkUser
 *
 * $url = url('Login', 'checkUser', array('name' => 'test'));
 * // $url ����Ϊ ?controller=Login&action=checkUser&name=test
 *
 * $url = url('Article', 'View', array('id' => 1'), '#details');
 * // $url ����Ϊ ?controller=Article&action=View&id=1#details
 * </code>
 *
 * @param string $controllerName
 * @param string $actionName
 * @param array $params
 * @param string $anchor
 * @param array $options
 *
 * @return string
 */
function url($controllerName = null, $actionName = null, $params = null, $anchor = null, $options = null)
{

	
	$dispatcherName = Qee::getAppInf('dispatcher');
	$dispatcher = Qee::getSingleton($dispatcherName);
	return $dispatcher->url($controllerName, $actionName, $params, $anchor);
}
