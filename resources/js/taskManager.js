/**
 * Task Manager - Kanban tipo Jira con drag and drop.
 * Consumo de API REST con fetch; arrastrar tarjetas entre columnas actualiza el estado.
 */

const API_BASE = typeof window !== 'undefined' && window.API_BASE ? window.API_BASE : '';

const STATUSES = ['pendiente', 'en_progreso', 'completada'];

function getTasksUrl() {
  return `${API_BASE}/tasks`;
}

function getTaskUrl(id) {
  return `${API_BASE}/tasks/${id}`;
}

async function request(url, options = {}) {
  const config = {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...options.headers,
    },
    ...options,
  };
  if (config.body && typeof config.body === 'object' && !(config.body instanceof FormData)) {
    config.body = JSON.stringify(config.body);
  }
  try {
    const response = await fetch(url, config);
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      const error = new Error(data.message || 'Error en la petición');
      error.status = response.status;
      error.errors = data.errors || {};
      throw error;
    }
    return data;
  } catch (err) {
    if (err.status !== undefined) throw err;
    const networkError = new Error('Error de conexión. Compruebe la red e inténtelo de nuevo.');
    networkError.status = 0;
    networkError.errors = {};
    throw networkError;
  }
}

const api = {
  list() {
    return request(getTasksUrl());
  },
  create(payload) {
    return request(getTasksUrl(), { method: 'POST', body: payload });
  },
  get(id) {
    return request(getTaskUrl(id));
  },
  update(id, payload) {
    return request(getTaskUrl(id), { method: 'PUT', body: payload });
  },
  delete(id) {
    return request(getTaskUrl(id), { method: 'DELETE' });
  },
};

// --- Estado y DOM ---

let state = {
  tasks: [],
  loading: false,
  error: null,
  editingId: null,
  draggedTaskId: null,
};

const el = {
  form: null,
  taskId: null,
  title: null,
  description: null,
  status: null,
  submitBtn: null,
  cancelEditBtn: null,
  message: null,
  loading: null,
  empty: null,
  kanbanBoard: null,
};

function getEl(id) {
  return document.getElementById(id);
}

function showMessage(text, type = 'success') {
  const msg = el.message;
  if (!msg) return;
  msg.textContent = text;
  msg.className = `task-manager__message task-manager__message--${type}`;
  msg.classList.remove('task-manager__message--hidden');
  msg.setAttribute('aria-live', 'polite');
  setTimeout(() => {
    msg.classList.add('task-manager__message--hidden');
  }, 4000);
}

function setLoading(loading) {
  state.loading = loading;
  if (el.loading) el.loading.style.display = loading ? 'block' : 'none';
  if (el.kanbanBoard) el.kanbanBoard.style.display = loading ? 'none' : 'flex';
  if (el.empty) el.empty.style.display = 'none';
  if (el.submitBtn) el.submitBtn.disabled = loading;
}

function setError(err) {
  state.error = err;
  if (err) {
    const msg = err.errors && Object.keys(err.errors).length
      ? Object.values(err.errors).flat().join(' ')
      : (err.message || 'Error de conexión');
    showMessage(msg, 'error');
  }
}

function escapeHtml(str) {
  if (str == null) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function createTaskCard(task) {
  const card = document.createElement('div');
  card.className = 'kanban__card';
  card.draggable = true;
  card.dataset.taskId = String(task.id);
  card.dataset.status = task.status || 'pendiente';
  card.setAttribute('role', 'button');
  card.setAttribute('tabindex', '0');
  card.setAttribute('aria-label', `Tarea: ${escapeHtml(task.title)}`);

  const desc = task.description ? `<p class="kanban__card-description">${escapeHtml(task.description)}</p>` : '';
  card.innerHTML = `
    <div class="kanban__card-body">
      <h3 class="kanban__card-title">${escapeHtml(task.title)}</h3>
      ${desc}
    </div>
    <div class="kanban__card-actions">
      <button type="button" class="kanban__card-btn kanban__card-btn--edit" data-id="${task.id}" aria-label="Editar">Editar</button>
      <button type="button" class="kanban__card-btn kanban__card-btn--delete" data-id="${task.id}" aria-label="Eliminar">Eliminar</button>
    </div>
  `;

  const editBtn = card.querySelector('.kanban__card-btn--edit');
  const deleteBtn = card.querySelector('.kanban__card-btn--delete');

  editBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    startEdit(task);
  });
  deleteBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    confirmDelete(task);
  });

  card.addEventListener('dragstart', handleCardDragStart);
  card.addEventListener('dragend', handleCardDragEnd);
  card.addEventListener('click', (e) => {
    if (e.target.closest('.kanban__card-actions')) return;
    startEdit(task);
  });

  return card;
}

function handleCardDragStart(e) {
  const card = e.target.closest('.kanban__card');
  if (!card) return;
  state.draggedTaskId = card.dataset.taskId;
  card.classList.add('kanban__card--dragging');
  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/plain', card.dataset.taskId);
  e.dataTransfer.setData('application/json', JSON.stringify({ taskId: card.dataset.taskId }));
}

function handleCardDragEnd(e) {
  const card = e.target.closest('.kanban__card');
  if (card) card.classList.remove('kanban__card--dragging');
  state.draggedTaskId = null;
  document.querySelectorAll('.kanban__column').forEach((col) => col.classList.remove('kanban__column--drag-over'));
}

