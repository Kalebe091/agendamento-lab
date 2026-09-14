// global.js - Utilitários globais do sistema

// 1. Injetar o container de Toasts
if (!document.getElementById('toast-container')) {
    const toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    document.body.appendChild(toastContainer);
}

// 2. Injetar o Modal de Confirmação Global
if (!document.getElementById('custom-confirm-modal')) {
    const confirmHTML = `
        <div id="custom-confirm-modal" class="modal-overlay">
            <div class="modal-content confirm-modal-content" style="max-width: 420px; padding: 2.5rem 2rem; text-align: center; border-radius: 20px;">
                <div style="width: 64px; height: 64px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem auto;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 id="confirm-title" style="margin-bottom: 0.75rem; color: var(--text-main); font-size: 1.4rem; font-weight: 700;">Confirmar Ação</h3>
                <p id="confirm-message" style="color: var(--text-muted); margin-bottom: 2rem; font-size: 1rem; line-height: 1.6;">Você tem certeza?</p>
                <div class="modal-actions" style="display: flex; gap: 1rem; justify-content: center;">
                    <button id="confirm-cancel-btn" class="btn btn-secondary" style="flex: 1; padding: 0.75rem; font-weight: 600;">Cancelar</button>
                    <button id="confirm-ok-btn" class="btn btn-primary" style="flex: 1; padding: 0.75rem; font-weight: 600;">Confirmar</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', confirmHTML);
}

// 3. Injetar o Modal de Detalhes (Eventos do Calendário)
if (!document.getElementById('details-modal')) {
    const detailsHTML = `
        <div id="details-modal" class="modal-overlay">
            <div class="modal-content" style="max-width: 500px; border-radius: 20px; overflow: hidden;">
                <div class="modal-header" style="background-color: var(--surface-hover); padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color);">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-calendar-day" style="color: var(--primary-color);"></i> Detalhes do Agendamento</h2>
                    <button class="close-modal" id="close-details-btn" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body" id="details-modal-body" style="padding: 2rem; color: var(--text-secondary); line-height: 1.6;">
                    <!-- Preenchido via JS -->
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', detailsHTML);
    
    document.getElementById('details-modal').addEventListener('click', (e) => {
        if (e.target.id === 'details-modal') {
            document.getElementById('details-modal').classList.remove('active');
        }
    });
    document.getElementById('close-details-btn').addEventListener('click', () => {
        document.getElementById('details-modal').classList.remove('active');
    });
}

