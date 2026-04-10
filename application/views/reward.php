<?php
$currentPage = 'reward';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Rewards Page</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <style>
    :root {
      --bg: #f5f5f5;
      --primary: #AB2732;
    }

    body {
      background: var(--bg);
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

    .reward-banner {
      background: var(--primary);
      color: white;
      padding: 28px;
      border-radius: 24px;
      text-align: center;
      margin-bottom: 32px;
    }

    .reward-card {
      background: white;
      border-radius: 12px;
      padding: 32px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .rule-card {
      background: #fbfbfb;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
    }

    .rule-card span {
      font-size: 36px;
      color: #AB2732;
      margin-bottom: 8px;
    }

    .submit-btn {
      background: var(--primary);
      color: white;
      font-weight: 600;
      padding: 12px;
      border-radius: 8px;
      border: none;
      width: 100%;
    }

    .submit-btn:hover {
      background: #8B1F28;
    }

    .submit-btn:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="main-wrapper">
    <main class="container-fluid p-4">

      <!-- Header -->
      <div class="reward-banner d-flex align-items-center">
        <span class="material-symbols-outlined" style="font-size:64px;">volunteer_activism</span>
        <div class="d-flex flex-column ms-3 align-items-start">
          <div class="mt-2 opacity-90">Reward Points</div>
          <h3 class="fw-bold" id="balanceDisplay"><?= htmlspecialchars($userBalance) ?> pts</h3>
        </div>


      </div>

      <!-- Rules -->
      <div class="row g-2 mb-4">
        <div class="col-6">
          <div class="rule-card">
            <span class="material-symbols-outlined">balance</span>
            <h6>Transfers Points</h6>
            <div class="d-flex flex-column">
              <small>Sevak can give 5000 pts</small>
              <small>Sangrakshak can give 2000 pts</small>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="rule-card">
            <span class="material-symbols-outlined">swap_horiz</span>
            <div>
              <small class="text-muted">
                <strong>Transfers Limits</strong><br>
                <?php if (isset($rewardLimits[$userRole])): ?>
                  <?php foreach ($rewardLimits[$userRole] as $role => [$min, $max]): ?>
                    <?= $min ?>–<?= $max ?> pts for <?= $role ?><br>
                  <?php endforeach; ?>
                <?php endif; ?>
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Alert Container (for AJAX alerts) -->
      <div id="alertContainer">
        <?php if (isset($alert) && $alert): ?>
          <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($alert['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
      </div>

      <!-- Reward Form -->
      <div class="reward-card">


        <h5 class="mb-4">Send Reward</h5>

        <form method="post" id="rewardForm" action="<?= base_url('reward') ?>">

          <?= $this->security->get_csrf_field() ?>

          <!-- Recipient -->
          <div class="mb-3">
            <label class="form-label">Select Recipient</label>
            <select class="form-select" name="recipient" id="recipient" required placeholder="Search by Name or Emp ID...">
              <option value="">Search by Name or Emp ID...</option>
              <?php foreach ($recipients as $r): ?>
                <?php if ($r['emp_cat'] !== '1'): ?>
                  <option value="<?= $r['id'] ?>"
                    data-role="<?= $r['emp_cat'] ?>"
                    data-empid="<?= $r['id'] ?>"> <?= htmlspecialchars($r['name']) ?> (ID: <?= $r['id'] ?>) - <?= $r['emp_cat'] == '3' ? 'Utpadak' : ($r['emp_cat'] == '2' ? 'Sangrakshak' : 'Sevak') ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Reason -->
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <input type="text" class="form-control" name="reason" placeholder="E.g., Excellent teamwork" required>
          </div>

          <!-- Points -->
          <div class="mb-3">
            <label class="form-label">Points</label>
            <input type="number" class="form-control" name="points" id="points" min="1" required>
            <small class="text-muted" id="limitHint">Select a recipient to see limits</small>
          </div>

          <button type="submit" class="submit-btn" id="submitBtn">
            <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">send</span>
            Send
          </button>

        </form>
      </div>

    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Reward limits from PHP
    const rewardLimits = <?= json_encode($rewardLimits[$userRole] ?? []) ?>;
    // 1. Map the numeric emp_cat to the string names in your rewardLimits
    const categoryMap = {
      '3': 'Utpadak',
      '2': 'Sangrakshak',
      '1': 'Sevak' // Included for completeness
    };

    // 2. Initialize Tom Select
    const recipientSelect = new TomSelect("#recipient", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc"
      },
      searchField: ['text', 'empid'],
      placeholder: "Type Name or Emp ID...",
      allowEmptyOption: true,
    });

    // 3. Unified Change Handler
    recipientSelect.on('change', function(value) {
      const limitHint = document.getElementById('limitHint');
      const pointsInput = document.getElementById('points');

      // Get the original <option> element to read data attributes
      const selectedOption = document.getElementById('recipient').querySelector(`option[value="${value}"]`);

      if (!value || !selectedOption) {
        limitHint.textContent = 'Select a recipient to see limits';
        pointsInput.removeAttribute('max');
        pointsInput.min = 1;
        return;
      }

      // Get category ID (e.g., "3") and map it to Name (e.g., "Utpadak")
      const catId = selectedOption.dataset.role;
      const roleName = categoryMap[catId];

      if (roleName && rewardLimits[roleName]) {
        const [min, max] = rewardLimits[roleName];
        limitHint.innerHTML = `<strong>Limits for ${roleName}:</strong> ${min} – ${max} points`;
        pointsInput.min = min;
        pointsInput.max = max;
        pointsInput.placeholder = `${min}-${max}`;
      } else {
        limitHint.textContent = 'No transfer limits defined for this role.';
        pointsInput.removeAttribute('max');
      }
    });

    // Update limit hint when recipient changes
    document.getElementById('recipient').addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const role = selectedOption.dataset.role;
      const limitHint = document.getElementById('limitHint');
      const pointsInput = document.getElementById('points');

      if (role && rewardLimits[role]) {
        const [min, max] = rewardLimits[role];
        limitHint.textContent = `Limits for ${role}: ${min}–${max} points`;
        pointsInput.min = min;
        pointsInput.max = max;
      } else {
        limitHint.textContent = 'Select a recipient to see limits';
        pointsInput.min = 1;
        pointsInput.removeAttribute('max');
      }
    });

    // Handle form submission with AJAX
    document.getElementById('rewardForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const submitBtn = document.getElementById('submitBtn');
      const formData = new FormData(this);

      // Disable submit button
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

      fetch('<?= base_url('reward') ?>', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="<?= $this->security->get_csrf_token_name() ?>"]').value
          },
          body: formData
        })
        .then(res => res.text())
        .then(text => {
          console.log('Response:', text);

          // Try to parse as JSON
          let data;
          try {
            data = JSON.parse(text);
          } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Server returned invalid response');
          }

          // Show alert
          const alertContainer = document.getElementById('alertContainer');
          const alertType = data.status === 'success' ? 'success' : 'danger';

          alertContainer.innerHTML = `
      <div class="alert alert-${alertType} alert-dismissible fade show">
        ${data.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;

          // Update balance display
          if (data.newBalance !== undefined) {
            document.getElementById('balanceDisplay').textContent = data.newBalance + ' points';
          }

          // Reset form on success
          if (data.status === 'success') {
            document.getElementById('rewardForm').reset();
            document.getElementById('limitHint').textContent = 'Select a recipient to see limits';
            document.getElementById('points').removeAttribute('max');
          }

          // Scroll to alert
          alertContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
          });
        })
        .catch(error => {
          console.error('Error:', error);
          const alertContainer = document.getElementById('alertContainer');
          alertContainer.innerHTML = `
      <div class="alert alert-danger alert-dismissible fade show">
        An error occurred. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
        })
        .finally(() => {
          // Re-enable submit button
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">send</span>Send';
        });
    });
  </script>

</body>

</html>