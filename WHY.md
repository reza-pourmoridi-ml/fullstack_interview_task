# WHY.md

This document captures the key architectural and implementation decisions for the `fullstack_interview_task`.

## 1. Architecture & Dockerization
- **PSR-4 Autoloading:** Switched to Composer autoloading to remove manual `require` chains, reduce bootstrap fragility, and make the codebase easier to scale and refactor.
- **Layered Backend Structure:** Split responsibilities into `Http/Controllers`, `Services`, `Repositories`, and `Support` so request handling, business logic, and data access stay isolated and testable.
- **Thin Controller Approach:** Kept controllers focused on input/output concerns only, while moving orchestration into services and SQL-heavy logic into repositories.
- **Cursor-Based Load More:** Matched frontend pagination with backend keyset pagination by passing `next_cursor` between requests.
- **Incremental List Updates:** Appended newly fetched records during pagination instead of replacing existing state.
- **Metadata-Driven UI State:** Derived load-more availability from API pagination metadata rather than static UI rules.

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
- **Centralized Orders Store:** Moved orders data, filters, loading state, and pagination into a dedicated store for predictable state transitions.
- **Separated API Concerns:** Kept HTTP and orders request logic in a dedicated API layer to avoid coupling network code to components.
- **Composable UI Structure:** Broke the orders page into smaller components for filters, summary, table, and pagination controls.

## 5. Algorithmic Decisions
- **Rejected Pairwise Shortcut:** I explicitly avoided the common but incorrect shortcut of validating only consecutive time differences. A sequence such as `10:00`, `10:04`, `10:08` has adjacent gaps under 5 minutes, yet the total span is 8 minutes
- **Streaming Trade-off:** The streaming version is more scalable for sequential ingestion because it keeps only recent timestamps per user and processes the input in one pass, but this comes at the cost of more stateful logic and stronger assumptions about event ordering.
