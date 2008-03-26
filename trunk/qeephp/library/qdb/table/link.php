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
 * 定义 QDB_Table_Link 类
 *
 * @package database
 * @version $Id$
 */

/**
 * QDB_Table_Link 封装数据表之间的关联关系
 *
 * @package database
 */
class QDB_Table_Link
{
    /**
     * 该连接的名字，用于检索指定的连接
     *
     * 同一个数据表的多个关联不能使用相同的名字。如果定义关联时没有指定名字，
     * 则以关联对象的 $mapping_name 属性作为这个关联的名字。
     *
     * @var string
     */
    public $name;

    /**
     * 关联中的主表
     *
     * @var QDB_Table
     */
    public $main_table;

    /**
     * 关联到哪一个表数据入口对象
     *
     * @var QDB_Table
     */
    public $assoc_table;

    /**
     * many to many 关联中处理中间表的表数据入口对象
     *
     * @var QDB_Table
     */
    public $mid_table;

    /**
     * 关联数据映射为什么名字的嵌套数组
     *
     * @var string
     */
    public $mapping_name;

    /**
     * 关联关系在主表中使用哪一个字段
     *
     * 对于 has many 和 has one 关联，main_key 的默认值是主表的主键字段
     * 对于 belongs to 关联，main_key 的默认值是主表中与关联表主键字段同名的字段
     * 对于 many to many 关联，main_key 的默认值是主表的主键字段
     *
     * @var string
     */
    public $main_key;

    /**
     * 查询时，主表的关联字段使用什么别名
     *
     * @var string
     */
    public $main_key_alias;

    /**
     * 关联关系在关联表中使用哪一个字段
     *
     * 对于 has many、has one 关联，assoc_key 的默认值是关联表中与主表主键字段同名的字段
     * 对于 belongs to 关联，assoc_key 的默认值是关联表的主键字段
     * 对于 many to many 关联，assoc_key 的默认值是关联表的主键字段
     *
     * @var string
     */
    public $assoc_key;

    /**
     * 查询时，关联表的关联字段使用什么别名
     *
     * @var string
     */
    public $assoc_key_alias;

    /**
     * 指示在中间表中，用哪个字段存储对主表的 main_key 引用（仅用于 many to many 关联）
     * mid_main_key 的默认值是中间表中与 main_key 同名的字段
     *
     * @var string
     */
    public $mid_main_key;

    /**
     * 指示在中间表中，用哪个字段存储对关联表的 assoc_key 引用（仅用于 many to many 关联）
     * mid_assoc_key 的默认值是中间表中与 assoc_key 同名的字段
     *
     * @var string
     */
    public $mid_assoc_key;

    /**
     * 指示查询 many to many 关联时，中间表的哪些字段要包含在查询结果中
     *
     * null     - 不查询中间表的字段
     *  *       - 查询中间表的所有字段
     * 字段列表 - 以逗号分隔的字段名或者包含字段名的数组
     *
     * mid_on_find_fields 的默认值是 null。
     *
     * @var string|array
     */
    public $mid_on_find_fields = null;

    /**
     * 指示查询 many to many 关联，中间表的字段包含在查询结果中时要加上什么前缀
     * 默认的 mid_on_find_prefix 是 'mid_'
     *
     * @var string
     */
    public $mid_on_find_prefix = 'mid_';

    /**
     * 指示是否读取关联的记录
     *
     * all  - 读取所有关联记录
     * skip - 跳过，不读取内容记录
     * 整数 - 仅读取指定个数的内容记录，例如 on_find = 5 表示仅读取每个作者的 5 个内容记录
     * 数组 - 包含读取起始位置和要读取的个数，例如 array($offset, $nums)
     *
     * 对于所有类型的关联，on_find 的默认值都是 all
     *
     * @var string|int|array
     */
    public $on_find = 'all';

    /**
     * 查询关联表时使用的查询条件
     *
     * @var array|string
     */
    public $on_find_where = null;

    /**
     * 指示按照什么排序规则查询关联的记录
     */
    public $on_find_order = null;

    /**
     * 指示在读取关联记录时，只获取关联记录的哪些字段
     */
    public $on_find_fields = '*';

    /**
     * 指示在删除主表记录时，如何处理关联的记录
     *
     * cascade   - 删除所有的关联记录
     * set_null  - 将关联记录的外键字段设置为 NULL
     * set_value - 将关联记录的外键字段设置为指定的值
     * skip      - 不处理关联记录
     *
     * 对于 belongs to 和 many to many 关联，on_delete 的默认值是 skip
     * 对于 has many 和 has one 关联，默认值则是 cascade
     *
     * @var string|boolean
     */
    public $on_delete = 'skip';

