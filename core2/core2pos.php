<?php
// ═══════════════════════════════════════════════════════════════════════════
// POS System — index.php
// Single-file Point of Sale integrated with the "system" central database.
// Uses the shared sidebar/styles.css design from the parent system.
// ═══════════════════════════════════════════════════════════════════════════

// ── Database Connection (inline db.php) ──────────────────────────────────────
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ── Auto-create POS tables if they don't exist in the system database ────────
// These tables are POS-specific and won't conflict with other system modules.
$conn->query("CREATE TABLE IF NOT EXISTS `cashier_sessions` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cashier_name`  VARCHAR(60)     NOT NULL,
    `login_time`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `logout_time`   DATETIME        DEFAULT NULL,
    `starting_cash` DECIMAL(12,2)   NOT NULL DEFAULT 2000.00,
    `total_sales`   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `ending_cash`   DECIMAL(12,2)   DEFAULT NULL,
    `status`        ENUM('active','closed') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`id`),
    INDEX `idx_cashier` (`cashier_name`),
    INDEX `idx_status`  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `sales` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `transaction_ref` VARCHAR(30)     NOT NULL UNIQUE,
    `session_id`      INT UNSIGNED    NOT NULL,
    `cashier_name`    VARCHAR(60)     NOT NULL,
    `subtotal`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `tax_amount`      DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `total_amount`    DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `cash_received`   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `change_amount`   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `status`          ENUM('completed','voided') NOT NULL DEFAULT 'completed',
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_session`    (`session_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `sales_items` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `sale_id`      INT UNSIGNED    NOT NULL,
    `product_id`   INT UNSIGNED    NOT NULL,
    `product_name` VARCHAR(200)    NOT NULL,
    `barcode`      VARCHAR(100)    NOT NULL,
    `quantity`     INT             NOT NULL DEFAULT 1,
    `unit_price`   DECIMAL(10,2)   NOT NULL,
    `line_total`   DECIMAL(12,2)   NOT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_sale_id` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `session_id`   INT UNSIGNED    DEFAULT NULL,
    `cashier_name` VARCHAR(60)     DEFAULT NULL,
    `action`       VARCHAR(100)    NOT NULL,
    `description`  TEXT,
    `ip_address`   VARCHAR(45)     DEFAULT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_action`     (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── AJAX API Handler ──────────────────────────────────────────────────────────
if (isset($_GET['action'])) {
  header('Content-Type: application/json; charset=utf-8');
  // Prevent any accidental HTML output from being mixed in
  ob_clean();

  $action = $_GET['action'];

  // Helper: send JSON and exit
  function respond(bool $ok, mixed $data, int $status = 200): void
  {
    http_response_code($status);
    echo json_encode(
      $ok
        ? ['success' => true,  'data'  => $data]
        : ['success' => false, 'error' => $data]
    );
    exit;
  }

  // ── GET: Look up product by barcode ───────────────────────────────────
  if ($action === 'get_product') {
    $barcode = trim($_GET['barcode'] ?? '');
    if (!$barcode) respond(false, 'Barcode is required.', 400);

    $stmt = $conn->prepare("
            SELECT p.id AS product_id, p.barcode, p.sku, p.product_name,
                   p.description, p.store_price AS price, p.status,
                   pc.category_name AS category,
                   COALESCE(SUM(i.store_quantity), 0) AS stock
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id
            LEFT JOIN product_categories pc ON pc.id = p.category_id
            WHERE p.barcode = ? AND p.status = 'active'
            GROUP BY p.id LIMIT 1
        ");
    $stmt->bind_param('s', $barcode);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) respond(false, 'Product not found for barcode: ' . htmlspecialchars($barcode), 404);
    $product['product_id'] = (int)$product['product_id'];
    $product['price']      = (float)$product['price'];
    $product['stock']      = (int)$product['stock'];
    respond(true, $product);
  }

  // ── GET: Search products ──────────────────────────────────────────────
  if ($action === 'search_products') {
    $q     = '%' . trim($_GET['q'] ?? '') . '%';
    $limit = min(30, max(1, (int)($_GET['limit'] ?? 15)));

    $stmt = $conn->prepare("
            SELECT p.id AS product_id, p.barcode, p.product_name,
                   p.store_price AS price, pc.category_name AS category,
                   COALESCE(SUM(i.store_quantity), 0) AS stock
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id
            LEFT JOIN product_categories pc ON pc.id = p.category_id
            WHERE p.status = 'active' AND (p.product_name LIKE ? OR p.barcode LIKE ?)
            GROUP BY p.id ORDER BY p.product_name ASC LIMIT ?
        ");
    $stmt->bind_param('ssi', $q, $q, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($rows as &$r) {
      $r['product_id'] = (int)$r['product_id'];
      $r['price']      = (float)$r['price'];
      $r['stock']      = (int)$r['stock'];
    }
    respond(true, $rows);
  }

  // ── GET: All products ─────────────────────────────────────────────────
  if ($action === 'all_products') {
    $result = $conn->query("
            SELECT p.id AS product_id, p.barcode, p.sku, p.product_name,
                   p.store_price AS price, p.status,
                   pc.category_name AS category,
                   COALESCE(SUM(i.store_quantity), 0) AS stock
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id
            LEFT JOIN product_categories pc ON pc.id = p.category_id
            WHERE p.status = 'active'
            GROUP BY p.id ORDER BY pc.category_name, p.product_name
        ");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$r) {
      $r['product_id'] = (int)$r['product_id'];
      $r['price']      = (float)$r['price'];
      $r['stock']      = (int)$r['stock'];
    }
    respond(true, $rows);
  }

  // ── POST: Start cashier session ───────────────────────────────────────
  if ($action === 'start_session') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? [];
    $name = strtoupper(trim($body['cashier_name'] ?? ''));

    if (!$name || !preg_match('/^[A-Z0-9_\-\s]{1,40}$/', $name))
      respond(false, 'Invalid cashier name. Use letters, numbers, underscores only.', 400);

    // Close any existing active session for this cashier
    $stmt = $conn->prepare("UPDATE cashier_sessions SET status='closed', logout_time=NOW() WHERE cashier_name=? AND status='active'");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->close();

    // Create new session with ₱2,000 starting cash
    $stmt = $conn->prepare("INSERT INTO cashier_sessions (cashier_name, starting_cash) VALUES (?, 2000.00)");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $sessionId = (int)$conn->insert_id;
    $stmt->close();

    // Log the login action
    $desc = "Session started with ₱2,000 starting cash";
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare("INSERT INTO activity_logs (session_id, cashier_name, action, description, ip_address) VALUES (?, ?, 'CASHIER_LOGIN', ?, ?)");
    $stmt->bind_param('isss', $sessionId, $name, $desc, $ip);
    $stmt->execute();
    $stmt->close();

    $session = $conn->query("SELECT * FROM cashier_sessions WHERE id = $sessionId")->fetch_assoc();
    respond(true, $session, 201);
  }

  // ── GET: Active session ───────────────────────────────────────────────
  if ($action === 'active_session') {
    $session = $conn->query("SELECT * FROM cashier_sessions WHERE status='active' ORDER BY login_time DESC LIMIT 1")->fetch_assoc();
    respond(true, $session ?: null);
  }

  // ── POST: End session ─────────────────────────────────────────────────
  if ($action === 'end_session') {
    $raw       = file_get_contents('php://input');
    $body      = json_decode($raw, true) ?? [];
    $sessionId = (int)($body['session_id'] ?? 0);
    if (!$sessionId) respond(false, 'session_id required.', 400);

    $session = $conn->query("SELECT * FROM cashier_sessions WHERE id=$sessionId AND status='active'")->fetch_assoc();
    if (!$session) respond(false, 'No active session found.', 404);

    $totalSales = (float)$conn->query("SELECT COALESCE(SUM(total_amount),0) t FROM sales WHERE session_id=$sessionId AND status='completed'")->fetch_assoc()['t'];
    $endingCash = (float)$session['starting_cash'] + $totalSales;

    $stmt = $conn->prepare("UPDATE cashier_sessions SET status='closed', logout_time=NOW(), total_sales=?, ending_cash=? WHERE id=?");
    $stmt->bind_param('ddi', $totalSales, $endingCash, $sessionId);
    $stmt->execute();
    $stmt->close();

    $cashierName = $session['cashier_name'];
    $desc = "Session ended. Total sales: ₱" . number_format($totalSales, 2);
    $stmt = $conn->prepare("INSERT INTO activity_logs (session_id, cashier_name, action, description) VALUES (?, ?, 'CASHIER_LOGOUT', ?)");
    $stmt->bind_param('iss', $sessionId, $cashierName, $desc);
    $stmt->execute();
    $stmt->close();

    respond(true, ['total_sales' => $totalSales, 'ending_cash' => $endingCash]);
  }

  // ── POST: Checkout ────────────────────────────────────────────────────
  if ($action === 'checkout') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? [];

    $sessionId   = (int)  ($body['session_id']    ?? 0);
    $cashierName = trim($body['cashier_name']   ?? '');
    $cashRecv    = (float) ($body['cash_received'] ?? 0);
    $items       = $body['items'] ?? [];

    if (!$sessionId)   respond(false, 'session_id required.', 400);
    if (!$cashierName) respond(false, 'cashier_name required.', 400);
    if (empty($items)) respond(false, 'Cart is empty.', 400);

    $sessCheck = $conn->query("SELECT id FROM cashier_sessions WHERE id=$sessionId AND status='active'")->fetch_assoc();
    if (!$sessCheck) respond(false, 'No active session.', 403);

    // Validate all items
    $validated = [];
    foreach ($items as $idx => $item) {
      $pid   = (int)  ($item['product_id']   ?? 0);
      $qty   = (int)  ($item['quantity']     ?? 0);
      $price = (float)($item['price']        ?? 0);
      $pname = trim($item['product_name']  ?? '');
      $bc    = trim($item['barcode']       ?? '');
      if ($pid <= 0 || $qty <= 0 || $price < 0)
        respond(false, "Invalid item at index $idx.", 400);
      $validated[] = compact('pid', 'qty', 'price', 'pname', 'bc');
    }

    $conn->begin_transaction();
    try {
      // Validate stock for each item
      foreach ($validated as $v) {
        $stmt = $conn->prepare("SELECT COALESCE(SUM(store_quantity),0) AS total FROM inventory WHERE product_id=? FOR UPDATE");
        $stmt->bind_param('i', $v['pid']);
        $stmt->execute();
        $totalStock = (int)$stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();
        if ($totalStock < $v['qty']) {
          $conn->rollback();
          respond(false, "Insufficient stock for \"{$v['pname']}\". Available: $totalStock, requested: {$v['qty']}.", 422);
        }
      }

      $subtotal    = array_sum(array_map(fn($v) => $v['price'] * $v['qty'], $validated));
      $totalAmount = round($subtotal, 2);
      $change      = round($cashRecv - $totalAmount, 2);

      if ($cashRecv < $totalAmount) {
        $conn->rollback();
        respond(false, 'Cash received is less than total amount.', 400);
      }

      $txnRef = 'TXN-' . strtoupper(date('Ymd')) . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
      $zero   = 0.00;

      // Insert sale header
      $stmt = $conn->prepare("INSERT INTO sales (transaction_ref, session_id, cashier_name, subtotal, tax_amount, total_amount, cash_received, change_amount) VALUES (?,?,?,?,?,?,?,?)");
      $stmt->bind_param('sissdddd', $txnRef, $sessionId, $cashierName, $subtotal, $zero, $totalAmount, $cashRecv, $change);
      $stmt->execute();
      $saleId = (int)$conn->insert_id;
      $stmt->close();

      // Insert each line item and deduct inventory (FIFO)
      $receiptItems = [];
      foreach ($validated as $v) {
        $lineTotal = round($v['price'] * $v['qty'], 2);

        // Insert sales_items row
        $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, product_name, barcode, quantity, unit_price, line_total) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('iissids', $saleId, $v['pid'], $v['pname'], $v['bc'], $v['qty'], $v['price'], $lineTotal);
        $stmt->execute();
        $stmt->close();

        // FIFO: deduct store_quantity batch by batch (oldest first)
        $bStmt = $conn->prepare("SELECT id, store_quantity FROM inventory WHERE product_id=? AND store_quantity>0 ORDER BY created_at ASC FOR UPDATE");
        $bStmt->bind_param('i', $v['pid']);
        $bStmt->execute();
        $batches   = $bStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $bStmt->close();

        $remaining = $v['qty'];
        foreach ($batches as $batch) {
          if ($remaining <= 0) break;
          $deduct    = min($remaining, (int)$batch['store_quantity']);
          $remaining -= $deduct;
          $uStmt = $conn->prepare("UPDATE inventory SET store_quantity = store_quantity - ?, updated_at = NOW() WHERE id = ?");
          $uStmt->bind_param('ii', $deduct, $batch['id']);
          $uStmt->execute();
          $uStmt->close();
        }

        $receiptItems[] = ['product_name' => $v['pname'], 'quantity' => $v['qty'], 'unit_price' => $v['price'], 'line_total' => $lineTotal];
      }

      // Update session running total
      $stmt = $conn->prepare("UPDATE cashier_sessions SET total_sales = total_sales + ? WHERE id = ?");
      $stmt->bind_param('di', $totalAmount, $sessionId);
      $stmt->execute();
      $stmt->close();

      $conn->commit();

      // Log activity
      $desc = "Ref: $txnRef | Total: ₱" . number_format($totalAmount, 2) . " | Items: " . count($validated);
      $stmt = $conn->prepare("INSERT INTO activity_logs (session_id, cashier_name, action, description) VALUES (?, ?, 'SALE_COMPLETED', ?)");
      $stmt->bind_param('iss', $sessionId, $cashierName, $desc);
      $stmt->execute();
      $stmt->close();

      respond(true, [
        'sale_id' => $saleId,
        'transaction_ref' => $txnRef,
        'subtotal' => $subtotal,
        'tax_amount' => 0.00,
        'total_amount' => $totalAmount,
        'cash_received' => $cashRecv,
        'change_amount' => $change,
        'item_count' => count($validated),
        'cashier_name' => $cashierName,
        'timestamp' => date('Y-m-d H:i:s'),
        'items' => $receiptItems,
      ], 201);
    } catch (Exception $e) {
      $conn->rollback();
      respond(false, 'Transaction failed: ' . $e->getMessage(), 500);
    }
  }

  // ── GET: Reports summary ──────────────────────────────────────────────
  if ($action === 'reports_summary') {
    $today = $conn->query("SELECT COALESCE(SUM(total_amount),0) t FROM sales WHERE DATE(created_at)=CURDATE() AND status='completed'")->fetch_assoc()['t'];
    $week  = $conn->query("SELECT COALESCE(SUM(total_amount),0) t FROM sales WHERE YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1) AND status='completed'")->fetch_assoc()['t'];
    $month = $conn->query("SELECT COALESCE(SUM(total_amount),0) t FROM sales WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE()) AND status='completed'")->fetch_assoc()['t'];
    $txns  = $conn->query("SELECT COUNT(*) c FROM sales WHERE DATE(created_at)=CURDATE() AND status='completed'")->fetch_assoc()['c'];
    $chart = $conn->query("SELECT DATE(created_at) as date, SUM(total_amount) as total FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status='completed' GROUP BY DATE(created_at) ORDER BY date ASC")->fetch_all(MYSQLI_ASSOC);
    respond(true, compact('today', 'week', 'month', 'txns', 'chart'));
  }

  // ── GET: Recent sales ─────────────────────────────────────────────────
  if ($action === 'recent_sales') {
    $limit  = min(50, (int)($_GET['limit'] ?? 20));
    $result = $conn->query("SELECT id, transaction_ref, cashier_name, total_amount, cash_received, change_amount, status, created_at FROM sales ORDER BY created_at DESC LIMIT $limit");
    respond(true, $result->fetch_all(MYSQLI_ASSOC));
  }

  // ── GET: Sale detail ──────────────────────────────────────────────────
  if ($action === 'sale_detail') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) respond(false, 'id required.', 400);
    $sale = $conn->query("SELECT * FROM sales WHERE id=$id")->fetch_assoc();
    if (!$sale) respond(false, 'Sale not found.', 404);
    $sale['items'] = $conn->query("SELECT * FROM sales_items WHERE sale_id=$id")->fetch_all(MYSQLI_ASSOC);
    respond(true, $sale);
  }

  // ── GET: Sessions ─────────────────────────────────────────────────────
  if ($action === 'sessions') {
    respond(true, $conn->query("SELECT * FROM cashier_sessions ORDER BY login_time DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC));
  }

  // ── GET: Activity log ─────────────────────────────────────────────────
  if ($action === 'activity_log') {
    $limit = min(200, (int)($_GET['limit'] ?? 50));
    respond(true, $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT $limit")->fetch_all(MYSQLI_ASSOC));
  }

  // ── GET: Export CSV ───────────────────────────────────────────────────
  if ($action === 'export_csv') {
    $range = $_GET['range'] ?? 'today';
    $from  = $conn->real_escape_string($_GET['from'] ?? date('Y-m-d'));
    $to    = $conn->real_escape_string($_GET['to']   ?? date('Y-m-d'));

    $where = match ($range) {
      'week'   => "YEARWEEK(s.created_at,1)=YEARWEEK(CURDATE(),1)",
      'month'  => "YEAR(s.created_at)=YEAR(CURDATE()) AND MONTH(s.created_at)=MONTH(CURDATE())",
      'custom' => "DATE(s.created_at) BETWEEN '$from' AND '$to'",
      default  => "DATE(s.created_at)=CURDATE()",
    };

    $result = $conn->query("SELECT s.transaction_ref, s.cashier_name, si.product_name, si.barcode, si.quantity, si.unit_price, si.line_total, s.total_amount, s.created_at FROM sales s JOIN sales_items si ON si.sale_id=s.id WHERE s.status='completed' AND $where ORDER BY s.created_at DESC");

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_' . $range . '_' . date('Ymd') . '.csv"');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_' . $range . '_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Transaction Ref', 'Cashier', 'Product', 'Barcode', 'Qty', 'Unit Price', 'Line Total', 'Sale Total', 'Date']);
    while ($row = $result->fetch_assoc()) fputcsv($out, array_values($row));
    fclose($out);
    exit;
  }

  respond(false, 'Unknown action: ' . htmlspecialchars($action), 404);
}

// Close DB — not needed for the HTML output below, but clean practice
// $conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>POS Terminal — POSMart</title>

  <!-- Shared system stylesheet (same as all other modules) -->
  <link rel="stylesheet" href="../includes/styles.css" />

  <style>
    /* ── POS-specific overrides on top of styles.css ── */

    /* Full-height layout like other system pages */
    body {
      overflow: hidden;
    }

    /* POS Terminal page: two-column split layout */
    #page-pos {
      display: none;
      height: calc(100vh - 52px);
      overflow: hidden;
    }

    #page-pos.active {
      display: grid;
      grid-template-columns: 1fr 360px;
    }

    /* Regular pages scroll normally */
    .page {
      display: none;
      height: calc(100vh - 52px);
      overflow-y: auto;
      padding: 24px 28px;
    }

    .page.active {
      display: block;
    }

    .page::-webkit-scrollbar {
      width: 5px;
    }

    .page::-webkit-scrollbar-thumb {
      background: #dee2e6;
      border-radius: 4px;
    }

    /* ── Left POS panel ── */
    .pos-left {
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border-right: 1px solid #e3e8f0;
      background: #f4f6fb;
    }

    .scan-bar {
      padding: 14px 18px;
      background: #fff;
      border-bottom: 1px solid #e3e8f0;
      flex-shrink: 0;
    }

    .scan-bar-label {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 7px;
    }

    .scan-bar-row {
      display: flex;
      gap: 8px;
    }

    #barcode-input {
      flex: 1;
      height: 44px;
      background: #f4f6fb;
      border: 1.5px solid #e3e8f0;
      border-radius: 8px;
      color: #111827;
      font-family: 'Courier New', monospace;
      font-size: 15px;
      font-weight: 600;
      padding: 0 14px;
      letter-spacing: 1.5px;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s;
    }

    #barcode-input:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.12);
    }

    #barcode-input.found {
      border-color: #28a745;
      animation: flash-ok .35s ease;
    }

    #barcode-input.not-found {
      border-color: #dc3545;
      animation: flash-err .35s ease;
    }

    @keyframes flash-ok {
      50% {
        background: rgba(40, 167, 69, 0.06);
      }
    }

    @keyframes flash-err {
      50% {
        background: rgba(220, 53, 69, 0.06);
      }
    }

    .btn-scan {
      height: 44px;
      padding: 0 18px;
      background: #1e2532;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'Courier New', monospace;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1px;
      cursor: pointer;
      transition: background .15s;
      white-space: nowrap;
    }

    .btn-scan:hover {
      background: #2c3e50;
    }

    /* Search bar */
    .search-bar {
      padding: 10px 18px;
      background: #f8f9fa;
      border-bottom: 1px solid #e3e8f0;
      flex-shrink: 0;
    }

    .search-container {
      position: relative;
    }

    #product-search {
      width: 100%;
      height: 36px;
      background: #fff;
      border: 1px solid #e3e8f0;
      border-radius: 8px;
      padding: 0 12px 0 32px;
      font-size: 13px;
      color: #111827;
      outline: none;
      transition: border-color .15s;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: 10px center;
    }

    #product-search:focus {
      border-color: #007bff;
    }

    #search-results {
      position: absolute;
      top: 36px;
      left: 0;
      right: 0;
      z-index: 50;
      background: #fff;
      border: 1px solid #e3e8f0;
      border-radius: 0 0 8px 8px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      max-height: 220px;
      overflow-y: auto;
      display: none;
    }

    .search-result-item {
      padding: 10px 14px;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #f1f3f4;
      font-size: 13px;
      transition: background .12s;
    }

    .search-result-item:last-child {
      border-bottom: none;
    }

    .search-result-item:hover {
      background: #e8f0fe;
    }

    .sr-name {
      font-weight: 600;
      color: #111827;
    }

    .sr-meta {
      font-size: 11px;
      color: #6b7280;
      font-family: monospace;
    }

    .sr-price {
      color: #007bff;
      font-weight: 700;
      font-family: monospace;
    }

    /* Scan status bar */
    #scan-status {
      padding: 5px 18px;
      min-height: 26px;
      font-size: 11px;
      font-family: monospace;
      background: #f8f9fa;
      border-bottom: 1px solid #e3e8f0;
      flex-shrink: 0;
    }

    #scan-status.ok {
      color: #28a745;
    }

    #scan-status.err {
      color: #dc3545;
    }

    #scan-status.load {
      color: #f59e0b;
    }

    /* Cart */
    .cart-area {
      flex: 1;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .cart-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 18px;
      border-bottom: 1px solid #e3e8f0;
      background: #fff;
      flex-shrink: 0;
    }

    .cart-title-text {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #6b7280;
    }

    #cart-count {
      font-family: monospace;
      font-size: 10px;
      background: rgba(0, 123, 255, 0.07);
      color: #007bff;
      padding: 2px 8px;
      border-radius: 20px;
      border: 1px solid rgba(0, 123, 255, 0.2);
    }

    .cart-scroll {
      flex: 1;
      overflow-y: auto;
      background: #fff;
    }

    .cart-scroll::-webkit-scrollbar {
      width: 4px;
    }

    .cart-scroll::-webkit-scrollbar-thumb {
      background: #dee2e6;
    }

    #cart-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 20px;
      color: #9ca3af;
      font-size: 13px;
      gap: 10px;
    }

    #cart-empty .empty-ico {
      font-size: 40px;
      opacity: 0.3;
    }

    table#cart-table {
      width: 100%;
      border-collapse: collapse;
    }

    #cart-table thead th {
      position: sticky;
      top: 0;
      z-index: 2;
      background: #f8f9fa;
      padding: 9px 14px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #9ca3af;
      border-bottom: 1px solid #e3e8f0;
      text-align: left;
    }

    #cart-table thead th:nth-child(2),
    #cart-table thead th:nth-child(4) {
      text-align: right;
    }

    #cart-table tbody tr {
      border-bottom: 1px solid #f1f3f4;
      transition: background .12s;
    }

    #cart-table tbody tr:hover {
      background: #f8f9fa;
    }

    #cart-table td {
      padding: 11px 14px;
      vertical-align: middle;
    }

    .cart-pname {
      font-weight: 600;
      font-size: 13px;
      color: #111827;
    }

    .cart-pcode {
      font-family: monospace;
      font-size: 10px;
      color: #9ca3af;
      margin-top: 1px;
    }

    .cart-price-cell {
      font-family: monospace;
      font-size: 12px;
      color: #374151;
      text-align: right;
      white-space: nowrap;
    }

    .cart-total-cell {
      font-family: monospace;
      font-size: 13px;
      font-weight: 700;
      color: #007bff;
      text-align: right;
      white-space: nowrap;
    }

    .qty-controls {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 6px;
    }

    .qty-btn {
      width: 24px;
      height: 24px;
      border-radius: 6px;
      border: 1px solid #dee2e6;
      background: #f8f9fa;
      color: #6b7280;
      font-size: 13px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .12s;
      line-height: 1;
    }

    .qty-btn:hover {
      background: rgba(0, 123, 255, 0.07);
      color: #007bff;
      border-color: rgba(0, 123, 255, 0.3);
    }

    .qty-btn.del {
      color: #dc3545;
      border-color: rgba(220, 53, 69, 0.2);
    }

    .qty-btn.del:hover {
      background: rgba(220, 53, 69, 0.07);
    }

    .qty-val {
      font-family: monospace;
      font-size: 13px;
      font-weight: 700;
      min-width: 22px;
      text-align: center;
      color: #111827;
    }

    /* ── Right POS panel ── */
    .pos-right {
      display: flex;
      flex-direction: column;
      background: #fff;
      overflow: hidden;
    }

    .pos-right-scroll {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
    }

    .pos-right-scroll::-webkit-scrollbar {
      width: 4px;
    }

    .summary-block {
      background: #f4f6fb;
      border: 1px solid #e3e8f0;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 12px;
    }

    .summary-block-hd {
      padding: 10px 14px;
      border-bottom: 1px solid #e3e8f0;
      font-size: 9px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #9ca3af;
    }

    .sum-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 14px;
      font-size: 13px;
    }

    .sum-row .lbl {
      color: #6b7280;
    }

    .sum-row .val {
      font-family: monospace;
      font-weight: 600;
      color: #111827;
    }

    .sum-row.grand {
      border-top: 1px solid #e3e8f0;
      padding: 13px 14px;
      margin-top: 4px;
    }

    .sum-row.grand .lbl {
      font-size: 14px;
      font-weight: 700;
      color: #111827;
    }

    .sum-row.grand .val {
      font-size: 22px;
      font-weight: 800;
      color: #007bff;
      letter-spacing: -.5px;
    }

    .cash-block {
      background: #f4f6fb;
      border: 1px solid #e3e8f0;
      border-radius: 12px;
      padding: 14px;
      margin-bottom: 12px;
    }

    .cash-block label {
      display: block;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 6px;
    }

    #cash-received {
      width: 100%;
      height: 44px;
      background: #fff;
      border: 1.5px solid #e3e8f0;
      border-radius: 8px;
      font-family: monospace;
      font-size: 18px;
      font-weight: 700;
      color: #111827;
      padding: 0 12px;
      text-align: right;
      outline: none;
      transition: border-color .15s;
    }

    #cash-received:focus {
      border-color: #007bff;
    }

    .change-display {
      margin-top: 8px;
      padding: 10px 12px;
      background: rgba(40, 167, 69, 0.07);
      border: 1px solid rgba(40, 167, 69, 0.2);
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .change-display.negative {
      background: rgba(220, 53, 69, 0.07);
      border-color: rgba(220, 53, 69, 0.2);
    }

    .change-label {
      font-size: 11px;
      font-weight: 600;
      color: #28a745;
    }

    .change-display.negative .change-label {
      color: #dc3545;
    }

    .change-value {
      font-family: monospace;
      font-size: 18px;
      font-weight: 800;
      color: #28a745;
    }

    .change-display.negative .change-value {
      color: #dc3545;
    }

    .pos-actions {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .btn-checkout {
      width: 100%;
      height: 52px;
      background: #1e2532;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: background .15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .btn-checkout:hover:not(:disabled) {
      background: #2c3e50;
    }

    .btn-checkout:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }

    .btn-clear {
      width: 100%;
      height: 40px;
      background: transparent;
      color: #dc3545;
      border: 1px solid rgba(220, 53, 69, 0.3);
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: all .15s;
    }

    .btn-clear:hover:not(:disabled) {
      background: rgba(220, 53, 69, 0.07);
      border-color: #dc3545;
    }

    .btn-clear:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }

    /* ── Page headers ── */
    .page-hd {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .page-hd h2 {
      margin: 0;
      font-size: 22px;
      font-weight: 700;
      color: #1e2532;
    }

    .page-hd p {
      margin: 4px 0 0;
      font-size: 12px;
      color: #6b7280;
    }

    /* ── Stat cards ── */
    .stat-cards {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-bottom: 20px;
    }

    .stat-card {
      background: #fff;
      border: 1px solid #e3e8f0;
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }

    .stat-label {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 8px;
    }

    .stat-value {
      font-family: monospace;
      font-size: 22px;
      font-weight: 800;
      color: #1e2532;
      letter-spacing: -.5px;
    }

    .stat-value.blue {
      color: #007bff;
    }

    .stat-value.green {
      color: #28a745;
    }

    .stat-value.purple {
      color: #6f42c1;
    }

    .stat-sub {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 4px;
    }

    /* ── Charts ── */
    .chart-wrap {
      background: #fff;
      border: 1px solid #e3e8f0;
      border-radius: 12px;
      padding: 18px;
      margin-bottom: 20px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }

    .chart-title {
      font-size: 12px;
      font-weight: 700;
      color: #374151;
      margin-bottom: 14px;
    }

    .bar-chart {
      display: flex;
      align-items: flex-end;
      gap: 8px;
      height: 100px;
    }

    .bar-wrap {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
    }

    .bar {
      width: 100%;
      background: #1e2532;
      border-radius: 4px 4px 0 0;
      min-height: 4px;
      transition: height .5s ease;
    }

    .bar-lbl {
      font-size: 9px;
      color: #9ca3af;
      font-family: monospace;
    }

    /* ── Export bar ── */
    .export-bar {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
    }

    .filter-select {
      height: 34px;
      padding: 0 10px;
      background: #fff;
      border: 1px solid #e3e8f0;
      border-radius: 8px;
      color: #374151;
      font-size: 12px;
      outline: none;
      cursor: pointer;
    }

    .filter-select:focus {
      border-color: #007bff;
    }

    .btn-exp {
      height: 34px;
      padding: 0 14px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all .15s;
      white-space: nowrap;
      border: 1px solid;
    }

    .btn-exp-csv {
      background: rgba(40, 167, 69, 0.07);
      color: #28a745;
      border-color: rgba(40, 167, 69, 0.25);
    }

    .btn-exp-csv:hover {
      background: #28a745;
      color: #fff;
    }

    .btn-exp-excel {
      background: rgba(0, 123, 255, 0.07);
      color: #007bff;
      border-color: rgba(0, 123, 255, 0.25);
    }

    .btn-exp-excel:hover {
      background: #007bff;
      color: #fff;
    }

    /* ── Data tables ── */
    .data-card {
      background: #fff;
      border: 1px solid #e3e8f0;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 20px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }

    .data-card-hd {
      padding: 12px 16px;
      border-bottom: 1px solid #e3e8f0;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #9ca3af;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .data-table thead th {
      background: #f8f9fa;
      padding: 10px 14px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #6b7280;
      border-bottom: 1px solid #e3e8f0;
      text-align: left;
    }

    .data-table tbody tr {
      border-bottom: 1px solid #f1f3f4;
      transition: background .12s;
    }

    .data-table tbody tr:last-child {
      border-bottom: none;
    }

    .data-table tbody tr:hover {
      background: #f8f9fa;
    }

    .data-table td {
      padding: 11px 14px;
      color: #374151;
    }

    .data-table td.mono {
      font-family: monospace;
      font-size: 11px;
    }

    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 700;
    }

    .badge-ok {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }

    .badge-low {
      background: rgba(255, 193, 7, 0.1);
      color: #b8860b;
    }

    .badge-out {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
    }

    .badge-blue {
      background: rgba(0, 123, 255, 0.1);
      color: #007bff;
    }

    .badge-grey {
      background: rgba(108, 117, 125, 0.1);
      color: #6c757d;
    }

    /* Products search bar */
    .tbl-search {
      height: 34px;
      padding: 0 10px 0 30px;
      border-radius: 8px;
      border: 1px solid #e3e8f0;
      font-size: 13px;
      color: #111827;
      outline: none;
      width: 220px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: 8px center;
      background-color: #fff;
    }

    .tbl-search:focus {
      border-color: #007bff;
    }

    /* Activity log badges */
    .log-CASHIER_LOGIN {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }

    .log-CASHIER_LOGOUT {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
    }

    .log-SALE_COMPLETED {
      background: rgba(0, 123, 255, 0.1);
      color: #007bff;
    }

    .log-PRODUCT_SCANNED {
      background: rgba(255, 193, 7, 0.1);
      color: #b8860b;
    }

    /* ── Modals ── */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      backdrop-filter: blur(3px);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal.open {
      display: flex;
    }

    .modal-content {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
      animation: slideDown .22s ease-out;
      max-height: 90vh;
      overflow-y: auto;
    }

    @keyframes slideDown {
      from {
        transform: translateY(-10px);
        opacity: 0;
      }
    }

    /* Cashier login modal */
    .cashier-modal {
      width: 380px;
      padding: 32px;
      text-align: center;
    }

    .cashier-modal h2 {
      font-size: 20px;
      font-weight: 800;
      margin: 0 0 4px;
    }

    .cashier-modal p {
      font-size: 13px;
      color: #6b7280;
      margin: 0 0 20px;
    }

    .cashier-code-input {
      width: 100%;
      height: 52px;
      background: #f4f6fb;
      border: 2px solid #e3e8f0;
      border-radius: 10px;
      font-family: monospace;
      font-size: 20px;
      font-weight: 700;
      color: #111827;
      padding: 0 14px;
      text-align: center;
      letter-spacing: 3px;
      text-transform: uppercase;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
      margin-bottom: 12px;
    }

    .cashier-code-input:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.12);
    }

    .btn-start-session {
      width: 100%;
      height: 48px;
      background: #1e2532;
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: background .15s;
    }

    .btn-start-session:hover {
      background: #2c3e50;
    }

    .cashier-hint {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 10px;
      line-height: 1.6;
    }

    /* Receipt modal */
    .receipt-modal {
      width: 420px;
    }

    .receipt-modal-hd {
      padding: 20px 24px;
      text-align: center;
      border-bottom: 1px dashed #e3e8f0;
    }

    .receipt-ok-icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: rgba(40, 167, 69, 0.1);
      border: 2px solid #28a745;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      margin: 0 auto 10px;
      color: #28a745;
    }

    .receipt-modal-hd h2 {
      font-size: 16px;
      font-weight: 800;
      margin: 0 0 2px;
    }

    .receipt-modal-hd p {
      font-size: 11px;
      color: #6b7280;
      font-family: monospace;
      margin: 0;
    }

    .receipt-modal-body {
      padding: 18px 24px;
    }

    .receipt-modal-footer {
      padding: 14px 24px 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      border-top: 1px solid #e3e8f0;
    }

    .btn-print-r {
      width: 100%;
      height: 38px;
      background: #f8f9fa;
      color: #374151;
      border: 1px solid #e3e8f0;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: border-color .15s;
    }

    .btn-print-r:hover {
      border-color: #dee2e6;
    }

    .btn-new-sale {
      width: 100%;
      height: 44px;
      background: #1e2532;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      transition: background .15s;
    }

    .btn-new-sale:hover {
      background: #2c3e50;
    }

    /* Receipt content */
    .receipt-wrap {
      font-family: monospace;
      font-size: 12px;
      color: #374151;
    }

    .r-store {
      font-size: 15px;
      font-weight: 800;
      text-align: center;
      color: #111827;
      letter-spacing: 1px;
    }

    .r-addr {
      font-size: 10px;
      text-align: center;
      color: #9ca3af;
      margin-bottom: 6px;
    }

    .r-divider {
      text-align: center;
      color: #9ca3af;
      font-size: 10px;
      margin: 6px 0;
    }

    .r-meta {
      font-size: 11px;
      color: #6b7280;
      line-height: 1.8;
    }

    .r-items {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0;
    }

    .r-items th {
      font-size: 10px;
      text-align: left;
      color: #9ca3af;
      padding: 4px 0;
      border-bottom: 1px solid #e3e8f0;
    }

    .r-items th.r,
    .r-items td.r {
      text-align: right;
    }

    .r-items td {
      padding: 4px 0;
      font-size: 11px;
      border-bottom: 1px solid #f1f3f4;
    }

    .r-totals {
      width: 100%;
      border-collapse: collapse;
    }

    .r-totals td {
      padding: 3px 0;
      font-size: 12px;
    }

    .r-totals td.r {
      text-align: right;
    }

    .r-totals tr.grand td {
      font-size: 14px;
      font-weight: 800;
      padding-top: 6px;
      color: #111827;
    }

    .r-thanks {
      text-align: center;
      font-size: 11px;
      color: #9ca3af;
      margin-top: 8px;
      line-height: 1.8;
    }

    /* Loading spinner */
    #loading-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      opacity: 0;
      pointer-events: none;
      transition: opacity .15s;
    }

    #loading-overlay.on {
      opacity: 1;
      pointer-events: auto;
    }

    .spinner {
      width: 38px;
      height: 38px;
      border: 3px solid rgba(255, 255, 255, 0.25);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .65s linear infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    /* Notification toasts */
    #notif-area {
      position: fixed;
      bottom: 20px;
      right: 20px;
      display: flex;
      flex-direction: column;
      gap: 7px;
      z-index: 3000;
      pointer-events: none;
    }

    .notif {
      background: #fff;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 9px 13px;
      font-size: 12px;
      color: #111827;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
      animation: notif-in .25s ease;
      pointer-events: auto;
      max-width: 260px;
    }

    .notif.ok {
      border-left: 3px solid #28a745;
    }

    .notif.err {
      border-left: 3px solid #dc3545;
    }

    .notif.inf {
      border-left: 3px solid #007bff;
    }

    @keyframes notif-in {
      from {
        transform: translateY(12px);
        opacity: 0;
      }
    }

    /* Responsive: hide stat cards on narrow screens */
    @media (max-width: 1100px) {
      .stat-cards {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 700px) {
      .stat-cards {
        grid-template-columns: 1fr;
      }
    }

    /* Print */
    @media print {

      .sidebar,
      #topbar,
      .pos-left,
      .pos-right,
      .page {
        display: none !important;
      }

      .modal {
        position: static;
        background: none;
        backdrop-filter: none;
      }

      .modal.open {
        display: block;
      }

      .modal-content {
        box-shadow: none;
        border: none;
        width: 100%;
        max-height: none;
      }

      .receipt-modal-footer {
        display: none;
      }

      body {
        background: #fff;
        color: #000;
      }
    }
  </style>
</head>

<body>

  <!-- ═══════════════════════════════════════════════════════
     SIDEBAR — matches styles.css .sidebar design
═══════════════════════════════════════════════════════ -->
  <div class="sidebar" id="sidebar">
    <div class="brand">
      <h1>POS System</h1>
      <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>

    <!-- Cashier session info inside sidebar -->
    <div id="sidebar-session" style="display:none; padding:10px 14px;">
      <div style="background:rgba(255,255,255,0.07);border-radius:8px;padding:10px 12px;border:1px solid rgba(255,255,255,0.08);">
        <div style="font-size:9px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-bottom:3px;">Active Cashier</div>
        <div style="color:#fff;font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;">
          <span style="width:7px;height:7px;border-radius:50%;background:#28a745;box-shadow:0 0 6px #28a745;flex-shrink:0;display:inline-block;animation:pulse-g 2s ease infinite;"></span>
          <span id="session-cashier-name">—</span>
        </div>
        <div style="font-size:10px;color:rgba(255,255,255,0.35);margin-top:2px;font-family:monospace;" id="session-time"></div>
      </div>
    </div>

    <!-- No session / click to login -->
    <div id="sidebar-no-session" style="padding:10px 14px;cursor:pointer;">
      <div style="background:rgba(220,53,69,0.12);border:1px solid rgba(220,53,69,0.2);border-radius:8px;padding:10px 12px;color:rgba(255,255,255,0.5);font-size:12px;text-align:center;">
        <strong style="display:block;color:#fff;font-size:13px;margin-bottom:2px;">No Active Session</strong>
        Click to log in
      </div>
    </div>

    <ul class="nav-list">
      <!-- POS Group -->
      <li class="nav-section">
        <div class="nav-section-title" data-expanded="false">
          <span class="nav-section-icon">🖥️</span>
          <span class="nav-section-label">Point of Sale</span>
          <span class="nav-icon">▾</span>
        </div>
        <ul class="nav-sublist">
          <li><a href="#" class="nav-link active" data-page="pos"><span class="nav-text">POS Terminal</span></a></li>
          <li><a href="#" class="nav-link" data-page="products"><span class="nav-text">Products</span></a></li>
        </ul>
      </li>

      <!-- Management Group -->
      <li class="nav-section">
        <div class="nav-section-title" data-expanded="false">
          <span class="nav-section-icon">📊</span>
          <span class="nav-section-label">Management</span>
          <span class="nav-icon">▾</span>
        </div>
        <ul class="nav-sublist">
          <li><a href="#" class="nav-link" data-page="reports"><span class="nav-text">Reports</span></a></li>
          <li><a href="#" class="nav-link" data-page="sessions"><span class="nav-text">Cashier Sessions</span></a></li>
          <li><a href="#" class="nav-link" data-page="activity"><span class="nav-text">Activity Log</span></a></li>
        </ul>
      </li>

      <!-- Session Control -->
      <li class="nav-section">
        <div class="nav-section-title" data-expanded="false">
          <span class="nav-section-icon">⚙️</span>
          <span class="nav-section-label">Session</span>
          <span class="nav-icon">▾</span>
        </div>
        <ul class="nav-sublist">
          <li><a href="#" id="btn-logout"><span class="nav-text">End Session / Logout</span></a></li>
        </ul>
      </li>
    </ul>
  </div>

  <!-- ═══════════════════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════════════════ -->
  <div class="main" id="mainContent">

    <!-- Top bar -->
    <div class="page-header" id="topbar">
      <h2 id="topbar-title">POS Terminal</h2>
      <div style="display:flex;align-items:center;gap:16px;">
        <span id="clock-display" style="font-family:monospace;font-size:13px;color:#6b7280;"></span>
      </div>
    </div>

    <!-- ══════ PAGE: POS TERMINAL ══════ -->
    <div id="page-pos" class="active">

      <!-- Left: Scanner + Cart -->
      <div class="pos-left">

        <div class="scan-bar">
          <div class="scan-bar-label">Barcode Scanner</div>
          <div class="scan-bar-row">
            <input type="text" id="barcode-input"
              placeholder="Scan barcode or type + Enter…"
              autocomplete="off" autocorrect="off" spellcheck="false" />
            <button class="btn-scan" id="btn-scan" type="button">SCAN</button>
          </div>
        </div>

        <div class="search-bar">
          <div class="search-container">
            <input type="text" id="product-search" placeholder="Search products by name or barcode…" />
            <div id="search-results"></div>
          </div>
        </div>

        <div id="scan-status"></div>

        <div class="cart-area">
          <div class="cart-header-row">
            <span class="cart-title-text">Cart</span>
            <span id="cart-count">0 items</span>
          </div>
          <div class="cart-scroll">
            <div id="cart-empty">
              <div class="empty-ico">🛒</div>
              <span>Cart is empty — scan a product to start</span>
            </div>
            <table id="cart-table" style="display:none;">
              <thead>
                <tr>
                  <th>Product</th>
                  <th style="text-align:right">Price</th>
                  <th style="text-align:right">Qty</th>
                  <th style="text-align:right">Total</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right: Summary + Checkout -->
      <div class="pos-right">
        <div class="pos-right-scroll">

          <div class="summary-block">
            <div class="summary-block-hd">Order Summary</div>
            <div class="sum-row"><span class="lbl">Subtotal</span><span class="val" id="subtotal-val">₱0.00</span></div>
            <div class="sum-row grand"><span class="lbl">Total</span><span class="val" id="total-val">₱0.00</span></div>
          </div>

          <div class="cash-block">
            <label for="cash-received">Cash Received</label>
            <input type="number" id="cash-received" placeholder="0.00" min="0" step="0.01" />
            <div class="change-display" id="change-display">
              <span class="change-label">Change:</span>
              <span class="change-value" id="change-val">₱0.00</span>
            </div>
          </div>

          <div class="pos-actions">
            <button class="btn-checkout" id="btn-checkout" disabled type="button">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12" />
              </svg>
              Checkout
            </button>
            <button class="btn-clear" id="btn-clear" disabled type="button">Clear Cart</button>
          </div>

        </div>
      </div>
    </div><!-- /page-pos -->

    <!-- ══════ PAGE: PRODUCTS ══════ -->
    <div id="page-products" class="page">
      <div class="page-hd">
        <div>
          <h2>Products</h2>
          <p>Browse active product catalog with live store stock</p>
        </div>
        <input type="text" class="tbl-search" id="products-search" placeholder="Search…" />
      </div>
      <div class="data-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th>Barcode</th>
              <th>Category</th>
              <th>Store Price</th>
              <th>Store Stock</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="products-tbody">
            <tr>
              <td colspan="8" style="text-align:center;padding:32px;color:#9ca3af">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════ PAGE: REPORTS ══════ -->
    <div id="page-reports" class="page">
      <div class="page-hd">
        <div>
          <h2>Reports & Analytics</h2>
          <p>Sales performance overview</p>
        </div>
      </div>

      <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-label">Today's Sales</div>
          <div class="stat-value blue" id="stat-today">₱0.00</div>
          <div class="stat-sub">Current day</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">This Week</div>
          <div class="stat-value green" id="stat-week">₱0.00</div>
          <div class="stat-sub">Mon – Sun</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">This Month</div>
          <div class="stat-value" id="stat-month">₱0.00</div>
          <div class="stat-sub">Calendar month</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Today's Transactions</div>
          <div class="stat-value purple" id="stat-txns">0</div>
          <div class="stat-sub">Completed sales</div>
        </div>
      </div>

      <div class="chart-wrap">
        <div class="chart-title">Daily Sales — Last 7 Days</div>
        <div class="bar-chart" id="bar-chart"></div>
      </div>

      <div class="chart-wrap">
        <div class="chart-title">Export Sales Report</div>
        <div class="export-bar">
          <select class="filter-select" id="export-range">
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="custom">Custom Range</option>
          </select>
          <div id="custom-date-range" style="display:none;display:flex;gap:8px;align-items:center">
            <input type="date" class="filter-select" id="export-from" />
            <span style="color:#9ca3af;font-size:12px">to</span>
            <input type="date" class="filter-select" id="export-to" />
          </div>
          <button class="btn-exp btn-exp-csv" id="btn-export-csv" type="button">⬇ Export CSV</button>
        </div>
      </div>

      <div class="data-card">
        <div class="data-card-hd">Recent Transactions <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#9ca3af;font-size:11px">(click row to view receipt)</span></div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Ref #</th>
              <th>Date & Time</th>
              <th>Cashier</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="recent-sales-tbody">
            <tr>
              <td colspan="5" style="text-align:center;padding:24px;color:#9ca3af">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════ PAGE: SESSIONS ══════ -->
    <div id="page-sessions" class="page">
      <div class="page-hd">
        <div>
          <h2>Cashier Sessions</h2>
          <p>Login/logout audit trail with cash tracking</p>
        </div>
      </div>
      <div class="data-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Cashier</th>
              <th>Time In</th>
              <th>Time Out</th>
              <th>Total Sales</th>
              <th>Starting Cash</th>
              <th>Ending Cash</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="sessions-tbody">
            <tr>
              <td colspan="7" style="text-align:center;padding:32px;color:#9ca3af">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════ PAGE: ACTIVITY LOG ══════ -->
    <div id="page-activity" class="page">
      <div class="page-hd">
        <div>
          <h2>Activity Log</h2>
          <p>Complete system event audit trail</p>
        </div>
      </div>
      <div class="data-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>Cashier</th>
              <th>Action</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody id="activity-tbody">
            <tr>
              <td colspan="4" style="text-align:center;padding:32px;color:#9ca3af">Loading…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /main -->

  <!-- ═══════════════════════════════════════════════════════
     CASHIER LOGIN MODAL
═══════════════════════════════════════════════════════ -->
  <div class="modal" id="cashier-modal">
    <div class="modal-content cashier-modal">
      <div style="width:56px;height:56px;background:rgba(0,123,255,0.08);border:2px solid rgba(0,123,255,0.25);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;">🏪</div>
      <h2>POS Terminal</h2>
      <p>Enter your cashier code to begin</p>
      <input type="text" class="cashier-code-input" id="cashier-code-input"
        placeholder="e.g. CASHIER01" maxlength="40"
        autocomplete="off" autocorrect="off" spellcheck="false"
        value="CASHIER01" />
      <!-- Quick-fill test codes -->
      <div style="display:flex;gap:6px;margin-bottom:12px;justify-content:center;">
        <button type="button"
          onclick="document.getElementById('cashier-code-input').value='CASHIER01'"
          style="padding:4px 12px;border-radius:6px;border:1px solid #e3e8f0;background:#f4f6fb;font-size:11px;font-weight:700;cursor:pointer;color:#1e2532;letter-spacing:0.5px;">
          CASHIER01
        </button>
      </div>
      <button class="btn-start-session" id="btn-start-session" type="button">Start Session</button>
      <div class="cashier-hint">No password required — your name becomes your session ID.<br>₱2,000 starting cash is recorded automatically.</div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════
     RECEIPT MODAL
═══════════════════════════════════════════════════════ -->
  <div class="modal" id="receipt-modal">
    <div class="modal-content receipt-modal">
      <div class="receipt-modal-hd">
        <div class="receipt-ok-icon">✓</div>
        <h2>Sale Complete</h2>
        <p id="r-timestamp">—</p>
      </div>
      <div class="receipt-modal-body">
        <div class="receipt-wrap">
          <div class="r-store">🏪 POSMART RETAIL</div>
          <div class="r-addr">123 Rizal Street, Manila, Philippines</div>
          <div class="r-divider">────────────────────────</div>
          <div class="r-meta">
            <span>Cashier: <strong id="r-cashier">—</strong></span><br>
            <span>Ref: <strong id="r-txn-ref">—</strong></span>
          </div>
          <div class="r-divider">────────────────────────</div>
          <table class="r-items">
            <thead>
              <tr>
                <th>Item</th>
                <th class="r">Qty</th>
                <th class="r">Price</th>
                <th class="r">Total</th>
              </tr>
            </thead>
            <tbody id="r-items"></tbody>
          </table>
          <div class="r-divider">────────────────────────</div>
          <table class="r-totals">
            <tr>
              <td>Items</td>
              <td class="r" id="r-item-count">—</td>
            </tr>
            <tr>
              <td>Subtotal</td>
              <td class="r" id="r-subtotal">—</td>
            </tr>
            <tr class="grand">
              <td>TOTAL</td>
              <td class="r" id="r-total">—</td>
            </tr>
            <tr>
              <td>Cash</td>
              <td class="r" id="r-cash">—</td>
            </tr>
            <tr>
              <td>Change</td>
              <td class="r" id="r-change">—</td>
            </tr>
          </table>
          <div class="r-divider">────────────────────────</div>
          <div class="r-thanks">Thank you for shopping!<br>Please come again 😊</div>
        </div>
      </div>
      <div class="receipt-modal-footer">
        <button class="btn-print-r" id="btn-print-r" type="button">🖨 Print Receipt</button>
        <button class="btn-new-sale" id="btn-new-sale" type="button">New Sale</button>
      </div>
    </div>
  </div>

  <!-- Loading + Notifications -->
  <div id="loading-overlay">
    <div class="spinner"></div>
  </div>
  <div id="notif-area"></div>

  <style>
    @keyframes pulse-g {

      0%,
      100% {
        opacity: 1
      }

      50% {
        opacity: 0.4
      }
    }
  </style>

  <script>
    'use strict';

    // ── State ──────────────────────────────────────────────────────────────────
    const S = {
      session: null,
      cart: [],
      scanning: false
    };

    // ── Helpers ────────────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const fmt = n => '₱' + Number(n).toLocaleString('en-PH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
    const fmtDate = s => s ? new Date(s).toLocaleString('en-PH', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }) : '—';

    // ── API calls — all go to this same file ──────────────────────────────────
    async function api(action, params = {}, method = 'GET', body = null) {
      const url = new URL(window.location.href);
      url.search = '';
      url.searchParams.set('action', action);
      Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

      const opts = {
        method
      };
      if (body) {
        opts.headers = {
          'Content-Type': 'application/json'
        };
        opts.body = JSON.stringify(body);
      }

      const r = await fetch(url.toString(), opts);
      return r.json();
    }

    // ── Notifications ─────────────────────────────────────────────────────────
    function notify(msg, type = 'inf', dur = 3000) {
      const el = document.createElement('div');
      el.className = `notif ${type}`;
      el.textContent = msg;
      $('notif-area').appendChild(el);
      setTimeout(() => el.remove(), dur);
    }
    const setLoading = v => $('loading-overlay').classList.toggle('on', v);

    // ── Clock ──────────────────────────────────────────────────────────────────
    setInterval(() => {
      $('clock-display').textContent = new Date().toLocaleTimeString('en-PH', {
        hour12: false
      });
    }, 1000);

    // ── Sidebar (reuse styles.css logic) ──────────────────────────────────────
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    document.getElementById('sidebarToggle').addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('collapsed');
    });

    // Accordion nav sections
    document.querySelectorAll('.nav-section').forEach(section => {
      const title = section.querySelector('.nav-section-title');
      const arrow = title.querySelector('.nav-icon');
      const open = (isOpen) => {
        section.classList.toggle('open', isOpen);
        title.setAttribute('data-expanded', isOpen);
        arrow.textContent = isOpen ? '▾' : '▸';
      };
      title.addEventListener('click', () => open(title.getAttribute('data-expanded') !== 'true'));
      open(false);
    });

    // ── Navigation ─────────────────────────────────────────────────────────────
    function navigate(pageId) {
      // Hide all pages
      document.querySelectorAll('.page, #page-pos').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('active'));

      const pg = document.getElementById('page-' + pageId);
      if (pg) pg.classList.add('active');

      document.querySelectorAll(`.nav-link[data-page="${pageId}"]`).forEach(el => el.classList.add('active'));

      $('topbar-title').textContent = {
        pos: 'POS Terminal',
        products: 'Products',
        reports: 'Reports & Analytics',
        sessions: 'Cashier Sessions',
        activity: 'Activity Log'
      } [pageId] || 'POS System';

      if (pageId === 'products') loadProducts();
      if (pageId === 'reports') loadReports();
      if (pageId === 'sessions') loadSessions();
      if (pageId === 'activity') loadActivity();
      if (pageId === 'pos') setTimeout(() => $('barcode-input')?.focus(), 50);
    }

    document.querySelectorAll('.nav-link[data-page]').forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();
        navigate(el.dataset.page);
      });
    });

    // ── Session Management ─────────────────────────────────────────────────────
    async function loadActiveSession() {
      try {
        const j = await api('active_session');
        if (j.success && j.data) setSession(j.data);
        else openModal('cashier-modal');
      } catch {
        openModal('cashier-modal');
      }
    }

    function setSession(sess) {
      S.session = sess;
      $('session-cashier-name').textContent = sess.cashier_name;
      $('session-time').textContent = 'Since ' + fmtDate(sess.login_time);
      $('sidebar-session').style.display = 'block';
      $('sidebar-no-session').style.display = 'none';
    }

    async function startSession() {
      const name = $('cashier-code-input').value.trim().toUpperCase();
      if (!name || !/^[A-Z0-9_\-\s]{1,40}$/.test(name)) {
        notify('Enter a valid cashier code', 'err');
        return;
      }
      setLoading(true);
      try {
        const j = await api('start_session', {}, 'POST', {
          cashier_name: name
        });
        if (j.success) {
          setSession(j.data);
          closeModal('cashier-modal');
          $('cashier-code-input').value = '';
          notify(`Welcome, ${j.data.cashier_name}! ₱2,000 starting cash recorded.`, 'ok', 4000);
          navigate('pos');
        } else {
          notify(j.error, 'err');
        }
      } catch {
        notify('Network error', 'err');
      } finally {
        setLoading(false);
      }
    }

    async function endSession() {
      if (!S.session) return;
      if (!confirm(`End session for ${S.session.cashier_name}?`)) return;
      setLoading(true);
      try {
        const j = await api('end_session', {}, 'POST', {
          session_id: S.session.id
        });
        if (j.success) {
          notify(`Session ended. Total sales: ${fmt(j.data.total_sales)}`, 'ok', 5000);
          S.session = null;
          S.cart = [];
          renderCart();
          $('sidebar-session').style.display = 'none';
          $('sidebar-no-session').style.display = 'block';
          openModal('cashier-modal');
        } else {
          notify(j.error, 'err');
        }
      } catch {
        notify('Network error', 'err');
      } finally {
        setLoading(false);
      }
    }

    // ── Modal helpers ──────────────────────────────────────────────────────────
    function openModal(id) {
      $(id).classList.add('open');
    }

    function closeModal(id) {
      $(id).classList.remove('open');
    }

    // ── Barcode Scanning ───────────────────────────────────────────────────────
    function initBarcodeInput() {
      const input = $('barcode-input');
      input?.focus();
      input?.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
          e.preventDefault();
          const v = input.value.trim();
          if (v) lookupBarcode(v);
        }
      });
      document.addEventListener('keydown', e => {
        const t = document.activeElement?.tagName;
        if (!['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON'].includes(t) && e.key.length === 1) input?.focus();
      });
    }

    async function lookupBarcode(barcode) {
      if (S.scanning) return;
      // SESSION CHECK DISABLED FOR BARCODE TESTING
      // Re-enable this line when done testing:
      // if (!S.session) { notify('No active session. Please log in first.', 'err'); return; }
      S.scanning = true;
      const input = $('barcode-input');
      setScanStatus('Searching…', 'load');
      input?.classList.remove('found', 'not-found');
      try {
        const j = await api('get_product', {
          barcode
        });
        if (j.success) {
          addToCart(j.data);
          input?.classList.add('found');
          setScanStatus(`✓ Added: ${j.data.product_name}`, 'ok');
          if (input) input.value = '';
          notify(j.data.product_name, 'ok', 1500);
        } else {
          input?.classList.add('not-found');
          setScanStatus(`✗ Not found: ${barcode}`, 'err');
          notify(`Product not found: ${barcode}`, 'err');
        }
      } catch {
        setScanStatus('✗ Network error', 'err');
        notify('Network error', 'err');
      } finally {
        S.scanning = false;
        setTimeout(() => input?.classList.remove('found', 'not-found'), 2000);
        input?.focus();
      }
    }

    function setScanStatus(msg, cls = '') {
      const el = $('scan-status');
      if (!el) return;
      el.textContent = msg;
      el.className = cls;
    }

    // ── Product Search ─────────────────────────────────────────────────────────
    let searchTimer;

    function initProductSearch() {
      const input = $('product-search'),
        results = $('search-results');
      if (!input || !results) return;
      input.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = input.value.trim();
        if (!q) {
          results.style.display = 'none';
          return;
        }
        searchTimer = setTimeout(async () => {
          try {
            const j = await api('search_products', {
              q
            });
            if (!j.success || !j.data.length) {
              results.style.display = 'none';
              return;
            }
            results.innerHTML = j.data.map(p => `
                    <div class="search-result-item" onclick="addToCartObj(${p.product_id},'${esc(p.product_name)}',${p.price},${p.stock},'${esc(p.barcode)}')">
                        <div><div class="sr-name">${esc(p.product_name)}</div><div class="sr-meta">${esc(p.barcode)} · Stock: ${p.stock}</div></div>
                        <span class="sr-price">${fmt(p.price)}</span>
                    </div>`).join('');
            results.style.display = 'block';
          } catch {
            results.style.display = 'none';
          }
        }, 280);
      });
      document.addEventListener('click', e => {
        if (!input.contains(e.target) && !results.contains(e.target)) results.style.display = 'none';
      });
    }

    function addToCartObj(id, name, price, stock, barcode) {
      addToCart({
        product_id: id,
        product_name: name,
        price,
        stock,
        barcode
      });
      $('search-results').style.display = 'none';
      $('product-search').value = '';
    }

    // ── Cart ───────────────────────────────────────────────────────────────────
    function addToCart(product) {
      const idx = S.cart.findIndex(i => i.product_id === product.product_id);
      if (idx >= 0) {
        if (S.cart[idx].quantity < product.stock) S.cart[idx].quantity++;
        else {
          notify(`Max stock: ${product.stock}`, 'err', 2000);
          return;
        }
      } else {
        if (product.stock < 1) {
          notify(`${product.product_name} is out of stock`, 'err');
          return;
        }
        S.cart.push({
          product_id: product.product_id,
          barcode: product.barcode,
          product_name: product.product_name,
          price: product.price,
          stock: product.stock,
          quantity: 1
        });
      }
      renderCart();
    }

    function updateQty(pid, delta) {
      const idx = S.cart.findIndex(i => i.product_id === pid);
      if (idx < 0) return;
      const nq = S.cart[idx].quantity + delta;
      if (nq <= 0) S.cart.splice(idx, 1);
      else if (nq > S.cart[idx].stock) {
        notify('Max stock reached', 'err', 1500);
        return;
      } else S.cart[idx].quantity = nq;
      renderCart();
    }

    function removeItem(pid) {
      S.cart = S.cart.filter(i => i.product_id !== pid);
      renderCart();
    }

    function clearCart() {
      if (!S.cart.length) return;
      if (!confirm('Clear cart?')) return;
      S.cart = [];
      renderCart();
    }

    function renderCart() {
      const tbody = document.querySelector('#cart-table tbody');
      const empty = $('cart-empty'),
        table = $('cart-table');
      const count = $('cart-count'),
        btnCO = $('btn-checkout'),
        btnCL = $('btn-clear');
      const total = S.cart.reduce((s, i) => s + i.quantity, 0);

      if (count) count.textContent = total + ' item' + (total !== 1 ? 's' : '');

      if (!S.cart.length) {
        empty.style.display = 'flex';
        table.style.display = 'none';
        btnCO.disabled = true;
        btnCL.disabled = true;
      } else {
        empty.style.display = 'none';
        table.style.display = 'table';
        btnCO.disabled = false;
        btnCL.disabled = false;
      }

      if (tbody) {
        tbody.innerHTML = S.cart.map(item => `
            <tr>
                <td><div class="cart-pname">${esc(item.product_name)}</div><div class="cart-pcode">${esc(item.barcode)}</div></td>
                <td class="cart-price-cell">${fmt(item.price)}</td>
                <td>
                    <div class="qty-controls">
                        <button class="qty-btn del" onclick="removeItem(${item.product_id})" title="Remove">✕</button>
                        <button class="qty-btn" onclick="updateQty(${item.product_id},-1)">−</button>
                        <span class="qty-val">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQty(${item.product_id},1)">+</button>
                    </div>
                </td>
                <td class="cart-total-cell">${fmt(item.price * item.quantity)}</td>
            </tr>`).join('');
      }
      updateSummary();
    }

    function updateSummary() {
      const sub = S.cart.reduce((s, i) => s + i.price * i.quantity, 0);
      const cash = parseFloat($('cash-received')?.value || 0);
      const chng = cash - sub;
      if ($('subtotal-val')) $('subtotal-val').textContent = fmt(sub);
      if ($('total-val')) $('total-val').textContent = fmt(sub);
      const cd = $('change-display'),
        cv = $('change-val');
      if (cd && cv) {
        cv.textContent = fmt(Math.abs(chng));
        cd.classList.toggle('negative', chng < 0 && cash > 0);
        cd.querySelector('.change-label').textContent = chng < 0 ? 'Short:' : 'Change:';
      }
    }

    // ── Checkout ───────────────────────────────────────────────────────────────
    async function processCheckout() {
      if (!S.cart.length) return;
      if (!S.session) {
        notify('No active session', 'err');
        return;
      }
      const sub = S.cart.reduce((s, i) => s + i.price * i.quantity, 0);
      const cash = parseFloat($('cash-received')?.value || '0');
      if (!cash || cash <= 0) {
        notify('Enter cash received', 'err');
        $('cash-received')?.focus();
        return;
      }
      if (cash < sub) {
        notify(`Cash insufficient. Need ${fmt(sub)}`, 'err');
        return;
      }

      setLoading(true);
      try {
        const j = await api('checkout', {}, 'POST', {
          session_id: S.session.id,
          cashier_name: S.session.cashier_name,
          cash_received: cash,
          items: S.cart.map(i => ({
            product_id: i.product_id,
            quantity: i.quantity,
            price: i.price,
            product_name: i.product_name,
            barcode: i.barcode
          })),
        });
        if (j.success) showReceipt(j.data);
        else notify(j.error || 'Checkout failed', 'err', 5000);
      } catch {
        notify('Network error during checkout', 'err');
      } finally {
        setLoading(false);
      }
    }

    function showReceipt(txn) {
      $('r-txn-ref').textContent = txn.transaction_ref;
      $('r-cashier').textContent = txn.cashier_name;
      $('r-timestamp').textContent = fmtDate(txn.timestamp);
      $('r-items').innerHTML = txn.items.map(i => `
        <tr>
            <td>${esc(i.product_name)}</td>
            <td class="r">${i.quantity}</td>
            <td class="r">${fmt(i.unit_price)}</td>
            <td class="r">${fmt(i.line_total)}</td>
        </tr>`).join('');
      $('r-subtotal').textContent = fmt(txn.subtotal);
      $('r-total').textContent = fmt(txn.total_amount);
      $('r-cash').textContent = fmt(txn.cash_received);
      $('r-change').textContent = fmt(txn.change_amount);
      $('r-item-count').textContent = txn.item_count;
      openModal('receipt-modal');
    }

    function startNewSale() {
      closeModal('receipt-modal');
      S.cart = [];
      renderCart();
      if ($('cash-received')) $('cash-received').value = '';
      setScanStatus('');
      $('barcode-input')?.focus();
      notify('Ready for next sale', 'inf', 2000);
    }

    // ── Products Page ──────────────────────────────────────────────────────────
    let allProducts = [];
    async function loadProducts(filter = '') {
      const tbody = $('products-tbody');
      if (!tbody) return;
      try {
        if (!allProducts.length || !filter) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:#9ca3af">Loading…</td></tr>';
          const j = await api('all_products');
          if (!j.success) throw new Error(j.error);
          allProducts = j.data;
        }
        const rows = filter ? allProducts.filter(p =>
          p.product_name.toLowerCase().includes(filter.toLowerCase()) ||
          p.barcode?.includes(filter)
        ) : allProducts;

        if (!rows.length) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:#9ca3af">No products found</td></tr>';
          return;
        }

        tbody.innerHTML = rows.map(p => {
          const sb = p.stock <= 0 ? 'badge-out' : p.stock <= 5 ? 'badge-low' : 'badge-ok';
          const sl = p.stock <= 0 ? 'Out of Stock' : p.stock <= 5 ? 'Low Stock' : 'In Stock';
          return `<tr>
                <td><strong>${esc(p.product_name)}</strong></td>
                <td class="mono">${esc(p.sku || '—')}</td>
                <td class="mono">${esc(p.barcode || '—')}</td>
                <td>${esc(p.category || '—')}</td>
                <td style="font-family:monospace;font-weight:700;color:#007bff">${fmt(p.price)}</td>
                <td style="font-family:monospace">${p.stock}</td>
                <td><span class="badge ${sb}">${sl}</span></td>
                <td><button class="btn btn-primary" style="padding:5px 12px;font-size:12px" onclick="addToCartObj(${p.product_id},'${esc(p.product_name)}',${p.price},${p.stock},'${esc(p.barcode)}');navigate('pos')">Add to Cart</button></td>
            </tr>`;
        }).join('');
      } catch (e) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:#dc3545;padding:20px">${esc(e.message)}</td></tr>`;
      }
    }

    // ── Reports ────────────────────────────────────────────────────────────────
    async function loadReports() {
      try {
        const j = await api('reports_summary');
        if (!j.success) return;
        const d = j.data;
        if ($('stat-today')) $('stat-today').textContent = fmt(d.today);
        if ($('stat-week')) $('stat-week').textContent = fmt(d.week);
        if ($('stat-month')) $('stat-month').textContent = fmt(d.month);
        if ($('stat-txns')) $('stat-txns').textContent = d.txns;
        renderBarChart(d.chart);
      } catch {}
      loadRecentSales();
    }

    function renderBarChart(data) {
      const c = $('bar-chart');
      if (!c || !data?.length) return;
      const max = Math.max(...data.map(d => parseFloat(d.total)), 1);
      c.innerHTML = data.map(d => {
        const pct = (parseFloat(d.total) / max * 100).toFixed(1);
        const lbl = new Date(d.date).toLocaleDateString('en-PH', {
          month: 'short',
          day: 'numeric'
        });
        return `<div class="bar-wrap"><div class="bar" style="height:${pct}%" title="${fmt(d.total)}"></div><div class="bar-lbl">${lbl}</div></div>`;
      }).join('');
    }

    async function loadRecentSales() {
      const tbody = $('recent-sales-tbody');
      if (!tbody) return;
      try {
        const j = await api('recent_sales', {
          limit: 20
        });
        if (!j.success) return;
        tbody.innerHTML = j.data.map(s => `
            <tr style="cursor:pointer" onclick="viewReceipt(${s.id})">
                <td class="mono">${esc(s.transaction_ref)}</td>
                <td>${fmtDate(s.created_at)}</td>
                <td>${esc(s.cashier_name)}</td>
                <td style="font-family:monospace;font-weight:700;color:#007bff">${fmt(s.total_amount)}</td>
                <td><span class="badge badge-ok">Completed</span></td>
            </tr>`).join('');
      } catch {}
    }

    async function viewReceipt(id) {
      setLoading(true);
      try {
        const j = await api('sale_detail', {
          id
        });
        if (!j.success) {
          notify('Sale not found', 'err');
          return;
        }
        const s = j.data;
        showReceipt({
          transaction_ref: s.transaction_ref,
          cashier_name: s.cashier_name,
          timestamp: s.created_at,
          items: s.items.map(i => ({
            product_name: i.product_name,
            quantity: i.quantity,
            unit_price: i.unit_price,
            line_total: i.line_total
          })),
          subtotal: s.subtotal,
          total_amount: s.total_amount,
          cash_received: s.cash_received,
          change_amount: s.change_amount,
          item_count: s.items.length
        });
      } catch {
        notify('Failed to load receipt', 'err');
      } finally {
        setLoading(false);
      }
    }

    // ── Sessions ───────────────────────────────────────────────────────────────
    async function loadSessions() {
      const tbody = $('sessions-tbody');
      if (!tbody) return;
      try {
        const j = await api('sessions');
        if (!j.success) return;
        tbody.innerHTML = j.data.map(s => `
            <tr>
                <td><strong>${esc(s.cashier_name)}</strong></td>
                <td class="mono">${fmtDate(s.login_time)}</td>
                <td class="mono">${s.logout_time ? fmtDate(s.logout_time) : '<span style="color:#28a745">Active</span>'}</td>
                <td style="font-family:monospace;font-weight:700;color:#007bff">${fmt(s.total_sales)}</td>
                <td style="font-family:monospace">${fmt(s.starting_cash)}</td>
                <td style="font-family:monospace">${s.ending_cash ? fmt(s.ending_cash) : '—'}</td>
                <td><span class="badge ${s.status==='active'?'badge-ok':'badge-grey'}">${s.status}</span></td>
            </tr>`).join('');
      } catch {}
    }

    // ── Activity Log ───────────────────────────────────────────────────────────
    async function loadActivity() {
      const tbody = $('activity-tbody');
      if (!tbody) return;
      try {
        const j = await api('activity_log', {
          limit: 100
        });
        if (!j.success) return;
        tbody.innerHTML = j.data.map(l => `
            <tr>
                <td class="mono">${fmtDate(l.created_at)}</td>
                <td>${esc(l.cashier_name || '—')}</td>
                <td><span class="badge log-${esc(l.action)}">${esc(l.action)}</span></td>
                <td style="font-size:12px;color:#6b7280">${esc(l.description || '')}</td>
            </tr>`).join('');
      } catch {}
    }

    // ── Export ─────────────────────────────────────────────────────────────────
    function exportCSV() {
      const range = $('export-range')?.value || 'today';
      const from = $('export-from')?.value || '';
      const to = $('export-to')?.value || '';
      let url = `?action=export_csv&range=${range}`;
      if (range === 'custom') url += `&from=${from}&to=${to}`;
      window.location.href = url;
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
      loadActiveSession();
      initBarcodeInput();
      initProductSearch();
      renderCart();

      $('btn-start-session')?.addEventListener('click', startSession);
      $('cashier-code-input')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') startSession();
      });
      $('btn-logout')?.addEventListener('click', e => {
        e.preventDefault();
        endSession();
      });
      $('sidebar-no-session')?.addEventListener('click', () => openModal('cashier-modal'));

      $('btn-scan')?.addEventListener('click', () => {
        const v = $('barcode-input')?.value.trim();
        if (v) lookupBarcode(v);
      });
      $('btn-checkout')?.addEventListener('click', processCheckout);
      $('btn-clear')?.addEventListener('click', clearCart);
      $('cash-received')?.addEventListener('input', updateSummary);

      $('btn-new-sale')?.addEventListener('click', startNewSale);
      $('btn-print-r')?.addEventListener('click', () => window.print());
      $('receipt-modal')?.addEventListener('click', e => {
        if (e.target === $('receipt-modal')) closeModal('receipt-modal');
      });
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && $('receipt-modal').classList.contains('open')) closeModal('receipt-modal');
      });

      $('products-search')?.addEventListener('input', e => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadProducts(e.target.value.trim()), 280);
      });
      $('export-range')?.addEventListener('change', e => {
        const c = $('custom-date-range');
        if (c) c.style.display = e.target.value === 'custom' ? 'flex' : 'none';
      });
      $('btn-export-csv')?.addEventListener('click', exportCSV);

      navigate('pos');
      notify('POS System ready — scan a barcode to begin', 'inf', 3000);
    });
  </script>

</body>

</html>