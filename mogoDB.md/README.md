# MongoDB 操作指南

此目录用于记录需要在 MongoDB 中手动执行的操作步骤。

## 用户集合索引
1. 进入 MongoDB shell：`mongo`
2. 切换数据库：`use dts`
3. 为 `users` 集合创建唯一索引：
   ```javascript
   db.users.createIndex({ username: 1 }, { unique: true })
   ```

## 初始管理员账号
1. 注册完普通用户后，如需设置管理员权限，请在 MongoDB 中手动修改：
   ```javascript
   db.users.updateOne({ username: '<your name>' }, { $set: { role: 'admin' } })
   ```

如果后续还有无法在代码中完成的数据库操作，请在此目录补充说明。

## 刷新令牌字段
1. 进入 MongoDB shell：`mongo`
2. 切换数据库：`use dts`
3. 为现有 `users` 文档添加 `refreshToken` 字段：
   ```javascript
   db.users.updateMany(
     { refreshToken: { $exists: false } },
     { $set: { refreshToken: '' } }
   )
   ```

## 管理员角色字段
1. 进入 MongoDB shell：`mongo`
2. 切换数据库：`use dts`
3. 为现有 `users` 文档添加 `role` 字段并设为 `user`：
   ```javascript
   db.users.updateMany(
     { role: { $exists: false } },
     { $set: { role: 'user' } }
   )
   ```

## 游戏信息集合
1. 进入 MongoDB shell：`mongo`
2. 切换数据库：`use dts`
3. 创建 `gameinfos` 集合并插入初始记录：
   ```javascript
   db.gameinfos.insertOne({
     version: '1.0',
     gamenum: 0,
     gametype: 0,
     gamestate: 0,
     groomid: 0,
     groomtype: 0,
     groomstatus: 0,
     starttime: 0,
     afktime: 0,
     validnum: 0,
     alivenum: 0,
     deathnum: 0,
     combonum: 0,
     weather: 0,
     hack: 0,
     hdamage: 0,
     hplayer: '',
     winmode: 0,
     winner: '',
     areanum: 0,
     areatime: 0,
     areawarn: 0,
     arealist: '',
     noisevars: '',
     roomvars: '',
     gamevars: ''
   })
   ```

## 游戏核心集合
1. 创建 `players` 集合并建立索引：
   ```javascript
   db.players.createIndex({ pid: 1 })
   db.players.createIndex({ type: 1 })
   db.players.createIndex({ name: 1 })
   db.players.createIndex({ pls: 1 })
   ```
2. 创建 `shopitems` 集合：
   ```javascript
   db.shopitems.createIndex({ sid: 1 })
   db.shopitems.createIndex({ kind: 1, area: 1 })
   ```
3. 创建 `logs`、`chats`、`mapitems`、`maptraps`、`newsinfos`、`roomlisteners`、`histories` 集合：
   ```javascript
   db.logs.createIndex({ lid: 1 })
   db.chats.createIndex({ cid: 1 })
   db.mapitems.createIndex({ iid: 1 })
   db.maptraps.createIndex({ tid: 1 })
   db.newsinfos.createIndex({ nid: 1 })
   db.roomlisteners.createIndex({ port: 1 })
   db.histories.createIndex({ gid: 1 })
   ```

上述集合结构请参考 `backend/src/models` 中的同名模型文件。