    /**
     * 如果 on_delete 为 set_value，则通过 on_delete_set_value 指定要填充的值。on_delete_set_value 的默认值为 null
     *
     * @var mixed
     */
    public $on_delete_set_value = null;

    /**
     * 指示是否保存关联的记录
     *
     * save        - 根据关联记录是否具有主键值来决定是创建记录还是更新现有记录
     * create      - 强制创建新记录
     * update      - 强制更新记录
     * replace     - 使用数据库的 replace 操作来尝试替换记录
     * skip        - 不处理关联记录
     * only_create - 仅仅保存需要创建的记录（根据是否具备主键值判断）
     * only_update - 仅仅保存需要更新的记录（根据是否具备主键值判断）
     *
     * 对于 belongs to 和 many to many 关联，on_save 的默认值是 skip
     * 对于 has many 和 has one 关联，on_save 的默认值是 save
     *
     * @var string
     */
    public $on_save = 'skip';

    /**
     * 指示连接两个数据集的行时，是一对一连接还是一对多连接
     *
     * @var boolean
     */
    public $one_to_one = false;

    /**
     * 关联的类型
     *
     * @var const
     */
    public $type;

    /**
     * 当 enabled 为 false 时，表数据入口的任何操作都不会处理该关联
     *
     * enabled 的优先级高于 linkRead、linkCreate、linkUpdate 和 linkRemove。
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * 指示关联的表数据入口是否已经初始化
     *
     * @var boolean
     */
    private $is_init = false;

    /**
     * 仅仅用于初始化关联对象的参数
     *
     * @var array
     */
    private $init_params = array();

    /**
     * 字段别名增量
     *
     * @var int
     */
    private static $alias_index = 0;

    /**
     * 构造函数
     *
     * @param array $define
     * @param const $type
     * @param QDB_Table $main_table
     *
     * @return QDB_Table_Link
     */
    protected function __construct(array $define, $type, QDB_Table $main_table)
    {
        self::$alias_index++;

        $this->name = strtolower($define['name']);
        $this->mapping_name = $define['mapping_name'];
        $this->type = $type;
        $this->main_table = $main_table;

        static $init_params = array(
            'table_obj',
            'table_class',
            'table_name',
            'assoc_table_pk',
            'main_key',
            'main_key_alias',
            'assoc_key',
            'assoc_key_alias',
            'mid_main_key',
            'mid_assoc_key',
            'mid_table_name',
            'mid_table_class',
            'mid_on_find_fields',
            'mid_on_find_prefix',
            'on_find',
            'on_find_where',
            'on_find_order',
            'on_find_fields',
            'on_delete',
            'on_delete_set_value',
            'on_save',
        );

        foreach ($init_params as $key) {
            if (!empty($define[$key])) {
                $this->init_params[$key] = $define[$key];
            }
        }
    }

    /**
     * 创建 QDB_Table_Link 对象实例
     *
     * @param array $define
     * @param const $type
     * @param QDB_Table $main_table
     *
     * @return QDB_Table_Link
     */
    static function createLink(array $define, $type, QDB_Table $main_table)
    {
        if (empty($define['mapping_name'])) {
            // LC_MSG: Expected parameter "mapping_name".
            throw new QDB_Table_Link_Exception(__('Expected parameter "mapping_name".'));
        }
        if (empty($define['name'])) {
            $define['name'] = $define['mapping_name'];
        }
        // 返回 QDB_Table_Link 继承类实例
        return new QDB_Table_Link($define, $type, $main_table);
    }

