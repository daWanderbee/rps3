<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'dashboard';
define('MAX_POINTS', 50000);


// Calculate monthly totals for graph
function getMonthlyData($transactions)
{
  $monthlyData = [];

  foreach ($transactions as $txn) {
    $month = date('Y-m', strtotime($txn['transaction_date']));

    if (!isset($monthlyData[$month])) {
      $monthlyData[$month] = ['credit' => 0, 'debit' => 0];
    }

    if ($txn['type'] === 'credit') {
      $monthlyData[$month]['credit'] += $txn['points'];
    } else {
      $monthlyData[$month]['debit'] += abs((int)$txn['points']);
    }
  }

  // Sort by month
  ksort($monthlyData);

  return $monthlyData;
}

$monthlyData = getMonthlyData($transactions);

// Calculate statistics
$totalCredit = array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'credit'), 'points'));
$totalDebit = array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'debit'), 'points'));
$netPoints = $totalCredit + $totalDebit;

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>RPS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <!-- Chart.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: var(--bg);
    }

    .main-wrapper {
      margin-left: 200px;
      background-color: var(--bg);
      ;
    }

    @media (max-width: 768px) {
      .main-wrapper {
        margin-left: 0;
      }
    }

    /* Welcome Banner */
    .welcome-banner {
      background: var(--primary);
      color: white;
      padding: 32px;
      border-radius: 16px;
      margin-bottom: 24px;
      position: relative;
      overflow: hidden;
    }

    .welcome-banner::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .welcome-banner::after {
      content: '';
      position: absolute;
      bottom: -30%;
      left: -5%;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 50%;
    }

    .welcome-content {
      position: relative;
      z-index: 1;
    }


    /* Task Card */
    .task-card {
      background-color: white;
      padding: 12px;
      transition: all 0.2s;
      border: 2px solid transparent;
    }

    .task-card:hover {
      transform: translateX(8px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
      border-radius: 10px;
    }

    .task-icon {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
    }

    .task-progress {
      height: 6px;
      background: #e5e7eb;
      border-radius: 3px;
      overflow: hidden;
      margin-top: 8px;
    }

    .task-progress-bar {
      height: 100%;
      background: var(--primary);
      transition: width 0.3s;
    }

    .points-badge {
      background: var(--bg);
      color: var(--primary);
      padding: 4px 8px;
      justify-self: center;
      border-radius: 12px;
      font-weight: 500;
      font-size: 0.6rem;
      text-align: center;
    }


    /* Leaderboard Item */
    .lb-item {
      padding: 10px 12px;
      background: white;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.2s;
    }

    .lb-item:hover {
      transform: translateX(8px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
    }

    .lb-rank {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #6b7280;
      margin-right: 12px;
    }

    .lb-rank.gold {
      background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
      color: white;
    }

    .lb-rank.silver {
      background: linear-gradient(135deg, #e5e7eb 0%, #9ca3af 100%);
      color: white;
    }

    .lb-rank.bronze {
      background: linear-gradient(135deg, #d97706 0%, #92400e 100%);
      color: white;
    }

    /* Card */
    .card-custom {
      background: white;
      border-radius: 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      overflow: hidden;
    }

    .card-header-custom {
      padding: 16px;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card-body-custom {
      padding: 16px;
    }

    .view-all-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      font-size: 0.875rem;
      transition: all 0.2s;
    }

    .view-all-link:hover {
      color: #8B1F28;
    }
  </style>

</head>

<body>

  <!-- Sidebar -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="main-wrapper">
    <main class="container-fluid p-4">

      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <div class="welcome-content">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h2 class="mb-2">Welcome, <?= $user['name'] ?? "User" ?></h2>
              <p class="mb-0 opacity-90">Track your progress, complete tasks, and earn amazing rewards</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
              <div style="font-size: 2.5rem; font-weight: 700;"><?= number_format($user['points_received'] ?? 0 ) ?></div>
              <div class="opacity-90">Total Points</div>
            </div>
          </div>
        </div>
      </div>


      <!-- Main Grid -->
      <div class="row">

        <!-- Left Column -->
        <div class="col mb-4">

          <!-- Progress Card -->
          <div class="card-custom mb-4">
            <div class="card-header-custom">
              <h5 class="mb-0">Points Trends</h5>
              <a href="/rps/transactions" class="view-all-link">View Points History →</a>
            </div>
            <div class="card-body-custom">
              <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Tasks Card -->
          <div class="card-custom">
            <div class="card-header-custom">
              <h5 class="mb-0">Available Tasks</h5>
              <a href="/rps/tasks" class="view-all-link">View All →</a>
            </div>
            <div class="card-body-custom">
              <?php foreach ($tasks as $task): ?>
                <div class="task-card">
                  <div class="d-flex align-items-start">
                    <div class="task-icon">
                      <span class="material-symbols-outlined" style="font-size:20px;">task_alt</span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <div class="d-flex justify-content-between align-items-center mb-0">
                        <h6 class="mb-0" style="font-size: 0.875rem;"><?= $task['title'] ?></h6>
                        <span class="points-badge"><?= $task['points'] ?> pts</span>
                      </div>
                      <div class="task-progress mb-1">
                        <div class="task-progress-bar" style="width: <?= $task['is_completed'] ? '100' : '0' ?>%"></div>
                      </div>
                      <?php if ($task['is_completed']): ?>
                        <small class="text-muted">Completed</small>
                      <?php else: ?>
                        <small class="text-muted">Not started</small>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>

        <!-- Right Column -->
        <div class="col">

          <!-- Leaderboard Card -->
          <div class="card-custom mb-4">
            <div class="card-header-custom">
              <h5 class="mb-0">Leaderboard</h5>
              <a href="/rps/leaderboard" class="view-all-link">View All →</a>
            </div>
            <div class="card-body-custom">
              <?php foreach ($leaderboard as $users): ?>
                <div class="lb-item">
                  <div class="d-flex align-items-center">
                    <div class="lb-rank <?= $users['rank'] === 1 ? 'gold' : ($users['rank'] === 2 ? 'silver' : ($users['rank'] === 3 ? 'bronze' : '')) ?>">
                      <?= $users['rank'] ?>
                    </div>
                    <span class="fw-semibold"><?= $users['name'] ?></span>
                  </div>
                  <span class="fw-bold" style="color: #AB2732;"><?= number_format($users['points']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Featured Rewards Card -->
          <div class="card-custom">
            <div class="card-header-custom">
              <h5 class="mb-0">Featured Rewards</h5>
              <a href="/rps/redeem" class="view-all-link">View All →</a>
            </div>
            <div class="card-body-custom">
              <?php
              // The controller already provides $featuredRewards
              ?>


              <?php foreach ($featuredRewards as $reward): ?>
                <?php
                $isLocked = isset($reward['lock_days']) && $reward['lock_days'] > 0;
                $canAfford = ($user['points_received'] ?? 0 )  >= $reward['points'];

                $isDisabled = !$canAfford || $isLocked;
                $btnText = $canAfford ? 'Redeem Now' : 'Insufficient Points';
                if ($isLocked) $btnText = "Locked ({$reward['lock_days']}d)";
                ?>
                <div class="task-card" style="<?= !$canAfford ? 'opacity: 0.6;' : '' ?>">
                  <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex align-items-start flex-grow-1">
                      <div class="task-icon">
                        <span class="material-symbols-outlined" style="font-size:24px;">redeem</span>
                      </div>
                      <div class="ms-3">
                        <h6 class="mb-1"><?= $reward['name'] ?></h6>
                        <span class="text-muted" style="font-size: 0.875rem;"><?= $reward['points'] ?> pts</span>
                      </div>
                    </div>
                    <?php if ($isLocked): ?>
                      <span class="points-badge stock-limited" style="background: #fff1f2; color: #be123c;">
                        <?= $reward['lock_days'] ?>d left
                      </span>
                      
                    <?php endif; ?>
                  </div>
                  <button class="btn btn-sm" style="background: var(--primary); color: var(--bg); width: 100%; margin-top: 12px; font-weight: 600;" <?= $isDisabled ? 'disabled' : '' ?> onclick="location.href='/rps/redeem'">
                    <?= $btnText ?>
                  </button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Prepare data for Chart.js
    const monthlyData = <?= json_encode($monthlyData) ?>;

    const labels = Object.keys(monthlyData).map(month => {
      const date = new Date(month + '-01');
      return date.toLocaleDateString('en-US', {
        month: 'short',
        year: 'numeric'
      });
    });

    const creditData = Object.values(monthlyData).map(d => d.credit);
    const debitData = Object.values(monthlyData).map(d => d.debit);

    // Create chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
            label: 'Points Earned',
            data: creditData,
            borderColor: 'rgb(5, 150, 105)',
            backgroundColor: 'rgba(5, 150, 105, 0.50)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
          },
          {
            label: 'Points Redeemed',
            data: debitData,
            borderColor: 'rgb(220, 38, 38)',
            backgroundColor: 'rgba(220, 38, 38, 0.50)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
          }
        ]

      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              usePointStyle: true,
              padding: 10,
              font: {
                size: 13,
                weight: '600'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
              size: 12,
              weight: 'bold'
            },
            bodyFont: {
              size: 10
            },
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' pts';
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              font: {
                size: 10
              }
            },
            grid: {
              display: false
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString() + ' pts';
              },
              font: {
                size: 10
              }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
        }
      }
    });
  </script>

</body>

</html>