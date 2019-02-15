## ShardingQuery

分表数据查询类

## 使用方法

ThinkPHP 5 的范例，支持任何框架，只需修改 `query` 参数的闭包代码。

```php
$shardingQuery = new ShardingQuery([
    'query'    => function ($sql) {
        return \think\Db::query($sql);
    },
    'table'    => [
        'order',
        'order_201805',
        'order_201804',
    ],
    'field'    => '{table}.*, u.name',
    'leftJoin' => [
        'user AS u ON u.member_id = {table}.member_id',
    ],
    'where'    => '{table}.member_id = 10001 AND status = 1',
    'order'    => '{table}.add_time DESC',
    'offset'   => 0,
    'limit'    => 10,
]);
// 查询结果
$result        = $shardingQuery->select();
// 总行数，用于分页
$count         = $shardingQuery->count();
// 追踪数据，用于调试
$trace         = $shardingQuery->trace();
```

全部参数：

- `query`：执行 sql 返回结果的闭包，接收一个 $sql 参数，返回查询结果。
- `table`：要查询的多个同构表的清单，数组类型。
- `field`：select 选择的列名，字符串类型。
- `innerJoin`：join信息，数组类型。
- `leftJoin`：join信息，数组类型。
- `where`：查询条件，字符串类型。
- `order`：排序，字符串类型。
- `offset`：偏移数，整数类型。
- `limit`：限制数，整数类型。

## License

Apache License Version 2.0, http://www.apache.org/licenses/