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
 * 定义 QDB_Select 类
 *
 * 部分代码来自 Zend Framework 的 QDB_Select，特此感谢。
 *
 * @package database
 * @version $Id$
 */

/**
 * QDB_Select 类实现了一个数据库查询接口，提供了进行复杂查询的能力
 *
 * 开发者可以通过下述几个途径构造一个 QDB_Select 对象（查询对象）：
 *
 * 1、从数据库连接的 select() 获得一个查询对象；
 * 2、通过某个表数据入口对象的 find() 方法发起一个查询来获得查询对象；
 * 3、从 ActiveRecord 继承类的 find() 方法发起查询来获得一个查询对象。
 *
 * QDB_Select 可以使用连贯方法操作，例如：
 *
 * <code>
 * $select->from('posts', 'title, body')
 *        ->order('created DESC')
 *        ->limitPage($page, $page_size)
 *        ->query();
 * </code>
 *
 * 查询对象最后通过 query() 方法来进行实际查询并返回查询结果（数组或对象）。
 *
 * @package database
 */
class QDB_Select
{
    /**
     * 定义查询使用的常量
     */
    const DISTINCT       = 'distinct';
    const COLUMNS        = 'columns';
    const FROM           = 'from';
    const UNION          = 'union';
    const WHERE          = 'where';
    const GROUP          = 'group';
    const HAVING         = 'having';
    const ORDER          = 'order';
    const LIMIT_COUNT    = 'limitcount';
    const LIMIT_OFFSET   = 'limitoffset';
    const FOR_UPDATE     = 'forupdate';
    const AGGREGATE      = 'aggregate';

    const USED_LINKS     = 'used_links';
    const NON_LAZY_QUERY = 'non_lazy_query';
    const AS_ARRAY       = 'as_array';
    const LINK_FOR_RECURSION = 'link_for_recursion';

    const INNER_JOIN     = 'inner join';
    const LEFT_JOIN      = 'left join';
    const RIGHT_JOIN     = 'right join';
    const FULL_JOIN      = 'full join';
    const CROSS_JOIN     = 'cross join';
    const NATURAL_JOIN   = 'natural join';
    const RECURSION      = 'recursion';

    const SQL_WILDCARD   = '*';
    const SQL_SELECT     = 'SELECT';
    const SQL_UNION      = 'UNION';
    const SQL_UNION_ALL  = 'UNION ALL';
    const SQL_FROM       = 'FROM';
    const SQL_WHERE      = 'WHERE';
    const SQL_DISTINCT   = 'DISTINCT';
    const SQL_GROUP_BY   = 'GROUP BY';
    const SQL_ORDER_BY   = 'ORDER BY';
    const SQL_HAVING     = 'HAVING';
    const SQL_FOR_UPDATE = 'FOR UPDATE';
    const SQL_AND        = 'AND';
    const SQL_AS         = 'AS';
    const SQL_OR         = 'OR';
    const SQL_ON         = 'ON';
    const SQL_ASC        = 'ASC';
    const SQL_DESC       = 'DESC';
    const SQL_COUNT      = 'COUNT';
    const SQL_MAX        = 'MAX';
    const SQL_MIN        = 'MIN';
    const SQL_AVG        = 'AVG';
    const SQL_SUM        = 'SUM';

    /**
     * 用于初始化一个查询的内容
     *
     * @var array
     */
    protected static $_parts_init = array(
        self::DISTINCT     => false,
        self::COLUMNS      => array(),
        self::AGGREGATE    => array(),
        self::UNION        => array(),
        self::FROM         => array(),
        self::WHERE        => null,
        self::GROUP        => array(),
        self::HAVING       => null,
        self::ORDER        => array(),
        self::LIMIT_COUNT  => 1,
        self::LIMIT_OFFSET => null,
        self::FOR_UPDATE   => false,
    );

    /**
     * 可用的集合类型
     *
     * @var array
     */
    protected static $_aggregate_types = array(
        self::SQL_COUNT    => self::SQL_COUNT,
        self::SQL_MAX      => self::SQL_MAX,
        self::SQL_MIN      => self::SQL_MIN,
        self::SQL_AVG      => self::SQL_AVG,
        self::SQL_SUM      => self::SQL_SUM,
    );

    /**
     * 可用的 JOIN 操作类型
     *
     * @var array
     */
    protected static $_join_types = array(
        self::INNER_JOIN   => self::INNER_JOIN,
        self::LEFT_JOIN    => self::LEFT_JOIN,
        self::RIGHT_JOIN   => self::RIGHT_JOIN,
        self::FULL_JOIN    => self::FULL_JOIN,
        self::CROSS_JOIN   => self::CROSS_JOIN,
        self::NATURAL_JOIN => self::NATURAL_JOIN,
    );

    /**
     * 可用的 UNICODE 类型
     *
     * @var array
     */
    protected static $_union_types = array(
        self::SQL_UNION     => self::SQL_UNION,
        self::SQL_UNION_ALL => self::SQL_UNION_ALL,
    );

    /**
     * 查询参数初始化
     *
     * @var array
     */
    protected static $_query_params_init = array(
        self::USED_LINKS        => array(),
        self::NON_LAZY_QUERY    => array(),
        self::AS_ARRAY          => true,
        self::RECURSION         => 1,
        self::LINK_FOR_RECURSION => null,
    );

    /**
     * 构造查询的各个部分
     *
     * @var array
     */
    protected $_parts = array();

    /**
     * 查询可以使用的关联
     *
     * @var array
     */
    protected $_links = array();

    /**
     * 查询参数（仅用于一次查询）
     *
     * @var array
     */
    protected $_query_params;

    /**
     * 指示查询上下文中当前的表名称或其别名
     *
     * @var string
     */
    protected $_current_table;

    /**
     * 字段名映射
     *
     * @var array
     */
    protected $_columns_mapping = array();

    /**
     * 当前查询所服务的 ActiveRecord 继承类的元信息对象
     *
     * @var QDB_ActiveRecord_Meta
     */
    protected $_meta;

    /**
     * 执行数据库查询的适配器
     *
     * @var QDB_Adapter_Abstract
     */
    protected $_conn;

    /**
     * 查询ID
     *
     * @var int
     */
    static private $_query_id = 0;

    /**
     * 构造函数
     *
     * @param QDB_Adapter_Abstract $conn
     */
    function __construct(QDB_Adapter_Abstract $conn)
    {
        self::$_query_id++;
        $this->_conn = $conn;
        $this->_parts = self::$_parts_init;
        $this->_query_params = self::$_query_params_init;
    }

