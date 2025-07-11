module.exports = {
  players: [
    { name: 'pid', label: '玩家ID', type: 'number' },
    { name: 'name', label: '昵称', type: 'text' },
    { name: 'hp', label: '生命值', type: 'number' },
    { name: 'sp', label: '体力', type: 'number' },
    { name: 'money', label: '金钱', type: 'number' },
    { name: 'state', label: '状态', type: 'number' }
  ],
  shopitems: [
    { name: 'sid', label: '物品ID', type: 'number' },
    { name: 'item', label: '名称', type: 'text' },
    { name: 'price', label: '价格', type: 'number' },
    { name: 'area', label: '地区', type: 'number' }
  ],
  users: [
    { name: 'username', label: '用户名', type: 'text' },
    { name: 'role', label: '角色', type: 'select', options: ['user','admin'] }
  ],
  logs: [
  { name: 'lid', label: '日志ID', type: 'number' },
  { name: 'time', label: '时间戳', type: 'number' },
  { name: 'log', label: '日志内容', type: 'text' }
],
chats: [
  { name: 'cid', label: '聊天ID', type: 'number' },
  { name: 'type', label: '频道', type: 'select', options: ['0','1','2','3'] },
  { name: 'name', label: '发送者', type: 'text' },
  { name: 'msg', label: '内容', type: 'text' },
  { name: 'time', label: '时间戳', type: 'number' }
],
mapitems: [
  { name: 'itm', label: '道具名', type: 'text' },
  { name: 'itmk', label: '种类', type: 'text' },
  { name: 'itme', label: '效果值', type: 'number' },
  { name: 'itms', label: '次数/耐久', type: 'text' },
  { name: 'itmsk', label: '属性', type: 'text' },
  { name: 'pls', label: '所在区域', type: 'number' }
],
maptraps: [
  { name: 'itm', label: '陷阱名', type: 'text' },
  { name: 'itmk', label: '类型', type: 'text' },
  { name: 'itme', label: '伤害', type: 'number' },
  { name: 'itms', label: '用途/次数', type: 'text' },
  { name: 'pls', label: '布设区域', type: 'number' }
],
newsinfos: [
  { name: 'nid', label: '新闻ID', type: 'number' },
  { name: 'news', label: '内容', type: 'text' },
  { name: 'time', label: '时间戳', type: 'number' }
],
roomlisteners: [
  { name: 'port', label: '端口', type: 'number' },
  { name: 'status', label: '状态', type: 'select', options: ['监听中','已关闭'] }
],
histories: [
  { name: 'gid', label: '游戏ID', type: 'number' },
  { name: 'winner', label: '胜利者', type: 'text' },
  { name: 'winmode', label: '胜利方式', type: 'number' },
  { name: 'endtime', label: '结束时间戳', type: 'number' }
],
gameinfos: [
  { name: 'gamenum', label: '局数', type: 'number' },
  { name: 'gamestate', label: '状态', type: 'select', options: ['未开始', '进行中', '已结束'] },
  { name: 'starttime', label: '开始时间', type: 'number' },
  { name: 'alivenum', label: '存活人数', type: 'number' },
  { name: 'arealist', label: '禁区列表', type: 'text' },
  { name: 'winner', label: '胜利者', type: 'text' },
  { name: 'winmode', label: '胜利模式', type: 'number' }
],



}
