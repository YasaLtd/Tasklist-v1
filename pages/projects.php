<?php
/**
 * Yasa LTD Task List - Projects Page
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

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$sort = $_GET['sort'] ?? 'created_desc';

$projectsManager = new Projects();
$tasksManager = new Tasks();

// Single project view
if ($projectId) {
    $project = $projectsManager->get($projectId, $currentUser['id']);
    if (!$project) {
        header('Location: ' . SITE_URL . '/pages/projects.php');
        exit;
    }
    $taskSort = $_GET['task_sort'] ?? 'created_desc';
    $projectTasks = $tasksManager->getAll($currentUser['id'], $projectId, null, $taskSort);
    $pageTitle = $project['name'];
    $isOwner = $project['owner_id'] == $currentUser['id'];
} else {
    $allProjects = $projectsManager->getAll($currentUser['id'], null, $sort);
    $pageTitle = 'Projects';
}

$currentPage = 'projects';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="projects-container">
    <?php if ($projectId && $project): ?>
    <!-- Single Project View -->
    <div class="page-header">
        <div class="page-header-content">
            <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Projects
            </a>
            <div class="project-header-main">
                <div class="project-color-large" style="background-color: <?php echo sanitize($project['color']); ?>"></div>
                <div>
                    <h1 class="page-title"><?php echo sanitize($project['name']); ?></h1>
                    <div class="project-meta-header">
                        <span class="badge <?php echo getStatusClass($project['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                        </span>
                        <span class="badge <?php echo getPriorityClass($project['priority']); ?>">
                            <?php echo ucfirst($project['priority']); ?>
                        </span>
                        <?php if ($project['deadline']): ?>
                        <span class="deadline-badge <?php echo isOverdue($project['deadline']) ? 'overdue' : ''; ?>">
                            <i class="fas fa-calendar"></i> Due: <?php echo formatDate($project['deadline']); ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!$isOwner): ?>
                        <span class="badge badge-shared"><i class="fas fa-share-alt"></i> Shared with you</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($project['assigned_users'])): ?>
                    <div class="assigned-users-display">
                        <i class="fas fa-users"></i>
                        <span>Shared with: </span>
                        <div class="user-avatars">
                            <?php foreach ($project['assigned_users'] as $u): ?>
                            <span class="user-avatar-small" style="background-color: <?php echo $u['avatar_color']; ?>" title="<?php echo sanitize($u['display_name']); ?>">
                                <?php echo strtoupper(substr($u['display_name'], 0, 1)); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($project['description']): ?>
            <p class="project-description"><?php echo nl2br(sanitize($project['description'])); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openTaskModal(null, <?php echo $project['id']; ?>)">
                <i class="fas fa-plus"></i> Add Task
            </button>
            <?php if ($isOwner): ?>
            <button class="btn btn-outline" onclick="openProjectModal(<?php echo $project['id']; ?>)">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-outline" onclick="openAssignModal('project', <?php echo $project['id']; ?>)">
                <i class="fas fa-user-plus"></i> Share
            </button>
            <button class="btn btn-danger" onclick="confirmDelete('project', <?php echo $project['id']; ?>)">
                <i class="fas fa-trash"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Project Tasks -->
    <div class="tasks-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-tasks"></i> Tasks
                <span class="task-count-badge"><?php echo count($projectTasks); ?></span>
            </h2>
            
            <div class="section-tools">
                <select class="form-select filter-select" id="task-status-filter" onchange="filterTasks()">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                
                <select class="form-select filter-select" id="task-sort" onchange="sortTasks(this.value)">
                    <option value="created_desc" <?php echo $taskSort === 'created_desc' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="created_asc" <?php echo $taskSort === 'created_asc' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="title_asc" <?php echo $taskSort === 'title_asc' ? 'selected' : ''; ?>>A-Z</option>
                    <option value="title_desc" <?php echo $taskSort === 'title_desc' ? 'selected' : ''; ?>>Z-A</option>
                    <option value="deadline_asc" <?php echo $taskSort === 'deadline_asc' ? 'selected' : ''; ?>>Deadline (Earliest)</option>
                    <option value="deadline_desc" <?php echo $taskSort === 'deadline_desc' ? 'selected' : ''; ?>>Deadline (Latest)</option>
                    <option value="priority_desc" <?php echo $taskSort === 'priority_desc' ? 'selected' : ''; ?>>Priority (High-Low)</option>
                    <option value="priority_asc" <?php echo $taskSort === 'priority_asc' ? 'selected' : ''; ?>>Priority (Low-High)</option>
                </select>
            </div>
        </div>
        
        <?php if (empty($projectTasks)): ?>
        <div class="empty-state large">
            <i class="fas fa-clipboard-list"></i>
            <h3>No tasks yet</h3>
            <p>Start by adding your first task</p>
            <button class="btn btn-primary" onclick="openTaskModal(null, <?php echo $project['id']; ?>)">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
        <?php else: ?>
        <div class="task-list" id="task-list">
            <?php foreach ($projectTasks as $task): ?>
            <?php $canEditTask = $task['owner_id'] == $currentUser['id'] || $isOwner; ?>
            <div class="task-card <?php echo $task['status']; ?>" 
                 data-task-id="<?php echo $task['id']; ?>"
                 data-status="<?php echo $task['status']; ?>"
                 data-priority="<?php echo $task['priority']; ?>">
                
                <div class="task-checkbox">
                    <?php if ($canEditTask): ?>
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
                
                <div class="task-content">
                    <h3 class="task-title <?php echo $task['status'] === 'completed' ? 'completed' : ''; ?>">
                        <?php echo sanitize($task['title']); ?>
                        <?php if ($task['is_recurring']): ?>
                        <span class="recurring-badge" title="Recurring: <?php echo ucfirst($task['recurrence_type']); ?>">
                            <i class="fas fa-sync-alt"></i>
                        </span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if ($task['description']): ?>
                    <p class="task-description"><?php echo sanitize(substr($task['description'], 0, 150)); ?></p>
                    <?php endif; ?>
                    
                    <div class="task-meta">
                        <span class="badge <?php echo getStatusClass($task['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                        </span>
                        <span class="badge <?php echo getPriorityClass($task['priority']); ?>">
                            <?php echo ucfirst($task['priority']); ?>
                        </span>
                        
                        <?php if (!empty($task['assigned_users'])): ?>
                        <span class="task-assignees">
                            <i class="fas fa-users"></i>
                            <?php echo count($task['assigned_users']); ?> assigned
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($task['deadline']): ?>
                        <span class="task-deadline <?php echo isOverdue($task['deadline']) && $task['status'] !== 'completed' ? 'overdue' : ''; ?>">
                            <i class="fas fa-calendar"></i>
                            <?php echo formatDate($task['deadline']); ?>
                        </span>
                        <?php endif; ?>
                        
                        <span class="task-owner">
                            <span class="user-avatar-tiny" style="background-color: <?php echo $task['owner']['avatar_color']; ?>">
                                <?php echo strtoupper(substr($task['owner']['display_name'], 0, 1)); ?>
                            </span>
                            <?php echo sanitize($task['owner']['display_name']); ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($canEditTask): ?>
                <div class="task-actions">
                    <button class="btn btn-icon btn-sm" onclick="openTaskModal(<?php echo $task['id']; ?>, <?php echo $task['project_id']; ?>)" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-icon btn-sm" onclick="openAssignModal('task', <?php echo $task['id']; ?>)" title="Assign">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button class="btn btn-icon btn-sm btn-danger" onclick="confirmDelete('task', <?php echo $task['id']; ?>)" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
    <!-- Projects List -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title"><i class="fas fa-folder"></i> My Projects</h1>
            <p class="page-subtitle">Projects you own or have access to</p>
        </div>
        
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openProjectModal()">
                <i class="fas fa-plus"></i> New Project
            </button>
        </div>
    </div>
    
    <!-- Filters & Sort -->
    <div class="filters-bar">
        <div class="filter-group">
            <select class="form-select" id="project-status-filter" onchange="filterProjects()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="on_hold">On Hold</option>
            </select>
            
            <select class="form-select" id="project-sort" onchange="sortProjects(this.value)">
                <option value="created_desc" <?php echo $sort === 'created_desc' ? 'selected' : ''; ?>>Newest First</option>
                <option value="created_asc" <?php echo $sort === 'created_asc' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>A-Z</option>
                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Z-A</option>
                <option value="deadline_asc" <?php echo $sort === 'deadline_asc' ? 'selected' : ''; ?>>Deadline (Earliest)</option>
                <option value="deadline_desc" <?php echo $sort === 'deadline_desc' ? 'selected' : ''; ?>>Deadline (Latest)</option>
            </select>
        </div>
        
        <div class="search-group">
            <input type="text" class="form-input" id="project-search" placeholder="Search projects..." oninput="filterProjects()">
            <i class="fas fa-search"></i>
        </div>
    </div>
    
    <?php if (empty($allProjects)): ?>
    <div class="empty-state large">
        <i class="fas fa-folder-plus"></i>
        <h3>No projects yet</h3>
        <p>Create your first project to get started</p>
        <button class="btn btn-primary" onclick="openProjectModal()">
            <i class="fas fa-plus"></i> Create Project
        </button>
    </div>
    <?php else: ?>
    <div class="projects-grid" id="projects-grid">
        <?php foreach ($allProjects as $proj): ?>
        <?php $isProjOwner = $proj['owner_id'] == $currentUser['id']; ?>
        <div class="project-card" 
             data-project-id="<?php echo $proj['id']; ?>"
             data-status="<?php echo $proj['status']; ?>"
             data-name="<?php echo strtolower($proj['name']); ?>">
            <div class="project-card-header" style="border-color: <?php echo sanitize($proj['color']); ?>">
                <div class="project-card-color" style="background-color: <?php echo sanitize($proj['color']); ?>"></div>
                <h3 class="project-card-title">
                    <a href="<?php echo SITE_URL; ?>/pages/projects.php?id=<?php echo $proj['id']; ?>">
                        <?php echo sanitize($proj['name']); ?>
                    </a>
                </h3>
                <?php if ($isProjOwner): ?>
                <div class="project-card-menu">
                    <button class="btn btn-icon btn-sm" onclick="toggleMenu(event, this)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo SITE_URL; ?>/pages/projects.php?id=<?php echo $proj['id']; ?>" class="dropdown-item">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="dropdown-item" onclick="openProjectModal(<?php echo $proj['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="dropdown-item" onclick="openAssignModal('project', <?php echo $proj['id']; ?>)">
                            <i class="fas fa-user-plus"></i> Share
                        </button>
                        <button class="dropdown-item text-danger" onclick="confirmDelete('project', <?php echo $proj['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="project-card-body">
                <?php if ($proj['description']): ?>
                <p class="project-card-description"><?php echo sanitize(substr($proj['description'], 0, 80)); ?></p>
                <?php endif; ?>
                
                <div class="project-card-info">
                    <?php if (!$isProjOwner): ?>
                    <span class="badge badge-shared"><i class="fas fa-share-alt"></i> Shared</span>
                    <?php endif; ?>
                    
                    <?php if (!empty($proj['assigned_users'])): ?>
                    <span class="project-users-count">
                        <i class="fas fa-users"></i> <?php echo count($proj['assigned_users']); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="project-card-progress">
                    <?php $progress = $proj['task_count'] > 0 ? round(($proj['completed_count'] / $proj['task_count']) * 100) : 0; ?>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <span class="progress-text"><?php echo $proj['completed_count']; ?>/<?php echo $proj['task_count']; ?> tasks</span>
                </div>
            </div>
            
            <div class="project-card-footer">
                <span class="badge <?php echo getStatusClass($proj['status']); ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $proj['status'])); ?>
                </span>
                <?php if ($proj['deadline']): ?>
                <span class="project-card-deadline <?php echo isOverdue($proj['deadline']) && $proj['status'] !== 'completed' ? 'overdue' : ''; ?>">
                    <i class="fas fa-calendar"></i> <?php echo formatDate($proj['deadline']); ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Project Modal -->
<div class="modal" id="project-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="project-modal-title">New Project</h2>
                <button class="modal-close" onclick="closeModal('project-modal')"><i class="fas fa-times"></i></button>
            </div>
            <form id="project-form" onsubmit="saveProject(event)">
                <input type="hidden" id="project-id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="project-name" class="form-label">Project Name *</label>
                        <input type="text" id="project-name" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="project-description" class="form-label">Description</label>
                        <textarea id="project-description" name="description" class="form-textarea" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project-status" class="form-label">Status</label>
                            <select id="project-status" name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="project-priority" class="form-label">Priority</label>
                            <select id="project-priority" name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project-deadline" class="form-label">Deadline</label>
                            <input type="date" id="project-deadline" name="deadline" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="project-color" class="form-label">Color</label>
                            <input type="color" id="project-color" name="color" class="form-color" value="#37505d">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('project-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="project-submit-btn">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Modal -->
<div class="modal" id="task-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="task-modal-title">New Task</h2>
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
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="task-deadline" class="form-label">Deadline</label>
                        <input type="date" id="task-deadline" name="deadline" class="form-input">
                    </div>
                    
                    <!-- Recurring Task Options -->
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
                    <button type="submit" class="btn btn-primary" id="task-submit-btn">Create Task</button>
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
                <h2 class="modal-title" id="assign-modal-title">Assign User</h2>
                <button class="modal-close" onclick="closeModal('assign-modal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assign-type">
                <input type="hidden" id="assign-entity-id">
                
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

<!-- Confirm Modal -->
<div class="modal" id="confirm-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete</h2>
                <button class="modal-close" onclick="closeModal('confirm-modal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p id="confirm-message">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('confirm-modal')">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-btn" onclick="executeDelete()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const SITE_URL = '<?php echo SITE_URL; ?>';
const CURRENT_USER = <?php echo json_encode($currentUser); ?>;
<?php if ($projectId && $project): ?>
const CURRENT_PROJECT_ID = <?php echo $projectId; ?>;
<?php endif; ?>

let deleteType = null;
let deleteId = null;

// Modal functions
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

function toggleMenu(e, btn) {
    e.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
        if (m !== menu) m.classList.remove('show');
    });
    menu.classList.toggle('show');
}

document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
});

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span class="toast-icon"><i class="fas fa-${type === 'success' ? 'check' : 'exclamation-circle'}"></i></span><span class="toast-message">${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}

// Project functions
async function openProjectModal(projectId = null) {
    document.getElementById('project-form').reset();
    document.getElementById('project-id').value = '';
    document.getElementById('project-color').value = '#37505d';
    
    if (projectId) {
        document.getElementById('project-modal-title').textContent = 'Edit Project';
        document.getElementById('project-submit-btn').textContent = 'Save Changes';
        
        try {
            const response = await fetch(`${SITE_URL}/api/projects.php?id=${projectId}`);
            const result = await response.json();
            if (result.success) {
                const p = result.data;
                document.getElementById('project-id').value = p.id;
                document.getElementById('project-name').value = p.name;
                document.getElementById('project-description').value = p.description || '';
                document.getElementById('project-status').value = p.status;
                document.getElementById('project-priority').value = p.priority;
                document.getElementById('project-deadline').value = p.deadline ? p.deadline.split(' ')[0] : '';
                document.getElementById('project-color').value = p.color || '#37505d';
            }
        } catch (error) {
            showToast('Error loading project', 'error');
        }
    } else {
        document.getElementById('project-modal-title').textContent = 'New Project';
        document.getElementById('project-submit-btn').textContent = 'Create Project';
    }
    
    openModal('project-modal');
}

async function saveProject(e) {
    e.preventDefault();
    
    const projectId = document.getElementById('project-id').value;
    const data = {
        name: document.getElementById('project-name').value,
        description: document.getElementById('project-description').value,
        status: document.getElementById('project-status').value,
        priority: document.getElementById('project-priority').value,
        deadline: document.getElementById('project-deadline').value || null,
        color: document.getElementById('project-color').value
    };
    
    try {
        const url = projectId ? `${SITE_URL}/api/projects.php?id=${projectId}` : `${SITE_URL}/api/projects.php`;
        const response = await fetch(url, {
            method: projectId ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message);
            closeModal('project-modal');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Error', 'error');
        }
    } catch (error) {
        showToast('Error saving project', 'error');
    }
}

// Task functions
function toggleRecurringOptions() {
    const checkbox = document.getElementById('task-recurring');
    const options = document.getElementById('recurring-options');
    options.classList.toggle('hidden', !checkbox.checked);
}

async function openTaskModal(taskId = null, projectId = null) {
    document.getElementById('task-form').reset();
    document.getElementById('task-id').value = '';
    document.getElementById('task-project-id').value = projectId || '';
    document.getElementById('recurring-options').classList.add('hidden');
    
    if (taskId) {
        document.getElementById('task-modal-title').textContent = 'Edit Task';
        document.getElementById('task-submit-btn').textContent = 'Save Changes';
        
        try {
            const response = await fetch(`${SITE_URL}/api/tasks.php?id=${taskId}`);
            const result = await response.json();
            if (result.success) {
                const t = result.data;
                document.getElementById('task-id').value = t.id;
                document.getElementById('task-project-id').value = t.project_id;
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
        }
    } else {
        document.getElementById('task-modal-title').textContent = 'New Task';
        document.getElementById('task-submit-btn').textContent = 'Create Task';
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
        const url = taskId ? `${SITE_URL}/api/tasks.php?id=${taskId}` : `${SITE_URL}/api/tasks.php`;
        const response = await fetch(url, {
            method: taskId ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message);
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
                title.classList.add('completed');
            } else {
                card.classList.remove('completed');
                title.classList.remove('completed');
            }
            showToast(completed ? 'Task completed!' : 'Task reopened');
            
            // Reload if recurring to show new task
            if (result.data && result.data.is_recurring && completed) {
                setTimeout(() => location.reload(), 1000);
            }
        }
    } catch (error) {
        showToast('Error updating task', 'error');
    }
}

// Assign functions
async function openAssignModal(type, entityId) {
    document.getElementById('assign-type').value = type;
    document.getElementById('assign-entity-id').value = entityId;
    document.getElementById('assign-user-input').value = '';
    document.getElementById('assign-result').innerHTML = '';
    document.getElementById('assign-modal-title').textContent = type === 'project' ? 'Share Project' : 'Assign Task';
    
    // Load current assignments
    await loadAssignedUsers(type, entityId);
    
    openModal('assign-modal');
}

async function loadAssignedUsers(type, entityId) {
    const list = document.getElementById('assigned-users-list');
    list.innerHTML = '<p class="loading">Loading...</p>';
    
    try {
        const url = type === 'project' 
            ? `${SITE_URL}/api/projects.php?id=${entityId}`
            : `${SITE_URL}/api/tasks.php?id=${entityId}`;
        
        const response = await fetch(url);
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
                        <button class="btn btn-icon btn-sm btn-danger" onclick="removeAssignedUser('${type}', ${entityId}, ${u.id})">
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
    const type = document.getElementById('assign-type').value;
    const entityId = document.getElementById('assign-entity-id').value;
    const identifier = document.getElementById('assign-user-input').value.trim();
    const resultDiv = document.getElementById('assign-result');
    
    if (!identifier) {
        resultDiv.innerHTML = '<p class="text-danger">Please enter an email or username</p>';
        return;
    }
    
    try {
        const url = type === 'project' 
            ? `${SITE_URL}/api/projects.php?id=${entityId}&action=assign`
            : `${SITE_URL}/api/tasks.php?id=${entityId}&action=assign`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ identifier: identifier })
        });
        
        const result = await response.json();
        
        if (result.success) {
            resultDiv.innerHTML = `<p class="text-success"><i class="fas fa-check"></i> ${result.message}</p>`;
            document.getElementById('assign-user-input').value = '';
            await loadAssignedUsers(type, entityId);
        } else {
            resultDiv.innerHTML = `<p class="text-danger"><i class="fas fa-exclamation-circle"></i> ${result.message}</p>`;
        }
    } catch (error) {
        resultDiv.innerHTML = '<p class="text-danger">Error assigning user</p>';
    }
}

async function removeAssignedUser(type, entityId, userId) {
    try {
        const url = type === 'project' 
            ? `${SITE_URL}/api/projects.php?id=${entityId}&action=unassign`
            : `${SITE_URL}/api/tasks.php?id=${entityId}&action=unassign`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('User removed');
            await loadAssignedUsers(type, entityId);
        } else {
            showToast(result.message || 'Error', 'error');
        }
    } catch (error) {
        showToast('Error removing user', 'error');
    }
}

// Delete functions
function confirmDelete(type, id) {
    deleteType = type;
    deleteId = id;
    const message = type === 'project' 
        ? 'Are you sure you want to delete this project? All tasks will be deleted.'
        : 'Are you sure you want to delete this task?';
    document.getElementById('confirm-message').textContent = message;
    openModal('confirm-modal');
}

async function executeDelete() {
    closeModal('confirm-modal');
    
    try {
        const url = deleteType === 'project'
            ? `${SITE_URL}/api/projects.php?id=${deleteId}`
            : `${SITE_URL}/api/tasks.php?id=${deleteId}`;
        
        const response = await fetch(url, { method: 'DELETE' });
        const result = await response.json();
        
        if (result.success) {
            showToast(deleteType === 'project' ? 'Project deleted' : 'Task deleted');
            
            if (deleteType === 'project') {
                setTimeout(() => window.location.href = `${SITE_URL}/pages/projects.php`, 500);
            } else {
                const card = document.querySelector(`[data-task-id="${deleteId}"]`);
                if (card) card.remove();
            }
        } else {
            showToast(result.message || 'Error', 'error');
        }
    } catch (error) {
        showToast('Error deleting', 'error');
    }
}

// Filter & Sort functions
function filterProjects() {
    const status = document.getElementById('project-status-filter')?.value || '';
    const search = document.getElementById('project-search')?.value.toLowerCase() || '';
    
    document.querySelectorAll('.project-card').forEach(card => {
        const cardStatus = card.dataset.status;
        const cardName = card.dataset.name;
        const matchStatus = !status || cardStatus === status;
        const matchSearch = !search || cardName.includes(search);
        card.style.display = (matchStatus && matchSearch) ? '' : 'none';
    });
}

function sortProjects(sort) {
    window.location.href = `${SITE_URL}/pages/projects.php?sort=${sort}`;
}

function filterTasks() {
    const status = document.getElementById('task-status-filter')?.value || '';
    
    document.querySelectorAll('.task-card').forEach(card => {
        const cardStatus = card.dataset.status;
        const matchStatus = !status || cardStatus === status;
        card.style.display = matchStatus ? '' : 'none';
    });
}

function sortTasks(sort) {
    const projectId = typeof CURRENT_PROJECT_ID !== 'undefined' ? CURRENT_PROJECT_ID : '';
    window.location.href = `${SITE_URL}/pages/projects.php?id=${projectId}&task_sort=${sort}`;
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