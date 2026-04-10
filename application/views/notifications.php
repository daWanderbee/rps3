<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'notifications';


// Filter by read status
$filter = $_GET['filter'] ?? 'all';
$filteredNotifications = $notifications;

if ($filter === 'unread') {
  $filteredNotifications = array_filter($notifications, fn($n) => $n['isUnread']);
} elseif ($filter === 'read') {
  $filteredNotifications = array_filter($notifications, fn($n) => !$n['isUnread']);
}

$unreadCount = count(array_filter($notifications, fn($n) => $n['isUnread']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Notifications</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="<?= $this->security->get_csrf_token_name() ?>" content="<?= $this->security->get_csrf_hash() ?>">

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

    .mark-all-btn {
      background: transparent;
      border: 2px solid var(--primary);
      color: #6b7280;
      padding: 4px 8px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.2s;
      cursor: pointer;
    }

    .mark-all-btn:hover {
      border-color: var(--primary);
      color: var(--primary);
      background: var(--bg);
      scale: 1.05;
    }

    /* Notification Card */
    .notification-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      margin-bottom: 12px;
      overflow: hidden;
      transition: all 0.2s;
      cursor: pointer;
    }

    .notification-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
    }

    .notification-card.unread {
      border-left: 4px solid var(--primary);
      background: #fffbfb;
    }

    .notification-card.unread:hover {
      border-left: 6px solid var(--primary);
    }

    .notification-content {
      padding: 20px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
    }

    .notification-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: linear-gradient(135deg, rgba(171, 39, 50, 0.1) 0%, rgba(171, 39, 50, 0.05) 100%);
    }

    .notification-icon .material-symbols-outlined {
      font-size: 24px;
    }

    .notification-body {
      flex-grow: 1;
      min-width: 0;
    }

    .notification-title {
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 4px;
      font-size: 0.95rem;
    }

    .notification-card.unread .notification-title {
      color: var(--primary);
    }

    .notification-message {
      color: #6b7280;
      font-size: 0.875rem;
      margin-bottom: 8px;
      line-height: 1.5;
    }

    .notification-time {
      color: #9ca3af;
      font-size: 0.75rem;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .notification-badge {
      width: 8px;
      height: 8px;
      background: var(--primary);
      border-radius: 50%;
      flex-shrink: 0;
    }

    .notification-actions {
      display: flex;
      gap: 8px;
      flex-shrink: 0;
    }

    .action-btn {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      border: none;
      background: var(--bg);
      color: #6b7280;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      cursor: pointer;
    }

    .action-btn:hover {
      background: var(--primary);
      color: white;
    }

    /* Empty State */
    .empty-state {
      padding: 64px 24px;
      height: 70svh;
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
      margin-bottom: 16px;
    }

    .empty-state h5 {
      color: #6b7280;
      margin-bottom: 8px;
    }

    .empty-state p {
      color: #9ca3af;
      margin-bottom: 0;
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
        <div class="d-flex align-items-center gap-3">
          <h3 class="mb-1">Notifications</h3>
          <?php if ($unreadCount > 0): ?>
            <span class="btn-custom"><?= $unreadCount ?> New</span>
          <?php endif; ?>
        </div>
        <p class="text-muted mb-0">Stay updated with your latest activities and alerts</p>
      </div>

      <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <div class="d-flex gap-2">
            <a href="?filter=all" class="<?= $filter === 'all' ? 'btn-custom' : 'btn btn-sm btn-outline-secondary' ?>">All</a>
            <a href="?filter=unread" class="<?= $filter === 'unread' ? 'btn-custom' : 'btn btn-sm btn-outline-secondary' ?>">Unread</a>
            <a href="?filter=read" class="<?= $filter === 'read' ? 'btn-custom' : 'btn btn-sm btn-outline-secondary' ?>">Read</a>
          </div>
          <div class="d-flex gap-2">
            <button class="mark-all-btn mark-all-read">
              <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">done_all</span>
              Mark all read
            </button>

            <button class="mark-all-btn discard-all-btn">
              <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">delete_sweep</span>
              Discard all
            </button>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <?php if (!empty($filteredNotifications)): ?>
        <?php foreach ($filteredNotifications as $notification): ?>
          <div class="notification-card <?= $notification['isUnread'] ? 'unread' : '' ?>"
            data-id="<?= $notification['id'] ?>">
            <div class="notification-content">
              <div class="notification-icon" style="background: var(--bg)">
                <span class="material-symbols-outlined" style="color: #121212;">
                  <?= $notification['icon'] ?>
                </span>
              </div>

              <div class="notification-body">
                <div class="notification-title">
                  <?= htmlspecialchars($notification['description']) ?>
                </div>
                <div class="notification-time">
                  <?php if ($notification['isUnread']): ?>
                    <span class="notification-badge"></span>
                  <?php endif; ?>
                  <span class="material-symbols-outlined" style="font-size:14px;">schedule</span>
                  <?= $notification['transaction_time'] ?>
                </div>
              </div>

              <div class="notification-actions">
                <?php if ($notification['isUnread']): ?>
                  <button class="action-btn mark-read" title="Mark as read">
                    <span class="material-symbols-outlined" style="font-size:18px;">check</span>
                  </button>
                <?php endif; ?>
                <button class="action-btn delete" title="Delete">
                  <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <span class="material-symbols-outlined">notifications_off</span>
          <h5>No notifications yet</h5>
          <p>When you have new notifications, they'll appear here</p>
        </div>
      <?php endif; ?>

    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Mark all as read
    document.querySelector('.mark-all-read')?.addEventListener('click', () => {
      fetch('/notifications/read-all', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= $this->security->get_csrf_hash() ?>' // Add CSRF Token here
          },
        })
        .then(() => {
          document.querySelectorAll('.notification-card').forEach(card => {
            card.classList.remove('unread');
          });
          document.querySelector('.unread-badge')?.remove();
        });
    });


    // Discard Single Notification
    document.querySelectorAll('.action-btn.delete').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const card = this.closest('.notification-card');
        const id = card.dataset.id;

        fetch('<?= base_url('notifications/discard') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '<?= $this->security->get_csrf_hash() ?>'
          },
          body: `id=${id}`
        }).then(() => card.remove());
      });
    });

    // Discard All Notifications
    document.querySelector('.discard-all-btn')?.addEventListener('click', () => {
      fetch('<?= base_url('notifications/discard-all') ?>', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '<?= $this->security->get_csrf_hash() ?>'
          }
        })
        .then(() => {
          location.reload(); // Simplest way to show the empty state
        });
    });

    document.querySelectorAll('.mark-read').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const card = this.closest('.notification-card');
        const id = card.dataset.id;

        fetch('<?= base_url('notifications/read') ?>', { // Use base_url for safety
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': '<?= $this->security->get_csrf_hash() ?>' // Add CSRF Token here
            },
            body: `id=${id}`
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'ok') {
              card.classList.remove('unread');
              this.remove();
            }
          });
      });
    });
  </script>

</body>

</html>