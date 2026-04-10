<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'support';

$faqCategories = [];
foreach ($faqs as $faq) {
  $faqCategories[$faq['category']][] = $faq;
}

$selectedCategory = $_GET['category'] ?? 'all';
$filterCategories = ['all' => 'All'];

foreach ($faqCategories as $categoryName => $_) {
  $filterCategories[$categoryName] = $categoryName;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Help & Support</title>
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
      background: var(--bg);
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

    /* Header Banner */
    .help-banner {
      background: var(--primary);
      color: white;
      padding: 24px 20px;
      border-radius: 24px;
      margin-bottom: 24px;
      text-align: center;
    }

    .help-banner h2 {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .help-banner p {
      opacity: 0.7;
      font-size: 0.75rem;
    }

    /* Search Box */
    .search-box {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      margin-bottom: 32px;
    }

    .search-input {
      width: 100%;
      padding: 12px 12px 12px 60px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.2s;
    }

    .search-input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(171, 39, 50, 0.1);
    }

    .search-wrapper {
      position: relative;
    }

    .search-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
    }

    /* Category Section */
    .category-section {
      margin-bottom: 32px;
    }

    .category-header {
      background: white;
      padding: 20px 24px;
      border-radius: 12px 12px 0 0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .category-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
    }

    .category-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #1f2937;
      margin: 0;
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

    /* FAQ Item */
    .faq-item {
      background: white;
      border-bottom: 1px solid var(--bg);
      transition: all 0.2s;
    }

    .faq-item:last-child {
      border-bottom: none;
      border-radius: 0 0 12px 12px;
    }

    .faq-question {
      padding: 20px 24px;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.2s;
      user-select: none;
    }

    .faq-question:hover {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border: 2px solid var(--primary);
    }

    .faq-question-text {
      font-weight: 600;
      color: #1f2937;
      font-size: 1.05rem;
      flex-grow: 1;
    }

    .faq-icon {
      transition: transform 0.3s ease;
      color: #AB2732;
      font-size: 28px;
    }

    .faq-item.active .faq-icon {
      transform: rotate(180deg);
    }

    .faq-answer {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease, padding 0.3s ease;
      padding: 0 24px;
      border: 1px solid white;
      border-top: none;
      border-bottom: none;
      background: white;
    }

    .faq-item.active .faq-answer {
      max-height: 500px;
      padding: 20px 24px;
    }

    .faq-answer-text {
      color: #4b5563;
      line-height: 1.7;
      font-size: 0.75rem;
    }

    /* Contact Support */
    .contact-card {
      background: white;
      padding: 32px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      text-align: center;
      margin-top: 32px;
    }

    .contact-card h4 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 12px;
      color: #1f2937;
    }

    .contact-card p {
      color: #6b7280;
      margin-bottom: 24px;
    }

    .contact-btn {
      background: var(--primary);
      color: white;
      padding: 12px 32px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
    }

    .contact-btn:hover {
      background: #8B1F28;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(171, 39, 50, 0.3);
      color: white;
    }

    .hidden {
      display: none;
    }

    /* Category Filter Section */
    .category-filter-section {
      background: white;
      padding: 16px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      margin-bottom: 32px;
    }

    .category-filter-label {
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 16px;
      font-size: 1.05rem;
    }

    .category-filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .category-filter-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 8px;
      border: 2px solid var(--bg);
      background: white;
      color: #6b7280;
      font-weight: 500;
      transition: all 0.2s;
      cursor: pointer;
      font-size: 0.7rem;
    }

    .category-filter-btn:hover {
      border-color: var(--primary);
      color: var(--primary);
      background: var(--bg);
    }

    .category-filter-btn.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    .category-filter-btn .material-symbols-outlined {
      font-size: 20px;
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
    <main class="container-fluid p-4">

      <!-- Header Banner -->
      <div class="help-banner">
        <span class="material-symbols-outlined" style="font-size: 64px; margin-bottom: 12px;">help</span>
        <h2>Help & Support</h2>
        <p>Find answers to FAQs about the RPS</p>
      </div>

      <!-- Search Box -->
      <div class="search-box">
        <div class="search-wrapper">
          <span class="material-symbols-outlined search-icon">search</span>
          <input
            type="text"
            class="search-input"
            id="searchInput"
            placeholder="Search"
            autocomplete="off">
        </div>
      </div>

      <!-- Category Filter -->
      <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
          <?php foreach ($filterCategories as $key => $label): ?>
            <a
              href="?category=<?= urlencode($key) ?>"
              class="<?= $selectedCategory === $key ? 'btn-custom' : 'btn btn-sm btn-outline-secondary' ?>">
              <?= htmlspecialchars($label) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>


      <!-- FAQ Categories -->
      <?php foreach ($faqCategories as $categoryName => $categoryFaqs): ?>
        <?php
        if ($selectedCategory !== 'all' && $selectedCategory !== $categoryName) {
          continue;
        }
        ?>
        <div class="category-section" data-category="<?= strtolower($categoryName) ?>">

          <div class="category-header">
            <div class="category-icon">
              <span class="material-symbols-outlined">
              <?php
              $icons = [
                'Getting Started' => 'rocket_launch',
                'Earning Points' => 'savings',
                'Gifting Points' => 'card_giftcard',
                'Redemption' => 'shopping_cart',
                'Points Expiry' => 'schedule',
                'Leaderboard' => 'leaderboard',
                'Rules & Policies' => 'gavel',
                'Technical' => 'settings',
              ];

              echo $icons[$categoryName] ?? 'help';
              ?>
              </span>
            </div>
            <h3 class="category-title"><?= htmlspecialchars($categoryName) ?></h3>
          </div>

          <?php foreach ($categoryFaqs as $faq): ?>
            <div class="faq-item" data-faq-id="<?= $faq['id'] ?>">
              <div class="faq-question" onclick="toggleFAQ(<?= $faq['id'] ?>)">
                <div class="faq-question-text"><?= htmlspecialchars($faq['question']) ?></div>
                <span class="material-symbols-outlined faq-icon">expand_more</span>
              </div>
              <div class="faq-answer">
                <div class="faq-answer-text"><?= htmlspecialchars($faq['answer']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>

        </div>
      <?php endforeach; ?>



      <!-- Contact Support -->
      <div class="contact-card">
        <span class="material-symbols-outlined" style="font-size: 48px; color: #AB2732; margin-bottom: 16px;">support_agent</span>
        <h4>Still have questions?</h4>
        <p>Can't find what you're looking for? Our support team is here to help.</p>
        <a href="mailto:support@pakka.com" class="contact-btn">
          <span class="material-symbols-outlined">email</span>
          Contact Support
        </a>
      </div>

    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Toggle FAQ
    function toggleFAQ(id) {
      const faqItem = document.querySelector(`[data-faq-id="${id}"]`);
      const allFaqItems = document.querySelectorAll('.faq-item');

      // Close other FAQs
      allFaqItems.forEach(item => {
        if (item !== faqItem && item.classList.contains('active')) {
          item.classList.remove('active');
        }
      });

      // Toggle current FAQ
      faqItem.classList.toggle('active');
    }


    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const categories = document.querySelectorAll('.category-section');
    const faqItems = document.querySelectorAll('.faq-item');

    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();

      // Reset category filter when searching
      if (searchTerm !== '') {
        currentFilter = 'all';
        document.querySelectorAll('.category-filter-btn').forEach(btn => {
          btn.classList.toggle('active', btn.dataset.filter === 'all');
        });
      }

      if (searchTerm === '') {
        // Apply current category filter
        filterByCategory(currentFilter);
        return;
      }

      // Search through FAQs
      let hasResults = false;

      categories.forEach(category => {
        let categoryHasResults = false;
        const categoryFaqs = category.querySelectorAll('.faq-item');

        categoryFaqs.forEach(faq => {
          const question = faq.querySelector('.faq-question-text').textContent.toLowerCase();
          const answer = faq.querySelector('.faq-answer-text').textContent.toLowerCase();

          if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            faq.classList.remove('hidden');
            categoryHasResults = true;
            hasResults = true;
          } else {
            faq.classList.add('hidden');
          }
        });

        // Show/hide category based on results
        if (categoryHasResults) {
          category.classList.remove('hidden');
        } else {
          category.classList.add('hidden');
        }
      });
    });

    // Close FAQ when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.faq-item')) {
        document.querySelectorAll('.faq-item.active').forEach(item => {
          item.classList.remove('active');
        });
      }
    });
  </script>

</body>

</html>