<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'leaderboard';

function getMedalIcon($rank)
{
  if ($rank === 1) return '<span class="material-symbols-outlined text-warning" style="font-size:24px">workspace_premium</span>';
  if ($rank === 2) return '<span class="material-symbols-outlined" style="font-size:24px;color:#94a3b8">workspace_premium</span>';
  if ($rank === 3) return '<span class="material-symbols-outlined" style="font-size:24px;color:#cd7f32">workspace_premium</span>';
  return '<span class="text-muted fw-semibold">#' . $rank . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Leaderboard</title>
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

    .badge-icon {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin: 4px;
      transition: all 0.2s;
    }

    .badge-icon.active {
      background-color: var(--primary);
      color: white;
    }

    .badge-icon.inactive {
      background-color: var(--bg);
      color: #9ca3af;
    }

    .user-row {
      transition: all 0.2s;
      background-color: var(--bg) !important;
      height: 8svh;
    }

    .user-row:hover {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
    }

    .toggle-btn-group .btn {
      border-radius: 8px;
      font-weight: 500;
      padding: 8px 20px;
    }

    .badge-icon {
      position: relative;
      cursor: help;
    }

    .badge-icon .tooltip-text {
      visibility: hidden;
      background-color: #1f2937;
      color: white;
      text-align: center;
      border-radius: 6px;
      padding: 6px 12px;
      position: absolute;
      z-index: 1000;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      white-space: nowrap;
      font-size: 12px;
      opacity: 0;
      transition: opacity 0.2s;
    }

    .badge-icon .tooltip-text::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: #1f2937 transparent transparent transparent;
    }

    .badge-icon:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }

    .btn-custom {
      background-color: var(--primary);
      color: white;
      border-radius: 8px;
      font-weight: 500;
      padding: 8px 20px;
      align-items: center;
      justify-self: center;
      text-decoration: none;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="main-wrapper">
    <main class="container-fluid p-4 ">
      <div class="row" style="min-height: 100vh;">
        <div class="col-12 p-4">

          <!-- Page Header -->
          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
              <h3 class="mb-1">Leaderboard</h3>
            </div>
            <!-- Toggle -->
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-center">
              <a href="?type=monthly" class="<?= $type === 'monthly' ? 'btn-custom' : 'btn btn-outline-secondary' ?>">
                Monthly
              </a>
              <a href="?type=quarterly" class="<?= $type === 'quarterly' ? 'btn-custom' : 'btn btn-outline-secondary' ?>">
                Quarterly
              </a>
            </div>
          </div>

          <!-- Leaderboard Card -->
          <div class="leaderboard-card">
            <div>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr>
                      <th style="width:80px;font-size: 0.875rem;" class="ps-3 bg-light">Rank</th>
                      <th class="bg-light" style="font-size: 0.875rem;">Name</th>
                      <th class="text-end pe-3 bg-light" style="font-size: 0.875rem;">Points</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($leaderboard)): ?>
                      <tr>
                        <td colspan="3" class="text-center p-4 text-muted">No transactions found for this period.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($leaderboard as $key => $u):
                        $rank = $key + 1;
                      ?>
                        <tr class="user-row <?= ($u['emp_id'] == $this->session->userdata('emp_id')) ? 'current-user-row' : '' ?>">
                          <td class="ps-4 bg-light">
                            <?= getMedalIcon($rank) ?>
                          </td>
                          <td class="bg-light">
                            <div class="d-flex align-items-center">
                              <span class="fw-semibold me-2"><?= htmlspecialchars($u['name']) ?></span>

                              <?php
                              $streakCount = $streaks[$u['emp_id']] ?? 0;
                              if ($streakCount >= 3):
                              ?>

                                <span class="material-symbols-outlined" style="font-size: 18px; font-variation-settings: 'FILL' 1;">🔥</span>

                                <small class="fw-bold" style="font-size: 16px;">
                                  <?= $streakCount ?>
                                </small>
                              <?php endif; ?>

                              <?php if ($u['emp_id'] == ($this->session->userdata('emp_id'))): ?>
                                <span class="badge bg-warning text-dark ms-2">You</span>
                              <?php endif; ?>
                            </div>
                          </td>
                          <td class="text-end bg-light pe-4">
                            <span class="fw-bold" style="color: #AB2732"><?= number_format($u['points']) ?></span>
                            <span class="text-muted">pts</span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

            </div>
          </div>

        </div>
      </div>
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>