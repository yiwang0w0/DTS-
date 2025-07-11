<template>
  <div class="page">
    <h2>欢迎来到 DTS</h2>
    <div v-if="!loggedIn" class="auth-box">
      <el-form @submit.prevent="login">
        <el-form-item label="用户名">
          <el-input v-model="loginForm.username" autocomplete="off"></el-input>
        </el-form-item>
        <el-form-item label="密码">
          <el-input type="password" v-model="loginForm.password" autocomplete="off"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="login">登录</el-button>
        </el-form-item>
      </el-form>
      <el-form @submit.prevent="register" class="register-form">
        <el-form-item label="新用户注册">
          <el-input v-model="registerForm.username" placeholder="用户名"></el-input>
        </el-form-item>
        <el-form-item>
          <el-input type="password" v-model="registerForm.password" placeholder="密码"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button @click="register">注册</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div v-else class="welcome">
      <p>已登录：{{ user }}</p>
      <el-button type="primary" @click="logout">退出登录</el-button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'

const loginForm = reactive({ username: '', password: '' })
const registerForm = reactive({ username: '', password: '' })
const user = ref(localStorage.getItem('user') || '')

const loggedIn = computed(() => !!user.value)

function login() {
  if (loginForm.username) {
    user.value = loginForm.username
    localStorage.setItem('user', user.value)
  }
}

function register() {
  if (registerForm.username) {
    user.value = registerForm.username
    localStorage.setItem('user', user.value)
  }
}

function logout() {
  user.value = ''
  localStorage.removeItem('user')
}
</script>

<style scoped>
.page { padding: 20px; }
.auth-box { max-width: 400px; margin: 0 auto; }
.register-form { margin-top: 20px; }
.welcome { margin-top: 20px; }
</style>
