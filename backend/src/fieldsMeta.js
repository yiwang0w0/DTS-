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
  ]
}
