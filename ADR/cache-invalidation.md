# ADR: Cache Invalidation for /api/orders

- Candidate: CAND-RZ4

## Context
`GET /api/orders` is implemented as a read-only endpoint and is cached to reduce repeated database reads. The cache uses a versioned namespace and a short TTL. There are currently no order write routes (`POST`, `PUT`, `DELETE`), so there is no live mutation path that needs immediate invalidation.

## Decision
We use a **TTL-based cache with namespace versioning support**.

## Implementation
- `Cache::remember()` performs read-through caching [Cache.php:65](backend/src/Support/Cache.php:65)
- `Cache::bumpNamespaceVersion()` exists for version-based invalidation [Cache.php:90](backend/src/Support/Cache.php:90)
- `OrderService::getOrdersList()` builds the cache key with:
  `orders:v{version}:user:{userId}:cursor:{cursor}:pp:{perPage}:s:{start}:e:{end}` [OrderService.php:34](backend/src/Services/OrderService.php:34)
- The endpoint uses a `10s` TTL [OrderService.php:44](backend/src/Services/OrderService.php:44)
- `GET /api/orders` is routed through the controller/service stack [index.php:26-30](backend/public/index.php:26) [OrderController.php:17,33,42](backend/src/Http/Controllers/OrderController.php:17)

## Rationale
- Simple to operate and fits the current file-based cache
- Short TTL limits stale data exposure
- No pub/sub or extra infrastructure is required
- Adequate for a read-heavy path with no current write operations

## Rollback Plan
If write routes are added later, connect `bumpNamespaceVersion()` to the mutation flow and reduce TTL or migrate to event-driven invalidation if stricter freshness is needed.
