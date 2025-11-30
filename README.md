# Flash_Sale – Full Project Documentation

This document provides a complete explanation of the **Flash_Sale** project, including its purpose, architecture, API endpoints, environment setup, and how to run automated tests.

---

## Overview

-   **Project:** A lightweight flash sale system for product reservation, order creation, and payment processing.
-   **Technologies:** Laravel (PHP), SQLite (testing), PHPUnit.

---

## System Functional Goals

-   Create products with initial stock.
-   Reserve a quantity of a product temporarily (**Hold**) with expiration.
-   Create an **Order** from a valid Hold.
-   Receive **payment webhooks** and update order status.
-   Handle idempotency to prevent duplicated webhook processing.
-   Restore product stock automatically if the payment fails.

---

## Important Directories

### app/Models

-   Product
-   Hold
-   Order
-   Payment
-   User

### routes/api.php

-   Holds API
-   Orders API
-   Payment Webhook API

### tests/Feature

-   Integration tests such as `PaymentTest.php`

---

## Database Configuration for Testing

phpunit.xml:

```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

tests/TestCase.php:

```
use RefreshDatabase;
```

This ensures:

-   Migrations run before every test.
-   A fresh in-memory SQLite database is used.

---

## Running the Project Locally

### 1. Install dependencies

```
composer install
```

### 2. Create `.env`

```
cp .env.example .env
```

Update DB settings.

### 3. Run migrations

```
php artisan migrate
```

### 4. Start server

```
php artisan serve
```

---

## Running Tests

```
php artisan test
```

---

## Main API Endpoints

### 1. Create Hold

POST /api/holds

-   product_id
-   qty
    Returns: hold_id (UUID)

### 2. Create Order

POST /api/orders

-   hold_id
    Returns: order_id (public_id)

### 3. Payment Webhook

POST /api/payments/webhook

-   idempotency_key
-   order_id
-   status
-   additional fields

Handles repeated webhook calls using idempotency.

---

## Database Diagram

<img src="./public/build/assets/image/desgin-db.png" alt="desgin data base" width="100%" />

Diagram file: `docs/db_design.svg`

---

## Extensions Available

-   Add curl examples
-   Add Postman collection
-   Add Sequence Diagram
-   Add workflow description
