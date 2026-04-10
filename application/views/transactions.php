<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'transactions';
if (!isset($transactions)) {
  $transactions = []; // Or load your data
}
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  $filter = $_GET['filter'] ?? 'all';
  $title = $filter === 'debit' ? 'redemptions' : ($filter === 'credit' ? 'earnings' : 'all_transactions');
  // Clear any previous output buffers to ensure a clean file
  if (ob_get_length()) ob_end_clean();
  $filename = $title . "_" . date('Y-m-d') . ".csv";
  // Set headers
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Pragma: no-cache');
  header('Expires: 0');

  $output = fopen('php://output', 'w');

  // Add BOM for Excel (Fixes special character issues in Excel)
  fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

  // Column Headers
  fputcsv($output, ['ID', 'Date', 'Time', 'Type', 'Category', 'Description', 'Points']);

  // Get the correct data based on filter
  $exportData = ($filter === 'all')
    ? $transactions
    : array_filter($transactions, fn($t) => $t['type'] === $filter);

  foreach ($exportData as $t) {
    fputcsv($output, [
      $t['id'],
      $t['transaction_date'],
      $t['transaction_time'],
      ucfirst($t['type']),
      ucfirst($t['category']),
      $t['description'],
      $t['points']
    ]);
  }

  fclose($output);
  exit; // IMPORTANT: Prevents the rest of the HTML from being added to the CSV
}

// Filter by type
$filter = $_GET['filter'] ?? 'all';
$filteredTransactions = $filter === 'all' ? $transactions : array_filter($transactions, fn($t) => $t['type'] === $filter);

