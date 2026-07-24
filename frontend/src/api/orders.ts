import { getJSON } from './http'
import type { OrdersResponse } from '@/types/order'

export type FetchOrdersParams = {
  userId?: number | null
  perPage?: number
  start?: string
  end?: string
  cursorCreatedAt?: string | null
  cursorId?: number | null
}

export function fetchOrders(params: FetchOrdersParams = {}, signal?: AbortSignal) {
  const qs = new URLSearchParams()

  if (params.userId !== undefined && params.userId !== null) qs.set('user_id', String(params.userId))
  if (params.perPage !== undefined) qs.set('per_page', String(params.perPage))
  if (params.start) qs.set('start', params.start)
  if (params.end) qs.set('end', params.end)

  if (params.cursorCreatedAt && params.cursorId !== undefined && params.cursorId !== null) {
    qs.set('cursor_created_at', params.cursorCreatedAt)
    qs.set('cursor_id', String(params.cursorId))
  }

  const query = qs.toString()
  const path = `/api/orders${query ? `?${query}` : ''}`

  return getJSON<OrdersResponse>(path, signal)
}
