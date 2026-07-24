# WHY.md

This document captures the key architectural and implementation decisions for the `fullstack_interview_task`.

## 1. Architecture & Dockerization
- **PSR-4 Autoloading:** Switched to Composer autoloading to remove manual `require` chains, reduce bootstrap fragility, and make the codebase easier to scale and refactor.
- **Layered Backend Structure:** Split responsibilities into `Http/Controllers`, `Services`, `Repositories`, and `Support` so request handling, business logic, and data access stay isolated and testable.
- **Thin Controller Approach:** Kept controllers focused on input/output concerns only, while moving orchestration into services and SQL-heavy logic into repositories.
- **Centralized Bootstrap:** Consolidated shared setup such as PDO initialization and dependency wiring to keep entrypoints minimal and consistent.

## 2. Database Optimization
- **Financial Precision:** Changed `orders.total` from `REAL` to `INTEGER` (storing values in base units, e.g., cents). This eliminates floating-point rounding errors common in financial calculations.
- **Indexing Strategy:**
    - Implemented a composite index `(user_id, created_at)` on the `orders` table. This covers the most frequent access patterns (filtering by user + sorting by date) in a single B-Tree traversal.
    - Added foreign key indexes to `order_items` and `payments` to optimize join performance.
- **Data Integrity:** Enabled `PRAGMA foreign_keys = ON` to enforce relational integrity at the database level, preventing orphaned records in `order_items` and `payments`.

## 3. API Design & Caching
- **Keyset Pagination:** Replaced `LIMIT/OFFSET` pagination with cursor-based pagination using `(created_at, id)` to avoid deep-scan slowdown on large datasets.
- **N+1 Elimination:** Moved related lookups into SQL with joins and aggregated subqueries so each request is served with a predictable number of queries.
- **Cache-Aside Strategy:** Added a small application-level cache with `remember()` to reduce repeated reads on hot order-list queries without coupling cache logic to controllers.
- **Versioned Invalidation:** Used namespace versioning for cache invalidation so related cached results can be invalidated safely without scanning and deleting many individual keys.
- **Stable Cache Identity:** Designed cache keys around query-shaping inputs such as `user_id`, date filters, page size, and cursor to prevent collisions between different result sets.

## 4. Frontend State Management

## 5. Algorithmic Decisions

