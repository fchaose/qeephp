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
 * 定义 QDB_Expr 类
 *
 * @package database
 * @version $Id: expr.php 955 2008-03-16 23:52:44Z dualface $
 */

/**
 * QDB_Expr 封装一个表达式
 *
 * @package database
 */
class QDB_Expr
{
    /**
     * 封装的表达式
     *
     * @var string
     */
    protected $expr;

    /**
     * 构造函数
     *
     * @param string $expr
     */
    function __construct($expr)
    {
        $this->expr = $expr;
    }

    /**
     * 返回表达式的字符串表示
     *
     * @return string
     */
    function toString()
    {
        return $this->expr;
    }
}