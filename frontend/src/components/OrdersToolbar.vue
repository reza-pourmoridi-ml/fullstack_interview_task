<template>
  <div class="toolbar">
    <div class="field">
      <label for="userId">user_id</label>
      <input id="userId" v-model.number="filters.userId" type="number" min="1" />
    </div>

    <div class="field">
      <label for="perPage">per_page (1..100)</label>
      <input id="perPage" v-model.number="filters.perPage" type="number" min="1" max="100" />
    </div>

    <div class="field">
      <label for="start">start</label>
      <input id="start" v-model="filters.start" type="text" placeholder="YYYY-MM-DD" />
    </div>

    <div class="field">
      <label for="end">end</label>
      <input id="end" v-model="filters.end" type="text" placeholder="YYYY-MM-DD" />
    </div>

    <div class="actions">
      <button class="primary" :disabled="loading" @click="$emit('apply')">
        {{ loading ? 'Loading...' : 'Apply' }}
      </button>

      <button class="secondary" :disabled="loading" @click="$emit('reset')">
        Reset
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  filters: {
    userId: number | null
    perPage: number
    start: string
    end: string
  }
  loading: boolean
}>()

defineEmits<{
  (e: 'apply'): void
  (e: 'reset'): void
}>()
</script>
