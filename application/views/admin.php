<?php
$currentPage = 'admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Task Distribution</title>
    <meta name="<?= $this->security->get_csrf_token_name() ?>" content="<?= $this->security->get_csrf_hash() ?>" id="csrf-meta">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg);
        }

        .main-wrapper {
            margin-left: 200px;
            transition: all 0.3s;
            background-color: var(--bg);
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .btn-group-toggle .btn {
            margin-right: 5px;
            border-radius: 8px !important;
        }

        .user-row:hover {
            background-color: #f8f9fa;
        }

        .admin-badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                margin-left: 0;
            }

        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="main-wrapper">
        <main class="container-fluid p-4 min-vh-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark">Admin Panel</h2>
                    <p class="text-muted">Distribute tasks across the entire organization, specific sanghs, or individual</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2" style="height: 50px; border-radius: 12px;" onclick="openGlobalTaskModal()">
                    <span class="material-symbols-outlined">campaign</span> Mass Assign
                </button>
            </div>

            <div class="row mb-2">
                <div>
                    <div class="card p-3 bg-white mb-2">
                        <label class="form-label fw-bold">Filter by Sangh</label>
                        <select class="form-select mb-2" id="sanghFilter" onchange="filterTable()">
                            <option value="all">All Sanghs</option>
                            <?php foreach ($sanghs as $sangh): ?>
                                <option value="<?= htmlspecialchars($sangh['sangh_code']) ?>">
                                    <?= htmlspecialchars($sangh['sangh_code']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <span class="material-symbols-outlined">search</span>
                                    </span>
                                    <input type="text" id="tableSearch" class="form-control"
                                        placeholder="Search by name or employee ID..."
                                        onkeyup="filterTable()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end pe-4 mb-2">
                <button id="bulkAssignBtn" class="btn btn-sm btn-primary d-none" onclick="openBulkTaskModal()">
                    Assign to Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table align-middle mb-0 max-width-100">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">
                                    <input type="checkbox" class="form-check-input border-black" id="selectAll" onclick="toggleSelectAll(this)">
                                </th>
                                <th>Member</th>
                                <th>Sangh</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            <?php if (!empty($employees)): ?>
                                <?php foreach ($employees as $user): ?>
                                    <tr class="user-row" data-sangh="<?= htmlspecialchars($user['sangh_code']) ?>">
                                        <td class="ps-4">
                                            <input type="checkbox" class="form-check-input border border-black user-checkbox"
                                                value="<?= (int)$user['emp_id'] ?>"
                                                data-name="<?= htmlspecialchars($user['name']) ?>"
                                                onclick="updateSelection()">
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
                                            <small class="text-muted">ID: <?= htmlspecialchars($user['emp_id']) ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user['sangh_code'] ?: 'N/A') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>


    <div class="modal fade" id="viewTasksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Tasks for <span id="viewTaskMemberName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="taskListContainer" class="list-group list-group-flush">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="adminTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Assign New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="adminTaskForm">
                        <input type="hidden" id="assignType" value="individual">
                        <input type="hidden" id="targetId">

                        <div id="targetDisplay" class="alert alert-info py-2 mb-3">
                            Target: <strong id="targetName">Loading...</strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Task Title</label>
                            <input type="text" id="taskTitle" class="form-control" placeholder="e.g., Monthly Inventory Review" required>
                        </div>
