# logs 集合示例

以下命令演示如何向 `logs` 集合插入示例文档，字段与 `backend/src/models/Log.js` 中定义一致。

```javascript
// 在 mongo shell 中执行
use dts;
db.logs.insertMany([
  { lid: 1, toid: 0, type: 's', time: 0, log: '游戏开始' },
  { lid: 2, toid: 1, type: 'b', time: 10, log: '玩家遭遇敌人' }
]);
```

这些数据对应 DTS-SAMPLE `bra_log` 表的字段。根据需要可以调整 `lid` 等字段的取值。
