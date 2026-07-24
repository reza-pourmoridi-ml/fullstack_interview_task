import { beforeEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import OrdersPage from '@/views/OrdersPage.vue'

vi.mock('@/api/orders', () => ({
  fetchOrders: vi.fn(),
}))

import { fetchOrders } from '@/api/orders'

describe('OrdersPage', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('renders loaded order', async () => {
    vi.mocked(fetchOrders).mockResolvedValueOnce({
      data: [
        {
          id: 10,
          total: 2500,
          created_at: '2026-07-24 12:00:00',
          payment: { method: 'cash', status: 'paid' },
          items_count: 3,
        },
      ],
      meta: {
        total: 1,
        next_cursor: null,
      },
    })

    const wrapper = mount(OrdersPage)

    await new Promise((r) => setTimeout(r, 0))

    expect(wrapper.text()).toContain('Orders')
    expect(wrapper.text()).toContain('10')
    expect(wrapper.text()).toContain('cash')
  })
})
