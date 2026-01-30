import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../pages/Dashboard.vue'
import Products from '../pages/Products.vue'
import Categories from '../pages/Categories.vue'
import Sales from '../pages/Sales.vue'
import Receivables from '../pages/Receivables.vue'
import Reports from '../pages/Reports.vue'
import Settings from '../pages/Settings.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/products',
    name: 'Products',
    component: Products
  },
  {
    path: '/categories',
    name: 'Categories',
    component: Categories
  },
  {
    path: '/sales',
    name: 'Sales',
    component: Sales
  },
  {
    path: '/receivables',
    name: 'Receivables',
    component: Receivables
  },
  {
    path: '/reports',
    name: 'Reports',
    component: Reports
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
