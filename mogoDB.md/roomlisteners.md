# roomlisteners 集合示例

`bra_roomlisteners` 在 DTS-SAMPLE 中记录房间的监听端口。MongoDB 对应集合可这样初始化：

```javascript
use dts;
db.roomlisteners.insertMany([
  { port: 3000, timestamp: 1690000000, roomid: 1, uniqid: 'abc123' },
  { port: 3001, timestamp: 1690000100, roomid: 2, uniqid: 'def456' }
]);
```

字段定义见 `backend/src/models/RoomListener.js`。
