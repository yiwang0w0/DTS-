const User = require('../models/User');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');

exports.register = async (req, res) => {
  try {
    const { username, password } = req.body;
    if (!username || !password) {
      return res.status(400).json({ msg: '用户名和密码不能为空' });
    }
    const exists = await User.findOne({ username });
    if (exists) {
      return res.status(400).json({ msg: '用户名已存在' });
    }
    const hashed = await bcrypt.hash(password, 10);
    const user = await User.create({ username, password: hashed });
    const token = jwt.sign({ id: user._id }, process.env.JWT_SECRET);
    res.json({ token, userId: user._id, username: user.username });
  } catch (err) {
    console.error(err);
    res.status(500).json({ msg: '注册失败' });
  }
};

exports.login = async (req, res) => {
  try {
    const { username, password } = req.body;
    const user = await User.findOne({ username });
    if (!user) {
      return res.status(400).json({ msg: '用户不存在' });
    }
    const match = await bcrypt.compare(password, user.password);
    if (!match) {
      return res.status(400).json({ msg: '密码错误' });
    }
    const token = jwt.sign({ id: user._id }, process.env.JWT_SECRET);
    res.json({ token, userId: user._id, username: user.username });
  } catch (err) {
    console.error(err);
    res.status(500).json({ msg: '登录失败' });
  }
};
