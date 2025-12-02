# Flash Sale Checkout System

A high-performance Laravel-based flash sale checkout system designed to handle concurrent requests and prevent overselling during high-traffic events.

## System Overview

This system implements a robust flash sale checkout flow with inventory holds, payment processing, and webhook handling. It's built to handle high concurrency while maintaining data consistency and preventing overselling.

### Key Features

- **Inventory Hold System**: Temporary product reservations with automatic expiry
- **Concurrency Control**: Database-level locks prevent overselling
- **Webhook Idempotency**: Safe handling of duplicate webhook requests
- **Payment Integration**: Support for multiple payment methods
- **Automatic Cleanup**: Expired holds are automatically released

## Core Assumptions and Invariants

### Business Logic Invariants

1. **No Overselling**: The system guarantees that available stock is never exceeded, even under high concurrent load
2. **Hold Expiry**: All holds expire after 2 minutes if not converted to orders
3. **Atomic Operations**: All critical operations (hold creation, order conversion) are wrapped in database transactions
4. **Idempotent Webhooks**: Payment webhooks can be safely retried using idempotency keys
5. **Stock Calculation**: Available stock = total stock - (active holds + confirmed orders)

### Technical Invariants

1. **Database Consistency**: All critical operations use `lockForUpdate()` to prevent race conditions
2. **Transaction Isolation**: Manual transaction management with `DB::beginTransaction()`/`DB::commit()`
3. **Webhook Ordering**: System handles webhooks arriving before order creation
4. **Cache Management**: Webhook results are cached for idempotency
5. **Job Reliability**: Background jobs handle hold expiry with retry mechanisms

### Data Integrity Rules

- Orders can only be created from valid, non-expired holds
- Payment status transitions follow strict state machine rules
- Stock reservations are immediately reflected in availability calculations
- Expired holds automatically release reserved inventory

## How to Run the Application

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL/MariaDB or SQLite
- Node.js (for frontend assets, if applicable)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Flash-Sale-Checkout-Hossam-Eissa
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Update `.env` with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=flash_sale_checkout
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seed data**
   ```bash
   php artisan migrate
   php artisan db:seed --class=ProductSeeder
   ```

### Running the Application

1. **Start the web server**
   ```bash
   php artisan serve
   ```

2. **Start the queue worker** (for background jobs)
   ```bash
   php artisan queue:work
   ```

3. **Schedule hold expiry** (in production, add to cron)
   ```bash
   php artisan orders:expire-holds
   ```

### API Endpoints

- `POST /api/holds` - Create inventory hold
- `POST /api/orders` - Convert hold to order
- `GET /api/orders/{id}` - Retrieve order details
- `GET /api/holds/{id}` - Retrieve hold details
- `POST /api/webhooks/payment` - Payment webhook endpoint

### Example Usage

1. **Create a hold**
   ```bash
   curl -X POST http://localhost:8000/api/holds \
     -H "Content-Type: application/json" \
     -d '{"product_id": 1, "qty": 2}'
   ```

2. **Convert hold to order**
   ```bash
   curl -X POST http://localhost:8000/api/orders \
     -H "Content-Type: application/json" \
     -d '{"hold_id": 123, "payment_status": "online_payment"}'
   ```

## Running Tests

### Full Test Suite

```bash
php artisan test
```

### Specific Test Categories

```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# Specific test file
php artisan test tests/Feature/PaymentWebhookTest.php
```

### Test Database Setup

Tests use a separate SQLite database that's automatically created and migrated:

```bash
# Run tests with verbose output
php artisan test --verbose

# Run specific test with debugging
php artisan test --filter="processes successful payment webhook"
```

## Logs and Metrics

### Application Logs

**Location**: `storage/logs/laravel.log`

**Log Levels Available**:
- `debug` - Detailed application flow
- `info` - General application events
- `warning` - Unexpected but handled situations
- `error` - Error conditions
- `critical` - Critical failures

### Key Logged Events

1. **Hold Operations**
   ```
   [INFO] Hold created: ID 123, Product: 1, Qty: 2, Expires: 2025-12-03 10:15:00
   [INFO] Hold expired: ID 123, Status changed to cancelled
   ```

2. **Order Processing**
   ```
   [INFO] Order created from hold: Hold ID 123 -> Order ID 456
   [WARNING] Hold conversion failed: Hold 123 already expired
   ```

