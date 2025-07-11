# mapitems 集合示例

配置文件 `DTS-SAMPLE/include/modules/base/itemmain/config/mapitem.config.php` 中给出了地图道具的原始数据。下面根据该配置转换成 `insertMany` 示例：

```javascript
use dts;
db.mapitems.insertMany([
  { iid: 1, itm: '煤气罐', itmk: 'GBi', itme: 1, itms: '10', itmsk: '', pls: 0 },
  { iid: 2, itm: '增幅设备', itmk: 'X', itme: 1, itms: '1', itmsk: '', pls: 1 }
]);
```

以上字段对齐 `backend/src/models/MapItem.js`，仅为示例可按需修改。
