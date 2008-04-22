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
 * 定义 QDB_Cond 类
 *
 * @package database
 * @version $Id$
 */

/**
 * QDB_Cond 类封装复杂的查询条件
 *
 * @package database
 */
class QDB_Cond
{
    /**
     * 构成查询条件的各个部分
     *
     * @var array
     */
    protected $_parts = array();

    const BEGIN_GROUP = '(';
    const END_GROUP = ')';

    /**
     * 构造函数
     */
    function __construct()
    {
        $args = func_get_args();
        if (!empty($args)) {
            $this->_parts[] = array($args, true);
        }
    }

    /**
     * 直接创建一个 QDB_Cond 对象
     *
     * @param string|array|QDB_Expr|QDB_Cond $cond
     * @param array $cond_args
     *
     * @return QDB_Cond
     */
    static function createCronDirect($cond, array $cond_args = null)
    {
        $c = new QDB_Cond();
        array_unshift($cond_args, $cond);
        return $c->appendDirect($cond_args);
    }

    /**
     * 直接添加一个查询条件
     *
     * @param array $args
     * @param bool $bool
     *
     * @return QDB_Cond
     */
    function appendDirect(array $args, $bool = true)
    {
        $this->_parts[] = array($args, $bool);
        return $this;
    }

    /**
     * 添加一个新条件，与其他条件之间使用 AND 布尔运算符连接
     *
     * @return QDB_Cond
     */
    function andCond()
    {
        $this->_parts[] = array(func_get_args(), true);
        return $this;
    }

    /**
     * 添加一个新条件，与其他条件之间使用 OR 布尔运算符连接
     *
     * @return QDB_Cond
     */
    function orCond()
    {
        $this->_parts[] = array(func_get_args(), false);
        return $this;
    }

    /**
     * 开始一个条件组，AND
     *
     * @return QDB_Cond
     */
    function andGroup()
    {
        $this->_parts[] = array(self::BEGIN_GROUP, true);
        $this->_parts[] = array(func_get_args(), true);
        return $this;
    }

    /**
     * 开始一个条件组，OR
     *
     * @return QDB_Cond
     */
    function orGroup()
    {
        $this->_parts[] = array(self::BEGIN_GROUP, false);
        $this->_parts[] = array(func_get_args(), false);
        return $this;
    }

    /**
     * 结束一个条件组
     *
     * @return QDB_Cond
     */
    function endGroup()
    {
        $this->_parts[] = array(self::END_GROUP, null);
        return $this;
    }

    /**
     * 格式化为字符串
     *
     * @param QDB_Adapter_Abstract $conn
     * @param string $table_name
     */
    function formatToString($conn, $table_name = null)
    {
        $sql = '';

        $skip = true;
        $bool = true;
        foreach ($this->_parts as $part) {
            list($args, $_bool) = $part;
            if (empty($args)) { continue; }

            if (!is_null($_bool)) {
                $bool = $_bool;
            }

            if (!is_array($args)) {
                if ($args == self::BEGIN_GROUP) {
                    if (!$skip) {
                        $sql .= ($bool) ? ' AND ' : ' OR ';
                    }
                    $sql .= self::BEGIN_GROUP;
                    $skip = true;
                } else {
                    $sql .= self::END_GROUP;
                }
                continue;
            } else {
                if ($skip) {
                    $skip = false;
                } else {
                    $sql .= ($bool) ? ' AND ' : ' OR ';
                }
            }

            $cond = reset($args);
            array_shift($args);
            if ($cond instanceof QDB_Cond || $cond instanceof QDB_Expr) {
                $part = $cond->formatToString($conn, $table_name);
            } elseif (is_array($cond)) {
                $part = array();
                foreach ($cond as $field => $value) {
                    $part[] = $conn->qfield($field, $table_name) . '=' . $conn->qstr($value);
                }
                $part = implode(' AND ', $part);
            } else {
                $style = (strpos($cond, '?') === false) ? QDB::PARAM_CL_NAMED : QDB::PARAM_QM;
                $part = $conn->qinto($conn->qfieldsInto($cond, $table_name), $args, $style);
            }

            $sql .= $part;
        }

        return '(' . $sql . ')';
    }
}