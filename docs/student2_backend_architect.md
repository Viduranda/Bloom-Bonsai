# 🎓 Student 2: Backend Developer & Database Architect

**Role:** MySQL Relational Database Architecture, RESTful PHP APIs, JWT Security, & Order Processing  
**Project:** Bloom & Bonsai — Luxury Botanical E-Commerce Platform  

---

## 📂 1. Assigned Modules & File Ownership

| File / Component | Type | Responsibility |
| :--- | :--- | :--- |
| `database_schema.sql` | SQL Schema | 12-table relational database design (`products`, `categories`, `users`, `orders`, `order_items`, `cart_items`, `coupons`, `reviews`, etc.) |
| `api/config.php` | PHP Core | Central PDO database connection pool, global response helper (`respond`), and environment loader |
| `api/auth.php` | PHP Security | JWT Token generation (`createToken`), verification (`verifyToken`), signature HMAC-SHA256, and header/cookie parser |
| `api/cart/*` | PHP API | Shopping cart endpoints (`add.php`, `get.php`, `update.php`, `delete.php`) |
| `api/orders/*` | PHP API & PDF | Order creation pipeline (`create.php`), stock isolation transaction, and FPDF Invoice Generator (`invoice.php`) |
| `api/coupons/validate.php` | PHP API | Promo code validator & discount calculation engine |

---

## 🗄️ 2. Technical Contributions & Feature Implementations

### A. Relational Database Schema (`database_schema.sql`)
* Architected a 12-table normalized relational schema:
  * `products` table storing pricing, stock, care parameters (water, light, soil), scientific names, and category foreign keys.
  * `orders` & `order_items` tables linking users to purchased items with snapshot pricing at checkout.
  * `cart_items` table managing persistent customer shopping sessions.
  * `coupons` table supporting percentage (`percent`) and fixed (`fixed`) discount rules with expiry thresholds.

### B. Security & JWT Authentication Engine (`api/auth.php`)
* **Password Hashing:** Enforced `PASSWORD_BCRYPT` for user credential security.
* **Stateless JWT Tokens:** Built custom HMAC-SHA256 JWT builder (`createToken`) generating signed tokens containing `user_id`, `role`, `iat`, and `exp`.
* **Multi-Header Token Extraction (`getUserFromToken`):** Supports `Authorization: Bearer <token>`, HTTP server headers, cookies, and POST fallback.

### C. Cart & Transactional Order Checkout (`api/orders/create.php`)
* **Stock Lock & Concurrency Control:** Executes PDO transactions (`beginTransaction`, `FOR UPDATE`) to lock stock rows during order placement and prevent race conditions.
* **Automatic Stock Deduction:** Deducts ordered quantities automatically from product inventory upon checkout confirmation.

### D. Automated PDF Invoice Generator (`api/orders/invoice.php`)
* Integrated FPDF engine to auto-generate official downloadable PDF invoices containing order summary, customer shipping address, Sri Lankan Rupee totals, and itemized receipts.

---

## 🔍 3. Verification & Key Code Snippets

```sql
-- Core Orders Relational Schema
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `coupon_code` VARCHAR(50) DEFAULT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `status` VARCHAR(30) DEFAULT 'confirmed',
  `shipping_address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'cod',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

```php
// Signed JWT Token Verification Engine (api/auth.php)
function verifyToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$header, $payload, $signature] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', "$header.$payload", jwtSecret(), true));
    if (!hash_equals($expected, $signature)) return null;
    $data = json_decode(base64UrlDecode($payload), true);
    if (!$data || ($data['exp'] ?? 0) < time()) return null;
    return $data;
}
```
