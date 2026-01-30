<?php
/**
 * Yasa LTD Task List - Projects API
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
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = $_GET['action'] ?? null;

$projects = new Projects();

// Handle actions (assign/unassign)
if ($action && $projectId && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'assign':
            $identifier = $data['identifier'] ?? '';
            if (empty($identifier)) {
                jsonResponse(['success' => false, 'message' => 'Email or username required'], 400);
            }
            $result = $projects->assignUser($projectId, $identifier, $currentUser['id']);
            jsonResponse($result, $result['success'] ? 200 : 400);
            break;
            
        case 'unassign':
            $userId = $data['user_id'] ?? null;
            if (!$userId) {
                jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
            }
            $result = $projects->removeUser($projectId, $userId, $currentUser['id']);
            if ($result) {
                jsonResponse(['success' => true, 'message' => 'User removed']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Permission denied'], 403);
            }
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
    }
}

// Standard CRUD operations
switch ($method) {
    case 'GET':
        if ($projectId) {
            $project = $projects->get($projectId, $currentUser['id']);
            if ($project) {
                jsonResponse(['success' => true, 'data' => $project]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Project not found or access denied'], 404);
            }
        } else {
            $status = $_GET['status'] ?? null;
            $sort = $_GET['sort'] ?? 'created_desc';
            
            try {
                $allProjects = $projects->getAll($currentUser['id'], $status, $sort);
                jsonResponse(['success' => true, 'data' => $allProjects]);
            } catch (Exception $e) {
                error_log("Projects getAll error: " . $e->getMessage());
                jsonResponse(['success' => false, 'message' => 'Error loading projects'], 500);
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name'])) {
            jsonResponse(['success' => false, 'message' => 'Project name is required'], 400);
        }
        
        try {
            $newProjectId = $projects->create($data, $currentUser['id']);
            
            if ($newProjectId) {
                $project = $projects->get($newProjectId, $currentUser['id']);
                jsonResponse(['success' => true, 'message' => 'Project created', 'data' => $project], 201);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to create project'], 500);
            }
        } catch (Exception $e) {
            error_log("Projects create error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Error creating project: ' . $e->getMessage()], 500);
        }
        break;
        
    case 'PUT':
        if (!$projectId) {
            jsonResponse(['success' => false, 'message' => 'Project ID required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $projects->update($projectId, $data, $currentUser['id']);
        
        if ($result) {
            $project = $projects->get($projectId, $currentUser['id']);
            jsonResponse(['success' => true, 'message' => 'Project updated', 'data' => $project]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Update failed or permission denied'], 403);
        }
        break;
        
    case 'DELETE':
        if (!$projectId) {
            jsonResponse(['success' => false, 'message' => 'Project ID required'], 400);
        }
        
        $result = $projects->delete($projectId, $currentUser['id']);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Project deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Delete failed or permission denied'], 403);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
