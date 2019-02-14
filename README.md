## ShardingQuery

分表数据查询类

## 使用方法

ThinkPHP 5

```php
$shardingQuery = new ShardingQuery([
    'callback' => 'think\Db::query',
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
$res           = $shardingQuery->select();
$count         = $shardingQuery->count();
$trace         = $shardingQuery->trace();
```
