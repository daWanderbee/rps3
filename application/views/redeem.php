<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'redeem';

$categories = [
  'all' => 'All',
  'everyday_rewards' => 'Everyday',
  'lifestyle_essentials' => 'Lifestyle Essentials',
  'premium_merchandise' => 'Merchandise',
  'tech_and_vouchers' => 'Tech & Vouchers',
  'wellbeing' => 'Mindful Living & Wellbeing',
  'lifestyle_experiences' => 'Lifestyle & Experiences'
];

$selectedCategory = $_GET['category'] ?? 'all';

$hasRewards = false;

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Redeem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <!-- CSRF Token -->
  <meta name="<?= $this->security->get_csrf_token_name() ?>" content="<?= $this->security->get_csrf_hash() ?>">

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

    .points-banner {
      background: var(--primary);
      color: white;
      padding: 16px;
      border-radius: 12px;
      margin-bottom: 16px;
    }

    .points-value {
      font-size: 2rem;
      font-weight: 700;
    }

    .reward-card {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      transition: all 0.3s;
      border: 2px solid transparent;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .reward-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border-color: var(--primary);
    }

    .reward-card.insufficient {
      opacity: 0.6;
    }

    .reward-icon {
      padding: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .reward-body {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .reward-icon img {
      aspect-ratio: 2 / 1;
      border-radius: 8px;
    }


    .reward-name {
      font-size: 1.1rem;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 8px;
    }

    .reward-description {
      color: #6b7280;
      font-size: 0.9rem;
      margin-bottom: 16px;
      flex-grow: 1;
    }

    .reward-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 16px;
      border-top: 1px solid var(--bg);
    }

    .reward-points {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--primary);
    }

    .stock-badge {
      font-size: 0.75rem;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
    }

    .stock-available {
      background: var(--bg);
      color: #065f46;
    }

    .stock-limited {
      background: var(--bg);
      color: var(--primary);
    }

    .redeem-btn {
      background: var(--primary);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.2s;
      width: 100%;
      margin-top: 12px;
    }

    .redeem-btn:hover:not(:disabled) {
      background: #8B1F28;
      transform: scale(1.02);
    }

    .redeem-btn:disabled {
      background: #d1d5db;
      cursor: not-allowed;
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


    @media (max-width: 768px) {
      .main-wrapper {
        margin-left: 0;
      }
    }

    @media (max-width: 576px) {

      .btn-custom,
      .btn-outline-secondary {
        font-size: 0.5rem !important;
        /* smaller text */
        padding: 4px 6px !important;
        /* tighter padding */
        border-radius: 4px;
      }

    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="main-wrapper">
    <main class="container-fluid">
      <div class="row">
        <div class="col-12 p-4">

          <!-- Page Header -->
          <div class="mb-4">
            <h3 class="mb-1">Redeem Store</h3>
            <p class="text-muted mb-0">Redeem your points for exciting rewards</p>
          </div>

          <!-- Points Banner -->
          <div class="points-banner">
            <div class="row align-items-center flex-row">
              <div class="col-8">
                <div class="d-flex align-items-center gap-3">
                  <span class="material-symbols-outlined" style="font-size: 48px;">account_balance_wallet</span>
                  <div>
                    <div class="opacity-90">Your Available Points</div>
                    <div class="points-value" id="userPoints">
                      <?= number_format($userPoints) ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-4 text-end">
                <a href="/rps/transactions" class="btn btn-light">
                  <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">history</span>
                  History
                </a>
              </div>
            </div>
          </div>

          <!-- Category Filter -->
          <div class="mb-4">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-center">
              <?php foreach ($categories as $key => $label): ?>
                <a
                  href="?category=<?= $key ?>"
                  class="<?= $selectedCategory === $key ? 'btn-custom' : 'btn btn-outline-secondary' ?>">
                  <?= $label ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>



          <!-- Rewards Grid -->
          <div class="row g-4">
            <?php foreach ($rewards as $reward): ?>
              <?php
              $hasRewards = true;
              $isLocked = isset($reward['lock_days']) && $reward['lock_days'] > 0;
              $canAfford = $userPoints >= $reward['points'];

              $btnDisabled = !$canAfford || $isLocked;
              $btnText = 'Redeem Now';
              if ($isLocked) {
                $btnText = "Locked for {$reward['lock_days']}d";
              } elseif (!$canAfford) {
                $btnText = 'Insufficient Points';
              }
              if ($selectedCategory !== 'all' && $reward['category'] !== $selectedCategory) {
                continue;
              }
              $canAfford = $userPoints >= $reward['points'];
              ?>

              <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="reward-card <?= !$canAfford ? 'insufficient' : '' ?>">
                  <div class="reward-icon">
                    <?php $imgPath = '/uploads/rps' . $reward['image']; ?>

                  <img src="<?= $imgPath ?>"
                  alt="<?= htmlspecialchars($reward['name']) ?>"
                  class="img-fluid">
                  </div>

                  <div class="reward-body">
                    <div class="reward-name"><?= htmlspecialchars($reward['name']) ?></div>
                    <div class="reward-description"><?= htmlspecialchars($reward['description']) ?></div>

                    <div class="reward-footer">
                      <div class="reward-points"><?= number_format($reward['points']) ?> pts</div>
                      <?php if ($isLocked): ?>
                        <span class="stock-badge stock-limited">
                          Available in <?= $reward['lock_days'] ?> days
                        </span>
                      <?php else: ?>
                        <span class="stock-badge stock-available">Available</span>
                      <?php endif; ?>
                    </div>

                    <button
                      class="redeem-btn"
                      data-reward-id="<?= $reward['id'] ?>"
                      data-reward-points="<?= $reward['points'] ?>"
                      <?= $btnDisabled ? 'disabled' : '' ?>
                      onclick="openRedeemModal(<?= $reward['id'] ?>, '<?= htmlspecialchars($reward['name'], ENT_QUOTES) ?>', <?= $reward['points'] ?>, <?= $reward['frequency_days'] ?>, '<?= htmlspecialchars($reward['delivery_info'], ENT_QUOTES) ?>')">
                      <?= $btnText ?>
                    </button>

                  </div>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (!$hasRewards): ?>
              <div class="col-12">
                <p class="text-center text-muted">No rewards available in this category.</p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let userPoints = <?= $userPoints ?>;
    let selectedReward = {
      id: null,
      points: 0,
      frequency_days: 0,
      delivery_info: ''
    };

    function openRedeemModal(id, name, points, frequency_days, delivery_info) {
      selectedReward.id = id;
      selectedReward.points = points;
      selectedReward.frequency_days = frequency_days;
      selectedReward.delivery_info = delivery_info;

      if(delivery_info !== "Fast delivery" && delivery_info !== "According to the plan" && delivery_info !== "Instant delivery"){
        delivery_info = "Delivery in " + delivery_info;
      }
      document.getElementById('redeemRewardId').value = id;
      document.getElementById('redeemRewardName').innerText = name;
      document.getElementById('redeemRewardPoints').innerText =
        points.toLocaleString() + ' pts';

      document.getElementById('redeemRewardFrequency').innerText = frequency_days + ' days';
      document.getElementById('redeemRewardDelivery').innerText = delivery_info;

      new bootstrap.Modal(document.getElementById('redeemModal')).show();
    }

    async function confirmRedeem() {
      if (userPoints < selectedReward.points) return;

      const rewardName = document.getElementById('redeemRewardName').innerText;
      const confirmBtn = event.target; // Get reference to the clicked button
      confirmBtn.disabled = true; // Prevent double-clicks

      try {
        const csrfToken = document.querySelector('meta[name="X-CSRF-TOKEN"]').getAttribute('content');
        const response = await fetch('/redeem/processRedemption', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            reward_id: selectedReward.id,
            reward_name: rewardName,
            points: selectedReward.points,
            frequency_days: selectedReward.frequency_days,
            delivery_info: selectedReward.delivery_info
          })
        });

        const result = await response.json();

        if (result.success) {
          userPoints -= selectedReward.points;

          document.getElementById('userPoints').innerText = userPoints.toLocaleString();

          const btn = document.querySelector(`[data-reward-id="${selectedReward.id}"]`);
          if (btn) {
            btn.disabled = true;
            btn.innerText = 'Redeemed';
            btn.classList.add('btn-secondary');
          }

          bootstrap.Modal.getInstance(document.getElementById('redeemModal')).hide();
          showToast('Reward redeemed and transaction recorded!');
        } else {
          alert('Error: ' + (result.message || 'Could not process redemption.'));
        }
      } catch (error) {
        console.error('Error:', error);
        alert('A network error occurred.');
      } finally {
        // Re-enable the button in case of error
        confirmBtn.disabled = false;
      }
    }

    function showToast(message) {
      document.getElementById('toastMessage').innerText = message;
      new bootstrap.Toast(document.getElementById('redeemToast')).show();
    }
  </script>



  <!-- Redeem Modal -->
  <div class="modal fade" id="redeemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title" id="redeemModalTitle">Redeem Reward</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <p class="mb-2">
            You are about to redeem:
          </p>

          <h6 class="fw-bold" id="redeemRewardName"></h6>

          <p class="text-muted mb-0">
            Points required:
            <strong id="redeemRewardPoints"></strong>
          </p>
          <p class="text-muted mb-0">
            Redeem frequency:
            <strong id="redeemRewardFrequency"></strong> | <span id="redeemRewardDelivery"></span>

          </p>

          <input type="hidden" id="redeemRewardId">
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
          <button class="btn btn-primary" onclick="confirmRedeem()">
            Confirm Redeem
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="redeemToast" class="toast align-items-center text-bg-success border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">
          Reward redeemed successfully!
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>



</body>

</html>