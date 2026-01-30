<?php
/**
 * Yasa LTD Task List - Dashboard
 */

// Session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();
$currentUser = $auth->validateSession($sessionToken);

if (!$currentUser) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$projects = new Projects();
$tasks = new Tasks();
$activityLog = new ActivityLog();

$allProjects = $projects->getAll($currentUser['id']);
$allTasks = $tasks->getAll($currentUser['id']);
$recentActivity = $activityLog->getRecent($currentUser['id'], 10);

// Stats
$totalProjects = count($allProjects);
$activeProjects = count(array_filter($allProjects, fn($p) => $p['status'] === 'active'));
$totalTasks = count($allTasks);
$completedTasks = count(array_filter($allTasks, fn($t) => $t['status'] === 'completed'));
$pendingTasks = count(array_filter($allTasks, fn($t) => $t['status'] === 'pending'));
$overdueTasks = count(array_filter($allTasks, fn($t) => $t['status'] !== 'completed' && isOverdue($t['deadline'])));

// Upcoming deadlines
$upcomingTasks = array_filter($allTasks, function($t) {
    return $t['status'] !== 'completed' && $t['deadline'] && strtotime($t['deadline']) > time();
});
usort($upcomingTasks, fn($a, $b) => strtotime($a['deadline']) - strtotime($b['deadline']));
$upcomingTasks = array_slice($upcomingTasks, 0, 5);

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </h1>
            <p class="page-subtitle">Welcome back, <?php echo sanitize($currentUser['display_name']); ?>!</p>
        </div>
        
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openProjectModal()">
                <i class="fas fa-plus"></i>
                <span>New Project</span>
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card stat-projects">
            <div class="stat-icon"><i class="fas fa-folder"></i></div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $activeProjects; ?></h3>
                <p class="stat-label">Active Projects</p>
                <span class="stat-sub">of <?php echo $totalProjects; ?> total</span>
            </div>
        </div>
        
        <div class="stat-card stat-tasks">
            <div class="stat-icon"><i class="fas fa-tasks"></i></div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $totalTasks; ?></h3>
                <p class="stat-label">Total Tasks</p>
                <span class="stat-sub"><?php echo $pendingTasks; ?> pending</span>
            </div>
        </div>
        
        <div class="stat-card stat-completed">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $completedTasks; ?></h3>
                <p class="stat-label">Completed</p>
                <span class="stat-sub"><?php echo $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0; ?>% done</span>
            </div>
        </div>
        
        <div class="stat-card stat-overdue <?php echo $overdueTasks > 0 ? 'has-overdue' : ''; ?>">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $overdueTasks; ?></h3>
                <p class="stat-label">Overdue</p>
                <span class="stat-sub">need attention</span>
            </div>
        </div>
    </div>
    
    <!-- Content Grid -->
    <div class="dashboard-grid">
        <!-- Recent Projects -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-folder-open"></i> My Projects</h2>
                <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="card-link">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($allProjects)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-plus"></i>
                    <p>No projects yet</p>
                    <button class="btn btn-outline btn-sm" onclick="openProjectModal()">Create Project</button>
                </div>
                <?php else: ?>
                <div class="project-list">
                    <?php foreach (array_slice($allProjects, 0, 5) as $project): ?>
                    <div class="project-item">
                        <div class="project-color" style="background-color: <?php echo sanitize($project['color'] ?? '#37505d'); ?>"></div>
                        <div class="project-info">
                            <a href="<?php echo SITE_URL; ?>/pages/projects.php?id=<?php echo $project['id']; ?>" class="project-name">
                                <?php echo sanitize($project['name']); ?>
                            </a>
                            <div class="project-meta">
                                <span class="badge <?php echo getStatusClass($project['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                </span>
                                <span class="task-count">
                                    <i class="fas fa-check"></i>
                                    <?php echo $project['completed_count']; ?>/<?php echo $project['task_count']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Upcoming Deadlines -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-clock"></i> Upcoming Deadlines</h2>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingTasks)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <p>No upcoming deadlines</p>
                </div>
                <?php else: ?>
                <div class="deadline-list">
                    <?php foreach ($upcomingTasks as $task): ?>
                    <div class="deadline-item">
                        <div class="deadline-date">
                            <span class="date-day"><?php echo date('d', strtotime($task['deadline'])); ?></span>
                            <span class="date-month"><?php echo date('M', strtotime($task['deadline'])); ?></span>
                        </div>
                        <div class="deadline-info">
                            <span class="deadline-title"><?php echo sanitize($task['title']); ?></span>
                            <span class="deadline-project" style="color: <?php echo $task['project_color'] ?? '#37505d'; ?>">
                                <?php echo sanitize($task['project_name']); ?>
                            </span>
                        </div>
                        <span class="badge <?php echo getPriorityClass($task['priority']); ?>">
                            <?php echo ucfirst($task['priority']); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="dashboard-card full-width">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Recent Activity</h2>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                <div class="empty-state">
                    <i class="fas fa-stream"></i>
                    <p>No recent activity</p>
                </div>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon activity-<?php echo $activity['action']; ?>">
                            <?php
                            $icons = ['create' => 'fa-plus', 'update' => 'fa-edit', 'delete' => 'fa-trash', 'complete' => 'fa-check'];
                            ?>
                            <i class="fas <?php echo $icons[$activity['action']] ?? 'fa-circle'; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-text"><?php echo sanitize($activity['details'] ?? ''); ?></p>
                            <span class="activity-time"><i class="fas fa-clock"></i> <?php echo timeAgo($activity['created_at']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
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
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const SITE_URL = '<?php echo SITE_URL; ?>';

function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

function openProjectModal() {
    document.getElementById('project-form').reset();
    document.getElementById('project-id').value = '';
    document.getElementById('project-color').value = '#37505d';
    document.getElementById('project-modal-title').textContent = 'New Project';
    openModal('project-modal');
}

async function saveProject(e) {
    e.preventDefault();
    
    const data = {
        name: document.getElementById('project-name').value,
        description: document.getElementById('project-description').value,
        status: document.getElementById('project-status').value,
        priority: document.getElementById('project-priority').value,
        deadline: document.getElementById('project-deadline').value || null,
        color: document.getElementById('project-color').value
    };
    
    try {
        const response = await fetch(SITE_URL + '/api/projects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Project created!');
            closeModal('project-modal');
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.open').forEach(m => m.classList.remove('open'));
        document.body.style.overflow = '';
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>