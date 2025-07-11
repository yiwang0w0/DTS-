const mongoose = require('mongoose');

const gameInfoSchema = new mongoose.Schema({
  version: { type: String, required: true },
  gamestate: { type: String, required: true },
  startTime: { type: Date, default: Date.now },
  areaInterval: { type: Number, default: 0 },
  areaAdd: { type: Number, default: 0 },
  areaNum: { type: Number, default: 0 },
  aliveCount: { type: Number, default: 0 },
  survivorCount: { type: Number, default: 0 },
  deathCount: { type: Number, default: 0 }
});

module.exports = mongoose.model('GameInfo', gameInfoSchema);
