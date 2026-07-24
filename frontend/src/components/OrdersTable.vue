<template>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Total</th>
          <th>Created At</th>
          <th>Payment</th>
          <th>Items</th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="!loading && orders.length === 0">
          <td colspan="5" class="muted">{{ emptyText }}</td>
        </tr>

        <tr v-for="order in orders" :key="order.id">
          <td>{{ order.id }}</td>
          <td>{{ formatMoney(order.total) }}</td>
          <td>{{ order.created_at }}</td>
          <td>{{ order.payment?.method }} / {{ order.payment?.status }}</td>
          <td>{{ order.items_count ?? 0 }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import type { Order } from '@/types/order'

defineProps<{
  orders: Order[]
  loading: boolean
  emptyText: string
}>()

function formatMoney(value: number | string) {
  const num = typeof value === 'string' ? Number(value) : value
  if (Number.isNaN(num)) return String(value)
  return new Intl.NumberFormat().format(num)
}
</script>
