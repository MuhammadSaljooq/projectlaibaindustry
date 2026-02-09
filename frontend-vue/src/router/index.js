import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../pages/Dashboard.vue'
import Products from '../pages/Products.vue'
import Categories from '../pages/Categories.vue'
import Sales from '../pages/Sales.vue'
import Receivables from '../pages/Receivables.vue'
import Reports from '../pages/Reports.vue'
import Settings from '../pages/Settings.vue'
import InventorySheet from '../pages/InventorySheet.vue'
import PurchaseEntry from '../pages/PurchaseEntry.vue'
import Payables from '../pages/Payables.vue'

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
    path: '/purchase-entry',
    name: 'PurchaseEntry',
    component: PurchaseEntry
  },
  {
    path: '/receivables',
    name: 'Receivables',
    component: Receivables
  },
  {
    path: '/payables',
    name: 'Payables',
    component: Payables
  },
  {
    path: '/inventory-sheet',
    name: 'InventorySheet',
    component: InventorySheet
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
