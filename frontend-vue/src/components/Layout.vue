<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
        <div class="flex justify-between h-14 sm:h-16">
          <div class="flex items-center">
            <router-link to="/" class="text-lg sm:text-xl font-bold text-primary-600">
              <span class="hidden sm:inline">Inventory & Sales</span>
              <span class="sm:hidden">I&S</span>
            </router-link>
          </div>
          
          <!-- Desktop Navigation -->
          <div class="hidden lg:flex items-center space-x-1">
            <router-link
              v-for="item in navItems"
              :key="item.path"
              :to="item.path"
              :class="[
                'flex items-center px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium transition-colors',
                $route.path === item.path
                  ? 'bg-primary-100 text-primary-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-primary-600'
              ]"
            >
              <component :is="item.icon" class="w-4 h-4 xl:w-5 xl:h-5 mr-1 xl:mr-2" />
              <span class="hidden xl:inline">{{ item.label }}</span>
            </router-link>
          </div>

          <!-- Mobile Menu Button -->
          <div class="lg:hidden flex items-center">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="p-2 text-gray-700 hover:text-primary-600"
            >
              <X v-if="mobileMenuOpen" class="w-6 h-6" />
              <Menu v-else class="w-6 h-6" />
            </button>
          </div>
        </div>

        <!-- Mobile Navigation -->
        <div v-if="mobileMenuOpen" class="lg:hidden border-t border-gray-200 py-2">
          <router-link
            v-for="item in navItems"
            :key="item.path"
            :to="item.path"
            @click="mobileMenuOpen = false"
            :class="[
              'flex items-center px-4 py-3 rounded-md text-base font-medium transition-colors',
              $route.path === item.path
                ? 'bg-primary-100 text-primary-700'
                : 'text-gray-700 hover:bg-gray-100 hover:text-primary-600'
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 mr-3" />
            {{ item.label }}
          </router-link>
        </div>
      </div>
    </nav>
    <main class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { LayoutDashboard, Package, FolderTree, ShoppingCart, ShoppingBag, Receipt, CreditCard, BarChart3, FileSpreadsheet, Settings, Menu, X } from 'lucide-vue-next'

const { t } = useI18n()
const mobileMenuOpen = ref(false)

const navItems = computed(() => [
  { path: '/', label: t('nav.dashboard'), icon: LayoutDashboard, key: 'dashboard' },
  { path: '/products', label: t('nav.products'), icon: Package, key: 'products' },
  { path: '/inventory-sheet', label: t('nav.inventorySheet'), icon: FileSpreadsheet, key: 'inventorySheet' },
  { path: '/categories', label: t('nav.categories'), icon: FolderTree, key: 'categories' },
  { path: '/sales', label: t('nav.sales'), icon: ShoppingCart, key: 'sales' },
  { path: '/purchase-entry', label: t('nav.purchaseEntry'), icon: ShoppingBag, key: 'purchaseEntry' },
  { path: '/receivables', label: t('nav.receivables') || 'Receivables', icon: Receipt, key: 'receivables' },
  { path: '/payables', label: t('nav.payables'), icon: CreditCard, key: 'payables' },
  { path: '/reports', label: t('nav.reports'), icon: BarChart3, key: 'reports' },
  { path: '/settings', label: t('nav.settings'), icon: Settings, key: 'settings' },
])
</script>