<div class="mb-3">
                            <label class="form-label">Task Description</label>
                            <input type="text" id="taskDescription" class="form-control" placeholder="e.g., Complete the monthly review for all departments" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Points</label><input type="number" id="taskPoints" class="form-control" value="10" min="1" max="500">

                            </div>
                            <div class="col-6">
                                <label class="form-label">Priority</label>
                                <select id="priority" class="form-select">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="isRecurring">
                            <label class="form-check-label">Set as Recurring Task</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary px-4" onclick="processAssignment()">Confirm Assignment</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="globalSelectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Select Distribution Scope</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group list-group-flush">
                        <button class="list-group-item list-group-item-action p-3" onclick="prepareMassAssign('all', 0, 'Everyone')">
                            <div class="fw-bold">All Employees</div>
                            <small class="text-muted">Total: <?= count($employees) ?> members</small>
                        </button>

                        <hr>
                        <label class="ps-3 mb-2 small text-uppercase fw-bold text-muted">Choose a Sangh</label>

                        <?php foreach ($sanghs as $sangh): ?>
                            <button class="list-group-item list-group-item-action p-3"
                                onclick="prepareMassAssign('sangh', '<?= htmlspecialchars($sangh['sangh_code']) ?>', 'Sangh: <?= htmlspecialchars($sangh['sangh_name'] ?: $sangh['sangh_code']) ?>')">
                                <div class="fw-bold"><?= htmlspecialchars($sangh['sangh_name'] ?: 'Unnamed Sangh') ?></div>
                                <small class="text-muted">Code: <?= htmlspecialchars($sangh['sangh_code']) ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= base_url() ?>';
        const taskModal = new bootstrap.Modal(document.getElementById('adminTaskModal'));
        const globalModal = new bootstrap.Modal(document.getElementById('globalSelectModal'));
        const viewModal = new bootstrap.Modal(document.getElementById('viewTasksModal'));
        let selectedUserIds = [];

        function filterTable() {
            const searchTerm = document.getElementById('tableSearch').value.toLowerCase();
            const sanghValue = document.getElementById('sanghFilter').value;
            const rows = document.querySelectorAll('#employeeTableBody tr.user-row');

            rows.forEach(row => {
                // Get text content for filtering
                const name = row.querySelector('.fw-bold').innerText.toLowerCase();
                const empId = row.querySelector('.text-muted').innerText.toLowerCase();
                const rowSangh = row.dataset.sangh;

                // Check if row matches Sangh filter
                const matchesSangh = (sanghValue === 'all' || rowSangh === sanghValue);

                // Check if row matches Search term (Name or ID)
                const matchesSearch = name.includes(searchTerm) || empId.includes(searchTerm);

                // Show row only if it satisfies BOTH conditions
                if (matchesSangh && matchesSearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            updateSelection();
        }



        function toggleSelectAll(masterCheckbox) {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => {
                // Only toggle visible rows (respecting your Sangh filter)
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = masterCheckbox.checked;
                }
            });
            updateSelection();
        }

        function updateSelection() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            selectedUserIds = Array.from(checkboxes).map(cb => cb.value);

            const btn = document.getElementById('bulkAssignBtn');
            const countSpan = document.getElementById('selectedCount');

            if (selectedUserIds.length > 0) {
                btn.classList.remove('d-none');
                countSpan.innerText = selectedUserIds.length;
            } else {
                btn.classList.add('d-none');
            }
        }

        function openBulkTaskModal() {

            const targetIds = selectedUserIds.join(',');
            const displayNames = selectedUserIds.length + " selected members";
            openTaskModal('individual_bulk', targetIds, displayNames);
        }

        async function viewEmployeeTasks(empId, name) {
            document.getElementById('viewTaskMemberName').innerText = name;
            const container = document.getElementById('taskListContainer');
            container.innerHTML = '<div class="p-4 text-center">Loading...</div>';
            viewModal.show();

            try {
                const res = await fetch('<?= base_url('management/getEmployeeTasks') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        emp_id: empId
                    })
                });
                const tasks = await res.json();

                if (tasks.length === 0) {
                    container.innerHTML = '<div class="p-4 text-center text-muted">No tasks found.</div>';
                    return;
                }

                container.innerHTML = tasks.map(t => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">${t.title}</div>
                    <small class="text-muted">${t.points} Points • ${t.is_recurring ? 'Recurring' : 'One-time'}</small>
                </div>
                ${t.is_completed == 1 
                    ? '<span class="badge bg-success">Completed</span>' 
                    : `<button class="btn btn-sm btn-success" onclick="markDone(${t.id})">Mark Complete</button>`}
            </div>
        `).join('');
            } catch (e) {
                container.innerHTML = '<div class="p-4 text-danger">Error loading tasks.</div>';
            }
        }

        async function processAssignment() {
            const btn = event.target;
            const data = {
                type: document.getElementById('assignType').value, // 'individual', 'sangh', or 'all'
                target_id: document.getElementById('targetId').value,
                task_title: document.getElementById('taskTitle').value,
                task_description: document.getElementById('taskDescription').value,
                points: document.getElementById('taskPoints').value,
                is_recurring: document.getElementById('isRecurring').checked ? 1 : 0
            };

            if (!data.task_title) return alert("Please enter a task title");
            if (!data.task_description) return alert("Please enter a task description");

            btn.disabled = true;
            btn.innerText = "Processing...";

            try {
                const res = await fetch('<?= base_url('admin/bulkAssignTask') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="<?= $this->security->get_csrf_token_name() ?>"]').content
                    },

                    body: JSON.stringify(data)
                });

                const result = await res.json();
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert("Error: " + (result.messages?.error || "Unknown error"));
                }
            } catch (e) {
                console.error(e);
            }
            btn.disabled = false;
            btn.innerText = "Confirm Assignment";
        }

        function openGlobalTaskModal() {
            globalModal.show();
        }

        function prepareMassAssign(type, id, name) {
            globalModal.hide();
            openTaskModal(type, id, name);
        }

        function openTaskModal(type, id, name) {
            document.getElementById('assignType').value = type;
            document.getElementById('targetId').value = id;
            document.getElementById('targetName').innerText = name;
            document.getElementById('adminTaskForm').reset();
            taskModal.show();
        }
    </script>
</body>

</html>