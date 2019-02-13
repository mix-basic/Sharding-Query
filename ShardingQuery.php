<?php

/**
 * 根据时间同构分表的查询类
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class ShardingQuery
{

    /**
     * 数据库查询的回调
     * 回调必须返回数组类型
     * @var callable
     */
    public $callback;

    /**
     * 数据表列表
     * 根据顺序查询
     * @var array
     */
    public $tables;

    /**
     * 查询语句
     * @var string
     */
    public $sql;

    /**
     * 限制数
     * @var int
     */
    public $limit;

    /**
     * 偏移数
     * @var int
     */
    public $offset;

    /**
     * 数据表的通配符
     * @var string
     */
    protected static $tableSymbol = '{table}';

    /**
     * ShardingSelect constructor.
     * @param array $tables
     * @param callable $callback
     * @param string $sql
     */
    public function __construct(callable $callback, array $tables, string $sql, int $limit, int $offset)
    {
        $this->callback = $callback;
        $this->tables   = $tables;
        $this->sql      = $sql;
        $this->limit    = $limit;
        $this->offset   = $offset;
    }

    /**
     * 执行查询
     * @return array
     */
    public function select()
    {
        $data = [];
        foreach ($this->tables as $num => $table) {
            if (count($data) < $this->limit) {
                $limit  = $this->limit;
                $offset = $this->offset;
                if ($num > 0) {
                    $limit  = $this->limit - count($data);
                    $offset = 0;
                }
                $sql    = "{$this->sql} LIMIT {$limit} OFFSET {$offset}";
                $sql    = str_replace(static::$tableSymbol, $table, $sql);
                $result = call_user_func($this->callback, $sql);
                if (empty($result)) {
                    return $data;
                }
                $data = array_merge($data, $result);
            }
            if (count($data) >= $this->limit) {
                return $data;
            }
        }
        return $data;
    }

}
