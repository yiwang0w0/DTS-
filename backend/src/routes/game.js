const express = require('express');
const router = express.Router();
const gameController = require('../controllers/gameController');

router.get('/info', gameController.getInfo);
router.post('/start', gameController.startGame);
router.post('/stop', gameController.stopGame);

module.exports = router;
