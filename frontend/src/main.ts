import { createApp } from 'vue'
import { createPinia } from 'pinia'
import OrdersPage from './views/OrdersPage.vue'
import './style.css'

createApp(OrdersPage).use(createPinia()).mount('#app')