// Calculate summary stats
$totalEarned = array_sum(array_map(fn($t) => $t['type'] === 'credit' ? $t['points'] : 0, $transactions));
$totalSpent = abs(array_sum(array_map(fn($t) => $t['type'] === 'debit' ? $t['points'] : 0, $transactions)));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Transaction History</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <style>
    :root {
      --bg: #f9fafb;
      --primary: #AB2732;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: var(--bg);
    }

    .main-wrapper {
      margin-left: 200px;
      background-color: var(--bg);
    }

    @media (max-width: 768px) {
      .main-wrapper {
        margin-left: 0;
      }
    }

    /* Summary Cards */
    .summary-card {
      background: white;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      height: 100%;
      transition: all 0.3s;
    }

    .summary-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
    }

    .summary-icon {
      width: 56px;
      height: 56px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
    }

    .summary-value {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .summary-label {
      color: #6b7280;
      font-size: 0.875rem;
      font-weight: 500;
    }

    /* Filter Buttons */
    .filter-group {
      background: white;
      padding: 16px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      margin-bottom: 24px;
    }

    .filter-btn {
      padding: 8px 20px;
      border-radius: 8px;
      border: 2px solid var(--bg);
      background: white;
      color: #6b7280;
      font-weight: 500;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-block;
      margin: 4px;
    }

    .filter-btn:hover {
      border-color: var(--primary);
      color: var(--primary);
      background: var(--bg);
    }

    .filter-btn.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    /* Transaction Card */
    .transaction-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      margin-bottom: 24px;
      overflow: hidden;
      height: 60svh;
    }

    .transaction-item {
      padding: 20px 24px;
      border-bottom: 1px solid var(--bg);
      display: flex;
      align-items: center;
      transition: all 0.2s;
    }

    .transaction-item:last-child {
      border-bottom: none;
    }

    .transaction-item:hover {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
    }

    .transaction-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 8px;
      flex-shrink: 0;
    }

    .transaction-icon.credit {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      color: #065f46;
    }

    .transaction-icon.debit {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #991b1b;
    }

    .transaction-details {
      flex-grow: 1;
      min-width: 0;
    }

    .transaction-description {
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 4px;
      font-size: 0.9rem;
    }

    .transaction-meta {
      color: #6b7280;
      font-size: 0.7rem;
    }

    .transaction-amount {
      font-size: 1.25rem;
      font-weight: 700;
      margin-right: 12px;
      flex-shrink: 0;
    }

    .transaction-amount.credit {
      color: #10b981;
    }

    .transaction-amount.debit {
      color: #ef4444;
    }

    /* Empty State */
    .empty-state {
      padding: 64px 24px;
      height: 60svh;
      background: white;
      border-radius: 12px;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .empty-state .material-symbols-outlined {
      font-size: 64px;
      color: #d1d5db;
      margin-bottom: 12px;
    }


    .btn-custom {
      background-color: var(--primary);
      color: white;
      border-radius: 4px;
      font-weight: 500;
      align-items: center;
      justify-self: center;
      padding: 4px 8px;
      text-decoration: none;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="main-wrapper">
    <main class="container-fluid p-4">

      <!-- Page Header -->
      <div class="mb-4">
        <h3 class="mb-1">Transaction History</h3>
        <p class="text-muted mb-0">View your points earnings and redemptions</p>
      </div>

      <!-- Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-4">
          <div class="summary-card">
            <div class="summary-icon" style="background: var(--primary);">
              <span class="material-symbols-outlined" style="color: white; font-size: 28px;">account_balance_wallet</span>
            </div>
            <?php
            $totalPoints = array_sum(array_column($transactions, 'points'));
            ?>

            <div class="summary-value" style="color: #AB2732;">
              <?= number_format($totalPoints) ?>
            </div>

            <div class="summary-label">Current Balance</div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);">
              <span class="material-symbols-outlined" style="color: #065f46; font-size: 28px;">trending_up</span>
            </div>
            <div class="summary-value" style="color: #10b981;"><?= number_format($totalEarned) ?></div>
            <div class="summary-label">Total Earned</div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
              <span class="material-symbols-outlined" style="color: #991b1b; font-size: 28px;">trending_down</span>
            </div>
            <div class="summary-value" style="color: #ef4444;"><?= number_format($totalSpent) ?></div>
            <div class="summary-label">Total Redeemed</div>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <div class="d-flex gap-2">
            <a href="?filter=all" class="<?= $filter === 'all' ? 'btn-custom' : 'btn btn-sm btn-outline-secondary' ?>">All</a>
            <a href="?filter=credit" class="<?= $filter === 'credit' ? 'btn-custom' : 'btn btn-sm btn-outline-secondary' ?>">Earned</a>
            <a href="?filter=debit" class="<?= $filter === 'debit' ? 'btn-custom' : 'btn btn-sm btn-outline-secondary' ?>">Redeemed</a>
          </div>

          <a href="?export=csv&filter=<?= $filter ?>" class="btn btn-sm btn-success d-flex align-items-center">
            <span class="material-symbols-outlined me-1" style="font-size: 18px;">download</span>
            Export to Excel (CSV)
          </a>
        </div>
      </div>

      <!-- Transactions List -->
      <?php if (!empty($filteredTransactions)): ?>
        <div class="transaction-card">
          <?php foreach ($filteredTransactions as $transaction): ?>
            <div class="transaction-item">
              <div class="transaction-icon <?= $transaction['type'] ?>">
                <span class="material-symbols-outlined"><?= $transaction['icon'] ?? '' ?></span>
              </div>

              <div class="transaction-details">
                <div class="transaction-description">
                  <?= htmlspecialchars($transaction['description']) ?>
                </div>
                <div class="transaction-meta">
                  <span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle;">calendar_today</span>
                  <?= date('M d, Y', strtotime($transaction['transaction_date'])) ?>
                  <span class="mx-2">•</span>
                  <span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle;">schedule</span>
                  <?= date('g:i A', strtotime($transaction['transaction_time'])) ?>
                </div>
              </div>

              <div class="transaction-amount <?= $transaction['type'] ?>">
                <?= $transaction['type'] === 'credit' ? '+' : '' ?><?= number_format($transaction['points']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="transaction-card">
          <div class="empty-state">
            <span class="material-symbols-outlined">receipt_long</span>
            <h5 class="text-muted">No transactions found</h5>
            <p class="text-muted mb-0">Try adjusting your filters</p>
          </div>
        </div>
      <?php endif; ?>

    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>