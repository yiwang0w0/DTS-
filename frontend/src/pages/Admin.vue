<template>
  <div class="page">
    <h2>后台管理</h2>
    <el-select v-model="collection" placeholder="选择集合" style="width: 200px">
      <el-option v-for="c in collections" :key="c.value" :label="c.label" :value="c.value" />
    </el-select>
    <el-button type="primary" size="small" @click="openCreate" style="margin-left:10px">新建</el-button>
    <el-table :data="items" style="margin-top: 20px" row-key="_id">
      <el-table-column prop="_id" label="ID" width="230" />
      <el-table-column v-for="f in displayFields" :key="f" :prop="f" :label="f" />
      <el-table-column label="数据">
        <template #default="{ row }">
          <pre>{{ json(row) }}</pre>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="150">
        <template #default="{ row }">
          <el-button size="small" @click="openEdit(row)">编辑</el-button>
          <el-button size="small" type="danger" @click="remove(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog v-model="dialogVisible" title="编辑" width="600px">
      <el-input
        type="textarea"
        v-model="editData"
        :rows="10"
        style="font-family: monospace"
      />
      <template #footer>
        <el-button @click="dialogVisible=false">取消</el-button>
        <el-button type="primary" @click="saveEdit">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import {
  adminList,
  adminCreate,
  adminUpdate,
  adminDelete,
  adminFields
} from '../api'

const collections = [
  { label: '玩家', value: 'players' },
  { label: '商店物品', value: 'shopitems' },
  { label: '日志', value: 'logs' },
  { label: '聊天', value: 'chats' },
  { label: '地图物品', value: 'mapitems' },
  { label: '陷阱', value: 'maptraps' },
  { label: '新闻', value: 'newsinfos' },
  { label: '房间监听', value: 'roomlisteners' },
  { label: '历史记录', value: 'histories' },
  { label: '游戏信息', value: 'gameinfos' },
  { label: '用户', value: 'users' }
]

const collection = ref('')
const items = ref([])
const dialogVisible = ref(false)
const editData = ref('')
const editId = ref('')
const fields = ref([])

const displayFields = computed(() => fields.value.slice(0, 5))
watch(collection, () => {
  fetchItems()
  fetchFields()
})

if (collections.length) collection.value = collections[0].value

function json(obj) {
  return JSON.stringify(obj, null, 2)
}

async function fetchItems() {
  if (!collection.value) return
  try {
    const { data } = await adminList(collection.value)
    items.value = data
  } catch (e) {
    alert(e.response?.data?.msg || '加载失败')
  }
}

async function fetchFields() {
  if (!collection.value) return
  try {
    const { data } = await adminFields(collection.value)
    fields.value = data
  } catch (e) {
    console.error(e)
    fields.value = []
  }
}

function openEdit(row) {
  editId.value = row._id
  editData.value = json(row)
  dialogVisible.value = true
}

function openCreate() {
  editId.value = ''
  editData.value = '{}'
  dialogVisible.value = true
}

async function saveEdit() {
  let dataObj
  try {
    dataObj = JSON.parse(editData.value)
  } catch (e) {
    alert('JSON格式错误')
    return
  }
  try {
    if (editId.value) {
      await adminUpdate(collection.value, editId.value, dataObj)
    } else {
      await adminCreate(collection.value, dataObj)
    }
    dialogVisible.value = false
    fetchItems()
  } catch (e) {
    alert(e.response?.data?.msg || '保存失败')
  }
}

async function remove(row) {
  if (!confirm('确定删除？')) return
  try {
    await adminDelete(collection.value, row._id)
    fetchItems()
  } catch (e) {
    alert(e.response?.data?.msg || '删除失败')
  }
}
</script>

<style scoped>
.page {
  padding: 20px;
}
pre {
  margin: 0;
  font-family: monospace;
  white-space: pre-wrap;
}
</style>

