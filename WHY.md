# WHY.md

This document captures the key architectural and implementation decisions for the `fullstack_interview_task`.

## 1. Architecture & Dockerization

## 2. Database Optimization
- **Financial Precision:** Changed `orders.total` from `REAL` to `INTEGER` (storing values in base units, e.g., cents). This eliminates floating-point rounding errors common in financial calculations.
- **Indexing Strategy:**
    - Implemented a composite index `(user_id, created_at)` on the `orders` table. This covers the most frequent access patterns (filtering by user + sorting by date) in a single B-Tree traversal.
    - Added foreign key indexes to `order_items` and `payments` to optimize join performance.
- **Data Integrity:** Enabled `PRAGMA foreign_keys = ON` to enforce relational integrity at the database level, preventing orphaned records in `order_items` and `payments`.

## 3. API Design & Caching

## 4. Frontend State Management

## 5. Algorithmic Decisions
