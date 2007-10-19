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


//include_once 'Qee/Container.php';

/**
 * ����һЩ���õĳ���
 */

// ���� QeePHP �汾�ų����� QeePHP ����·��
define('QEE_VERSION', '1.8');

// ��д�� DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);

// ��׼ URL ģʽ
define('URL_STANDARD',  1);

// PATHINFO ģʽ
define('URL_PATHINFO',  2);

// URL ��дģʽ
define('URL_REWRITE',   3);

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

// �����쳣��������
set_exception_handler('__QEE_EXCEPTION_HANDLER');

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
     * @param string $option
     * @param mixed $default
     *
     * @return mixed
     */
    static function getAppInf($option, $default = null)
    {
        return isset(self::$APP_INF[$option]) ? self::$APP_INF[$option] : $default;
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

        if (!class_exists($classname, false)) {
            require_once 'Qee/Exception/ExpectedClass.php';
            throw new Qee_Exception_ExpectedClass($classname, $filename);
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
     * Qee_Container::register(new MyObject(), 'my_obejct');
     * .....
     * // �Ժ�ȡ������
     * $obj = Qee::registry('my_object');
     * </code>
     *
     * Qee_Container �ṩһ������ע��������߿��Խ�һ���������ض�����ע�ᵽ���С�
     *
     * ��û���ṩ $name ����ʱ�����Զ������������Ϊע������
     *
     * �� $persistent ����Ϊ true ʱ�����󽫱�����־ô洢��������һ��ִ�нű�ʱ��
     * ����ͨ�� Qee_Container::registry() ȡ������־ô洢���Ķ����������¹������
     * ����������ԣ������߿��Խ�һЩ��Ҫ��������ʱ��Ķ������־ô洢����
     * �Ӷ�����ÿһ��ִ�нű���ȥ�������������
     *
     * ʹ����һ�ֳ־û��洢�������������Ӧ�ó������� objectPersistentProvier ������
     * ������ָ��һ���ṩ�־û�����Ķ�������
     *
     * example:
     * <code>
     * if (!Qee_Container::isRegister('ApplicationObject')) {
     * 		Qee_Container::loadClass('Application');
     * 		Qee_Container::register(new Application(), 'ApplicationObject', true);
     * }
     * $app = Qee_Container::registry('ApplicationObject');
     * </code>
     *
     * @param object $obj Ҫע��Ķ���
     * @param string $name ע�������
     * @param boolean $persistent �Ƿ񽫶������־û��洢��
     */
    static function register($obj, $name = null, $persistent = false)
    {
        // TODO: ʵ�ֶ� $persistent ������֧��

        if (is_null($name)) {
            $name = get_class($obj);
        }
        if (!isset(self::$OBJECTS[$name])) {
            self::$OBJECTS[$name] = $obj;
            return;
        }

        require_once 'Qee/Exception/DuplicateEntry.php';
        throw new Exception_DuplicateEntry('Qee_Container::register($name)', $name);
    }

    /**
     * ȡ��ָ�����ֵĶ���ʵ�������ָ�����ֵĶ��󲻴������׳��쳣
     *
     * ʹ��ʾ���ο� Qee_Container::register()��
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

        require_once 'Qee/Exception/NonExistentEntry.php';
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
     * ��ʼ�� WebControls������ Qee_WebControls ����ʵ��
     *
     * @return Qee_WebControls
     */
    static function initWebControls()
    {
        return self::getSingleton(self::getAppInf('webControlsClassName'));
    }

    /**
     * ��ʼ�� Ajax������ Qee_Ajax ����ʵ��
     *
     * @return Qee_Ajax
     */
    static function initAjax()
    {
        return self::getSingleton(self::getAppInf('ajaxClassName'));
    }

    /**
     * ����һ�����֣��������ֶ����һ��ʵ��
     *
     * @param string $helperName
     */
    static function loadHelper($helperName)
    {
        $settingName = 'helper.' . strtolower($helperName);
        $setting = self::getAppInf($settingName);
        if ($setting) {
            Qee_Container::loadFile($setting, true);
        }
    }

    /**
     * QeePHP Ӧ�ó��� MVC ģʽ���
     */
    static function runMVC()
    {
        self::init();
        $dispatcher = self::getSingleton(self::getAppInf('dispatcher'));
        return $dispatcher->dispatching();
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
        if (self::getAppInf('urlMode') != URL_STANDARD) {
            require 'Qee/Filter/Uri.php';
        }

        // ���� requestFilters
        foreach ((array)self::getAppInf('requestFilters') as $file) {
            self::loadFile($file);
        }

        // ���� autoLoad
        foreach ((array)self::getAppInf('autoLoad') as $file) {
            self::loadFile($file);
        }

        // ����ָ���� session �����ṩ����
        if (self::getAppInf('sessionProvider')) {
            self::getSingleton(self::getAppInf('sessionProvider'));
        }
        // �Զ����� session �Ự
        if (self::getAppInf('autoSessionStart')) {
            session_start();
        }

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
}



 function __autoload($class)
{
	Qee::loadCalss($class);
}

// ��������·��
Qee::import(dirname(__FILE__));
Qee::import(dirname(__FILE__).'/Qee');

/**
 * �ض����������ָ���� URL
 *
 * @param string $url Ҫ�ض���� url
 * @param int $delay �ȴ��������Ժ���ת
 * @param bool $js ָʾ�Ƿ񷵻�������ת�� JavaScript ����
 * @param bool $jsWrapped ָʾ���� JavaScript ����ʱ�Ƿ�ʹ�� <script> ��ǩ���а�װ
 * @param bool $return ָʾ�Ƿ񷵻����ɵ� JavaScript ����
 */
function redirect($url, $delay = 0, $js = false, $jsWrapped = true, $return = false)
{
    $delay = (int)$delay;
    if (!$js) {
        if (headers_sent() || $delay > 0) {
            echo <<<EOT
<html>
<head>
<meta http-equiv="refresh" content="{$delay};URL={$url}" />
</head>
</html>
EOT;
            exit;
        } else {
            header("Location: {$url}");
            exit;
        }
    }

    $out = '';
    if ($jsWrapped) {
        $out .= '<script language="JavaScript" type="text/javascript">';
    }
    if ($delay > 0) {
        $out .= "window.setTimeout(function () { document.location='{$url}'; }, {$delay});";
    } else {
        $out .= "document.location='{$url}';";
    }
    if ($jsWrapped) {
        $out .= '</script>';
    }

    if ($return) {
        return $out;
    }

    echo $out;
    exit;
}

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
 * $url = url('Login', 'checkUser', array('username' => 'dualface'));
 * // $url ����Ϊ ?controller=Login&action=checkUser&username=dualface
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
    static $baseurl = null, $currentBootstrap = null;

    // ȷ����ǰ�� URL ������ַ������ļ���
    if ($baseurl == null) {
        $baseurl = detect_uri_base();
        $p = strrpos($baseurl, '/');
        $currentBootstrap = substr($baseurl, $p + 1);
        $baseurl = substr($baseurl, 0, $p);
    }

    // ȷ������ url Ҫʹ�õ� bootstrap
    $options = (array)$options;
    if (isset($options['bootstrap'])) {
        $bootstrap = $options['bootstrap'];
    } else if ($currentBootstrap == '') {
        $bootstrap = Qee::getAppInf('urlBootstrap');
    } else {
        $bootstrap = $currentBootstrap;
    }

    // ȷ���������Ͷ���������
    if ($bootstrap != $currentBootstrap && $currentBootstrap != '') {
        $controllerName = !empty($controllerName) ? $controllerName : null;
        $actionName = !empty($actionName) ? $actionName : null;
    } else {
        $controllerName = !empty($controllerName) ? $controllerName : Qee::getAppInf('defaultController');
        $actionName = !empty($actionName) ? $actionName : Qee::getAppInf('defaultAction');
    }
    $lowerChar = isset($options['lowerChar']) ? $options['lowerChar'] : Qee::getAppInf('urlLowerChar');
    if ($lowerChar) {
        $controllerName = strtolower($controllerName);
        $actionName = strtolower($actionName);
    }

    $url = '';
    $mode = isset($options['mode']) ? $options['mode'] : Qee::getAppInf('urlMode');

    // PATHINFO �� REWRITE ģʽ
    if ($mode == URL_PATHINFO || $mode == URL_REWRITE) {
        $url = $baseurl;
        if ($mode == URL_PATHINFO) {
            $url .= '/' . $bootstrap;
        }
        if ($controllerName != '' && $actionName != '') {
            $pps = isset($options['parameterPairStyle']) ? $options['parameterPairStyle'] : Qee::getAppInf('urlParameterPairStyle');
            $url .= '/' . rawurlencode($controllerName) . '/' . rawurlencode($actionName);
            if (is_array($params) && !empty($params)) {
                $url .= '/' . encode_url_args($params, $mode, $pps);
            }
        }
        if ($anchor) { $url .= '#' . $anchor; }
        return $url;
    }

    // ��׼ģʽ
    $alwaysUseBootstrap = isset($options['alwaysUseBootstrap']) ? $options['alwaysUseBootstrap'] : Qee::getAppInf('urlAlwaysUseBootstrap');
    $url = $baseurl . '/';

    if ($alwaysUseBootstrap || $bootstrap != Qee::getAppInf('urlBootstrap')) {
        $url .= $bootstrap;
    }

    $parajoin = '?';
    if ($controllerName != '') {
        $url .= $parajoin . Qee::getAppInf('controllerAccessor'). '=' . rawurlencode($controllerName);
        $parajoin = '&';
    }
    if ($actionName != '') {
        $url .= $parajoin . Qee::getAppInf('actionAccessor') . '=' . rawurlencode($actionName);
        $parajoin = '&';
    }

    if (is_array($params) && !empty($params)) {
        $url .= $parajoin . encode_url_args($params, $mode);
    }
    if ($anchor) { $url .= '#' . $anchor; }

    return $url;
}

/**
 * ��õ�ǰ����� URL ��ַ
 *
 * ��л tsingson �ṩ�ú������������� QeePHP ԭ�� url() ����������Ӧ CGI ģʽ�����⡣
 *
 * @param boolean $queryMode �Ƿ� URL ��ѯ���������ڷ��ؽ����
 *
 * @return string
 */
function detect_uri_base($queryMode = false)
{
    $aURL = array();

    // Try to get the request URL
    if (!empty($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = str_replace(array('"',"'",'<','>'), array('%22','%27','%3C','%3E'), $_SERVER['REQUEST_URI']);
        $p = strpos($_SERVER['REQUEST_URI'], ':');
        if ($p > 0 && substr($_SERVER['REQUEST_URI'], $p + 1, 2) != '//') {
            $aURL = array('path' => $_SERVER['REQUEST_URI']);
        } else {
            $aURL = parse_url($_SERVER['REQUEST_URI']);
        }
        if (isset($aURL['path']) && isset($_SERVER['PATH_INFO'])) {
            $aURL['path'] = substr($aURL['path'], 0, - strlen($_SERVER['PATH_INFO']));
        }
    }

    // Fill in the empty values
    if (empty($aURL['scheme'])) {
        if (!empty($_SERVER['HTTP_SCHEME'])) {
            $aURL['scheme'] = $_SERVER['HTTP_SCHEME'];
        } else {
            $aURL['scheme'] = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
        }
    }

    if (empty($aURL['host'])) {
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $p = strpos($_SERVER['HTTP_X_FORWARDED_HOST'], ':');
            if ($p > 0) {
                $aURL['host'] = substr($_SERVER['HTTP_X_FORWARDED_HOST'], 0, $p);
                $aURL['port'] = substr($_SERVER['HTTP_X_FORWARDED_HOST'], $p + 1);
            } else {
                $aURL['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
            }
        } else if (!empty($_SERVER['HTTP_HOST'])) {
            $p = strpos($_SERVER['HTTP_HOST'], ':');
            if ($p > 0) {
                $aURL['host'] = substr($_SERVER['HTTP_HOST'], 0, $p);
                $aURL['port'] = substr($_SERVER['HTTP_HOST'], $p + 1);
            } else {
                $aURL['host'] = $_SERVER['HTTP_HOST'];
            }
        } else if (!empty($_SERVER['SERVER_NAME'])) {
            $aURL['host'] = $_SERVER['SERVER_NAME'];
        }
    }

    if (empty($aURL['port']) && !empty($_SERVER['SERVER_PORT'])) {
        $aURL['port'] = $_SERVER['SERVER_PORT'];
    }

    if (empty($aURL['path'])) {
        if (!empty($_SERVER['PATH_INFO'])) {
            $sPath = parse_url($_SERVER['PATH_INFO']);
        } else {
            $sPath = parse_url($_SERVER['PHP_SELF']);
        }
        $aURL['path'] = str_replace(array('"',"'",'<','>'), array('%22','%27','%3C','%3E'), $sPath['path']);
        unset($sPath);
    }

    // Build the URL: Start with scheme, user and pass
    $sURL = $aURL['scheme'].'://';
    if (!empty($aURL['user'])) {
        $sURL .= $aURL['user'];
        if (!empty($aURL['pass'])) {
            $sURL .= ':'.$aURL['pass'];
        }
        $sURL .= '@';
    }

    // Add the host
    $sURL .= $aURL['host'];

    // Add the port if needed
    if (!empty($aURL['port']) && (($aURL['scheme'] == 'http' && $aURL['port'] != 80) || ($aURL['scheme'] == 'https' && $aURL['port'] != 443))) {
        $sURL .= ':'.$aURL['port'];
    }

    $sURL .= $aURL['path'];

    // Add the path and the query string
    if ($queryMode && isset($aURL['query'])) {
        $sURL .= $aURL['query'];
    }

    unset($aURL);
    return $sURL;
}

/**
 * ������ת��Ϊ��ͨ�� url ���ݵ��ַ�������
 *
 * �÷���
 * <code>
 * $string = encode_url_args(array('username' => 'dualface', 'mode' => 'md5'));
 * // $string ����Ϊ username=dualface&mode=md5
 * </code>
 *
 * @param array $args
 * @param enum $urlMode
 * @param string $parameterPairStyle
 *
 * @return string
 */
function encode_url_args($args, $urlMode = URL_STANDARD, $parameterPairStyle = null)
{
    $str = '';
    switch ($urlMode) {
    case URL_STANDARD:
        if (is_null($parameterPairStyle)) {
            $parameterPairStyle = '=';
        }
        $sc = '&';
        break;
    case URL_PATHINFO:
    case URL_REWRITE:
        if (is_null($parameterPairStyle)) {
            $parameterPairStyle = Qee::getAppInf('urlParameterPairStyle');
        }
        $sc = '/';
        break;
    }

    foreach ($args as $key => $value) {
        if (is_array($value)) {
            $append = encode_url_args($value, $urlMode);
        } else {
            $append = rawurlencode($key) . $parameterPairStyle . rawurlencode($value);
        }
        if (substr($str, -1) != $sc) {
            $str .= $sc;
        }
        $str .= $append;
    }
    return substr($str, 1);
}

/**
 * ת�� HTML �����ַ�����ͬ�� htmlspecialchars()
 *
 * @param string $text
 *
 * @return string
 */
function h($text)
{
    return htmlspecialchars($text);
}

/**
 * ת�� HTML �����ַ��Լ��ո�ͻ��з�
 *
 * �ո��滻Ϊ &nbsp; �����з��滻Ϊ <br />��
 *
 * @param string $text
 *
 * @return string
 */
function t($text)
{
    return nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($text)));
}

