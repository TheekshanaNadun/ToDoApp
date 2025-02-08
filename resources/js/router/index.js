import { createRouter, createWebHistory } from 'vue-router'
import TodoList from '../components/TodoList.vue'

const routes = [
  {
    path: '/',
    component: TodoList
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
