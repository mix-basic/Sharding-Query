## ShardingQuery

根据时间分表的数据查询类

## 使用方法

ThinkPHP 5

```php
$shardingQuery = new ShardingQuery(
    'think\Db::query',
    [
        'order',
        'order_history',
    ],
    'SELECT * FROM {table}',
    10,
    0
);
$res           = $shardingQuery->select();
var_dump($res);
```