    /**
     * 允许使用该关联
     *
     * @return QDB_Table_Link
     */
    function enable()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * 禁用该关联
     *
     * @return QDB_Table_Link
     */
    function disable()
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * 初始化关联对象
     *
     * @return QDB_Table_Link
     */
    function init()
    {
        if ($this->is_init) { return $this; }

        $this->main_table->connect();

        $p = $this->init_params;
        /**
         * table_obj
         * 关联的表数据入口对象实例
         *
         * table_class
         * 关联到哪一个表数据入口类
         *
         * table_name
         * 关联到哪一个数据表
         *
         * assoc_pk
         * 关联数据表的主键
         *
         * table_obj、table_class、table_name 三者只需要指定一个，三者的优先级从上到下。
         * 如果 table_name 有效，则可以通过 assoc_table_???? 等一系列参数指示构造关联表数据入口时的选项。
         */
        if (!empty($p['table_obj'])) {
            $this->assoc_table = $p['table_obj'];
        } elseif (!empty($p['table_class'])) {
            $this->assoc_table = Q::getSingleton($p['table_class']);
        } elseif (!empty($p['table_name'])) {
            $params = array('table_name' => $p['table_name']);
            foreach ($p as $key => $value) {
                if (substr($key, 0, 12) == 'assoc_table_') {
                    $params[substr($key, 12)] = $value;
                }
            }
            $this->assoc_table = new QDB_Table($params);
        } else {
            // LC_MSG: Expected parameter "%s".
            $err = 'table_obj or table_class or table_name';
            throw new QDB_Table_Link_Exception(__('Expected parameter "%s" for link "%s".', $err, $this->name));
        }
        $this->assoc_table->connect();

        /**
         * 对于 many to many 关联，需要确定中间表
         *
         * mid_table_obj、mid_table_class、mid_table_name 三者只需要指定一个，三者的优先级从上到下。
         * 如果 mid_table_name 有效，则可以通过 mid_table_???? 等一系列参数指示构造关联表数据入口时的选项。
         *
         */
        if ($this->type == QDB_Table::many_to_many) {
            if (!empty($p['mid_table_obj'])) {
                $this->mid_table = $p['mid_table_obj'];
            } elseif (!empty($p['mid_table_class'])) {
                $this->mid_table = Q::getSingleton($p['mid_table_class']);
            } else {
                if (empty($p['mid_table_name'])) {
                    // 尝试自动设置中间表名称
                    $p['mid_table_name'] = $this->main_table->table_name . '_has_' . $this->assoc_table->table_name;
                }
                $params = array('table_name' => $p['mid_table_name']);
                foreach ($p as $key => $value) {
                    if (substr($key, 0, 10) == 'mid_table_' && $key != 'mid_table_name') {
                        $params[substr($key, 10)] = $value;
                    }
                }
                $this->mid_table = new QDB_Table($params);
            }
            $this->mid_table->connect();
        }

        /**
         * 根据关联类型设置各项默认值
         */
        switch ($this->type) {
        case QDB_Table::has_one:
        case QDB_Table::has_many:
            $this->main_key  = !empty($p['main_key'])  ? $p['main_key']  : $this->main_table->pk;
            $this->assoc_key = !empty($p['assoc_key']) ? $p['assoc_key'] : $this->main_table->pk;
            $this->on_delete = !empty($p['on_delete']) ? $p['on_delete'] : 'cascade';
            $this->on_save   = !empty($p['on_save'])   ? $p['on_save']   : 'save';
            $this->one_to_one = $this->type == QDB_Table::has_one;
            break;
        case QDB_Table::belongs_to:
            $this->main_key  = !empty($p['main_key'])  ? $p['main_key']  : $this->assoc_table->pk;
            $this->assoc_key = !empty($p['assoc_key']) ? $p['assoc_key'] : $this->assoc_table->pk;
            $this->on_delete = !empty($p['on_delete']) ? $p['on_delete'] : 'skip';
            $this->on_save   = !empty($p['on_save'])   ? $p['on_save']   : 'skip';
            $this->one_to_one = true;
            break;
        case QDB_Table::many_to_many:
            $this->main_key      = !empty($p['main_key'])      ? $p['main_key']      : $this->main_table->pk;
            $this->assoc_key     = !empty($p['assoc_key'])     ? $p['assoc_key']     : $this->assoc_table->pk;
            $this->mid_main_key  = !empty($p['mid_main_key'])  ? $p['mid_main_key']  : $this->main_table->pk;
            $this->mid_assoc_key = !empty($p['mid_assoc_key']) ? $p['mid_assoc_key'] : $this->assoc_table->pk;
            $this->mid_on_find_fields = !empty($p['mid_on_find_fields']) ? $p['mid_on_find_fields'] : null;
            $this->mid_on_find_prefix = !empty($p['mid_on_find_prefix']) ? $p['mid_on_find_prefix'] : 'mid_';
            $this->on_delete     = !empty($p['on_delete'])     ? $p['on_delete']     : 'skip';
            $this->on_save       = !empty($p['on_save'])       ? $p['on_save']       : 'skip';
            $this->one_to_one = false;
        }
        $this->main_key_alias = !empty($p['main_key_alias']) ? $p['main_key_alias'] : 'm_k_a_' . self::$alias_index;
        $this->assoc_key_alias = !empty($p['assoc_key_alias']) ? $p['assoc_key_alias'] : 'a_k_a_' . self::$alias_index;
        $this->on_find = !empty($p['on_find']) ? $p['on_find'] : 'all';
        $this->on_find_where = !empty($p['on_find_where']) ? $p['on_find_where'] : null;
        $this->on_find_fields = !empty($p['on_find_fields']) ? $p['on_find_fields'] : '*';
        $this->on_find_order = !empty($p['on_find_order']) ? $p['on_find_order'] : null;
        $this->on_delete_set_value = !empty($p['on_delete_set_value']) ? $p['on_delete_set_value'] : null;

        $this->is_init = true;

        return $this;
    }

