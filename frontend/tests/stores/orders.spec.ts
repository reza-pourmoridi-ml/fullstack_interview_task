import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useOrdersStore } from '@/stores/orders'

vi.mock('@/api/orders', () => ({
  fetchOrders: vi.fn(),
}))

import { fetchOrders } from '@/api/orders'

describe('useOrdersStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('loads initial orders', async () => {
    vi.mocked(fetchOrders).mockResolvedValueOnce({
      data: [
        {
          id: 1,
          total: 100,
          created_at: '2026-07-24 10:00:00',
          payment: { method: 'card', status: 'paid' },
          items_count: 2,
        },
      ],
      meta: {
        total: 1,
        next_cursor: {
          cursor_created_at: '2026-07-24 10:00:00',
          cursor_id: 1,
        },
      },
    })

    const store = useOrdersStore()
    await store.fetchOrders(true)

    expect(store.items).toHaveLength(1)
    expect(store.canLoadMore).toBe(true)
    expect(store.nextCursor).toEqual({
      cursor_created_at: '2026-07-24 10:00:00',
      cursor_id: 1,
    })
  })

  it('applies filters and resets cursor', async () => {
    vi.mocked(fetchOrders).mockResolvedValue({
      data: [],
      meta: { total: 0, next_cursor: null },
    })

    const store = useOrdersStore()
    store.filters.userId = 7
    store.filters.perPage = 10
    store.filters.start = '2026-07-01'
    store.filters.end = '2026-07-31'

    await store.applyFilters()

    expect(fetchOrders).toHaveBeenCalledWith(
      {
        userId: 7,
        perPage: 10,
        start: '2026-07-01',
        end: '2026-07-31',
      },
      expect.any(AbortSignal),
    )
  })

  it('loads more using cursor params', async () => {
    vi.mocked(fetchOrders)
      .mockResolvedValueOnce({
        data: [{ id: 1, total: 100, created_at: '2026-07-24 10:00:00' }],
        meta: {
          total: 2,
          next_cursor: {
            cursor_created_at: '2026-07-24 10:00:00',
            cursor_id: 1,
          },
        },
      })
      .mockResolvedValueOnce({
        data: [{ id: 2, total: 120, created_at: '2026-07-24 11:00:00' }],
        meta: {
          total: 2,
          next_cursor: null,
        },
      })

    const store = useOrdersStore()
    await store.fetchOrders(true)
    await store.loadMore()

    expect(store.items).toHaveLength(2)
    expect(store.canLoadMore).toBe(false)
  })
})
