## ShardingQuery

根据时间分表的数据查询类

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
    'field'    => '*',
    'where'    => 'member_id = 10001',
    'offset'   => 32,
    'limit'    => 3,
]);
$res           = $shardingQuery->select();
```
