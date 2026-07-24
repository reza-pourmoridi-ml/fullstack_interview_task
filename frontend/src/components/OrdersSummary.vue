<template>
  <div class="summary">
    <div class="badge">Loaded: {{ loaded }}</div>
    <div class="badge">Total: {{ total ?? 'unknown' }}</div>
<!--    <div class="badge">-->
<!--      Next cursor:-->
<!--      {{ nextCursor ? `${nextCursor.cursor_created_at} / ${nextCursor.cursor_id}` : 'none' }}-->
<!--    </div>-->
  </div>
</template>

<script setup lang="ts">
defineProps<{
  loaded: number
  total: number | null
  nextCursor: { cursor_created_at: string; cursor_id: number } | null
}>()
</script>
