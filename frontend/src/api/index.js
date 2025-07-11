import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api'
})

export const register = (username, password) =>
  api.post('/auth/register', { username, password })

export const login = (username, password) =>
  api.post('/auth/login', { username, password })

export const refresh = refreshToken =>
  api.post('/auth/refresh', { refreshToken })

export const logout = refreshToken =>
  api.post('/auth/logout', { refreshToken })

export const getGameInfo = () => api.get('/game/info')
export const startGame = () => api.post('/game/start')
export const stopGame = () => api.post('/game/stop')

export default api
