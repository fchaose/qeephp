<?php
/////////////////////////////////////////////////////////////////////////////
// QeePHP Framework
//
// Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
//
// 许可协议，请查看源代码中附带的 LICENSE.TXT 文件，
// 或者访问 http://www.qeephp.org/ 获得详细信息。
/////////////////////////////////////////////////////////////////////////////

/**
 * 定义 QFilter_View_Macros 类
 *
 * @package filter
 * @version $Id$
 */

/**
 * QFilter_View_Macros 将视图中的宏替换为运行时值
 *
 * @package filter
 */
class QFilter_View_Macros implements QFilter_Interface
{
    /**
     * 要搜索的宏
     *
     * @var array
     */
    static $search = array(
        '<macro: public_root />',
    );

    /**
     * 对特定内容应用过滤器
     *
     * @param string $content
     *
     * @return string
     */
    function apply($content)
    {
        // return str_replace(self::$search, $replace, $content);
    }
}
