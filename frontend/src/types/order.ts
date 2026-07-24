export type Cursor = {
  cursor_created_at: string
  cursor_id: number
} | null

export type OrderPayment = {
  method?: string | null
  status?: string | null
} | null

export type Order = {
  id: number
  total: number | string
  created_at: string
  payment?: OrderPayment
  items_count?: number
}

export type OrdersFilters = {
  userId: number | null
  perPage: number
  start: string
  end: string
}

export type OrdersResponse = {
  data: Order[]
  meta?: {
    total?: number
    next_cursor?: {
      cursor_created_at: string
      cursor_id: number
    } | null
  }
}
