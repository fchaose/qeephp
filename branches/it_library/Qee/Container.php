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
 * ���� Qee_Container ��
 *
 * @copyright Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
 * @author ������ dualface@gmail.com
 * @package Core
 * @version $Id$
 */

/**
 * Qee_Container ���ṩ��������롢����ע��ȷ���
 *
 * @package Core
 * @author ������ dualface@gmail.com
 * @version 1.0
 */
class Qee_Container
{
    /**
     * ����ע���
     *
     * @var array
     */
    protected static $OBJECTS = array();

    /**
     * ������·��
     *
     * @var array
     */
    protected static $CLASS_PATH = array();


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
        if (preg_match('/[^a-z0-9\-_.]/i', $filename)) {
            throw new Qee_Exception(Qee_Exception::t('Security check: Illegal character in filename: %s.', $filename));
        }

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
        if (!array_search($dir, self::$CLASS_PATH[$dir])) {
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
}
