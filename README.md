# Flash Sale Checkout System

A Laravel-based flash sale system with inventory holds and payment processing, designed to prevent overselling during high-traffic events.

## Core Assumptions and Invariants

### Business Rules
- **No Overselling**: Available stock = total stock - (active holds + confirmed orders)
- **Hold Expiry**: All holds expire after 2 minutes automatically
- **Atomic Operations**: Hold creation and order conversion use database transactions with `lockForUpdate()`
- **Webhook Idempotency**: Payment webhooks are safe to retry using idempotency keys

### Technical Guarantees  
- Database locks prevent race conditions during concurrent stock checks
- Manual transaction management with `DB::beginTransaction()`/`DB::commit()`
- Webhook results cached for duplicate request handling
- Background jobs handle hold expiry with automatic stock release

## Quick Setup

```bash
# Install dependencies
composer install
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Run application
php artisan serve
php artisan queue:work
```

## Core API Endpoints

```bash
# Create inventory hold
POST /api/holds
{"product_id": 1, "qty": 2}

# Convert hold to order
POST /api/orders  
{"hold_id": 123, "payment_status": "online_payment"}

# Payment webhook
POST /api/webhooks/payment
{"idempotency_key": "webhook_123", "order_id": 456, "event_type": "success"}
```

## Running Tests

```bash
# All tests
php artisan test

# Specific areas
php artisan test --filter="webhook"     # Payment webhook tests
php artisan test --filter="stock"      # Stock/hold tests  
php artisan test --filter="expired"    # Hold expiry tests
```

## Key Test Scenarios

### 1. **Parallel Stock Boundary Tests**
**Location**: Unit tests with concurrent hold creation
- Tests multiple requests when stock = 1, requests = 2
- Verifies only one succeeds due to `lockForUpdate()`
- Ensures no overselling occurs

### 2. **Hold Expiry Stock Release**  
**Tests**: `cannot create order from expired hold`
- Expired holds automatically release reserved stock
- Background job `ExpireOrderHoldJob` handles cleanup
- Stock becomes available again after expiry

### 3. **Webhook Idempotency**
**Test**: `returns cached result for duplicate webhook`
- Same `idempotency_key` returns cached response
- No duplicate payment processing
- Safe webhook retries

### 4. **Webhook Before Order**
**Test**: `handles webhook for non-existent payment gracefully`  
- Webhook arrives before payment record exists
- Returns error without crashing
- Supports retry when payment ready

## Models Overview

### Product
- **Fields**: `name`, `price`, `stock`
- **Key Method**: `available_stock` (calculated with active holds)

### Order  
- **Fields**: `is_hold`, `status`, `payment_status`, `total`, `expires_at`
- **States**: Hold (temporary) → Order (confirmed)
- **Scopes**: `activeHolds()`, `confirmedOrders()`

### Payment
- **Fields**: `order_id`, `status`, `idempotency_key`, `payload`  
- **States**: Pending → Processing → Completed/Failed
- **Webhook**: Updates status via webhook processing

### OrderItem
- **Fields**: `order_id`, `product_id`, `qty`, `unit_price`, `subtotal`
- **Purpose**: Links orders to products with quantities

## Logs and Monitoring

**Logs Location**: `storage/logs/laravel.log`

**Key Events**:
```
[INFO] Hold created: ID 123, Product: 1, Qty: 2
[INFO] Order created from hold: Hold ID 123 → Order ID 456  
[INFO] Webhook processed: Key webhook_123, Status: success
[WARNING] Insufficient stock: Available 5, Requested 10
```

**Stock Monitoring**:
```bash
# Check current stock levels
php artisan tinker
>>> App\Models\Product::all()->map(fn($p) => [
    'name' => $p->name, 
    'total' => $p->stock, 
    'available' => $p->available_stock
])

# Clean expired holds
php artisan orders:expire-holds 
```
