<?php
/**
 * Yasa LTD Task List - All Tasks Page
 */

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();
$currentUser = $auth->validateSession($sessionToken);

if (!$currentUser) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$projectsManager = new Projects();
$tasksManager = new Tasks();

$sort = $_GET['sort'] ?? 'created_desc';
$statusFilter = $_GET['status'] ?? null;
$projectFilter = $_GET['project'] ?? null;

$allProjects = $projectsManager->getAll($currentUser['id']);
$allTasks = $tasksManager->getAll($currentUser['id'], $projectFilter, $statusFilter, $sort);

$pageTitle = 'All Tasks';
$currentPage = 'tasks';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="tasks-page-container">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title"><i class="fas fa-tasks"></i> All Tasks</h1>
            <p class="page-subtitle">View and manage all your tasks across projects</p>
        </div>
    </div>
    
    <!-- Filters & Sort Bar -->
    <div class="filters-bar">
        <div class="filter-group">
            <select class="form-select" id="project-filter" onchange="applyFilters()">
                <option value="">All Projects</option>
                <?php foreach ($allProjects as $proj): ?>
                <option value="<?php echo $proj['id']; ?>" <?php echo $projectFilter == $proj['id'] ? 'selected' : ''; ?>>
                    <?php echo sanitize($proj['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <select class="form-select" id="status-filter" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            
            <select class="form-select" id="sort-select" onchange="applyFilters()">
                <option value="created_desc" <?php echo $sort === 'created_desc' ? 'selected' : ''; ?>>Newest First</option>
                <option value="created_asc" <?php echo $sort === 'created_asc' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>Title A-Z</option>
                <option value="title_desc" <?php echo $sort === 'title_desc' ? 'selected' : ''; ?>>Title Z-A</option>
                <option value="deadline_asc" <?php echo $sort === 'deadline_asc' ? 'selected' : ''; ?>>Deadline (Earliest)</option>
                <option value="deadline_desc" <?php echo $sort === 'deadline_desc' ? 'selected' : ''; ?>>Deadline (Latest)</option>
                <option value="priority_desc" <?php echo $sort === 'priority_desc' ? 'selected' : ''; ?>>Priority (High → Low)</option>
                <option value="priority_asc" <?php echo $sort === 'priority_asc' ? 'selected' : ''; ?>>Priority (Low → High)</option>
                <option value="status" <?php echo $sort === 'status' ? 'selected' : ''; ?>>By Status</option>
            </select>
        </div>
        
        <div class="filter-group">
            <div class="search-group">
                <input type="text" class="form-input" id="task-search" placeholder="Search tasks..." oninput="searchTasks()">
                <i class="fas fa-search"></i>
            </div>
            
            <?php if ($statusFilter || $projectFilter || $sort !== 'created_desc'): ?>
            <a href="<?php echo SITE_URL; ?>/pages/tasks.php" class="btn btn-outline btn-sm">
                <i class="fas fa-times"></i> Clear Filters
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Task Stats -->
    <div class="task-stats">
        <?php
        $pendingCount = count(array_filter($allTasks, fn($t) => $t['status'] === 'pending'));
        $inProgressCount = count(array_filter($allTasks, fn($t) => $t['status'] === 'in_progress'));
        $completedCount = count(array_filter($allTasks, fn($t) => $t['status'] === 'completed'));
        $overdueCount = count(array_filter($allTasks, fn($t) => $t['status'] !== 'completed' && isOverdue($t['deadline'])));
        ?>
        <div class="stat-pill stat-pending">
            <i class="fas fa-clock"></i> <?php echo $pendingCount; ?> Pending
        </div>
        <div class="stat-pill stat-progress">
            <i class="fas fa-spinner"></i> <?php echo $inProgressCount; ?> In Progress
        </div>
        <div class="stat-pill stat-completed">
            <i class="fas fa-check"></i> <?php echo $completedCount; ?> Completed
        </div>
        <?php if ($overdueCount > 0): ?>
        <div class="stat-pill stat-overdue">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $overdueCount; ?> Overdue
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Tasks List -->
    <?php if (empty($allTasks)): ?>
    <div class="empty-state large">
        <i class="fas fa-clipboard-list"></i>
        <h3>No tasks found</h3>
        <?php if ($statusFilter || $projectFilter): ?>
        <p>Try adjusting your filters</p>
        <a href="<?php echo SITE_URL; ?>/pages/tasks.php" class="btn btn-outline">Clear Filters</a>
        <?php else: ?>
        <p>Create a project and add tasks to get started</p>
        <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="btn btn-primary">
            <i class="fas fa-folder-plus"></i> Go to Projects
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="task-list-full" id="tasks-list">
        <?php foreach ($allTasks as $task): ?>
        <?php $canEdit = $task['owner_id'] == $currentUser['id']; ?>
        <div class="task-card-full <?php echo $task['status']; ?> <?php echo isOverdue($task['deadline']) && $task['status'] !== 'completed' ? 'overdue' : ''; ?>" 
             data-task-id="<?php echo $task['id']; ?>"
             data-project-id="<?php echo $task['project_id']; ?>"
             data-status="<?php echo $task['status']; ?>"
             data-priority="<?php echo $task['priority']; ?>"
             data-title="<?php echo strtolower($task['title']); ?>">
            
            <div class="task-checkbox">
                <?php if ($canEdit): ?>
                <input type="checkbox" 
                       class="task-complete-checkbox" 
                       id="task-<?php echo $task['id']; ?>" 
                       <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?>
                       onchange="toggleTaskComplete(<?php echo $task['id']; ?>, this.checked)">
                <label for="task-<?php echo $task['id']; ?>" class="checkbox-label"></label>
                <?php else: ?>
                <span class="checkbox-display <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?>">
                    <i class="fas fa-check"></i>
                </span>
                <?php endif; ?>
            </div>
            
            <div class="task-content-full">
                <div class="task-header-row">
                    <h3 class="task-title <?php echo $task['status'] === 'completed' ? 'completed' : ''; ?>">
                        <?php echo sanitize($task['title']); ?>
                        <?php if ($task['is_recurring']): ?>
                        <span class="recurring-badge" title="Recurring: <?php echo ucfirst($task['recurrence_type']); ?>">
                            <i class="fas fa-sync-alt"></i>
                        </span>
                        <?php endif; ?>
                    </h3>
                    <a href="<?php echo SITE_URL; ?>/pages/projects.php?id=<?php echo $task['project_id']; ?>" 
                       class="task-project-link" style="color: <?php echo $task['project_color']; ?>">
                        <i class="fas fa-folder"></i> <?php echo sanitize($task['project_name']); ?>
                    </a>
                </div>
                
                <?php if ($task['description']): ?>
                <p class="task-description"><?php echo sanitize(substr($task['description'], 0, 150)); ?></p>
                <?php endif; ?>
                
                <div class="task-meta-full">
                    <span class="badge <?php echo getStatusClass($task['status']); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                    </span>
                    <span class="badge <?php echo getPriorityClass($task['priority']); ?>">
                        <?php echo ucfirst($task['priority']); ?>
                    </span>
                    
                    <?php if (!empty($task['assigned_users'])): ?>
                    <span class="task-assignees">
                        <i class="fas fa-users"></i>
                        <?php foreach (array_slice($task['assigned_users'], 0, 3) as $u): ?>
                        <span class="user-avatar-tiny" style="background-color: <?php echo $u['avatar_color']; ?>" title="<?php echo sanitize($u['display_name']); ?>">
                            <?php echo strtoupper(substr($u['display_name'], 0, 1)); ?>
                        </span>
                        <?php endforeach; ?>
                        <?php if (count($task['assigned_users']) > 3): ?>
                        <span class="more-users">+<?php echo count($task['assigned_users']) - 3; ?></span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($task['deadline']): ?>
                    <span class="task-deadline <?php echo isOverdue($task['deadline']) && $task['status'] !== 'completed' ? 'overdue' : ''; ?>">
                        <i class="fas fa-calendar"></i>
                        <?php echo formatDate($task['deadline']); ?>
                        <?php if (isOverdue($task['deadline']) && $task['status'] !== 'completed'): ?>
                        <span class="overdue-label">Overdue!</span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    
                    <span class="task-created">
                        <i class="fas fa-clock"></i> <?php echo timeAgo($task['created_at']); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($canEdit): ?>
            <div class="task-actions">
                <button class="btn btn-icon btn-sm" onclick="openTaskModal(<?php echo $task['id']; ?>, <?php echo $task['project_id']; ?>)" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-icon btn-sm" onclick="openAssignModal('task', <?php echo $task['id']; ?>)" title="Assign Users">
                    <i class="fas fa-user-plus"></i>
                </button>
                <button class="btn btn-icon btn-sm btn-danger" onclick="confirmDelete(<?php echo $task['id']; ?>)" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Task Modal -->
<div class="modal" id="task-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="task-modal-title">Edit Task</h2>
                <button class="modal-close" onclick="closeModal('task-modal')"><i class="fas fa-times"></i></button>
            </div>
            <form id="task-form" onsubmit="saveTask(event)">
                <input type="hidden" id="task-id" name="id">
                <input type="hidden" id="task-project-id" name="project_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="task-title" class="form-label">Task Title *</label>
                        <input type="text" id="task-title" name="title" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="task-description" class="form-label">Description</label>
                        <textarea id="task-description" name="description" class="form-textarea" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="task-status" class="form-label">Status</label>
                            <select id="task-status" name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="task-priority" class="form-label">Priority</label>
                            <select id="task-priority" name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="task-deadline" class="form-label">Deadline</label>
                        <input type="date" id="task-deadline" name="deadline" class="form-input">
                    </div>
                    
                    <!-- Recurring Options -->
                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" id="task-recurring" name="is_recurring" onchange="toggleRecurringOptions()">
                            <span>Recurring Task</span>
                        </label>
                    </div>
                    
                    <div id="recurring-options" class="recurring-options hidden">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="task-recurrence-type" class="form-label">Repeat</label>
                                <select id="task-recurrence-type" name="recurrence_type" class="form-select">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="task-recurrence-interval" class="form-label">Every</label>
                                <input type="number" id="task-recurrence-interval" name="recurrence_interval" class="form-input" value="1" min="1">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="task-recurrence-end" class="form-label">End Date (optional)</label>
                            <input type="date" id="task-recurrence-end" name="recurrence_end_date" class="form-input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('task-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="task-submit-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign User Modal -->
<div class="modal" id="assign-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Assign Users</h2>
                <button class="modal-close" onclick="closeModal('assign-modal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assign-task-id">
                
                <div class="form-group">
                    <label for="assign-user-input" class="form-label">Enter email or username</label>
                    <div class="assign-input-group">
                        <input type="text" id="assign-user-input" class="form-input" placeholder="user@example.com or username">
                        <button type="button" class="btn btn-primary" onclick="assignUser()">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                
                <div id="assign-result" class="assign-result"></div>
                
                <div class="assigned-list" id="current-assigned">
                    <h4>Currently Assigned:</h4>
                    <div id="assigned-users-list"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('assign-modal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal" id="confirm-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete</h2>
                <button class="modal-close" onclick="closeModal('confirm-modal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this task?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('confirm-modal')">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="executeDelete()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const SITE_URL = '<?php echo SITE_URL; ?>';
const CURRENT_USER = <?php echo json_encode($currentUser); ?>;

let deleteTaskId = null;

// Modal functions
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span class="toast-icon"><i class="fas fa-${type === 'success' ? 'check' : 'exclamation-circle'}"></i></span><span class="toast-message">${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}

// Filter & Sort
function applyFilters() {
    const project = document.getElementById('project-filter').value;
    const status = document.getElementById('status-filter').value;
    const sort = document.getElementById('sort-select').value;
    
    let url = `${SITE_URL}/pages/tasks.php?`;
    const params = [];
    
    if (project) params.push(`project=${project}`);
    if (status) params.push(`status=${status}`);
    if (sort && sort !== 'created_desc') params.push(`sort=${sort}`);
    
    window.location.href = url + params.join('&');
}

function searchTasks() {
    const search = document.getElementById('task-search').value.toLowerCase();
    
    document.querySelectorAll('.task-card-full').forEach(card => {
        const title = card.dataset.title || '';
        card.style.display = title.includes(search) ? '' : 'none';
    });
}

// Task functions
function toggleRecurringOptions() {
    const checkbox = document.getElementById('task-recurring');
    const options = document.getElementById('recurring-options');
    options.classList.toggle('hidden', !checkbox.checked);
}

async function openTaskModal(taskId, projectId) {
    document.getElementById('task-form').reset();
    document.getElementById('task-id').value = taskId;
    document.getElementById('task-project-id').value = projectId;
    document.getElementById('recurring-options').classList.add('hidden');
    
    try {
        const response = await fetch(`${SITE_URL}/api/tasks.php?id=${taskId}`);
        const result = await response.json();
        
        if (result.success) {
            const t = result.data;
            document.getElementById('task-title').value = t.title;
            document.getElementById('task-description').value = t.description || '';
            document.getElementById('task-status').value = t.status;
            document.getElementById('task-priority').value = t.priority;
            document.getElementById('task-deadline').value = t.deadline ? t.deadline.split(' ')[0] : '';
            document.getElementById('task-recurring').checked = t.is_recurring == 1;
            
            if (t.is_recurring == 1) {
                document.getElementById('recurring-options').classList.remove('hidden');
                document.getElementById('task-recurrence-type').value = t.recurrence_type || 'daily';
                document.getElementById('task-recurrence-interval').value = t.recurrence_interval || 1;
                document.getElementById('task-recurrence-end').value = t.recurrence_end_date || '';
            }
        }
    } catch (error) {
        showToast('Error loading task', 'error');
        return;
    }
    
    openModal('task-modal');
}

async function saveTask(e) {
    e.preventDefault();
    
    const taskId = document.getElementById('task-id').value;
    const isRecurring = document.getElementById('task-recurring').checked;
    
    const data = {
        project_id: document.getElementById('task-project-id').value,
        title: document.getElementById('task-title').value,
        description: document.getElementById('task-description').value,
        status: document.getElementById('task-status').value,
        priority: document.getElementById('task-priority').value,
        deadline: document.getElementById('task-deadline').value || null,
        is_recurring: isRecurring ? 1 : 0
    };
    
    if (isRecurring) {
        data.recurrence_type = document.getElementById('task-recurrence-type').value;
        data.recurrence_interval = document.getElementById('task-recurrence-interval').value;
        data.recurrence_end_date = document.getElementById('task-recurrence-end').value || null;
    }
    
    try {
        const response = await fetch(`${SITE_URL}/api/tasks.php?id=${taskId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Task updated');
            closeModal('task-modal');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Error', 'error');
        }
    } catch (error) {
        showToast('Error saving task', 'error');
    }
}

async function toggleTaskComplete(taskId, completed) {
    try {
        const response = await fetch(`${SITE_URL}/api/tasks.php?id=${taskId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: completed ? 'completed' : 'pending' })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const card = document.querySelector(`[data-task-id="${taskId}"]`);
            const title = card.querySelector('.task-title');
            
            if (completed) {
                card.classList.add('completed');
                card.classList.remove('overdue');
                title.classList.add('completed');
            } else {
                card.classList.remove('completed');
                title.classList.remove('completed');
            }
            
            showToast(completed ? 'Task completed!' : 'Task reopened');
            
            // Reload if recurring
            if (result.data && result.data.is_recurring && completed) {
                setTimeout(() => location.reload(), 1000);
            }
        }
    } catch (error) {
        showToast('Error updating task', 'error');
    }
}

// Assign functions
async function openAssignModal(type, taskId) {
    document.getElementById('assign-task-id').value = taskId;
    document.getElementById('assign-user-input').value = '';
    document.getElementById('assign-result').innerHTML = '';
    
    await loadAssignedUsers(taskId);
    openModal('assign-modal');
}

async function loadAssignedUsers(taskId) {
    const list = document.getElementById('assigned-users-list');
    list.innerHTML = '<p class="loading">Loading...</p>';
    
    try {
        const response = await fetch(`${SITE_URL}/api/tasks.php?id=${taskId}`);
        const result = await response.json();
        
        if (result.success) {
            const users = result.data.assigned_users || [];
            
            if (users.length === 0) {
                list.innerHTML = '<p class="text-muted">No users assigned yet</p>';
            } else {
                list.innerHTML = users.map(u => `
                    <div class="assigned-user-item">
                        <span class="user-avatar-small" style="background-color: ${u.avatar_color}">
                            ${u.display_name.charAt(0).toUpperCase()}
                        </span>
                        <span class="user-info">
                            <strong>${u.display_name}</strong>
                            <small>${u.email}</small>
                        </span>
                        <button class="btn btn-icon btn-sm btn-danger" onclick="removeAssignedUser(${taskId}, ${u.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        list.innerHTML = '<p class="text-danger">Error loading users</p>';
    }
}

async function assignUser() {
    const taskId = document.getElementById('assign-task-id').value;
    const identifier = document.getElementById('assign-user-input').value.trim();
    const resultDiv = document.getElementById('assign-result');
    
    if (!identifier) {
        resultDiv.innerHTML = '<p class="text-danger">Please enter an email or username</p>';
        return;
    }
    
    try {
        const response = await fetch(`${SITE_URL}/api/tasks.php?id=${taskId}&action=assign`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ identifier: identifier })
        });
        
        const result = await response.json();
        
        if (result.success) {
            resultDiv.innerHTML = `<p class="text-success"><i class="fas fa-check"></i> ${result.message}</p>`;
            document.getElementById('assign-user-input').value = '';
            await loadAssignedUsers(taskId);
        } else {
            resultDiv.innerHTML = `<p class="text-danger"><i class="fas fa-exclamation-circle"></i> ${result.message}</p>`;
        }
    } catch (error) {
        resultDiv.innerHTML = '<p class="text-danger">Error assigning user</p>';
    }
}

async function removeAssignedUser(taskId, userId) {
    try {
        const response = await fetch(`${SITE_URL}/api/tasks.php?id=${taskId}&action=unassign`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('User removed');
            await loadAssignedUsers(taskId);
        } else {
            showToast(result.message || 'Error', 'error');
        }
    } catch (error) {
        showToast('Error removing user', 'error');
    }
}

// Delete functions
function confirmDelete(taskId) {
    deleteTaskId = taskId;
    openModal('confirm-modal');
}

async function executeDelete() {
    closeModal('confirm-modal');
    
    try {
        const response = await fetch(`${SITE_URL}/api/tasks.php?id=${deleteTaskId}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('Task deleted');
            const card = document.querySelector(`[data-task-id="${deleteTaskId}"]`);
            if (card) {
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => card.remove(), 300);
            }
        } else {
            showToast(result.message || 'Error', 'error');
        }
    } catch (error) {
        showToast('Error deleting task', 'error');
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.open').forEach(m => m.classList.remove('open'));
        document.body.style.overflow = '';
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>