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
 * 定义 Qee_View_Smarty 类
 *
 * @copyright Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
 * @author 起源科技(www.qeeyuan.com)
 * @package Core
 * @version $Id$
 */

// {{{ includes

do {
    if (class_exists('Smarty', false)) { break; }

    $viewConfig = Qee::getAppInf('viewConfig');
    if (!isset($viewConfig['smartyDir']) && !defined('SMARTY_DIR')) {
        //require_once 'Qee/View/Exception/NotConfiguration.php';
        throw new Qee_View_Exception_NotConfiguration('Smarty');
    }

    $filename = $viewConfig['smartyDir'] . '/Smarty.class.php';
    if (!file_exists($filename)) {
        //require_once 'Qee/View/Exception/InitEngineFailed.php';
        throw new Qee_View_Exception_InitEngineFailed('Smarty');
    }

    include $filename;
} while (false);

// }}}

/**
 * Qee_View_Smarty 提供了对 Smarty 模板引擎的支持
 *
 * @package Core
 * @author 起源科技(www.qeeyuan.com)
 * @version 1.0
 */
class Qee_View_Smarty extends Smarty
{
    /**
     * 构造函数
     *
     * @return Qee_View_Smarty
     */
    public function __construct()
    {
        parent::Smarty();

        $viewConfig = Qee::getAppInf('viewConfig');
        if (is_array($viewConfig)) {
            foreach ($viewConfig as $key => $value) {
                if (isset($this->{$key})) {
                    $this->{$key} = $value;
                }
            }
        }

        //require_once 'Qee/View/martyHelper.php';
        new Qee_View_SmartyHelper($this);
    }
}
