import { createRouter, createWebHistory } from 'vue-router'
import MapView from '../views/MapView.vue'
import PhotoDates from '../views/PhotoDates.vue'
import Explore from '../views/Explore.vue'
import MyPage from '../views/MyPage.vue'
import TestDB from '../views/TestDB.vue'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      redirect: '/map'
    },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/register',
      name: 'register',
      component: Register
    },
    {
      path: '/map',
      name: 'map',
      component: MapView
    },
    {
      path: '/photodates',
      name: 'photodates',
      component: PhotoDates
    },
    {
      path: '/explore',
      name: 'explore',
      component: Explore
    },
    {
      path: '/mypage',
      name: 'mypage',
      component: MyPage
    },
    {
      path: '/testdb',
      name: 'testdb',
      component: TestDB
    }
  ],
})


export default router
