<template>
  <div class="page">
    <h2>欢迎来到 DTS</h2>
    <div v-if="!loggedIn" class="auth-box">
      <el-tabs v-model="activeTab" stretch>
        <el-tab-pane label="登录" name="login">
          <el-form @submit.prevent="login">
            <el-form-item label="用户名">
              <el-input v-model="loginForm.username" autocomplete="off" />
            </el-form-item>
            <el-form-item label="密码">
              <el-input type="password" v-model="loginForm.password" autocomplete="off" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="login">登录</el-button>
            </el-form-item>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="注册" name="register">
          <el-form @submit.prevent="register">
            <el-form-item label="用户名">
              <el-input v-model="registerForm.username" placeholder="用户名" />
            </el-form-item>
            <el-form-item label="密码">
              <el-input type="password" v-model="registerForm.password" placeholder="密码" />
            </el-form-item>
            <el-form-item>
              <el-button @click="register">注册</el-button>
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>
    </div>
    <div v-else class="welcome">
      <p>已登录：{{ user }}</p>
      <el-button type="primary" @click="logout">退出登录</el-button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { login as loginApi, register as registerApi } from '../api'

const loginForm = reactive({ username: '', password: '' })
const registerForm = reactive({ username: '', password: '' })
const user = ref(localStorage.getItem('user') || '')
const activeTab = ref('login')

const loggedIn = computed(() => !!localStorage.getItem('token'))

async function login() {
  if (!loginForm.username || !loginForm.password) return
  try {
    const { data } = await loginApi(loginForm.username, loginForm.password)
    user.value = data.username
    localStorage.setItem('user', user.value)
    localStorage.setItem('token', data.token)
  } catch (e) {
    alert(e.response?.data?.msg || '登录失败')
  }
}

async function register() {
  if (!registerForm.username || !registerForm.password) return
  try {
    const { data } = await registerApi(registerForm.username, registerForm.password)
    user.value = data.username
    localStorage.setItem('user', user.value)
    localStorage.setItem('token', data.token)
  } catch (e) {
    alert(e.response?.data?.msg || '注册失败')
  }
}

function logout() {
  user.value = ''
  localStorage.removeItem('user')
  localStorage.removeItem('token')
}
</script>

<style scoped>
.page { padding: 20px; }
.auth-box { max-width: 400px; margin: 0 auto; }
.welcome { margin-top: 20px; }
</style>
