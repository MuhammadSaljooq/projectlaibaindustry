import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { setupI18n } from './i18n'
import './index.css'

const app = createApp(App)
const pinia = createPinia()

// Setup i18n
const i18n = setupI18n()

app.use(pinia)
app.use(router)
app.use(i18n)

app.mount('#app')
