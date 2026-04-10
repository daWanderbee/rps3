<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
helper(['url']);

$currentPage = 'management';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Management Page</title>
    <meta name="<?= $this->security->get_csrf_token_name() ?>" content="<?= $this->security->get_csrf_hash() ?>" id="csrf-meta">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body>
    <script>
        let CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';
        const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
    </script>
    <?php
    // Include sidebar (which has full HTML structure)
    include __DIR__ . '/partials/sidebar.php';
    ?>


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
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
        }

        .task-check {
            width: 1.2rem;
            height: 1.2rem;
            cursor: pointer;
        }

        .task-check:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .user-row:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: 2px solid var(--primary);
        }

        @media (max-width: 768px) {
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>

    <div class="main-wrapper">
        <main class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark">Tasks Management</h2>
                    <p class="text-muted">Review contributions and mark tasks as completed.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Member</th>
                                <th class="text-center">Tasks Creation</th>
                                <th class="text-end pe-4">Task Completion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($employees)): ?>
                                <?php foreach ($employees as $user): ?>
                                    <tr class="user-row">
                                        <td class="ps-4">
                                            <div class="fw-bold"><?php echo $user['name']; ?></div>

                                            <span class="badge bg-secondary"><?php echo $user['sangh_code']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="openAddTaskModal(<?php echo $user['emp_id']; ?>, '<?php echo addslashes($user['name']); ?>')">
                                                Add Task
                                            </button>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                onclick="openManageTasksModal(<?php echo $user['emp_id']; ?>, '<?php echo addslashes($user['name']); ?>')">
                                                Manage Tasks
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center p-4 text-muted">No employees found in your hierarchy.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Add Task for <span id="modalEmpName" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTaskForm">
                        <input type="hidden" id="employeeId">
                        <div class="mb-3">
                            <label class="form-label">Task Title</label>
                            <input type="text" id="taskTitleInput" class="form-control" placeholder="Enter task name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" id="taskPointsInput" class="form-control" placeholder="Enter points" required>
                        </div>
                        <div class="mb-3 form-check flex align-items-center">
                            <input type="checkbox" class="form-check-input" id="isRecurringInput">
                            <label class="form-check-label" for="isRecurringInput">Set up as Recurring Task</label>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">

                    <p class="remaining-points"></p>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="saveTaskBtn" class="btn btn-primary" onclick="saveTask()">Save Task</button>


                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manageTasksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Tasks: <span id="manageModalEmpName" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="taskListContainer">
                        <div class="text-center p-5" id="taskLoadingSpinner">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .task-check-large {
            transform: scale(1.5);
            cursor: pointer;
        }

        .task-item-row:hover {
            background-color: #f8f9fa;
            transition: 0.2s;
        }
    </style>
    <script>
        // Global State
        let currentManageEmpId = null;
        let remainingPoints = 0;


        // Load URL helper in PHP at top of file
        const BASE_URL = '<?= base_url() ?>';

        // --- Modal Controls ---
        async function openAddTaskModal(empId, empName) {
            document.getElementById('employeeId').value = empId;
            document.getElementById('modalEmpName').innerText = empName;
            document.getElementById('addTaskForm').reset();
            document.getElementById('taskPointsInput').addEventListener('input', function() {
                const enteredPoints = parseInt(this.value) || 0;
                const saveBtn = document.getElementById('saveTaskBtn');

                if (enteredPoints > remainingPoints || remainingPoints <= 0) {
                    saveBtn.disabled = true;
                    saveBtn.classList.add('btn-secondary');
                    saveBtn.classList.remove('btn-primary');
                } else {
                    saveBtn.disabled = false;
                }
            });


            const modal = new bootstrap.Modal(document.getElementById('addTaskModal'));
            modal.show();

            // Fetch remaining points dynamically
            try {
                const result = await postData(BASE_URL + 'management/getRemainingPoints', {
                    emp_id: empId
                });

                if (result.success) {
                    remainingPoints = result.remaining;
                    document.querySelector('.remaining-points').innerText =
                        `Remaining allocatable points tasks: ${result.remaining}`;
                    const saveBtn = document.getElementById('saveTaskBtn');

                    if (remainingPoints <= 0) {
                        saveBtn.disabled = true;
                        saveBtn.classList.add('btn-secondary');
                        saveBtn.classList.remove('btn-primary');
                    } else {
                        saveBtn.disabled = false;
                        saveBtn.classList.add('btn-primary');
                    }


                } else {
                    document.querySelector('.remaining-points').innerText =
                        'Unable to fetch remaining points';
                }
            } catch (e) {
                document.querySelector('.remaining-points').innerText =
                    'Error fetching remaining points';
            }
        }

        // --- Logic for Mark Complete ---
        async function confirmTask(checkbox) {
            if (!checkbox.checked) return;
            const taskId = checkbox.dataset.taskId;

            if (confirm('Mark this task as finished?')) {
                try {
                    const result = await postData(BASE_URL + 'management/markTaskComplete', {
                        id: taskId
                    });

                    if (result.success) {
                        checkbox.disabled = true;
                        const row = checkbox.closest('.task-item-row');
                        row.style.opacity = "0.5";
                        row.querySelector('h6').classList.add('text-decoration-line-through');
                        row.querySelector('h6').classList.add('text-muted');

                        // Show success message
                        showToast('Task marked as complete!', 'success');
                    } else {
                        checkbox.checked = false;
                        alert(result.message || 'Failed to mark task as complete');
                    }
                } catch (e) {
                    checkbox.checked = false;
                    alert('Error: ' + e.message);
                }
            } else {
                checkbox.checked = false;
            }
        }

        // --- Logic for Custom Tasks (Add Task Modal) ---
        async function saveTask() {
            const userId = document.getElementById('employeeId').value;
            const title = document.getElementById('taskTitleInput').value.trim();
            const pts = document.getElementById('taskPointsInput').value;
            const recurring = document.getElementById('isRecurringInput').checked ? 1 : 0;

            if (!title || !pts) {
                alert("Please fill in all fields");
                return;
            }

            if (pts <= 0) {
                alert("Points must be greater than 0");
                return;
            }

            try {
                const result = await postData(BASE_URL + 'management/addTask', {
                    emp_id: userId,
                    points: pts,
                    task_title: title,
                    is_recurring: recurring
                });

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addTaskModal')).hide();
                    showToast('Task added successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert("Error: " + (result.message || 'Failed to add task'));
                }
            } catch (e) {
                alert("Error: " + e.message);
            }
        }

        // --- Universal Helper for API calls ---
        async function postData(url, data) {
            try {
                const tokenElement = document.getElementById('csrf-meta');
                const token = tokenElement.getAttribute('content');
                const tokenName = tokenElement.getAttribute('name');

                const formData = new URLSearchParams();

                // Add your data
                for (const key in data) {
                    formData.append(key, data[key]);
                }

                // 🔥 Add CSRF token EXACTLY how CI expects
                formData.append(tokenName, token);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const text = await response.text();
                console.log("Response:", text);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const json = JSON.parse(text);

                // Refresh token if new one returned
                if (json.csrf_token) {
                    tokenElement.setAttribute('content', json.csrf_token);
                }

                return json;

            } catch (e) {
                console.error("API Error:", e);
                throw e;
            }
        }


        // --- Manage Tasks Modal ---
        async function openManageTasksModal(empId, empName) {
            currentManageEmpId = empId;
            document.getElementById('manageModalEmpName').innerText = empName;
            const container = document.getElementById('taskListContainer');

            const modalElement = document.getElementById('manageTasksModal');
            let modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            modal.show();

            container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

            try {
                const response = await postData(BASE_URL + 'management/getEmployeeTasks', {
                    emp_id: empId
                });

                const tasks = response.tasks;

                if (!Array.isArray(tasks)) {
                    throw new Error('Invalid response format');
                }


                if (tasks.length === 0) {
                    container.innerHTML = '<div class="text-center p-3 text-muted">No tasks found for this employee.</div>';
                    return;
                }

                container.innerHTML = '';
                tasks.forEach(task => {
                    const isDone = task.is_completed == 1;
                    container.innerHTML += `
                <div class="p-3 mb-2 border rounded d-flex justify-content-between align-items-center task-item-row" 
                     style="opacity: ${isDone ? '0.5' : '1'}">
                    <div>
                        <h6 class="mb-0 ${isDone ? 'text-decoration-line-through text-muted' : ''}">${escapeHtml(task.title)}</h6>
                        <small class="text-success">+${task.points} Points</small>
                        ${task.is_recurring == 0 ? '<small class="text-danger ms-2">One-Time</small>' : ''}
                    </div>
                    <div class="form-check">
                        <input class="form-check-input task-check-large" 
                               type="checkbox" 
                               ${isDone ? 'checked disabled' : ''}
                               data-task-id="${task.id}"
                               onchange="confirmTask(this)">
                    </div>
                </div>`;
                });
            } catch (e) {
                console.error("Load Error:", e);
                container.innerHTML = '<div class="alert alert-danger">Failed to load tasks: ' + escapeHtml(e.message) + '</div>';
            }
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function showToast(message, type = 'info') {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    </script>
</body>

</html>