function setupColumnDropZone(columnEl) {
  const cardsEl = columnEl.querySelector('.kanban__cards');
  if (!cardsEl) return;

  columnEl.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    columnEl.classList.add('kanban__column--drag-over');
  });

  columnEl.addEventListener('dragleave', (e) => {
    if (!columnEl.contains(e.relatedTarget)) {
      columnEl.classList.remove('kanban__column--drag-over');
    }
  });

  columnEl.addEventListener('drop', (e) => {
    e.preventDefault();
    columnEl.classList.remove('kanban__column--drag-over');
    const taskId = e.dataTransfer.getData('text/plain') || state.draggedTaskId;
    const newStatus = columnEl.dataset.status;
    if (!taskId || !newStatus) return;
    const task = state.tasks.find((t) => String(t.id) === String(taskId));
    if (!task || task.status === newStatus) return;
    updateTaskStatus(Number(taskId), newStatus);
  });
}

function updateTaskStatus(taskId, newStatus) {
  const task = state.tasks.find((t) => t.id === taskId);
  if (!task) return;
  const previousStatus = task.status;
  task.status = newStatus;
  renderTasks();

  api.update(taskId, { title: task.title, description: task.description, status: newStatus })
    .then(() => {
      showMessage('Estado actualizado.');
    })
    .catch((err) => {
      task.status = previousStatus;
      renderTasks();
      setError(err);
    });
}

function renderTasks() {
  if (!el.kanbanBoard) return;

  const byStatus = { pendiente: [], en_progreso: [], completada: [] };
  state.tasks.forEach((task) => {
    const status = task.status || 'pendiente';
    if (byStatus[status]) byStatus[status].push(task);
  });

  STATUSES.forEach((status) => {
    const container = getEl(`cards-${status}`);
    const countEl = getEl(`count-${status}`);
    if (!container) return;
    container.innerHTML = '';
    const tasks = byStatus[status] || [];
    if (countEl) countEl.textContent = String(tasks.length);
    tasks.forEach((task) => {
      container.appendChild(createTaskCard(task));
    });
  });

  el.kanbanBoard.style.display = 'flex';
  el.empty.style.display = !state.tasks.length && !state.loading ? 'block' : 'none';
  if (el.empty && el.empty.style.display === 'block') el.kanbanBoard.style.display = 'none';
}

function resetForm() {
  if (el.taskId) el.taskId.value = '';
  if (el.title) el.title.value = '';
  if (el.description) el.description.value = '';
  if (el.status) el.status.value = 'pendiente';
  state.editingId = null;
  if (el.submitBtn) el.submitBtn.textContent = 'Crear tarea';
  if (el.cancelEditBtn) el.cancelEditBtn.style.display = 'none';
}

function startEdit(task) {
  state.editingId = task.id;
  if (el.taskId) el.taskId.value = task.id;
  if (el.title) el.title.value = task.title;
  if (el.description) el.description.value = task.description || '';
  if (el.status) el.status.value = task.status || 'pendiente';
  if (el.submitBtn) el.submitBtn.textContent = 'Guardar cambios';
  if (el.cancelEditBtn) el.cancelEditBtn.style.display = 'inline-block';
  el.title.focus();
}

function confirmDelete(task) {
  if (!window.confirm(`¿Eliminar la tarea "${task.title}"?`)) return;
  deleteTask(task.id);
}

async function loadTasks() {
  setLoading(true);
  setError(null);
  try {
    const res = await api.list();
    state.tasks = res.data || [];
    renderTasks();
  } catch (err) {
    setError(err);
    state.tasks = [];
    renderTasks();
  } finally {
    setLoading(false);
  }
}

async function submitForm(e) {
  e.preventDefault();
  const title = (el.title && el.title.value || '').trim();
  if (!title) {
    showMessage('El título es obligatorio.', 'error');
    return;
  }

  const payload = {
    title,
    description: (el.description && el.description.value || '').trim() || null,
    status: (el.status && el.status.value) || 'pendiente',
  };

  setLoading(true);
  setError(null);
  try {
    if (state.editingId) {
      await api.update(state.editingId, payload);
      showMessage('Tarea actualizada.');
    } else {
      await api.create(payload);
      showMessage('Tarea creada.');
    }
    resetForm();
    await loadTasks();
  } catch (err) {
    setError(err);
  } finally {
    setLoading(false);
  }
}

async function deleteTask(id) {
  setError(null);
  try {
    await api.delete(id);
    showMessage('Tarea eliminada.');
    await loadTasks();
    if (state.editingId === id) resetForm();
  } catch (err) {
    setError(err);
  }
}

function bindElements() {
  el.form = getEl('task-form');
  el.taskId = getEl('task-id');
  el.title = getEl('task-title');
  el.description = getEl('task-description');
  el.status = getEl('task-status');
  el.submitBtn = getEl('submit-btn');
  el.cancelEditBtn = getEl('cancel-edit-btn');
  el.message = getEl('message');
  el.loading = getEl('loading');
  el.empty = getEl('empty');
  el.kanbanBoard = getEl('kanban-board');
}

function init() {
  try {
    bindElements();
  if (el.form) el.form.addEventListener('submit', submitForm);
  if (el.cancelEditBtn) el.cancelEditBtn.addEventListener('click', resetForm);

  document.querySelectorAll('.kanban__column').forEach(setupColumnDropZone);

  loadTasks();
  } catch (err) {
    console.error('TaskManager init error:', err);
    const msg = getEl('message');
    if (msg) {
      msg.textContent = 'Error al cargar la aplicación. Recargue la página.';
      msg.className = 'task-manager__message task-manager__message--error';
      msg.classList.remove('task-manager__message--hidden');
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
