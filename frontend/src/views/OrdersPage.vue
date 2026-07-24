<script setup lang="ts">
import { onMounted } from 'vue'
import { useOrdersStore } from '../stores/orders'
import OrdersToolbar from '../components/OrdersToolbar.vue'
import OrdersSummary from '../components/OrdersSummary.vue'
import OrdersTable from '../components/OrdersTable.vue'
import LoadMoreButton from '../components/LoadMoreButton.vue'

const store = useOrdersStore()

onMounted(() => {
  store.fetchOrders(true)
})
</script>

<template>
  <div class="container">
    <div class="card">
      <h1>Orders</h1>

      <div v-if="store.error" class="error">
        {{ store.error }}
      </div>

      <OrdersToolbar
        :filters="store.filters"
        :loading="store.loading"
        @apply="store.applyFilters()"
        @reset="store.resetFilters(); store.fetchOrders(true)"
      />

      <OrdersSummary
        :loaded="store.items.length"
        :total="store.total"
        :next-cursor="store.nextCursor"
      />

      <OrdersTable
        :orders="store.items"
        :loading="store.loading"
        :empty-text="store.initialized ? 'No orders found.' : 'Choose filters and load orders.'"
      />

      <LoadMoreButton
        :can-load-more="store.canLoadMore"
        :loading="store.loadingMore"
        @loadMore="store.loadMore()"
      />
    </div>
  </div>
</template>
