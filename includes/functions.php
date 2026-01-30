<?php
/**
 * Yasa LTD Task List - Functions
 */

if (!defined('YASA_TASKLIST')) {
    die('Direct access not permitted');
}

class Projects {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($userId, $status = null, $sort = 'created_desc') {
        $sql = "SELECT DISTINCT p.*, 
                       (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as task_count,
                       (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'completed') as completed_count
                FROM projects p
                LEFT JOIN project_users pu ON p.id = pu.project_id
                WHERE p.owner_id = ? OR pu.user_id = ?";
        
        $params = [$userId, $userId];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        switch ($sort) {
            case 'name_asc': $sql .= " ORDER BY p.name ASC"; break;
            case 'name_desc': $sql .= " ORDER BY p.name DESC"; break;
            case 'deadline_asc': $sql .= " ORDER BY p.deadline IS NULL, p.deadline ASC"; break;
            case 'deadline_desc': $sql .= " ORDER BY p.deadline IS NULL, p.deadline DESC"; break;
            case 'created_asc': $sql .= " ORDER BY p.created_at ASC"; break;
            default: $sql .= " ORDER BY p.created_at DESC";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll();
        
        foreach ($projects as &$project) {
            $project['owner'] = $this->getUser($project['owner_id']);
            $project['assigned_users'] = $this->getProjectUsers($project['id']);
            $project['user_role'] = ($project['owner_id'] == $userId) ? 'owner' : 'member';
        }
        
        return $projects;
    }
    
    public function get($id, $userId) {
        $stmt = $this->db->prepare(
            "SELECT p.* FROM projects p
             LEFT JOIN project_users pu ON p.id = pu.project_id
             WHERE p.id = ? AND (p.owner_id = ? OR pu.user_id = ?)
             LIMIT 1"
        );
        $stmt->execute([$id, $userId, $userId]);
        $project = $stmt->fetch();
        
        if ($project) {
            $project['owner'] = $this->getUser($project['owner_id']);
            $project['assigned_users'] = $this->getProjectUsers($id);
            $project['user_role'] = ($project['owner_id'] == $userId) ? 'owner' : 'member';
        }
        
        return $project ?: null;
    }
    
    public function create($data, $userId) {
        $stmt = $this->db->prepare(
            "INSERT INTO projects (name, description, status, priority, deadline, color, owner_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['status'] ?? 'active',
            $data['priority'] ?? 'medium',
            !empty($data['deadline']) ? $data['deadline'] : null,
            $data['color'] ?? '#37505d',
            $userId
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data, $userId) {
        if (!$this->isOwner($id, $userId)) return false;
        
        $fields = [];
        $params = [];
        
        foreach (['name', 'description', 'status', 'priority', 'deadline', 'color'] as $field) {
            if (isset($data[$field])) {
                if ($field === 'deadline' && empty($data[$field])) {
                    $fields[] = "$field = NULL";
                } else {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) return true;
        
        $params[] = $id;
        $sql = "UPDATE projects SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id, $userId) {
        if (!$this->isOwner($id, $userId)) return false;
        
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ? AND owner_id = ?");
        return $stmt->execute([$id, $userId]);
    }
    
    public function isOwner($projectId, $userId) {
        $stmt = $this->db->prepare("SELECT 1 FROM projects WHERE id = ? AND owner_id = ?");
        $stmt->execute([$projectId, $userId]);
        return $stmt->fetch() !== false;
    }
    
    public function userHasAccess($projectId, $userId) {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM projects p
             LEFT JOIN project_users pu ON p.id = pu.project_id
             WHERE p.id = ? AND (p.owner_id = ? OR pu.user_id = ?)"
        );
        $stmt->execute([$projectId, $userId, $userId]);
        return $stmt->fetch() !== false;
    }
    
    public function assignUser($projectId, $identifier, $assignedBy) {
        if (!$this->isOwner($projectId, $assignedBy)) {
            return ['success' => false, 'message' => 'Only owner can assign users'];
        }
        
        $auth = new Auth();
        $user = $auth->findUser($identifier);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $project = $this->get($projectId, $assignedBy);
        if ($user['id'] == $project['owner_id']) {
            return ['success' => false, 'message' => 'Cannot assign the owner'];
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO project_users (project_id, user_id, role, assigned_by) 
             VALUES (?, ?, 'viewer', ?)
             ON DUPLICATE KEY UPDATE assigned_by = ?, assigned_at = NOW()"
        );
        $stmt->execute([$projectId, $user['id'], $assignedBy, $assignedBy]);
        
        return ['success' => true, 'message' => 'User assigned', 'user' => $user];
    }
    
    public function removeUser($projectId, $userId, $removedBy) {
        if (!$this->isOwner($projectId, $removedBy)) return false;
        
        $stmt = $this->db->prepare("DELETE FROM project_users WHERE project_id = ? AND user_id = ?");
        return $stmt->execute([$projectId, $userId]);
    }
    
    public function getProjectUsers($projectId) {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.email, u.display_name, u.avatar_color, pu.role
             FROM project_users pu
             JOIN users u ON pu.user_id = u.id
             WHERE pu.project_id = ?"
        );
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    private function getUser($userId) {
        $stmt = $this->db->prepare("SELECT id, username, email, display_name, avatar_color FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: ['id' => 0, 'display_name' => 'Unknown', 'avatar_color' => '#37505d'];
    }
}

class Tasks {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($userId, $projectId = null, $status = null, $sort = 'created_desc') {
        $sql = "SELECT DISTINCT t.*, p.name as project_name, p.color as project_color
                FROM tasks t
                JOIN projects p ON t.project_id = p.id
                LEFT JOIN project_users pu ON p.id = pu.project_id
                LEFT JOIN task_assignments ta ON t.id = ta.task_id
                WHERE (p.owner_id = ? OR pu.user_id = ? OR t.owner_id = ? OR ta.user_id = ?)";
        
        $params = [$userId, $userId, $userId, $userId];
        
        if ($projectId) {
            $sql .= " AND t.project_id = ?";
            $params[] = $projectId;
        }
        
        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }
        
        switch ($sort) {
            case 'title_asc': $sql .= " ORDER BY t.title ASC"; break;
            case 'title_desc': $sql .= " ORDER BY t.title DESC"; break;
            case 'deadline_asc': $sql .= " ORDER BY t.deadline IS NULL, t.deadline ASC"; break;
            case 'deadline_desc': $sql .= " ORDER BY t.deadline IS NULL, t.deadline DESC"; break;
            case 'priority_desc': $sql .= " ORDER BY FIELD(t.priority, 'urgent', 'high', 'medium', 'low')"; break;
            case 'priority_asc': $sql .= " ORDER BY FIELD(t.priority, 'low', 'medium', 'high', 'urgent')"; break;
            case 'created_asc': $sql .= " ORDER BY t.created_at ASC"; break;
            default: $sql .= " ORDER BY t.created_at DESC";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();
        
        foreach ($tasks as &$task) {
            $task['owner'] = $this->getUser($task['owner_id']);
            $task['assigned_users'] = $this->getTaskAssignments($task['id']);
        }
        
        return $tasks;
    }
    
    public function get($id, $userId) {
        $stmt = $this->db->prepare(
            "SELECT t.*, p.name as project_name, p.color as project_color
             FROM tasks t
             JOIN projects p ON t.project_id = p.id
             LEFT JOIN project_users pu ON p.id = pu.project_id
             LEFT JOIN task_assignments ta ON t.id = ta.task_id
             WHERE t.id = ? AND (p.owner_id = ? OR pu.user_id = ? OR t.owner_id = ? OR ta.user_id = ?)
             LIMIT 1"
        );
        $stmt->execute([$id, $userId, $userId, $userId, $userId]);
        $task = $stmt->fetch();
        
        if ($task) {
            $task['owner'] = $this->getUser($task['owner_id']);
            $task['assigned_users'] = $this->getTaskAssignments($id);
        }
        
        return $task ?: null;
    }
    
    public function create($data, $userId) {
        $projects = new Projects();
        if (!$projects->userHasAccess($data['project_id'], $userId)) {
            return false;
        }
        
        $isRecurring = !empty($data['is_recurring']) && !empty($data['recurrence_type']);
        $nextOccurrence = null;
        
        if ($isRecurring && !empty($data['deadline'])) {
            $nextOccurrence = $this->calcNextOccurrence($data['deadline'], $data['recurrence_type'], $data['recurrence_interval'] ?? 1);
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO tasks (project_id, title, description, status, priority, deadline, owner_id, 
                               is_recurring, recurrence_type, recurrence_interval, recurrence_end_date, next_occurrence) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['project_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['status'] ?? 'pending',
            $data['priority'] ?? 'medium',
            !empty($data['deadline']) ? $data['deadline'] : null,
            $userId,
            $isRecurring ? 1 : 0,
            $isRecurring ? $data['recurrence_type'] : null,
            $isRecurring ? ($data['recurrence_interval'] ?? 1) : null,
            !empty($data['recurrence_end_date']) ? $data['recurrence_end_date'] : null,
            $nextOccurrence
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data, $userId) {
        if (!$this->canEdit($id, $userId)) return false;
        
        $task = $this->get($id, $userId);
        
        $fields = [];
        $params = [];
        
        foreach (['title', 'description', 'status', 'priority', 'deadline', 'is_recurring', 'recurrence_type', 'recurrence_interval', 'recurrence_end_date'] as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['deadline', 'recurrence_end_date', 'recurrence_type']) && empty($data[$field])) {
                    $fields[] = "$field = NULL";
                } else {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) return true;
        
        if (isset($data['status']) && $data['status'] === 'completed') {
            $fields[] = "completed_at = NOW()";
            if ($task && $task['is_recurring']) {
                $this->createNextRecurrence($task);
            }
        } elseif (isset($data['status'])) {
            $fields[] = "completed_at = NULL";
        }
        
        $params[] = $id;
        $sql = "UPDATE tasks SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id, $userId) {
        $task = $this->get($id, $userId);
        if (!$task) return false;
        
        $projects = new Projects();
        if ($task['owner_id'] != $userId && !$projects->isOwner($task['project_id'], $userId)) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function canEdit($taskId, $userId) {
        $task = $this->get($taskId, $userId);
        if (!$task) return false;
        if ($task['owner_id'] == $userId) return true;
        
        foreach ($task['assigned_users'] as $u) {
            if ($u['id'] == $userId) return true;
        }
        
        $projects = new Projects();
        return $projects->isOwner($task['project_id'], $userId);
    }
    
    public function assignUser($taskId, $identifier, $assignedBy) {
        if (!$this->canEdit($taskId, $assignedBy)) {
            return ['success' => false, 'message' => 'Permission denied'];
        }
        
        $auth = new Auth();
        $user = $auth->findUser($identifier);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO task_assignments (task_id, user_id, assigned_by) 
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE assigned_by = ?, assigned_at = NOW()"
        );
        $stmt->execute([$taskId, $user['id'], $assignedBy, $assignedBy]);
        
        return ['success' => true, 'message' => 'User assigned', 'user' => $user];
    }
    
    public function removeUserFromTask($taskId, $userId, $removedBy) {
        if (!$this->canEdit($taskId, $removedBy)) return false;
        
        $stmt = $this->db->prepare("DELETE FROM task_assignments WHERE task_id = ? AND user_id = ?");
        return $stmt->execute([$taskId, $userId]);
    }
    
    public function getComments($taskId) {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.display_name as user_name, u.avatar_color
             FROM task_comments c JOIN users u ON c.user_id = u.id
             WHERE c.task_id = ? ORDER BY c.created_at ASC"
        );
        $stmt->execute([$taskId]);
        return $stmt->fetchAll();
    }
    
    public function addComment($taskId, $userId, $comment) {
        if (!$this->get($taskId, $userId)) return false;
        
        $stmt = $this->db->prepare("INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$taskId, $userId, $comment]);
        return $this->db->lastInsertId();
    }
    
    private function getTaskAssignments($taskId) {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.email, u.display_name, u.avatar_color
             FROM task_assignments ta JOIN users u ON ta.user_id = u.id
             WHERE ta.task_id = ?"
        );
        $stmt->execute([$taskId]);
        return $stmt->fetchAll();
    }
    
    private function getUser($userId) {
        $stmt = $this->db->prepare("SELECT id, username, email, display_name, avatar_color FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: ['id' => 0, 'display_name' => 'Unknown', 'avatar_color' => '#37505d'];
    }
    
    private function calcNextOccurrence($date, $type, $interval = 1) {
        $d = new DateTime($date);
        switch ($type) {
            case 'daily': $d->modify("+{$interval} days"); break;
            case 'weekly': $d->modify("+{$interval} weeks"); break;
            case 'monthly': $d->modify("+{$interval} months"); break;
            case 'yearly': $d->modify("+{$interval} years"); break;
        }
        return $d->format('Y-m-d');
    }
    
    private function createNextRecurrence($task) {
        if (!$task['is_recurring'] || !$task['recurrence_type']) return;
        if ($task['recurrence_end_date'] && $task['next_occurrence'] > $task['recurrence_end_date']) return;
        
        $nextDeadline = $task['next_occurrence'] ?? $this->calcNextOccurrence(
            $task['deadline'] ?? date('Y-m-d'),
            $task['recurrence_type'],
            $task['recurrence_interval'] ?? 1
        );
        
        $stmt = $this->db->prepare(
            "INSERT INTO tasks (project_id, title, description, status, priority, deadline, owner_id, 
                               is_recurring, recurrence_type, recurrence_interval, recurrence_end_date, next_occurrence, parent_task_id) 
             VALUES (?, ?, ?, 'pending', ?, ?, ?, 1, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $task['project_id'],
            $task['title'],
            $task['description'],
            $task['priority'],
            $nextDeadline,
            $task['owner_id'],
            $task['recurrence_type'],
            $task['recurrence_interval'] ?? 1,
            $task['recurrence_end_date'],
            $this->calcNextOccurrence($nextDeadline, $task['recurrence_type'], $task['recurrence_interval'] ?? 1),
            $task['parent_task_id'] ?? $task['id']
        ]);
    }
}

class ActivityLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getRecent($userId, $limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.display_name as user_name, u.avatar_color
             FROM activity_log a JOIN users u ON a.user_id = u.id
             WHERE a.user_id = ?
             ORDER BY a.created_at DESC LIMIT " . intval($limit)
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function log($userId, $action, $entityType, $entityId, $details = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $action, $entityType, $entityId, $details]);
    }
}

function sanitize($input) {
    if (is_array($input)) return array_map('sanitize', $input);
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function formatDate($date, $format = 'M j, Y') {
    return $date ? date($format, strtotime($date)) : '-';
}

function timeAgo($datetime) {
    if (!$datetime) return '-';
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDate($datetime);
}

function getStatusClass($status) {
    $map = [
        'pending' => 'status-pending', 'in_progress' => 'status-progress',
        'completed' => 'status-completed', 'cancelled' => 'status-cancelled',
        'active' => 'status-active', 'on_hold' => 'status-hold'
    ];
    return $map[$status] ?? 'status-default';
}

function getPriorityClass($priority) {
    $map = [
        'low' => 'priority-low', 'medium' => 'priority-medium',
        'high' => 'priority-high', 'urgent' => 'priority-urgent'
    ];
    return $map[$priority] ?? 'priority-default';
}

function isOverdue($deadline) {
    return $deadline && strtotime($deadline) < time();
}