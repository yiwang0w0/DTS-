// 初始化 DTS 数据库
const dbName = 'dts';
const dbconn = connect('127.0.0.1/' + dbName);

// 导入 gameinfo
var gameinfo = JSON.parse(cat('./gameinfo.json')); // 数组形式
if (gameinfo.length) {
  db.gameinfos.remove({});
  db.gameinfos.insertMany(gameinfo);
}

// 导入 shopitems
var shopitems = JSON.parse(cat('./shopitems.json'));
if (shopitems.length) {
  db.shopitems.remove({});
  db.shopitems.insertMany(shopitems);
}

print('数据导入完成');
