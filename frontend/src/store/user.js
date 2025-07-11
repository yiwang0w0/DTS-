import { ref } from 'vue'

export const user = ref(localStorage.getItem('user') || '')
export const token = ref(localStorage.getItem('token') || '')
