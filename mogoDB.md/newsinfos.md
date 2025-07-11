# newsinfos 集合示例

`newsinfos` 集合对应 DTS-SAMPLE 中的 `bra_newsinfo` 表，用于记录游戏进程。示例：

```javascript
use dts;
db.newsinfos.insertMany([
  { nid: 1, time: 1690000000, news: 'gamestart', a: '游戏开始', b: '16名玩家', c: '', d: '', e: '' },
  { nid: 2, time: 1690000300, news: 'kill', a: 'Alice', b: 'Bob', c: '锐利的刀', d: '', e: '' }
]);
```

字段请参考 `backend/src/models/NewsInfo.js`。