3. **Webhook Processing**
   ```
   [INFO] Webhook processed: Key webhook_123, Event: success, Order: 456
   [WARNING] Duplicate webhook ignored: Key webhook_123 (cached)
   ```

4. **Stock Management**
   ```
   [WARNING] Insufficient stock: Product 1, Available: 5, Requested: 10
   [INFO] Stock reserved: Product 1, Qty: 2, Remaining: 48
   ```

### Performance Monitoring

**Database Query Logging** (enable in `.env`):
```
DB_LOG_QUERIES=true
LOG_LEVEL=debug
```

**Queue Job Monitoring**:
```bash
# Monitor failed jobs
php artisan queue:failed

# Monitor job status
php artisan horizon:status  # if using Horizon
```

### Metrics and Monitoring

1. **Stock Levels**
   ```bash
   # Check current stock status
   php artisan tinker
   >>> App\Models\Product::with('orderItems')->get()->map(fn($p) => [
       'name' => $p->name, 
       'total_stock' => $p->stock, 
       'available' => $p->available_stock
   ])
   ```

2. **Hold Statistics**
   ```bash
   # Active holds count
   php artisan tinker
   >>> App\Models\Order::activeHolds()->count()
   
   # Expired holds cleanup
   php artisan orders:expire-holds --force
   ```

3. **System Health Checks**
   ```bash
   # Check queue status
   php artisan queue:monitor

   # Database connectivity
   php artisan migrate:status
   ```

### Custom Log Channels

The system supports multiple log channels configured in `config/logging.php`:
- `single` - Single file logging
- `daily` - Daily rotating logs
- `stack` - Multiple channel logging
- `stderr` - Console output

## Automated Tests Coverage

### 1. Parallel Hold Attempts at Stock Boundary

**Test Location**: `tests/Unit/PaymentWebhookServiceTest.php` and related

**What it tests**: Multiple concurrent requests attempting to create holds when stock is at the boundary (e.g., only 1 item left, 2 requests)

**Implementation**:
- Uses `lockForUpdate()` on product records during hold creation
- Database transactions ensure atomic stock checking and reservation
- Tests verify that only the winning request succeeds

### 2. Hold Expiry Returns Availability

**Test Location**: `tests/Feature/OrderControllerTest.php`

**Test Cases**:
- `cannot create order from expired hold` - Verifies expired holds are rejected
- Hold expiry command tests in console commands

**Implementation**:
- Background jobs (`ExpireOrderHoldJob`) automatically expire holds
- Expired holds are excluded from stock calculations
- Tests verify stock becomes available again after expiry

### 3. Webhook Idempotency (Same Key Repeated)

**Test Location**: `tests/Feature/PaymentWebhookTest.php`

**Specific Test**: `returns cached result for duplicate webhook`

**What it tests**:
- Same webhook payload sent multiple times with identical `idempotency_key`
- First request processes normally, subsequent requests return cached result
- No duplicate side effects (payment status changes, order updates)

**Implementation**:
- Redis/database caching based on idempotency key
- Cached responses include full webhook result data

### 4. Webhook Arriving Before Order Creation

**Test Location**: `tests/Feature/PaymentWebhookTest.php`

**Specific Test**: `handles webhook for non-existent payment gracefully`

**What it tests**:
- Webhook received for payment that doesn't exist yet
- System gracefully handles the timing issue
- Returns appropriate error response without crashing

**Implementation**:
- Webhook validation checks payment existence first
- Graceful error handling for missing payment records
- Webhooks can be retried safely when payment exists

### Additional Test Coverage

**Stock Management Tests**:
- Verify available stock calculations include active holds
- Test stock release on hold expiry
- Concurrent access prevention

**Payment Webhook Tests**:
- Success/failure webhook processing
- Payload storage and validation
- Status transition verification

**Integration Tests**:
- End-to-end flow from hold creation to payment
- Error handling across the entire pipeline
- Database consistency under various failure scenarios

### Running Specific Test Scenarios

```bash
# Test concurrent stock management
php artisan test --filter="stock"

# Test webhook functionality  
php artisan test --filter="webhook"

# Test hold expiry
php artisan test --filter="expired"

# Test idempotency
php artisan test --filter="duplicate"
```

All tests are designed to run in isolation and can be executed in parallel without side effects, ensuring reliable verification of the system's concurrency and consistency guarantees.