    /**
     * 创建一个 SELECT DISTINCT 查询
     *
     * @param bool $flag 指示是否是一个 SELECT DISTINCT 查询（默认 true）
     *
     * @return QDB_Select
     */
    public function distinct($flag = true)
    {
        $this->_parts[self::DISTINCT] = (bool)$flag;
        return $this;
    }

    /**
     * 添加一个要查询的表及其要查询的字段
     *
     * $table 参数指定要从哪个数据表查询数据。该参数可以是字符串、名值对或者一个表数据入口对象。
     *
     * 如果 $table 是一个字符串，则假定为一个表名称，或者是“表名称 AS 别名”；
     * 如果 $table 是一个表数据入口对象，则表名称由表数据入口对象确定；
     * 如果 $table 是一个名值对，则键名假定为要在查询中使用的表别名，键值可以是字符串或表数据入口对象。
     *
     * 如果要指定数据表所属 schema，可以采用如下形式：
     *
     * <code>
     * $select->from('schema名.表名称');
     * // 或者
     * $select->from(array('表别名' => 'schema名.表名称'));
     * </code>
     *
     * 如果 $table 是一个表数据入口对象，则 schmea 由表数据入口对象确定。
     *
     *
     * $cols 参数指定了要查询该表的哪些字段，如果不指定则默认为 '*'（既查询所有字段）。
     * $cols 可以是一个以“,”分割的字段名字符串，也可以是一个数组，以及一个 QDB_Expr 对象。
     *
     * 例如：
     *
     * <code>
     * // SELECT `posts`.`title`, `posts`.`body` FROM `posts`
     * $select->from('posts', 'title, body');
     * </code>
     *
     * 还可以为字段名指定查询时使用的别名，例如：
     *
     * <code>
     * // 指定字段别名
     * // SELECT `posts`.`title` AS `t` FROM `posts`
     * $select->from('posts', array('t' => 'title'));
     * </code>
     *
     * $cols 是字符串或数组时，字段名会被自动转义。
     * 如果 $cols 是一个 QDB_Expr 对象，则表达式中的字段名需要使用“[]”来指定转义。
     *
     * <code>
     * // SELECT LEFT(`posts`.`title`, 5) FROM `posts`
     * $expr = new QDB_Expr('LEFT([title], 5)');
     * $select->form('posts', $expr);
     *
     * // SELECT LEFT(`p`.`title`, 5) FROM `posts` `p`
     * $expr = new QDB_Expr('LEFT([title], 5)');
     * $select->form(array('p' => 'posts'), $expr);
     * </code>
     *
     * from() 的更多用法：
     *
     * <code>
     * // 指定要查询的数据表及字段
     * $select->from('posts', 'title, body');
     *
     * // 为数据表指定别名
     * $select->from(array('别名' => '表名称'), '字段名, 字段名'));
     *
     * // 通过表数据入口指定表
     * $select->form($table_posts, '字段名, 字段名');
     *
     * // 通过表数据入口指定表和别名
     * $select->form(array('别名' => $table_posts), array('别名' => '字段名', '字段名'));
     * </code>
     *
     * 如果 $table 参数为空，则通过 $cols 参数指定的字段名前面不会添加数据表名称。
     *
     * @param  array|string|QDB_Table $table
     * @param  array|string|QDB_Expr $cols
     *
     * @return QDB_Select
     */
    function from($table, $cols = self::SQL_WILDCARD)
    {
        return $this->_join(self::INNER_JOIN, $table, $cols);
    }

    /**
     * 指定要查询的字段
     *
     * $cols 和 $table 参数的规则同 from() 方法。
     *
     * 如果没有指定 $table 参数，则假定这些字段属于第一个 FROM 操作添加的表。
     * 但是也可以用“表名称.字段名”的方式来指定字段所属表。
     *
     * 除此以外，还可以通过 $table 参数批量指定这些字段所属表。
     *
     * @param  array|string|QDB_Expr $cols
     * @param  array|string|QDB_Table $table
     *
     * @return QDB_Select
     */
    function columns($cols = self::SQL_WILDCARD, $table = null)
    {
        if (is_null($table)) {
            $table = $this->_current_table;
        }
        $this->_addCols($table, $cols);
        return $this;
    }

