const express = require('express');
const mongoose = require('mongoose');
const redis = require('redis');
const cors = require('cors');
require('dotenv').config();

const authRoutes = require('./routes/auth');
const gameRoutes = require('./routes/game');
const adminRoutes = require('./routes/admin');

const app = express();
app.use(cors());
app.use(express.json());

const mongoUri = process.env.MONGODB_URI;
mongoose.connect(mongoUri)
  .then(() => console.log('MongoDB 已连接'))
  .catch(err => console.error('MongoDB 连接失败', err));

const redisClient = redis.createClient({ url: process.env.REDIS_URL });
redisClient.connect().then(() => console.log('Redis 已连接'));

app.get('/api/ping', (req, res) => res.json({ msg: 'pong' }));
app.use('/api/auth', authRoutes);
app.use('/api/game', gameRoutes);
app.use('/api/admin', adminRoutes);

app.listen(process.env.PORT, () => {
  console.log(`后端服务已启动，端口：${process.env.PORT}`);
});
