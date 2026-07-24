<template>
  <div style="margin-top: 16px; display: flex; gap: 8px;">
    <button class="secondary" :disabled="!canLoadMore || loading" @click="$emit('loadMore')">
      {{ loading ? 'Loading more...' : 'Load more' }}
    </button>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  canLoadMore: boolean
  loading: boolean
}>()

defineEmits<{
  (e: 'loadMore'): void
}>()
</script>
