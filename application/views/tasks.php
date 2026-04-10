<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'tasks';


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Tasks</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <style>
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

    /* Points Banner */
    .points-banner {
      background: var(--primary);
      color: white;
      padding: 24px 32px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(171, 39, 50, 0.2);
      margin-bottom: 24px;
    }


    /* Program Card - List Style */
    .program-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s;
      border: 2px solid transparent;
      margin-bottom: 16px;
      display: flex;
      flex-direction: row;
      align-items: stretch;
    }

    .program-card:hover {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border-color: var(--primary);
    }

    .program-card.completed {
      opacity: 0.8;
    }

    .program-icon-section {
      background: var(--bg);
      padding: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 120px;
      flex-shrink: 0;
    }

    .program-icon {
      font-size: 48px !important;
      color: var(--primary);
    }

    .program-content {
      padding: 24px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .program-header-section {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 12px;
    }

    .program-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 8px;
    }

    .program-description {
      color: #6b7280;
      font-size: 0.95rem;
      margin-bottom: 16px;
    }

    .program-meta {
      display: flex;
      gap: 24px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #6b7280;
      font-size: 0.875rem;
    }

    .meta-item .material-symbols-outlined {
      font-size: 18px;
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .status-in_progress {
      background: #dbeafe;
      color: #1e40af;
    }

    .status-completed {
      background: #d1fae5;
      color: #065f46;
    }

    .status-available {
      background: #fef3c7;
      color: #92400e;
    }


    .progress-section {
      margin-bottom: 16px;
    }

    .progress-bar-container {
      height: 8px;
      background: #e5e7eb;
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 8px;
    }

    .progress-bar-fill {
      height: 100%;
      background: var(--primary);
      transition: width 0.3s;
    }

    .program-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 16px;
      border-top: 1px solid var(--bg);
    }

    .points-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
    }

    .action-btn {
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.2s;
      border: none;
      cursor: pointer;
      font-size: 0.95rem;
    }

    .btn-primary-custom {
      background: var(--primary);
      color: white;
    }

    .btn-primary-custom:hover {
      background: var(--primary);
      transform: scale(1.05);
    }

    .btn-continue {
      background: #3b82f6;
      color: white;
    }

    .btn-continue:hover {
      background: #2563eb;
      transform: scale(1.05);
    }

    .btn-completed {
      background: #10b981;
      color: white;
      cursor: default;
    }

    /* Task Card */
    .task-card {
      background: white;
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 12px;
      transition: all 0.2s;
      border: 2px solid transparent;
    }

    .task-card:hover {
      transform: translateX(4px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
    }

    .task-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
    }

    .task-progress {
      height: 6px;
      background: var(--bg);
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
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.75rem;
    }


    @media (max-width: 768px) {
      .program-card {
        flex-direction: column;
      }

      .program-icon-section {
        min-width: unset;
        padding: 24px;
      }

      .category-btn {
        padding: 6px 12px;
        font-size: 0.6rem;
      }

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
        <h3 class="mb-1">Tasks</h3>
        <p class="text-muted mb-0">Explore courses, events, and activities to earn points</p>
      </div>

      <!-- Points Banner -->
      <div class="points-banner">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-3">
            <span class="material-symbols-outlined" style="font-size: 40px;">account_balance_wallet</span>
            <div>
              <div class="small opacity-90">Your Current Balance</div>
              <div style="font-size: 2rem; font-weight: 700;"><?= number_format($userPoints) ?> pts</div>
            </div>
          </div>
          <div class="text-end d-none d-md-block">
            <div class="small opacity-90">Complete more programs</div>
            <div style="font-size: 1.25rem; font-weight: 600;">to earn rewards!</div>
          </div>
        </div>
      </div>



      <!-- Programs List -->
      <div class="programs-list">
        <?php foreach ($tasks as $task): ?>
          <div class="task-card">
            <div class="d-flex align-items-start">
              <div class="task-icon">
                <span class="material-symbols-outlined" style="font-size:24px;">task_alt</span>
              </div>
              <div class="flex-grow-1 ms-3">
                <div class="d-flex justify-content-between align-items-start mb-0">
                  <h6 class="mb-0"><?= $task['title'] ?></h6>
                  <span class="points-badge">+<?= $task['points'] ?></span>
                </div>
                <p class="text-muted mb-1" style="font-size:0.7rem;"><?= $task['description'] ?></p>
                <div class="task-progress">
                  <div class="task-progress-bar" style="width: <?= $task['is_completed'] ? '100%' : '0%' ?>;"></div>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= $task['is_completed'] ? 'Completed' : 'Not started' ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (empty($tasks)): ?>
        <div class="text-center py-5">
          <span class="material-symbols-outlined" style="font-size:64px;color:#d1d5db;">search_off</span>
          <p class="text-muted mt-3">No tasks found in this category</p>
        </div>
      <?php endif; ?>

      <!-- Bootstrap JS -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

      <script>
        function enrollProgram(id, title) {
          if (confirm(`Enroll in "${title}"?`)) {
            alert('Successfully enrolled! You can now start earning points.');
            // window.location.href = '/program-detail.php?id=' + id;
          }
        }

        function continueProgram(id) {
          alert('Redirecting to your in-progress program...');
          // window.location.href = '/program-continue.php?id=' + id;
        }
      </script>

</body>

</html>