    /**
     * 添加一个 WHERE 查询条件，与其他 WHERE 条件之间以 AND 布尔运算符连接
     *
     * where() 方法的参数格式是可变的，具有下列几种形式：
     *
     * <code>
     * // 使用字符串做查询条件
     * $select->where('id = 1')
     *
     * // 使用 ? 作为参数占位符
     * $select->where('id = ?', $id)
     *
     * // 使用多个参数占位符
     * $select->where('id = ? AND level_ix > ?', $id, $level_ix)
     *
     * // 使用数组提供多个参数占位符的值
     * $select->where('id = ? AND level_ix > ?', array($id, $level_ix))
     *
     * // 使用命名参数
     * $select->where('id = :id AND level_ix > :level_ix', array('id' => $id, 'level_ix' => $level_ix))
     *
     * // 使用名值对
     * $select->where(array('id' => $id, 'level_ix' => $level_ix));
     * </code>
     *
     * 注意：在使用命名参数时，where() 的第二个参数必须是一个名值对数组。其中键名是参数名。
     *
     * 在查询条件中，还可以使用“[]”来指定需要转义的字段名，例如：
     *
     * <code>
     * $select->where('[id] = 1');
     * $select->where('[posts.id'] = 1');
     * </code>
     *
     * 除了字符串和数组，$cond 参数还可以是 QDB_Expr 对象，例如：
     *
     * <code>
     * $expr = new QDB_Expr('[hits] < AVG([hits])');
     * $select->where($expr);
     * </code>
     *
     * 如果没有在字段名中指定表名称或者别名，则假定所有字段都是第一个通过 from() 指定的表。
     *
     * 更复杂的查询条件，可以使用 QDB_Cond 对象来构造。
     *
     * @param string|array|QDB_Expr|QDB_Cond $cond 查询条件
     *
     * @return QDB_Select
     */
    function where($cond)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->_addConditions($cond, $args, self::WHERE, self::SQL_AND);
    }

    /**
     * 添加一个 WHERE 查询条件，与其他 WHERE 条件之间以 OR 布尔运算符连接
     *
     *  参数规范参考 where() 方法。
     *
     * @param mixed $cond
     *
     * @return QDB_Select
     */
    function orWhere($cond)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->_addConditions($cond, $args, self::WHERE, self::SQL_OR);
    }

    /**
     * 添加关联
     *
     * 关联会在指定查询条件和进行递归查询时起作用。
     *
     * @param QDB_Table_Link_Abstract|array $link
     *
     * @return QDB_Select
     */
    function link($link)
    {
        if (!is_array($link)) {
            $links = array($link);
        } else {
            $links = $link;
        }

        foreach ($links as $link) {
            if ($link instanceof QDB_Table_Link_Abstract) {
                $this->_links[$link->mapping_name] = $link;
            } else {
                // LC_MSG: 关联必须是 QDB_Table_Link_Abstract 类型.
                throw new QDB_Select_Exception(__('关联必须是 QDB_Table_Link_Abstract 类型.'));
            }
        }

        return $this;
    }

    /**
     * 添加一个 JOIN 数据表和字段到查询中
     *
     * $table 和 $cols 参数的规则同 from()，但 $table 参数还可以是一个 QDB_Table_Link_Abstract 对象。
     *
     * 当 $table 参数是一个 QDB_Table_Link_Abstract 对象，则表示要将该关联对应的数据表添加到查询中，构造出一个关联查询。
     *
     * $cond 参数的规则同 where()。
     *
     * @param  array|string|QDB_Table|QDB_Table_Link_Abstract $name
     * @param  array|string|QDB_Expr $cols
     * @param  array|string|QDB_Expr|QDB_Cond $cond
     *
     * @return QDB_Select
     */
    function join($table, $cols = self::SQL_WILDCARD, $cond)
    {
        $args = func_get_args();
        return $this->_join(self::INNER_JOIN, $table, $cols, $cond, array_slice($args, 3));
    }

    /**
     * 添加一个 INNER JOIN 数据表和字段到查询中
     *
     * $table 和 $cols 参数的规则同 from()，$cond 参数的规则同 where()。
     *
     * @param  array|string|QDB_Table $name
     * @param  array|string|QDB_Expr $cols
     * @param  array|string|QDB_Expr|QDB_Cond $cond
     *
     * @return QDB_Select
     */
    function joinInner($table, $cols = self::SQL_WILDCARD, $cond)
    {
        $args = func_get_args();
        return $this->_join(self::INNER_JOIN, $table, $cols, $cond, array_slice($args, 3));
    }

    /**
     * 添加一个 LEFT JOIN 数据表和字段到查询中
     *
     * $table 和 $cols 参数的规则同 from()，$cond 参数的规则同 where()。
     *
     * @param  array|string|QDB_Table $table
     * @param  array|string|QDB_Expr $cols
     * @param  array|string|QDB_Expr|QDB_Cond $cond
     *
     * @return QDB_Select
     */
    function joinLeft($table, $cols = self::SQL_WILDCARD, $cond)
    {
        $args = func_get_args();
        return $this->_join(self::LEFT_JOIN, $table, $cols, $cond, array_slice($args, 3));
    }

    /**
     * 添加一个 RIGHT JOIN 数据表和字段到查询中
     *
     * $table 和 $cols 参数的规则同 from()，$cond 参数的规则同 where()。
     *
     * @param  array|string|QDB_Table $table
     * @param  array|string|QDB_Expr $cols
     * @param  array|string|QDB_Expr|QDB_Cond $cond
     *
     * @return QDB_Select
     */
    function joinRight($table, $cols = self::SQL_WILDCARD, $cond)
    {
        $args = func_get_args();
        return $this->_join(self::RIGHT_JOIN, $table, $cols, $cond, array_slice($args, 3));
    }

    /**
     * 添加一个 FULL OUTER JOIN 数据表和字段到查询中
     *
     * $table 和 $cols 参数的规则同 from()，$cond 参数的规则同 where()。
     *
     * @param  array|string|QDB_Table $table
     * @param  array|string|QDB_Expr $cols
     * @param  array|string|QDB_Expr|QDB_Cond $cond
     *
     * @return QDB_Select
     */
    function joinFull($table, $cols = self::SQL_WILDCARD, $cond)
    {
        $args = func_get_args();
        return $this->_join(self::FULL_JOIN, $table, $cols, $cond, array_slice($args, 3));
    }

    /**
     * 添加一个 CROSS JOIN 数据表和字段到查询中
     *
     * $table 和 $cols 参数的规则同 from()。
     *
     * @param  array|string|QDB_Table $table
     * @param  array|string|QDB_Expr $cols
     *
     * @return QDB_Select
     */
    function joinCross($table, $cols = self::SQL_WILDCARD)
    {
        return $this->_join(self::CROSS_JOIN, $table, $cols);
    }

    /**
     * 添加一个 NATURAL JOIN 数据表和字段到查询中
     *
     * @param  array|string|QDB_Table $table
     * @param  array|string|QDB_Expr $cols
     *
     * @return QDB_Select
     */
    function joinNatural($table, $cols = self::SQL_WILDCARD)
    {
        return $this->_join(self::NATURAL_JOIN, $table, $cols);
    }

    /**
     * 添加一个 UNION 查询
     *
     * $select 可以是一个字符串或一个 QDB_Select 对象，或者包含上述两者的数组。
     *
     * @param  array|string|QDB_Select $select
     *
     * @return QDB_Select
     */
    function union($select = array(), $type = self::SQL_UNION)
    {
        if (!is_array($select)) {
            $select = array($select);
        }

        if (!isset(self::$_union_types[$type])) {
            // LC_MSG: 无效的 UNION 类型 "%s".
            throw new QDB_Select_Exception(__('无效的 UNION 类型 "%s".', $type));
        }

        foreach ($select as $target) {
            $this->_parts[self::UNION][] = array($target, $type);
        }

        return $this;
    }

    /**
     * 指定 GROUP BY 子句
     *
     * $expr 可以是一个字符串或一个 QDB_Expr 对象，或者包含上述两者的数组。
     *
     * 如果需要在表达式中使用转义后的字段名，可以采用如下模式：
     *
     * <code>
     * $select->group('SUM([hits])');
     * </code>
     *
     * 所有被 [ 和 ] 包括的字段名将自动进行转义。如果有需要，还可以进一步指定字段所属的表或表别名。
     *
     * <code>
     * $select->group('SUM([mytable.hits])');
     * </code>
     *
     * @param string|QDB_Expr|array $expr
     *
     * @return QDB_Select
     */
    function group($expr)
    {
        if (!is_array($expr)) {
            $expr = array($expr);
        }

        foreach ($expr as $val) {
            if ($val instanceof QDB_Expr) {
                /* @var $val QDB_Expr */
                $val = $val->formatToString($this->_conn, $this->_current_table, $this->_columns_mapping);
            } else {
                $val = $this->_conn->qfieldsInto($val, $this->_current_table, $this->_columns_mapping);
            }
            $this->_parts[self::GROUP][] = $val;
        }

        return $this;
    }

    /**
     * 添加一个 HAVING 条件，与其他 HAVING 条件之间以 AND 布尔运算符连接
     *
     * 参数规范参考 where() 方法。
     *
     * @param string|array|QDB_Expr|QDB_Cond $cond 查询条件
     *
     * @return QDB_Select
     */
    function having($cond)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->_addConditions($cond, $args, self::HAVING, self::SQL_AND);
    }

    /**
     * 添加一个 HAVING 条件，与其他 HAVING 条件之间以 OR 布尔运算符连接
     *
     * 参数规范参考 where() 方法。
     *
     * @param string|array|QDB_Expr|QDB_Cond $cond 查询条件
     *
     * @return QDB_Select
     */
    function orHaving($cond)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->_addConditions($cond, $args, self::HAVING, self::SQL_OR);
    }

    /**
     * 添加排序
     *
     * $expr 可以是字符串或者 QDB_Expr 对象，例如：
     *
     * <code>
     * $select->order('title');
     * $select->order('users.username DESC');
     * $select->order(new QDB_Expr('SUM(hits) ASC');
     * </code>
     *
     * @param string $expr
     *
     * @return QDB_Select
     */
    function order($expr)
    {
        if (!is_array($expr)) {
            $expr = array($expr);
        }

        $m = null;
        foreach ($expr as $val) {
            if ($val instanceof QDB_Expr) {
                /* @var $val QDB_Expr */
                $val = $val->formatToString($this->_conn, $this->_current_table, $this->_columns_mapping);
                if (preg_match('/(.*\W)(' . self::SQL_ASC . '|' . self::SQL_DESC . ')\b/si', $val, $m)) {
                    $val = trim($m[1]);
                    $dir = $m[2];
                } else {
                    $dir = self::SQL_ASC;
                }
                $this->_parts[self::ORDER][] = $val . ' ' . $dir;
            } else {
                $_cols = explode(',', $val);
                foreach ($_cols as $val) {
                    $val = trim($val);
                    if (empty($val)) { continue; }
                    $current_table_name = $this->_current_table;
                    $dir = self::SQL_ASC;
                    $m = null;
                    if (preg_match('/(.*\W)(' . self::SQL_ASC . '|' . self::SQL_DESC . ')\b/si', $val, $m)) {
                        $val = trim($m[1]);
                        $dir = $m[2];
                    }
                    if (!preg_match('/\(.*\)/', $val)) {
                        if (preg_match('/(.+)\.(.+)/', $val, $m)) {
                            $current_table_name = $m[1];
                            $val = $m[2];
                        }
                        if (isset($this->_columns_mapping[$val])) {
                            $val = $this->_columns_mapping[$val];
                        }
                        $val = $this->_conn->qfield($val, $current_table_name);
                    }
                    $this->_parts[self::ORDER][] = $val . ' ' . $dir;
                }
            }
        }

        return $this;
    }

    /**
     * 指示查询所有符合条件的记录
     *
     * @return QDB_Select
     */
    function all()
    {
        $this->_parts[self::LIMIT_COUNT] = null;
        $this->_parts[self::LIMIT_OFFSET] = null;
        return $this;
    }

    /**
     * 限制查询结果总数
     *
     * @param int $count 只查询多少条数据
     * @param int $offset 从结果集的哪个位置开始查询（0 为第一条）
     *
     * @return QDB_Select
     */
    function limit($count, $offset = 0)
    {
        $this->_parts[self::LIMIT_COUNT] = abs(intval($count));
        $this->_parts[self::LIMIT_OFFSET] = abs(intval($offset));
        return $this;
    }

    /**
     * 设置分页查询
     *
     * limitPage() 是用于分页查询的主要方法。
     * 使用时通常只需要指定 $page 和 $page_size 参数。
     *
     * $page 参数指定要查询哪一页的数据，$page_size 指定了页大小。
     * 默认情况下，$page 为 1 时表示要查询第 1 页。
     *
     * 如果希望用 $page = 0 来表示查询第一页，应该指定 $base 参数为 0。
     *
     * @param int $page 要查询的页码
     * @param int $page_size 页的大小
     * @param int $base 页码基数
     *
     * @return QDB_Select
     */
    function limitPage($page, $page_size = 20, $base = 1)
    {
        $page = abs(intval($page));
        $page_size = abs(intval($page_size));
        $base = abs(intval($base));

        if ($page < $base) { $page = $base; }
        $this->page_base = $base;
        $this->page_size = $page_size;
        $this->page = $page;
        $this->pager = null;
        $this->page_query = true;

        return $this->limit($page_size, ($page - $base) * $page_size);
    }

    /**
     * 获得分页信息
     *
     * 要使用该方法，必须先用 limitPage() 指定有效的分页参数。
     *
     * 该方法返回一个数组，包含下列信息：
     *
     * record_count: 符合查询条件的记录数
     * page_count: 按照页大小计算出来的总页数
     * first: 第一页的索引，等同于 limitPage() 的 $base 参数，默认为 1
     * last: 最后一页的索引
     * current: 当前页的索引
     * next: 下一页的索引
     * prev: 上一页的索引
     * page_size: 页大小
     * page_base: 页码基数（也就是第一页的索引值，默认为 1）
     *
     * 获得这个数组后，就可以通过 WebControls 或者其他途径构造分页导航条等用户界面内容。
     *
     * @return array
     */
    function getPager()
    {
        if (is_null($this->pager)) {
            $this->_preparePagedQuery();
        }
        return $this->pager;
    }

    /**
     * 是否构造一个 FOR UPDATE 查询
     *
     * 如果查询出记录后马上就要更新并写回数据库，则可以调用 forUpdate() 方法来指示这种情况。
     * 此时数据库会尝试对查询出来的记录加锁，避免在数据更新回数据库之前被其他查询改变。
     *
     * @param boolean $flag
     *
     * @return QDB_Select
     */
    function forUpdate($flag = true)
    {
        $this->for_update = (bool)$flag;
        return $this;
    }

    /**
     * 统计符合条件的记录数
     *
     * $field 参数指定用于统计的字段或表达式。
     *
     * @param string|QDB_Expr $field
     * @param string $alias
     *
     * @return QDB_Select
     */
    function count($field, $alias = 'row_count')
    {
        return $this->_addAggregate(self::SQL_COUNT, $field, $alias);
    }

    /**
     * 统计平均值
     *
     * @param string|QDB_Expr $field
     * @param string $alias
     *
     * @return QDB_Select
     */
    function avg($field, $alias = 'avg_value')
    {
        return $this->_addAggregate(self::SQL_AVG, $field, $alias);
    }

    /**
     * 统计最大值
     *
     * @param string|QDB_Expr $field
     * @param string $alias
     *
     * @return QDB_Select
     */
    function max($field, $alias = 'max_value')
    {
        return $this->_addAggregate(self::SQL_MAX, $field, $alias);
    }

    /**
     * 统计最小值
     *
     * @param string|QDB_Expr $field
     * @param string $alias
     *
     * @return QDB_Select
     */
    function min($field, $alias = 'min_value')
    {
        return $this->_addAggregate(self::SQL_MIN, $field, $alias);
    }

    /**
     * 统计合计
     *
     * @param string|QDB_Expr $field
     * @param string $alias
     *
     * @return QDB_Select
     */
    function sum($field, $alias = 'sum_value')
    {
        return $this->_addAggregate(self::SQL_MAX, $field, $alias);
    }

    /**
     * 指示将查询结果封装为特定的 ActiveRecord 对象
     *
     * 通常对于从 ActiveRecord 发起的查询不需要再调用该方法，QeePHP 会确保此类查询都返回对象。
     * 但如果是从表数据入口发起的查询，并且希望返回对象，就应该调用这个方法指定一个类名称。
     *
     * 类名称所指定的 ActiveRecord 继承类应该是一个适合返回结果数据结构的对象，否则会导致构造对象失败。
     *
     * @param string $class_name
     *
     * @return QDB_Select
     */
    function asObject($class_name)
    {
        $this->_meta = QDB_ActiveRecord_Meta::getInstance($class_name);
        $this->_query_params[self::AS_ARRAY] = false;
        return $this;
    }

    /**
     * 指示将查询结果返回为数组
     *
     * 指示不管查询是由什么来源发起的，都将查询结果以数组方式返回。
     *
     * @return QDB_Select
     */
    function asArray()
    {
        $this->_meta = null;
        $this->_query_params[self::AS_ARRAY] = true;
        return $this;
    }

    /**
     * 设置一个或多个字段的映射名，如果 $mapping_to 为 NULL，则取消对指定字段的映射
     *
     * 映射名是指可以在查询参数中使用映射名作为字段名。
     * QDB_Select 会负责在生成查询时将映射名转换为实际的字段名。
     *
     * 例如：
     *
     * <code>
     * $select->columnMapping('title', 'post_title')
     *        ->where(array('post_title' => $title));
     * // 生成的查询条件是 `title` = {$title} 而不是 `post_title` = {$title}
     * </code>
     *
     * @param array|string $name
     * @param string $mapping_to
     *
     * @return QDB_Select
     */
    function columnMapping($name, $mapping_to = null)
    {
        if (is_array($name)) {
            $this->_columns_mapping = array_merge($this->_columns_mapping, $name);
        } else {
            if (empty($mapping_to)) {
                unset($this->_columns_mapping[$name]);
            } else {
                $this->_columns_mapping[$name] = $mapping_to;
            }
        }
        return $this;
    }

    /**
     * 设置递归关联查询的层数（默认为1层）
     *
     * 假设 A 关联到 B，B 关联到 C，而 C 关联到 D。则通过 recursion 参数，
     * 我们可以指定从 A 出发的查询要到达哪一个关联层次才停止。
     *
     * 默认的 $recursion = 1，表示从 A 出发的查询只查询到 B 的数据就停止。
     *
     * 注意：对于来自 ActiveRecord 的查询，无需指定该参数。
     * 因为可以利用 ActiveRecord 的延迟加载能力自动查询更深层次的数据。
     *
     * @param int $recursion
     *
     * @return QDB_Select
     */
    function recursion($recursion)
    {

        $this->_query_params[self::RECURSION] = abs(intval($recursion));
        return $this;
    }

    /**
     * 指定使用递归查询时，需要查询哪个关联的 target_key 字段
     *
     * @param QDB_Table_Link_Abstract $link
     *
     * @return QDB_Select
     */
    function linkForRecursion(QDB_Table_Link_Abstract $link)
    {
        $this->_query_params[self::LINK_FOR_RECURSION] = $link;
        return $this;
    }

    /**
     * 获得用于构造查询的指定部分内容
     *
     * @param string $part
     *
     * @return mixed
     */
    function getPart($part)
    {
        $part = strtolower($part);
        if (!array_key_exists($part, $this->_parts)) {
            // LC_MSG: 无效的部分名称 "%s".
            throw new QDB_Select_Exception(__('无效的部分名称 "%s".', $part));
        }
        return $this->_parts[$part];
    }

    /**
     * 重置整个查询对象或指定部分
     *
     * @param string $part
     *
     * @return QDB_Select
     */
    function reset($part = null)
    {
        if ($part == null) {
            $this->_parts = self::$_parts_init;
            $this->_query_params = self::$_query_params_init;
        } else if (array_key_exists($part, self::$_parts_init)) {
            $this->_parts[$part] = self::$_parts_init[$part];
        }
        return $this;
    }

    /**
     * 执行查询
     *
     * $included_links 用于指定查询时要包含的关联。
     *
     * 默认情况下，QDB_Select 对象会以数组形式返回查询结果。
     * 在这种模式下，关联的数据会被立即查询出来，并嵌入查询结果中。
     *
     * 如果指定 QDB_Select 以 ActiveRecord 对象返回查询结果，则只有 $included_links 指定的关联会被立即查询。
     * 否则在第一次访问返回的 ActiveRecord 对象的聚合属性时，才会进行关联对象的查询。
     *
     * @param array|string $included_links
     *
     * @return mixed
     */
    function query($included_links = null)
    {
        $this->_query_params[self::NON_LAZY_QUERY] = Q::normalize($included_links);
        if ($this->_query_params[self::AS_ARRAY]) {
            return $this->_queryArray(true);
        } else {
            return $this->_queryObjects();
        }
    }

    /**
     * 执行查询，返回结果句柄
     *
     * @return QDB_Result_Abstract
     */
    function getQueryHandle()
    {
        // 构造查询 SQL，并取得查询中用到的关联
        $sql = $this->__toString();

        $offset = $this->_parts[self::LIMIT_OFFSET];
        $count = $this->_parts[self::LIMIT_COUNT];

        if (is_null($offset) && is_null($count)) {
            return $this->_conn->execute($sql);
        } else {
            return $this->_conn->selectLimit($sql, $count, $offset);
        }
    }

    /**
     * 获得查询字符串
     *
     * @return string
     */
    function __toString()
    {
        $sql = self::SQL_SELECT;
        foreach (array_keys(self::$_parts_init) as $part) {
            $method = '_render' . ucfirst($part);
            if (method_exists($this, $method)) {
                $sql = $this->$method($sql);
            }
        }

        return $sql;
    }


    /**
     * 查询，并返回数组结果
     *
     * @param boolean $clean_up
     *
     * @return array
     */
    protected function _queryArray($clean_up = true)
    {
        $handle = $this->getQueryHandle();
        /* @var $handle QDB_Result_Abstract */

        if ($this->_query_params[self::RECURSION] > 0 && !empty($this->_used_links)) {
            // 对关联表进行查询，并组装数据
            $refs_value = null;
            $refs = null;
            $used_alias = array_keys($this->_query_params[self::USED_LINKS]);
            $rowset = $handle->fetchAllRefby($used_alias, $refs_value, $refs, $clean_up);
            $keys = array_keys($rowset);

            // 进行关联查询，并组装数据集
            foreach ($this->_query_params[self::USED_LINKS] as $link) {
                /* @var $link QDB_Table_Link_Abstract */
                foreach ($keys as $key) {
                    $rowset[$key][$link->mapping_name] = $link->one_to_one ? null : array();
                }

                $tka = $link->target_key_alias;
                $ska = $link->source_key_alias;
                if (empty($refs_value[$ska])) { continue; }

                $select = $link->target_table->find("[{$link->target_key}] IN (?)", $refs_value[$ska])
                                             ->recursion($this->_query_params[self::RECURSION] - 1)
                                             ->linkForRecursion($link)
                                             ->order($link->on_find_order)
                                             ->select($link->on_find_keys)
                                             ->where($link->on_find_where);
                if ($link->type == QDB::MANY_TO_MANY) {
                    // SELECT {$fields} FROM {$this->joinTDG->qtableName} INNER JOIN {$this->assocTDG->qtableName} ON {$this->assocTDG->qpk} = {$this->qassocForeignKey}
                    $select->join($link->mid_table->table_name, "[{$link->mid_target_key}] = [{$link->target_key}]");
                }
                if (is_int($link->on_find) || is_array($link->on_find)) {
                    $select->limit($link->on_find);
                } else {
                    $select->all();
                }

                $target_rowset = $select->queryArray(false);
                if ($link->on_find === 1) {
                    $target_rowset = array($target_rowset);
                }

                // 组装数据集
                if ($link->one_to_one) {
                    foreach (array_keys($target_rowset) as $offset) {
                        $v = $target_rowset[$offset][$tka];
                        unset($target_rowset[$offset][$tka]);

                        $i = 0;
                        foreach ($refs[$ska][$v] as $row) {
                            $refs[$ska][$v][$i][$link->mapping_name] = $target_rowset[$offset];
                            unset($refs[$ska][$v][$i][$ska]);
                            $i++;
                        }
                    }
                } else {
                    foreach (array_keys($target_rowset) as $offset) {
                        $v = $target_rowset[$offset][$tka];
                        unset($target_rowset[$offset][$tka]);

                        $i = 0;
                        foreach ($refs[$ska][$v] as $row) {
                            $refs[$ska][$v][$i][$link->mapping_name][] = $target_rowset[$offset];
                            unset($refs[$ska][$v][$i][$ska]);
                        }
                    }
                }
            }

            unset($refs);
            unset($refs_value);
            unset($row);
            if ($this->limit == 1) {
                $row = reset($rowset);
            }
        } else {
            // 非关联查询
            unset($row);
            if ($this->_parts[self::LIMIT_COUNT] == 1) {
                $row = $handle->fetchRow();
            } else {
                $rowset = $handle->fetchAll();
            }
        }

        if (count($this->_parts[self::AGGREGATE])) {
            $row = reset($rowset);
        }

        if (isset($row)) {
            return $row;
        } else {
            return $rowset;
        }
    }

    /**
     * 查询，并返回对象或对象集合
     *
     * @param boolean $coll_as_array
     *
     * @return QColl|QDB_ActiveRecord_Abstract
     */
    protected function _queryObjects($coll_as_array = false)
    {
        /**
         * 执行查询，获得一个查询句柄
         *
         * $this->_query_params[self::USED_LINKS] 是查询涉及到的关联（关联别名 => 关联对象）
         */
        $handle = $this->getQueryHandle();
        /* @var $handle QDB_Result_Abstract */

        /**
         * $batch_refs_value 是一个二维数组
         *
         * 格式是：
         *
         *    link1 mapping_name => array(
         *      source_key_value => 对象ID
         *      ....
         *    ),
         *    link2 mapping_name => array(
         *    ),
         */
        $batch_refs_value = null;
        $class_name = $this->_meta->class_name;
        $rowset = array();
        $this->_query_params[self::USED_LINKS] = $this->_query_params[self::USED_LINKS];
        while (($row = $handle->fetchRow())) {
            $obj = new $class_name($row);
            $id = $obj->id();
            foreach ($this->_query_params[self::USED_LINKS] as $alias_name => $link) {
                $batch_refs_value[$link->mapping_name][$row[$alias_name]] = $id;
            }
            $rowset[] = $obj;
        }

        if (empty($rowset)) {
            // 没有查询到数据时，返回 Null 对象或空集合
            if ($this->_parts[self::LIMIT_COUNT] == 1) {
                return $this->_meta->newNullObject();
            } else {
                return new QColl($this->_meta->class_name);
            }
        }

        if ($this->_parts[self::LIMIT_COUNT] == 1) {
            // 创建一个单独的对象
            $this->_meta->register($rowset[0], $batch_refs_value, self::$_query_id);
            return $rowset[0];
        } else{
            foreach ($rowset as $obj) {
                $this->_meta->register($obj, $batch_refs_value, self::$_query_id);
            }

            if ($coll_as_array) {
                return $rowset;
            } else {
                return QColl::createFromArray($rowset, $class_name);
            }
        }
    }

    /**
     * 构造 DISTINCT 子句
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderDistinct($sql)
    {
        if ($this->_parts[self::DISTINCT]) {
            $sql .= ' ' . self::SQL_DISTINCT;
        }

        return $sql;
    }

    /**
     * 构造查询字段子句
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _renderColumns($sql)
    {
        if (empty($this->_parts[self::COLUMNS])) {
            return '*';
        }

        // $this->_parts[self::COLUMNS] 每个元素的格式
        $columns = array();
        foreach ($this->_parts[self::COLUMNS] as $entry) {
            // array($current_table_name, $col, $alias | null)
            list($table_name, $col, $alias) = $entry;
            // $col 是一个字段名或者一个 QDB_Expr 对象
            if ($col instanceof QDB_Expr) {
                /* @var $col QDB_Expr */
                $col = $col->formatToString($this->_conn, $table_name, $this->_columns_mapping);
            } else {
                if (isset($this->_columns_mapping[$col])) {
                    $col = $this->_columns_mapping[$col];
                }
                $col = $this->_conn->qfield($col, $table_name);
                if ($col != self::SQL_WILDCARD && $alias) {
                    $columns[]  = $col . ' AS ' . $this->_conn->qfield($alias);
                } else {
                    $columns[]  = $col;
                }
            }
        }

        // 确定要查询的关联，从而确保查询主表时能够得到相关的关联字段
        if ($this->_query_params[self::RECURSION] > 0) {
            foreach ($this->_links as $link) {
                /* @var $link QDB_Table_Link_Abstract */
                $link->init();
                if (!$link->enabled || $link->on_find === false || $link->on_find == 'skip') { continue; }
                $link->init();
                $columns[] = $link->source_table->qfields($link->source_key) .
                             ' AS ' .
                             $link->source_table->conn->qfield($link->source_key_alias);
                $this->_query_params[self::USED_LINKS][$link->source_key_alias] = $link;
            }
        }

        // 如果指定了来源关联，则需要查询组装数据所需的关联字段
        if ($this->_query_params[self::LINK_FOR_RECURSION]) {
            $link = $this->_query_params[self::LINK_FOR_RECURSION];
            $columns[] = $link->target_table->qfields($link->target_key) .
                         ' AS ' .
                         $link->target_table->conn->qfield($link->target_key_alias);
        }

        return $sql .= ' ' . implode(', ', $columns);
    }

    /**
     * 构造集合查询字段
     *
     * @param string
     *
     * @return string
     */
    protected function _renderAggregate($sql)
    {
        $columns = array();
        foreach ($this->_parts[self::AGGREGATE] as $aggregate) {
            list(, $field, $alias) = $aggregate;
            if ($alias) {
                $columns[] = $field . ' AS ' . $alias;
            } else {
                $columns[] = $field;
            }
        }

        if (empty($columns)) { return $sql; }
        return $sql .= ' ' . implode(', ', $columns);
    }

    /**
     * 构造 FROM 子句
     *
     * @param string
     *
     * @return string
     */
    protected function _renderFrom($sql)
    {
        $from = array();

        foreach ($this->_parts[self::FROM] as $alias => $table) {
            $tmp = '';

            // $this->_parts[self::FROM][$alias] = array(
            //     'join_type'      => $join_type,
            //     'schema'         => $schema,
            //     'table_name'     => $table_name,
            //     'join_cond'      => $where_sql, // 字符串
            // );

            // 如果不是第一个 FROM，则添加 JOIN
            if (!empty($from)) {
                $tmp .= ' ' . strtoupper($table['join_type']) . ' ';
            }

            if ($alias == $table['table_name']) {
                $tmp .= $this->_conn->qtable($table['table_name'], $table['schema']);
            } else {
                $tmp .= $this->_conn->qtable($table['table_name'], $table['schema'], $alias);
            }

            // 添加 JOIN 查询条件
            if (!empty($from) && !empty($table['join_cond'])) {
                $tmp .= "\n  " .self::SQL_ON . ' ' . $table['join_cond'];
            }

            $from[] = $tmp;
        }

        // Add the list of all joins
        if (!empty($from)) {
            $sql .= "\n" . self::SQL_FROM . ' ' . implode("\n", $from);
        }

        return $sql;
    }

    /**
     * 构造 UNION 查询
     *
     * @param string
     *
     * @return string
     */
    protected function _renderUnion($sql)
    {
        if ($this->_parts[self::UNION]) {
            $parts = count($this->_parts[self::UNION]);
            foreach ($this->_parts[self::UNION] as $cnt => $union) {
                list($target, $type) = $union;
                if ($target instanceof QDB_Select) {
                    $target = $target->__toString();
                }
                $sql .= $target;
                if ($cnt < $parts - 1) {
                    $sql .= ' ' . $type . ' ';
                }
            }
        }

        return $sql;
    }

    /**
     * 构造 WHERE 子句
     *
     * @param string
     *
     * @return string
     */
    protected function _renderWhere($sql)
    {
        if (!empty($this->_parts[self::FROM]) && !is_null($this->_parts[self::WHERE])) {
            $where = $this->_parts[self::WHERE]->formatToString($this->_conn);
            if (!empty($where)) {
                $sql .= "\n" . self::SQL_WHERE . ' ' . $where;
            }
        }

        return $sql;
    }

    /**
     * 构造 GROUP 子句
     *
     * @param string
     *
     * @return string
     */
    protected function _renderGroup($sql)
    {
        if (!empty($this->_parts[self::FROM]) && !empty($this->_parts[self::GROUP])) {
            $sql .= "\n " . self::SQL_GROUP_BY . ' ' . implode(",\n\t", $this->_parts[self::GROUP]);
        }
        return $sql;
    }

    /**
     * 构造 HAVING 子句
     *
     * @param string
     *
     * @return string
     */
    protected function _renderHaving($sql)
    {
        if (!empty($this->_parts[self::FROM]) && !empty($this->_parts[self::HAVING])) {
            $sql .= "\n " . self::SQL_HAVING . ' ' . implode(",\n\t", $this->_parts[self::HAVING]);
        }
        return $sql;
    }

    /**
     * 构造 ORDER 子句
     *
     * @param string
     *
     * @return string
     */
    protected function _renderOrder($sql)
    {
        if (!empty($this->_parts[self::ORDER])) {
            $sql .= "\n " . self::SQL_ORDER_BY . ' ' . implode(', ', $this->_parts[self::ORDER]);
        }
        return $sql;
    }

    /**
     * 构造 FOR UPDATE 子句
     *
     * @param string
     *
     * @return string
     */
    protected function _renderForUpdate($sql)
    {
        if ($this->_parts[self::FOR_UPDATE]) {
            $sql .= "\n " . self::SQL_FOR_UPDATE;
        }
        return $sql;
    }

    /**
     * 添加一个 JOIN
     *
     * @param int $join_type
     * @param array|string|QDB_Table $name
     * @param array|string|QDB_Expr $cols
     * @param array|string|QDB_Expr|QDB_Cond $cond
     * @param array $cond_args
     *
     * @return QDB_Select
     */
    protected function _join($join_type, $name, $cols, $cond = null, $cond_args = null)
    {
        if (!isset(self::$_join_types[$join_type])) {
            // LC_MSG: 无效的 JOIN 类型 "%s".
            throw new QDB_Select_Exception(__('无效的 JOIN 类型 "%s".', $join_type));
        }

        if (count($this->_parts[self::UNION])) {
            // LC_MSG: 不能在使用 UNION 查询的同时使用 JOIN 查询.
            throw new QDB_Select_Exception(__('不能在使用 UNION 查询的同时使用 JOIN 查询.'));
        }

        // 根据 $name 的不同类型确定数据表名称、别名
        $m = array();
        if (empty($name)) {
            $table = $this->_current_table;
            $alias = '';
        } elseif (is_array($name)) {
            foreach ($name as $alias => $table) {
                if (!is_string($alias)) { $alias = ''; }
                break;
            }
        } elseif ($name instanceof QDB_Table) {
            $table = $name;
            $alias = '';
        } elseif (preg_match('/^(.+)\s+AS\s+(.+)$/i', $name, $m)) {
            $table = $m[1];
            $alias = $m[2];
        } else {
            $table = $name;
            $alias = '';
        }

        // 确定 table_name 和 schema
        if ($table instanceof QDB_Table) {
            /* @var $table QDB_Table */
            $table_name = $table->full_table_name;
            $schema = $table->schema;
        } else {
            $m = explode('.', $table);
            if (isset($m[1])) {
                $table_name = $m[1];
                $schema = $m[0];
            } else {
                $table_name = $table;
                $schema = null;
            }
        }

        // 获得一个唯一的别名
        $alias = $this->_uniqueAlias(empty($alias) ? $table_name : $alias);

        // 处理查询条件
        if (!($cond instanceof QDB_Cond)) {
            $cond = QDB_Cond::createCronDirect($cond, $cond_args);
        }
        /* @var $cond QDB_Cond */
        $where_sql = $cond->formatToString($this->_conn, $alias, $this->_columns_mapping);

        // 添加一个要查询的数据表
        $this->_parts[self::FROM][$alias] = array(
            'join_type'      => $join_type,
            'schema'         => $schema,
            'table_name'     => $table_name,
            'join_cond'      => $where_sql,
        );

        // 添加查询字段
        $this->_addCols($alias, $cols);

        return $this;
    }

    /**
     * 添加到内部的数据表->字段名映射数组
     *
     * @param string $table_name
     * @param array|string|QDB_Expr $cols
     */
    protected function _addCols($table_name, $cols)
    {
        if (!is_array($cols)) {
            $cols = array($cols);
        }

        if ($table_name == null) {
            $table_name = '';
        }

        $m = null;
        foreach (array_filter($cols) as $alias => $col) {
            if (is_string($col)) {
                // 将包含多个字段的字符串打散
                $_cols = explode(',', $col);
                foreach ($_cols as $col) {
                    $current_table_name = $table_name;
                    $col = trim($col);
                    if (empty($col)) { continue; }
                    // 检查是不是 "字段名 AS 别名"这样的形式
                    if (preg_match('/^(.+)\s+' . self::SQL_AS . '\s+(.+)$/i', $col, $m)) {
                        $col = $m[1];
                        $alias = $m[2];
                    }
                    // 检查字段名是否包含表名称
                    if (preg_match('/(.+)\.(.+)/', $col, $m)) {
                        $current_table_name = $m[1];
                        $col = $m[2];
                    }
                    if (isset($this->_columns_mapping[$col])) {
                        $col = $this->_columns_mapping[$col];
                    }

                    $this->_parts[self::COLUMNS][] = array($current_table_name, $col, is_string($alias) ? $alias : null);
                }
            } else {
                $this->_parts[self::COLUMNS][] = array($table_name, $col, is_string($alias) ? $alias : null);
            }
        }
    }

    /**
     * 添加查询条件的内容方法
     *
     * @param string|array|QDB_Expr|QDB_Cond $cond
     * @param array $args
     * @param int $part_type
     * @param bool $bool true = AND, false = OR
     *
     * @return QDB_Select
     */
    protected function _addConditions($cond, array $args, $part_type, $bool)
    {
        if (!($cond instanceof QDB_Cond)) {
            if (empty($cond)) { return $this; }
            $cond = QDB_Cond::createCronDirect($cond, $args);
        }

        $sql = $cond->formatToString($this->_conn, $this->_current_table, $this->_columns_mapping);
        if (empty($sql)) { return $this; }

        if (is_null($this->_parts[$part_type])) {
            $this->_parts[$part_type] = new QDB_Cond();
        }
        if ($bool) {
            $this->_parts[$part_type]->andCond($sql);
        } else {
            $this->_parts[$part_type]->orCond($sql);
        }
        return $this;
    }

    /**
     * 添加一个集合查询
     *
     * @param int $type
     * @param string|QDB_Expr $field
     * @param string $alias
     *
     * @return QDB_Select
     */
    protected function _addAggregate($type, $field, $alias)
    {
        if ($field instanceof QDB_Expr) {
            $field = $field->formatToString($this->_conn, $this->_current_table, $this->_columns_mapping);
        } else {
            if (isset($this->_columns_mapping[$field])) {
                $field = $this->_columns_mapping[$field];
            }
            $field = $this->_conn->qfieldsInto($field, $this->_current_table, $this->_columns_mapping);
            $field = "{$type}($field)";
        }
        $this->_parts[self::AGGREGATE][] = array($type, $field, $alias);
        return $this;
    }

    /**
     * 为准备分页查询获得分页数据
     */
    protected function _preparePagedQuery()
    {
        return;
        if (empty($this->page_query_sql)) {
            $this->toStringInternal();
        }
        $count = (int)$this->table->conn->getOne($this->page_query_sql);

        $pager = array();
        $pager['record_count'] = $count;
        $pager['page_count'] = ceil($count / $this->page_size);
        $pager['first'] = $this->page_base;
        $pager['last'] = $pager['page_count'] + $this->page_base - 1;
        if ($pager['last'] < $pager['first']) { $pager['last'] = $pager['first']; }

        if ($this->page >= $pager['page_count'] + $this->page_base) {
            $this->page = $pager['last'];
        }
        if ($this->page < $this->page_base) {
            $this->page = $pager['first'];
        }
        if ($this->page < $pager['last'] - 1) {
            $pager['next'] = $this->page + 1;
        } else {
            $pager['next'] = $pager['last'];
        }
        if ($this->page > $this->page_base) {
            $pager['prev'] = $this->page - 1;
        } else {
            $pager['prev'] = $pager['first'];
        }
        $pager['current'] = $this->page;
        $pager['page_size'] = $this->page_size;
        $pager['page_base'] = $this->page_base;

        $this->pager = $pager;
        $this->limit = array($this->page_size, ($this->page - 1) * $this->page_size);
    }


    /**
     * 获得一个唯一的别名
     *
     * @param string|array $name
     *
     * @return string
     */
    private function _uniqueAlias($name)
    {
        if (empty($name)) { return ''; }
        if (is_array($name)) {
            $c = end($name);
        } else {
            $dot = strrpos($name,'.');
            $c = ($dot === false) ? $name : substr($name, $dot+1);
        }
        for ($i = 2; array_key_exists($c, $this->_parts[self::FROM]); ++$i) {
            $c = $name . '_' . (string) $i;
        }
        return $c;
    }

}