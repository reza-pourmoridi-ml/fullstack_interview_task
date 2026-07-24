import { defineStore } from 'pinia'
import { fetchOrders } from '@/api/orders'
import type { Order, OrdersFilters, Cursor } from '@/types/order'

type OrdersState = {
  items: Order[]
  total: number | null
  loading: boolean
  loadingMore: boolean
  error: string | null
  canLoadMore: boolean
  nextCursor: Cursor
  filters: OrdersFilters
  activeFilters: OrdersFilters
  initialized: boolean
}

const defaultFilters = (): OrdersFilters => ({
  userId: null,
  perPage: 20,
  start: '',
  end: '',
})

export const useOrdersStore = defineStore('orders', {
  state: (): OrdersState => ({
    items: [],
    total: null,
    loading: false,
    loadingMore: false,
    error: null,
    canLoadMore: false,
    nextCursor: null,
    filters: defaultFilters(),
    activeFilters: defaultFilters(),
    initialized: false,
  }),

  actions: {
    normalizeFilters() {
      const f = this.filters

      return {
        userId:
          f.userId === null || Number.isNaN(Number(f.userId))
            ? null
            : Number(f.userId),
        perPage:
          Number.isFinite(Number(f.perPage)) && Number(f.perPage) > 0
            ? Math.min(100, Math.max(1, Number(f.perPage)))
            : 20,
        start: (f.start || '').trim(),
        end: (f.end || '').trim(),
      }
    },

    setActiveFromCurrent() {
      this.activeFilters = this.normalizeFilters()
    },

    resetFilters() {
      this.filters = defaultFilters()
      this.activeFilters = defaultFilters()
      this.nextCursor = null
      this.canLoadMore = true
      this.total = null
      this.items = []
      this.error = null
      this.initialized = false
    },

    async fetchOrders(reset = false) {
      if (reset) {
        this.items = []
        this.nextCursor = null
        this.canLoadMore = true
      }

      this.loading = true
      this.error = null

      const controller = new AbortController()
      const filters = this.activeFilters.userId === null && this.activeFilters.perPage === 20 && !this.activeFilters.start && !this.activeFilters.end
        ? this.normalizeFilters()
        : this.activeFilters

      try {
        const res = await fetchOrders(
          {
            userId: filters.userId,
            perPage: filters.perPage,
            start: filters.start,
            end: filters.end,
          },
          controller.signal,
        )

        const newItems = res.data ?? []
        this.items = reset ? newItems : [...this.items, ...newItems]
        this.total = res.meta?.total ?? this.total
        this.nextCursor = res.meta?.next_cursor ?? null
        this.canLoadMore = Boolean(this.nextCursor)
        this.initialized = true
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Unknown error'
      } finally {
        this.loading = false
      }
    },

    async applyFilters() {
      this.setActiveFromCurrent()
      await this.fetchOrders(true)
    },

    async loadMore() {
      if (!this.canLoadMore || this.loadingMore || !this.nextCursor) return

      this.loadingMore = true
      this.error = null

      const controller = new AbortController()

      try {
        const res = await fetchOrders(
          {
            userId: this.activeFilters.userId,
            perPage: this.activeFilters.perPage,
            start: this.activeFilters.start,
            end: this.activeFilters.end,
            cursorCreatedAt: this.nextCursor.cursor_created_at,
            cursorId: this.nextCursor.cursor_id,
          },
          controller.signal,
        )

        const newItems = res.data ?? []
        this.items = [...this.items, ...newItems]
        this.total = res.meta?.total ?? this.total
        this.nextCursor = res.meta?.next_cursor ?? null
        this.canLoadMore = Boolean(this.nextCursor)
      } catch (e) {
        this.error = e instanceof Error ? e.message : 'Unknown error'
      } finally {
        this.loadingMore = false
      }
    },
  },
})
