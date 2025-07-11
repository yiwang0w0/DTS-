# histories 集合示例

该集合对应 DTS-SAMPLE 的 `bra_history` 表。以下示例展示基本的历史记录插入方式：

```javascript
use dts;
db.histories.insertMany([
  {
    gid: 1,
    wmode: 0,
    winner: 'Alice',
    motto: '勇往直前',
    gametype: 0,
    vnum: 16,
    gtime: 600,
    gstime: 0,
    getime: 600,
    hdmg: 300,
    hdp: 'Alice',
    hkill: 3,
    hkp: 'Bob',
    winnernum: 1,
    winnerteamID: '',
    winnerlist: 'Alice',
    winnerpdata: '',
    validlist: 'Alice,Bob',
    hnews: '游戏结束'
  }
]);
```

字段名与 `backend/src/models/History.js` 保持一致，可根据需要扩充更多记录。
