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
