<?php
// ============================================================
// api/orders/invoice.php — Printable Order Invoice Generator
// ============================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();
header('Content-Type: text/html; charset=utf-8');

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) die("Order ID required");

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) die("Order not found");
if ($user['role'] !== 'admin' && (int)$order['user_id'] !== (int)$user['user_id']) {
    die("Access denied");
}

$itemsStmt = $pdo->prepare('SELECT oi.*, p.name AS product_name, p.scientific_name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice #BB-<?= $order['id'] ?> · Bloom & Bonsai</title>
<style>
  body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; margin: 0; padding: 40px; background: #fff; }
  .inv-box { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 30px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); }
  .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .header-table td { vertical-align: top; }
  .logo { font-size: 24px; font-weight: bold; color: #17482f; }
  .sub { color: #666; font-size: 13px; margin-top: 4px; }
  .title { text-align: right; font-size: 28px; font-weight: 300; color: #17482f; }
  .meta { font-size: 13px; color: #555; margin-top: 8px; }
  .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #faf9f5; border-radius: 8px; padding: 15px; }
  .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  .items-table th { background: #17482f; color: #fff; text-align: left; padding: 10px 12px; font-size: 13px; }
  .items-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
  .totals { width: 300px; margin-left: auto; margin-top: 20px; font-size: 14px; }
  .totals td { padding: 6px 0; }
  .btn-print { background: #17482f; color: #fff; border: none; padding: 10px 24px; border-radius: 20px; font-weight: bold; cursor: pointer; float: right; }
  @media print { .btn-print { display: none; } }
</style>
</head>
<body>
<div class="inv-box">
  <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
  <table class="header-table">
    <tr>
      <td>
        <div class="logo">🌱 Bloom & Bonsai</div>
        <div class="sub">Botanical Plant Nursery & Sanctuary</div>
        <div class="meta">Colombo & Gampaha, Sri Lanka | support@bloombonsai.lk</div>
      </td>
      <td>
        <div class="title">INVOICE</div>
        <div class="meta">
          <b>Invoice No:</b> #BB-<?= $order['id'] ?><br>
          <b>Date:</b> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?><br>
          <b>Status:</b> <?= strtoupper($order['status']) ?>
        </div>
      </td>
    </tr>
  </table>

  <div style="background:#faf8f3;border:1px solid #e7e2d3;padding:15px 20px;border-radius:10px;margin-bottom:20px;">
    <b style="color:#17482f;">Customer & Shipping Address:</b><br>
    <b>Name:</b> <?= htmlspecialchars($order['customer_name'] ?: 'Customer') ?><br>
    <b>Phone:</b> <?= htmlspecialchars($order['phone'] ?: 'N/A') ?><br>
    <b>Shipping City:</b> <?= htmlspecialchars($order['city'] . ' (' . $order['pincode'] . ')') ?><br>
    <b>Payment Method:</b> <?= strtoupper($order['payment_method']) ?>
  </div>

  <table class="items-table">
    <thead>
      <tr>
        <th>Item Description</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th style="text-align:right;">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $itemsSubtotal = 0;
      foreach ($items as $it): 
        $lineTotal = $it['price_at_purchase'] * $it['quantity'];
        $itemsSubtotal += $lineTotal;
      ?>
      <tr>
        <td>
          <b><?= htmlspecialchars($it['product_name']) ?></b>
          <?php if (!empty($it['scientific_name'])): ?>
            <br><small style="color:#777;font-style:italic;"><?= htmlspecialchars($it['scientific_name']) ?></small>
          <?php endif; ?>
        </td>
        <td><?= $it['quantity'] ?></td>
        <td>Rs. <?= number_format($it['price_at_purchase'], 2) ?></td>
        <td style="text-align:right;">Rs. <?= number_format($lineTotal, 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php
  $discountAmount = floatval($order['discount_amount'] ?? 0);
  $couponCode = trim($order['coupon_code'] ?? '');
  $shippingFee = ($itemsSubtotal >= 10000) ? 0 : 350;
  ?>

  <table class="totals">
    <tr>
      <td>Items Subtotal:</td>
      <td style="text-align:right;">Rs. <?= number_format($itemsSubtotal, 2) ?></td>
    </tr>
    <?php if ($discountAmount > 0): ?>
    <tr style="color:#c0392b;font-weight:600;">
      <td>Promo Discount (<?= htmlspecialchars($couponCode) ?>):</td>
      <td style="text-align:right;">-Rs. <?= number_format($discountAmount, 2) ?></td>
    </tr>
    <?php endif; ?>
    <tr>
      <td>Delivery &amp; Handling:</td>
      <td style="text-align:right;color:<?= $shippingFee == 0 ? '#2e7d32' : '#222' ?>;font-weight:<?= $shippingFee == 0 ? 'bold' : 'normal' ?>;">
        <?= $shippingFee == 0 ? 'FREE 🚚 (Over Rs. 10,000)' : 'Rs. ' . number_format($shippingFee, 2) ?>
      </td>
    </tr>
    <tr style="border-top:2px solid #17482f;font-weight:bold;font-size:16px;color:#17482f;">
      <td style="padding-top:10px;">Grand Total:</td>
      <td style="text-align:right;padding-top:10px;">Rs. <?= number_format($order['total'], 2) ?></td>
    </tr>
  </table>

  <div style="margin-top:40px;text-align:center;color:#888;font-size:12px;border-top:1px solid #eee;padding-top:20px;">
    Thank you for growing with Bloom & Bonsai! 🌿 Each plant includes a 4-week automated care guide in your dashboard.
  </div>
</div>
</body>
</html>
