<?php

/**
 * 根据时间同构分表的查询类
 * @package app\common\library
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
    public $table;

    /**
     * 字段
     * @var string
     */
    public $field;

    /**
     * 条件
     * @var string
     */
    public $where;

    /**
     * 排序
     * @var string
     */
    public $order;

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
     * 统计
     * @var array
     */
    protected $stats;

    /**
     * ShardingQuery constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            switch ($key) {
                case 'callback':
                    if (!is_callable($value)) {
                        throw new \RuntimeException("'callback' is not a callable type.");
                    }
                    $this->$key = $value;
                    break;
                case 'table':
                    if (!is_array($value)) {
                        throw new \RuntimeException("'table' is not a array type.");
                    }
                    $this->$key = $value;
                    break;
                case 'field':
                    if (!is_string($value)) {
                        throw new \RuntimeException("'field' is not a string type.");
                    }
                    $this->$key = $value;
                    break;
                case 'where':
                    if (!is_string($value)) {
                        throw new \RuntimeException("'where' is not a string type.");
                    }
                    $this->$key = $value;
                    break;
                case 'order':
                    if (!is_string($value)) {
                        throw new \RuntimeException("'order' is not a string type.");
                    }
                    $this->$key = $value;
                    break;
                case 'limit':
                    if (!is_int($value)) {
                        throw new \RuntimeException("'limit' is not a int type.");
                    }
                    $this->$key = $value;
                    break;
                case 'offset':
                    if (!is_int($value)) {
                        throw new \RuntimeException("'offset' is not a int type.");
                    }
                    $this->$key = $value;
                    break;
            }
        }
    }

    /**
     * 执行查询
     * @return array
     */
    public function select()
    {
        $this->stats = $this->stats($this->table);
        $range       = $this->range($this->stats);
        $data        = [];
        foreach ($range as $tableName => $item) {
            $sql = "SELECT {$this->field} FROM `{$tableName}`";
            if (!empty($this->where)) {
                $sql .= " WHERE {$this->where}";
            }
            if (!empty($this->order)) {
                $sql .= " ORDER BY {$this->order}";
            }
            $sql    = "{$sql} LIMIT {$item['limit']} OFFSET {$item['offset']}";
            $result = call_user_func($this->callback, $sql);
            $data   = array_merge($data, $result);
        }
        return $data;
    }

    /**
     * 获取表的统计信息
     * @param $table
     * @return array
     */
    protected function stats($table)
    {
        $end   = 0;
        $stats = [];
        foreach ($table as $num => $tableName) {
            $sql = "SELECT COUNT(*) FROM `{$tableName}`";
            if (!empty($this->where)) {
                $sql .= " WHERE {$this->where}";
            }
            $result            = call_user_func($this->callback, $sql);
            $first             = array_pop($result);
            $count             = array_pop($first);
            $start             = $end;
            $end               += $count;
            $stats[$tableName] = [
                'start' => $start,
                'end'   => $end,
            ];
        }
        return $stats;
    }

    /**
     * 获取要提取的表数据范围
     * @param $stats
     * @return array
     */
    protected function range($stats)
    {
        $limit  = $this->limit;
        $offset = $this->offset;
        $start  = $offset;
        $end    = $offset + $limit;
        $tables = [];
        foreach ($stats as $table => $item) {
            $before = $item['start'] <= $start && $item['end'] >= $start ? true : false;
            $center = $item['start'] > $start && $item['end'] < $end ? true : false;
            $after  = $item['start'] <= $end && $item['end'] >= $end ? true : false;
            if ($before && $after) {
                $tables[$table] = [
                    'offset' => $start - $item['start'],
                    'limit'  => $end - $start,
                ];
                continue;
            }
            if ($before) {
                $tables[$table] = [
                    'offset' => $start - $item['start'],
                    'limit'  => $item['end'] - $start,
                ];
                if ($tables[$table]['limit'] == 0) {
                    unset($tables[$table]);
                }
                continue;
            }
            if ($after) {
                $tables[$table] = [
                    'offset' => 0,
                    'limit'  => $end - $item['start'],
                ];
                continue;
            }
            if ($center) {
                $tables[$table] = [
                    'offset' => 0,
                    'limit'  => $item['end'] - $item['start'],
                ];
                continue;
            }
        }
        return $tables;
    }

    /**
     * 获取数据总数
     * @return int
     */
    public function count()
    {
        $stats  = $this->stats;
        $last   = array_pop($stats);
        $number = array_pop($last);
        return $number ?: 0;
    }

}
