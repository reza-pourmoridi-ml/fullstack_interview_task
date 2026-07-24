PRAGMA journal_mode=WAL;
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS orders (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  total INTEGER NOT NULL,
  created_at TEXT NOT NULL
);

CREATE INDEX idx_orders_user_created ON orders(user_id, created_at);


CREATE TABLE IF NOT EXISTS order_items (
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   order_id INTEGER NOT NULL,
   sku TEXT NOT NULL,
   qty INTEGER NOT NULL DEFAULT 1,
   FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);


CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    method TEXT,
    status TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    );
CREATE INDEX idx_payments_order_id ON payments(order_id);
CREATE UNIQUE INDEX idx_payments_order_id_unique ON payments(order_id);