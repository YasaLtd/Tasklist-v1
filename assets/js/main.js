/**
 * Yasa LTD Task List - Main JavaScript
 * 
 * @package YasaTaskList
 * @version 1.0.0
 */

(function() {
    'use strict';

    // ============================================
    // DOM Ready
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        initializeApp();
    });

    // ============================================
    // Initialize App
    // ============================================
    function initializeApp() {
        // Header functionality
        initUserMenu();
        initMobileMenu();
        
        // Back to top button
        initBackToTop();
        
        // Modals
        initModals();
        
        // Projects page
        initProjectsPage();
        
        // Tasks page
        initTasksPage();
        
        // Filters
        initFilters();
        
        // Task checkboxes
        initTaskCheckboxes();
    }

    // ============================================
    // User Menu
    // ============================================
    function initUserMenu() {
        const userMenu = document.getElementById('user-menu');
        const trigger = document.getElementById('user-menu-trigger');
        
        if (!userMenu || !trigger) return;
        
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('open');
        });
        
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('open');
            }
        });
    }

    // ============================================
    // Mobile Menu
    // ============================================
    function initMobileMenu() {
        const toggle = document.getElementById('mobile-menu-toggle');
        const nav = document.getElementById('mobile-nav');
        
        if (!toggle || !nav) return;
        
        toggle.addEventListener('click', function() {
            toggle.classList.toggle('active');
            nav.classList.toggle('open');
            document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
        });
    }

    // ============================================
    // Back to Top Button
    // ============================================
    function initBackToTop() {
        const btn = document.getElementById('back-to-top');
        if (!btn) return;
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                btn.classList.add('show');
            } else {
                btn.classList.remove('show');
            }
        });
        
        btn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ============================================
    // Toast Notifications
    // ============================================
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: 'fa-check',
            error: 'fa-times',
            warning: 'fa-exclamation'
        };
        
        toast.innerHTML = `
            <span class="toast-icon"><i class="fas ${icons[type] || 'fa-info'}"></i></span>
            <span class="toast-message">${message}</span>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;
        
        container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    };

    // ============================================
    // Loading Overlay
    // ============================================
    window.showLoading = function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.add('show');
    };

    window.hideLoading = function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.remove('show');
    };

    // ============================================
    // Modals
    // ============================================
    function initModals() {
        // Project Modal
        initProjectModal();
        
        // Task Modal
        initTaskModal();
        
        // Confirm Modal
        initConfirmModal();
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
    }

    // ============================================
    // Project Modal
    // ============================================
    function initProjectModal() {
        const modal = document.getElementById('project-modal');
        if (!modal) return;
        
        const form = document.getElementById('project-form');
        const closeBtn = document.getElementById('project-modal-close');
        const cancelBtn = document.getElementById('project-cancel-btn');
        
        // Create project buttons
        const createBtns = document.querySelectorAll('#create-project-btn, #create-first-project-btn');
        createBtns.forEach(btn => {
            btn.addEventListener('click', () => openProjectModal());
        });
        
        // Edit project buttons
        document.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.edit-project-btn');
            if (editBtn) {
                const projectId = editBtn.dataset.projectId;
                openProjectModal(projectId);
            }
        });
        
        // Delete project buttons
        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-project-btn');
            if (deleteBtn) {
                const projectId = deleteBtn.dataset.projectId;
                openConfirmModal('Are you sure you want to delete this project? All tasks will be deleted.', () => {
                    deleteProject(projectId);
                });
            }
        });
        
        // Close handlers
        closeBtn?.addEventListener('click', () => closeModal('project-modal'));
        cancelBtn?.addEventListener('click', () => closeModal('project-modal'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal('project-modal');
        });
        
        // Form submission
        form?.addEventListener('submit', async function(e) {
            e.preventDefault();
            await saveProject(new FormData(form));
        });
    }

    function openProjectModal(projectId = null) {
        const modal = document.getElementById('project-modal');
        const title = document.getElementById('project-modal-title');
        const submitBtn = document.getElementById('project-submit-btn');
        const form = document.getElementById('project-form');
        
        form.reset();
        document.getElementById('project-id').value = '';
        document.getElementById('project-color').value = '#37505d';
        
        if (projectId) {
            title.textContent = 'Edit Project';
            submitBtn.querySelector('.btn-text').textContent = 'Save Changes';
            loadProjectData(projectId);
        } else {
            title.textContent = 'New Project';
            submitBtn.querySelector('.btn-text').textContent = 'Create Project';
        }
        
        openModal('project-modal');
    }

    async function loadProjectData(projectId) {
        try {
            const response = await fetch(`${SITE_URL}/api/projects/${projectId}`);
            const result = await response.json();
            
            if (result.success) {
                const project = result.data;
                document.getElementById('project-id').value = project.id;
                document.getElementById('project-name').value = project.name;
                document.getElementById('project-description').value = project.description || '';
                document.getElementById('project-status').value = project.status;
                document.getElementById('project-priority').value = project.priority;
                document.getElementById('project-deadline').value = project.deadline ? project.deadline.split(' ')[0] : '';
                document.getElementById('project-color').value = project.color || '#37505d';
            }
        } catch (error) {
            console.error('Error loading project:', error);
            showToast('Error loading project data', 'error');
        }
    }

    async function saveProject(formData) {
        const submitBtn = document.getElementById('project-submit-btn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        
        try {
            const projectId = formData.get('id');
            const data = {
                name: formData.get('name'),
                description: formData.get('description'),
                status: formData.get('status'),
                priority: formData.get('priority'),
                deadline: formData.get('deadline') || null,
                color: formData.get('color')
            };
            
            const url = projectId 
                ? `${SITE_URL}/api/projects/${projectId}`
                : `${SITE_URL}/api/projects`;
            
            const response = await fetch(url, {
                method: projectId ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
                closeModal('project-modal');
                setTimeout(() => location.reload(), 500);
            } else {
                showToast(result.message || 'Error saving project', 'error');
            }
        } catch (error) {
            console.error('Error saving project:', error);
            showToast('Error saving project', 'error');
        } finally {
            submitBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
        }
    }

    async function deleteProject(projectId) {
        showLoading();
        
        try {
            const response = await fetch(`${SITE_URL}/api/projects/${projectId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Project deleted', 'success');
                setTimeout(() => {
                    window.location.href = `${SITE_URL}/projects`;
                }, 500);
            } else {
                showToast(result.message || 'Error deleting project', 'error');
            }
        } catch (error) {
            console.error('Error deleting project:', error);
            showToast('Error deleting project', 'error');
        } finally {
            hideLoading();
        }
    }

    // ============================================
    // Task Modal
    // ============================================
    function initTaskModal() {
        const modal = document.getElementById('task-modal');
        if (!modal) return;
        
        const form = document.getElementById('task-form');
        const closeBtn = document.getElementById('task-modal-close');
        const cancelBtn = document.getElementById('task-cancel-btn');
        
        // Add task buttons
        const addTaskBtns = document.querySelectorAll('#add-task-btn, #add-first-task-btn');
        addTaskBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const projectId = btn.dataset.projectId;
                openTaskModal(null, projectId);
            });
        });
        
        // Edit task buttons
        document.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.edit-task-btn');
            if (editBtn) {
                const taskId = editBtn.dataset.taskId;
                const projectId = editBtn.dataset.projectId;
                openTaskModal(taskId, projectId);
            }
        });
        
        // Delete task buttons
        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-task-btn');
            if (deleteBtn) {
                const taskId = deleteBtn.dataset.taskId;
                openConfirmModal('Are you sure you want to delete this task?', () => {
                    deleteTask(taskId);
                });
            }
        });
        
        // Close handlers
        closeBtn?.addEventListener('click', () => closeModal('task-modal'));
        cancelBtn?.addEventListener('click', () => closeModal('task-modal'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal('task-modal');
        });
        
        // Form submission
        form?.addEventListener('submit', async function(e) {
            e.preventDefault();
            await saveTask(new FormData(form));
        });
    }

    function openTaskModal(taskId = null, projectId = null) {
        const modal = document.getElementById('task-modal');
        const title = document.getElementById('task-modal-title');
        const submitBtn = document.getElementById('task-submit-btn');
        const form = document.getElementById('task-form');
        
        form.reset();
        document.getElementById('task-id').value = '';
        document.getElementById('task-project-id').value = projectId || '';
        
        if (taskId) {
            title.textContent = 'Edit Task';
            submitBtn.querySelector('.btn-text').textContent = 'Save Changes';
            loadTaskData(taskId);
        } else {
            title.textContent = 'New Task';
            submitBtn.querySelector('.btn-text').textContent = 'Create Task';
        }
        
        openModal('task-modal');
    }

    async function loadTaskData(taskId) {
        try {
            const response = await fetch(`${SITE_URL}/api/tasks/${taskId}`);
            const result = await response.json();
            
            if (result.success) {
                const task = result.data;
                document.getElementById('task-id').value = task.id;
                document.getElementById('task-project-id').value = task.project_id;
                document.getElementById('task-title').value = task.title;
                document.getElementById('task-description').value = task.description || '';
                document.getElementById('task-status').value = task.status;
                document.getElementById('task-priority').value = task.priority;
                document.getElementById('task-assigned').value = task.assigned_to || '';
                document.getElementById('task-deadline').value = task.deadline ? task.deadline.split(' ')[0] : '';
            }
        } catch (error) {
            console.error('Error loading task:', error);
            showToast('Error loading task data', 'error');
        }
    }

    async function saveTask(formData) {
        const submitBtn = document.getElementById('task-submit-btn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        
        try {
            const taskId = formData.get('id');
            const data = {
                project_id: formData.get('project_id'),
                title: formData.get('title'),
                description: formData.get('description'),
                status: formData.get('status'),
                priority: formData.get('priority'),
                assigned_to: formData.get('assigned_to') || null,
                deadline: formData.get('deadline') || null
            };
            
            const url = taskId 
                ? `${SITE_URL}/api/tasks/${taskId}`
                : `${SITE_URL}/api/tasks`;
            
            const response = await fetch(url, {
                method: taskId ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
                closeModal('task-modal');
                setTimeout(() => location.reload(), 500);
            } else {
                showToast(result.message || 'Error saving task', 'error');
            }
        } catch (error) {
            console.error('Error saving task:', error);
            showToast('Error saving task', 'error');
        } finally {
            submitBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
        }
    }

    async function deleteTask(taskId) {
        showLoading();
        
        try {
            const response = await fetch(`${SITE_URL}/api/tasks/${taskId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Task deleted', 'success');
                
                // Remove task card from DOM
                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (taskCard) {
                    taskCard.style.opacity = '0';
                    taskCard.style.transform = 'translateX(-100%)';
                    setTimeout(() => taskCard.remove(), 300);
                } else {
                    location.reload();
                }
            } else {
                showToast(result.message || 'Error deleting task', 'error');
            }
        } catch (error) {
            console.error('Error deleting task:', error);
            showToast('Error deleting task', 'error');
        } finally {
            hideLoading();
        }
    }

    // ============================================
    // Confirm Modal
    // ============================================
    let confirmCallback = null;

    function initConfirmModal() {
        const modal = document.getElementById('confirm-modal');
        if (!modal) return;
        
        const closeBtn = document.getElementById('confirm-modal-close');
        const cancelBtn = document.getElementById('confirm-cancel-btn');
        const deleteBtn = document.getElementById('confirm-delete-btn');
        
        closeBtn?.addEventListener('click', () => closeModal('confirm-modal'));
        cancelBtn?.addEventListener('click', () => closeModal('confirm-modal'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal('confirm-modal');
        });
        
        deleteBtn?.addEventListener('click', () => {
            closeModal('confirm-modal');
            if (confirmCallback) {
                confirmCallback();
                confirmCallback = null;
            }
        });
    }

    function openConfirmModal(message, callback) {
        const messageEl = document.getElementById('confirm-message');
        if (messageEl) {
            messageEl.textContent = message;
        }
        confirmCallback = callback;
        openModal('confirm-modal');
    }

    // ============================================
    // Projects Page
    // ============================================
    function initProjectsPage() {
        // Project card menu
        document.querySelectorAll('.project-menu-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const menu = this.closest('.project-card-menu');
                document.querySelectorAll('.project-card-menu').forEach(m => {
                    if (m !== menu) m.classList.remove('open');
                });
                menu.classList.toggle('open');
            });
        });
        
        document.addEventListener('click', function() {
            document.querySelectorAll('.project-card-menu').forEach(m => {
                m.classList.remove('open');
            });
        });
    }

    // ============================================
    // Tasks Page
    // ============================================
    function initTasksPage() {
        // Task checkboxes already handled separately
    }

    // ============================================
    // Task Checkboxes
    // ============================================
    function initTaskCheckboxes() {
        document.addEventListener('change', async function(e) {
            if (!e.target.classList.contains('task-complete-checkbox')) return;
            if (!IS_ADMIN) return;
            
            const checkbox = e.target;
            const taskId = checkbox.dataset.taskId;
            const isCompleted = checkbox.checked;
            
            try {
                const response = await fetch(`${SITE_URL}/api/tasks/${taskId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        status: isCompleted ? 'completed' : 'pending'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const taskCard = checkbox.closest('[data-task-id]');
                    const taskTitle = taskCard.querySelector('.task-title');
                    
                    if (isCompleted) {
                        taskCard.classList.add('completed');
                        taskTitle?.classList.add('completed');
                    } else {
                        taskCard.classList.remove('completed');
                        taskTitle?.classList.remove('completed');
                    }
                    
                    showToast(isCompleted ? 'Task completed!' : 'Task reopened', 'success');
                } else {
                    checkbox.checked = !isCompleted;
                    showToast(result.message || 'Error updating task', 'error');
                }
            } catch (error) {
                console.error('Error updating task:', error);
                checkbox.checked = !isCompleted;
                showToast('Error updating task', 'error');
            }
        });
    }

    // ============================================
    // Filters
    // ============================================
    function initFilters() {
        // Project filters
        const projectStatusFilter = document.getElementById('project-status-filter');
        const projectPriorityFilter = document.getElementById('project-priority-filter');
        const projectSearch = document.getElementById('project-search');
        
        if (projectStatusFilter || projectPriorityFilter || projectSearch) {
            [projectStatusFilter, projectPriorityFilter].forEach(filter => {
                filter?.addEventListener('change', filterProjects);
            });
            
            projectSearch?.addEventListener('input', debounce(filterProjects, 300));
        }
        
        // Task filters
        const taskProjectFilter = document.getElementById('task-project-filter');
        const taskStatusFilter = document.getElementById('task-status-filter');
        const taskPriorityFilter = document.getElementById('task-priority-filter');
        const taskSearch = document.getElementById('task-search');
        
        if (taskProjectFilter || taskStatusFilter || taskPriorityFilter || taskSearch) {
            [taskProjectFilter, taskStatusFilter, taskPriorityFilter].forEach(filter => {
                filter?.addEventListener('change', filterTasks);
            });
            
            taskSearch?.addEventListener('input', debounce(filterTasks, 300));
        }
    }

    function filterProjects() {
        const status = document.getElementById('project-status-filter')?.value || '';
        const priority = document.getElementById('project-priority-filter')?.value || '';
        const search = document.getElementById('project-search')?.value.toLowerCase() || '';
        
        document.querySelectorAll('.project-card').forEach(card => {
            const cardStatus = card.dataset.status;
            const cardPriority = card.dataset.priority;
            const cardName = card.dataset.name;
            
            const matchStatus = !status || cardStatus === status;
            const matchPriority = !priority || cardPriority === priority;
            const matchSearch = !search || cardName.includes(search);
            
            card.style.display = (matchStatus && matchPriority && matchSearch) ? '' : 'none';
        });
    }

    function filterTasks() {
        const projectId = document.getElementById('task-project-filter')?.value || '';
        const status = document.getElementById('task-status-filter')?.value || '';
        const priority = document.getElementById('task-priority-filter')?.value || '';
        const search = document.getElementById('task-search')?.value.toLowerCase() || '';
        
        const selector = '.task-card, .task-card-full';
        document.querySelectorAll(selector).forEach(card => {
            const cardProjectId = card.dataset.projectId;
            const cardStatus = card.dataset.status;
            const cardPriority = card.dataset.priority;
            const cardTitle = card.dataset.title || '';
            
            const matchProject = !projectId || cardProjectId === projectId;
            const matchStatus = !status || cardStatus === status;
            const matchPriority = !priority || cardPriority === priority;
            const matchSearch = !search || cardTitle.includes(search);
            
            card.style.display = (matchProject && matchStatus && matchPriority && matchSearch) ? '' : 'none';
        });
    }

    // ============================================
    // Utility Functions
    // ============================================
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.open').forEach(modal => {
                modal.classList.remove('open');
            });
            document.body.style.overflow = '';
        }
    });

})();