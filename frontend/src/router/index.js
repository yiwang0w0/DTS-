import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import Mail from '../pages/Mail.vue'
import Profile from '../pages/Profile.vue'
import EnterGame from '../pages/EnterGame.vue'
import Map from '../pages/Map.vue'
import Status from '../pages/Status.vue'
import Alive from '../pages/Alive.vue'
import Victory from '../pages/Victory.vue'
import Ranking from '../pages/Ranking.vue'
import Help from '../pages/Help.vue'
import Admin from '../pages/Admin.vue'

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/mail', name: 'Mail', component: Mail },
  { path: '/profile', name: 'Profile', component: Profile },
  { path: '/game', name: 'EnterGame', component: EnterGame },
  { path: '/map', name: 'Map', component: Map },
  { path: '/status', name: 'Status', component: Status },
  { path: '/alive', name: 'Alive', component: Alive },
  { path: '/victory', name: 'Victory', component: Victory },
  { path: '/ranking', name: 'Ranking', component: Ranking },
  { path: '/help', name: 'Help', component: Help },
  { path: '/admin', name: 'Admin', component: Admin },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
