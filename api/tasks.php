<?php
/**
 * Yasa LTD Task List - Tasks API
 */

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Authenticate
$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();
$currentUser = $auth->validateSession($sessionToken);

if (!$currentUser) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$taskId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = $_GET['action'] ?? null;

$tasks = new Tasks();

// Handle actions (assign/unassign)
if ($action && $taskId && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'assign':
            $identifier = $data['identifier'] ?? '';
            if (empty($identifier)) {
                jsonResponse(['success' => false, 'message' => 'Email or username required'], 400);
            }
            $result = $tasks->assignUser($taskId, $identifier, $currentUser['id']);
            jsonResponse($result, $result['success'] ? 200 : 400);
            break;
            
        case 'unassign':
            $userId = $data['user_id'] ?? null;
            if (!$userId) {
                jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
            }
            $result = $tasks->removeUserFromTask($taskId, $userId, $currentUser['id']);
            if ($result) {
                jsonResponse(['success' => true, 'message' => 'User removed']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            break;
            
        case 'comment':
            $comment = $data['comment'] ?? '';
            if (empty($comment)) {
                jsonResponse(['success' => false, 'message' => 'Comment text required'], 400);
            }
            $commentId = $tasks->addComment($taskId, $currentUser['id'], $comment);
            if ($commentId) {
                jsonResponse(['success' => true, 'message' => 'Comment added', 'data' => ['id' => $commentId]]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add comment'], 500);
            }
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
    }
}

// Standard CRUD operations
switch ($method) {
    case 'GET':
        if ($taskId) {
            $task = $tasks->get($taskId, $currentUser['id']);
            if ($task) {
                $task['comments'] = $tasks->getComments($taskId);
                jsonResponse(['success' => true, 'data' => $task]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Task not found or access denied'], 404);
            }
        } else {
            $projectId = $_GET['project_id'] ?? null;
            $status = $_GET['status'] ?? null;
            $sort = $_GET['sort'] ?? 'created_desc';
            
            $allTasks = $tasks->getAll($currentUser['id'], $projectId, $status, $sort);
            jsonResponse(['success' => true, 'data' => $allTasks]);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['title'])) {
            jsonResponse(['success' => false, 'message' => 'Task title is required'], 400);
        }
        
        if (empty($data['project_id'])) {
            jsonResponse(['success' => false, 'message' => 'Project ID is required'], 400);
        }
        
        // Verify user has access to project
        $projects = new Projects();
        if (!$projects->userHasAccess($data['project_id'], $currentUser['id'])) {
            jsonResponse(['success' => false, 'message' => 'Access denied to this project'], 403);
        }
        
        $newTaskId = $tasks->create($data, $currentUser['id']);
        
        if ($newTaskId) {
            $task = $tasks->get($newTaskId, $currentUser['id']);
            jsonResponse(['success' => true, 'message' => 'Task created', 'data' => $task], 201);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create task'], 500);
        }
        break;
        
    case 'PUT':
        if (!$taskId) {
            jsonResponse(['success' => false, 'message' => 'Task ID required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $tasks->update($taskId, $data, $currentUser['id']);
        
        if ($result) {
            $task = $tasks->get($taskId, $currentUser['id']);
            jsonResponse(['success' => true, 'message' => 'Task updated', 'data' => $task]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Update failed or permission denied'], 403);
        }
        break;
        
    case 'DELETE':
        if (!$taskId) {
            jsonResponse(['success' => false, 'message' => 'Task ID required'], 400);
        }
        
        $result = $tasks->delete($taskId, $currentUser['id']);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Task deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Delete failed or permission denied'], 403);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}