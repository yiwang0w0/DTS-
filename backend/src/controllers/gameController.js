const GameInfo = require('../models/GameInfo');

exports.getInfo = async (req, res) => {
  try {
    const info = await GameInfo.findOne();
    res.json(info || {});
  } catch (err) {
    console.error(err);
    res.status(500).json({ msg: '获取游戏信息失败' });
  }
};

exports.startGame = async (req, res) => {
  try {
    let info = await GameInfo.findOne();
    if (!info) {
      info = await GameInfo.create({ version: '1.0', gamestate: 'active' });
    } else {
      info.gamestate = 'active';
      await info.save();
    }
    res.json({ msg: '游戏已开始', gamestate: info.gamestate });
  } catch (err) {
    console.error(err);
    res.status(500).json({ msg: '启动游戏失败' });
  }
};

exports.stopGame = async (req, res) => {
  try {
    let info = await GameInfo.findOne();
    if (!info) {
      info = await GameInfo.create({ version: '1.0', gamestate: 'inactive' });
    } else {
      info.gamestate = 'inactive';
      await info.save();
    }
    res.json({ msg: '游戏已停止', gamestate: info.gamestate });
  } catch (err) {
    console.error(err);
    res.status(500).json({ msg: '停止游戏失败' });
  }
};