/**
 * ͨ�� JavaScript �ű���ʾ��ʾ�Ի��򣬲��رմ��ڻ����ض��������
 *
 * �÷���
 * <code>
 * js_alert('Dialog message', '', $url);
 * // ����
 * js_alert('Dialog message', 'window.close();');
 * </code>
 *
 * @param string $message Ҫ��ʾ����Ϣ
 * @param string $after_action ��ʾ��Ϣ��Ҫִ�еĶ���
 * @param string $url �ض���λ��
 */
function js_alert($message = '', $after_action = '', $url = '')
{
    $out = "<script language=\"javascript\" type=\"text/javascript\">\n";
    if (!empty($message)) {
        $out .= "alert(\"";
        $out .= str_replace("\\\\n", "\\n", t2js(addslashes($message)));
        $out .= "\");\n";
    }
    if (!empty($after_action)) {
        $out .= $after_action . "\n";
    }
    if (!empty($url)) {
        $out .= "document.location.href=\"";
        $out .= $url;
        $out .= "\";\n";
    }
    $out .= "</script>";
    echo $out;
    exit;
}

/**
 * �������ַ���ת��Ϊ JavaScript �ַ�������������β��"��
 *
 * @param string $content
 *
 * @return string
 */
function t2js($content)
{
    return str_replace(array("\r", "\n"), array('', '\n'), addslashes($content));
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
function __QEE_EXCEPTION_HANDLER(Exception $ex)
{
   // if (!Qee::getAppInf('displayErrors')) { exit; }
    if (Qee::getAppInf('friendlyErrorsMessage')) {
        $language = Qee::getAppInf('defaultLanguage');
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

/**
 * ������������ݣ�ͨ�����ڵ���
 *
 * @package Core
 *
 * @param mixed $vars Ҫ����ı���
 * @param string $label
 * @param boolean $return
 */
function dump($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label != '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) { return $content; }
    echo $content;
    return null;
}

/**
 * ��ʾӦ�ó���ִ��·����ͨ�����ڵ���
 *
 * @package Core
 *
 * @return string
 */
function dump_trace()
{
    $debug = debug_backtrace();
    $lines = '';
    $index = 0;
    for ($i = 0; $i < count($debug); $i++) {
        $file = $debug[$i];
        if ($file['file'] == '') { continue; }
        $line = "#{$index} {$file['file']}({$file['line']}): ";
        if (isset($file['class'])) {
            $line .= "{$file['class']}{$file['type']}";
        }
        $line .= "{$file['function']}(";
        if (isset($file['args']) && count($file['args'])) {
            foreach ($file['args'] as $arg) {
                $line .= gettype($arg) . ', ';
            }
            $line = substr($line, 0, -2);
        }
        $line .= ')';
        $lines .= $line . "\n";
        $index++;
    } // for
    $lines .= "#{$index} main\n";

    if (ini_get('html_errors')) {
        echo nl2br(str_replace(' ', '&nbsp;', $lines));
    } else {
        echo $lines;
    }
}