// 4. Injetar o Modal de Edição Global
if (!document.getElementById('edit-modal')) {
    const labsOptions = window.DB_LABS ? window.DB_LABS.map(l => `<option value="${l.id}">${l.name}</option>`).join('') : '';
    const editHTML = `
        <div id="edit-modal" class="modal-overlay">
            <div class="modal-content" style="max-width: 500px; border-radius: 20px; overflow: hidden;">
                <div class="modal-header" style="background-color: var(--surface-hover); padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color);">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-pen-to-square" style="color: var(--primary-color);"></i> Editar Agendamento</h2>
                    <button class="close-modal" id="close-edit-btn" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body" style="padding: 2rem;">
                    <form id="edit-form" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <input type="hidden" id="edit-id" name="id">
                    <input type="hidden" name="action" value="edit">
                    
                    <div class="form-group">
                        <label>Laboratório</label>
                        <select id="edit-lab" name="lab_id" class="form-control" required>
                            ${labsOptions}
                        </select>
                    </div>
                    <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>Início</label>
                            <input type="datetime-local" id="edit-start" name="start_time" class="form-control" required>
                        </div>
                        <div>
                            <label>Término</label>
                            <input type="datetime-local" id="edit-end" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary" id="save-edit-btn" style="padding: 0.75rem 1.5rem; font-weight: 600;"><i class="fa-solid fa-save"></i> Salvar Alterações</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', editHTML);
    
    document.getElementById('close-edit-btn').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('edit-modal').classList.remove('active');
    });

    document.getElementById('edit-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const btn = document.getElementById('save-edit-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Salvando...';
        btn.disabled = true;

        const formData = new FormData(e.target);
        
        fetch(window.location.pathname.includes('views/') ? '../controllers/edit_schedule.php' : 'controllers/edit_schedule.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Agendamento atualizado!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast('Erro: ' + data.message, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(() => {
            showToast('Erro na requisição', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

/**
 * Exibe um Toast flutuante na tela
 * @param {string} message A mensagem a ser exibida
 * @param {string} type 'success', 'error', ou 'info'
 */
window.showToast = function(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let icon = 'fa-circle-info';
    if (type === 'success') icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-circle-exclamation';

    toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
    
    container.appendChild(toast);

    // Entrada animada
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Saída animada
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300); // Tempo do fade out
    }, 4000);
};

/**
 * Exibe um modal de confirmação customizado (substitui o window.confirm)
 * @param {string} message A mensagem da pergunta
 * @param {Function} onConfirm Callback executado se o usuário clicar em Confirmar
 */
window.showConfirm = function(message, onConfirm) {
    const modal = document.getElementById('custom-confirm-modal');
    if (!modal) return;

    document.getElementById('confirm-message').textContent = message;
    
    const okBtn = document.getElementById('confirm-ok-btn');
    const cancelBtn = document.getElementById('confirm-cancel-btn');

    // Limpar eventos antigos clonando o botão
    const newOkBtn = okBtn.cloneNode(true);
    const newCancelBtn = cancelBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

    newOkBtn.addEventListener('click', () => {
        modal.classList.remove('active');
        if (onConfirm) onConfirm();
    });

    newCancelBtn.addEventListener('click', () => {
        modal.classList.remove('active');
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    modal.classList.add('active');
};

/**
 * Exibe os detalhes de um evento no Modal Customizado
 */
window.showEventDetails = function(eventObj, arg2, arg3, arg4, arg5) {
    const modal = document.getElementById('details-modal');
    if (!modal) return;
    
    // Backwards compatibility caso o app.js antigo esteja em cache no navegador do usuário
    if (typeof eventObj === 'string') {
        eventObj = {
            title: eventObj,
            prof: arg2 || '',
            startTimeStr: arg3 || '',
            endTimeStr: arg4 || '',
            labName: arg5 || '',
            id: null,
            rawStart: '',
            rawEnd: '',
            labId: ''
        };
    }
    
    const body = document.getElementById('details-modal-body');

    const isAdmin = eventObj.isAdmin || window.CURRENT_USER_ROLE === 'admin';
    let actionsHTML = '';
    if (eventObj.id && isAdmin) {
        const safeTitle = (eventObj.title || '').replace(/'/g, "\\'");
        actionsHTML = `
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="openEditEventModal(${eventObj.id}, '${eventObj.rawStart || ''}', '${eventObj.rawEnd || ''}', '${eventObj.labId || ''}', '${safeTitle}')" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                <button class="btn btn-reject" onclick="deleteEvent(${eventObj.id})" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-trash"></i> Excluir</button>
            </div>
        `;
    }

    body.innerHTML = `
        <div style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <strong><i class="fa-solid fa-book"></i> Disciplina:</strong> <span style="color: var(--text-main);">${eventObj.title}</span>
        </div>
        <div style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <strong><i class="fa-solid fa-user-tie"></i> Solicitante:</strong> <span style="color: var(--text-main);">${eventObj.prof}</span>
        </div>
        <div style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
            <strong><i class="fa-regular fa-clock"></i> Horário:</strong> <span style="color: var(--text-main);">${eventObj.startTimeStr} às ${eventObj.endTimeStr}</span>
        </div>
        <div style="margin-bottom: ${actionsHTML ? '0' : '0.5rem'};">
            <strong><i class="fa-solid fa-desktop"></i> Laboratório:</strong> <span style="color: var(--text-main);">${eventObj.labName}</span>
        </div>
        ${actionsHTML}
    `;
    
    modal.classList.add('active');
};

// Funções globais de ação do Admin (Edit/Delete)
window.deleteEvent = function(id) {
    try {
        showConfirm('Tem certeza que deseja excluir permanentemente este agendamento?', () => {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', 'delete');
            
            fetch(window.location.pathname.includes('views/') ? '../controllers/edit_schedule.php' : 'controllers/edit_schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Agendamento excluído!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Erro: ' + data.message, 'error');
                }
            })
            .catch(e => alert('Fetch Error: ' + e));
        });
    } catch(e) {
        alert('JS Error in deleteEvent: ' + e.message);
    }
};

window.openEditEventModal = function(id, rawStart, rawEnd, labId, title) {
    try {
        const detailsModal = document.getElementById('details-modal');
        if (detailsModal) {
            detailsModal.classList.remove('active');
        }
        
        const editModal = document.getElementById('edit-modal');
        if (!editModal) {
            alert('Erro: edit-modal não encontrado no DOM!');
            return;
        }
        
        document.getElementById('edit-id').value = id;
        
        // Converter de YYYY-MM-DD HH:mm:ss para formato datetime-local (YYYY-MM-DDTHH:mm)
        const startDateForInput = rawStart ? rawStart.replace(' ', 'T').substring(0, 16) : '';
        const endDateForInput = rawEnd ? rawEnd.replace(' ', 'T').substring(0, 16) : '';
        
        document.getElementById('edit-start').value = startDateForInput;
        document.getElementById('edit-end').value = endDateForInput;
        
        // Popula select se existir
        const labSelect = document.getElementById('edit-lab');
        if(labSelect) labSelect.value = labId;
        
        editModal.classList.add('active');
    } catch(e) {
        alert('JS Error in openEditEventModal: ' + e.message);
    }
};