    /**
     * 存储关联的数据
     *
     * @param array $link_data
     * @param mixed $assoc_key_value
     * @param int $recursion
     *
     * @return QDB_Table_Link
     */
    function saveAssocData(array $link_data, $assoc_key_value, $recursion)
    {
        switch ($this->type) {
        case QDB_Table::belongs_to:
        case QDB_Table::has_one:
            $this->saveOneToOne($link_data, $assoc_key_value, $recursion);
            break;
        case QDB_Table::has_many:
            $this->saveOneToMany($link_data, $assoc_key_value, $recursion);
            break;
        case QDB_Table::many_to_many:
            $this->saveManyToMany($link_data, $assoc_key_value, $recursion);
            break;
        }

        return $this;
    }

    /**
     * 保存一对一记录
     *
     * @param array $link_data
     * @param mixed $assoc_key_value
     * @param int $recursion
     */
    protected function saveOneToOne(array $link_data, $assoc_key_value, $recursion)
    {
        if ($this->on_save === false || $this->on_save == 'skip') { return; }
        $link_data[$this->assoc_key] = $assoc_key_value;
        $this->assoc_table->save($link_data, $recursion, $this->on_save);
    }

    /**
     * 保存一对多记录
     *
     * @param array $link_data
     * @param mixed $assoc_key_value
     * @param int $recursion
     */
    protected function saveOneToMany($link_data, $assoc_key_value, $recursion)
    {
        if ($this->on_save === false || $this->on_save == 'skip') { return; }
        foreach (array_keys($link_data) as $offset) {
            $link_data[$offset][$this->assoc_key] = $assoc_key_value;
        }
        $this->assoc_table->saveRowset($link_data, $recursion, $this->on_save);
    }

    /**
     * 保存多对多记录
     *
     * @param array $link_data
     * @param mixed $assoc_key_value
     * @param int $recursion
     */
    protected function saveManyToMany($link_data, $assoc_key_value, $recursion)
    {
        $mid_rowset = array();
        $akvs = array();

        $keys = array_keys($link_data);
        $len = strlen($this->mid_on_find_prefix);
        foreach ($keys as $offset) {
            if (!is_array($link_data[$offset])) {
                $akvs[$offset] = $link_data[$offset];
                continue;
            }

            // 分离出中间表字段
            foreach ($link_data[$offset] as $field => $value) {
                if (substr($field, 0, $len) == $this->mid_on_find_prefix) {
                    $mid_rowset[$offset][substr($field, $len)] = $value;
                    unset($link_data[$offset][$field]);
                }
            }

            if (!empty($link_data[$offset][$this->assoc_table->pk])) {
                $akvs[$offset] = $link_data[$offset][$this->assoc_table->pk];
            } else {
                // 如果关联记录尚未保存到数据库，则创建一条新的关联记录
                $akvs[$offset] = $this->assoc_table->create($link_data[$offset], $recursion);
            }
        }

        // 首先取出现有的关联信息
        $v = $this->assoc_table->getConn()->qstr($assoc_key_value);
        $sql = "SELECT {$this->mid_assoc_key} FROM {$this->mid_table->qtable_name} WHERE {$this->mid_main_key} = {$v}";
        $exists_mid = $this->mid_table->getConn()->getCol($sql);

        // 然后确定要添加的关联信息
        $insert_mid = array_flip(array_diff($akvs, $exists_mid));
        $remove_mid = array_flip(array_diff($exists_mid, $akvs));
        $exists_mid = array_flip($exists_mid);

        foreach ($keys as $offset) {
            if (isset($insert_mid[$akvs[$offset]])) {
                // 增加一个中间记录
                if (!empty($mid_rowset[$offset])) {
                    $row = $mid_rowset[$offset];
                } else {
                    $row = array();
                }
                $row[$this->mid_main_key] = $assoc_key_value;
                $row[$this->mid_assoc_key] = $akvs[$offset];
                $this->mid_table->create($row);
            } elseif (isset($remove_mid[$akvs[$offset]])) {
                // 删除一个中间记录
                $this->mid_table->remove($akvs[$offset]);
            } elseif (isset($exists_mid[$akvs[$offset]])) {
                // 增加一个中间记录
                if (empty($mid_rowset[$offset])) {
                    continue;
                }
                $row = $mid_rowset[$offset];
                $row[$this->mid_main_key] = $assoc_key_value;
                $row[$this->mid_assoc_key] = $akvs[$offset];
                $this->mid_table->update($row);
            }
        }
    }
}
