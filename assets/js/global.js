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
        <div id="custom-confirm-modal" class="modal">
            <div class="modal-content confirm-modal-content" style="max-width: 400px; text-align: center;">
                <h3 id="confirm-title" style="margin-bottom: 1rem; color: var(--text-primary);">Confirmar Ação</h3>
                <p id="confirm-message" style="color: var(--text-secondary); margin-bottom: 1.5rem;">Você tem certeza?</p>
                <div class="modal-actions" style="display: flex; gap: 1rem; justify-content: center;">
                    <button id="confirm-cancel-btn" class="btn btn-secondary">Cancelar</button>
                    <button id="confirm-ok-btn" class="btn btn-primary">Confirmar</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', confirmHTML);
}

// 3. Injetar o Modal de Detalhes (Eventos do Calendário)
if (!document.getElementById('details-modal')) {
    const detailsHTML = `
        <div id="details-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Detalhes do Agendamento</h2>
                    <button class="close-modal" id="close-details-btn"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body" id="details-modal-body" style="color: var(--text-secondary); line-height: 1.6;">
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
        <div id="edit-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Editar Agendamento</h2>
                    <button class="close-modal" id="close-edit-btn"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="edit-form" style="display: flex; flex-direction: column; gap: 1rem;">
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
                        <button type="submit" class="btn btn-primary" id="save-edit-btn">Salvar Alterações</button>
                    </div>
                </form>
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

    body.innerHTML = `
        <div style="margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
            <strong><i class="fa-solid fa-book"></i> Disciplina:</strong> <span style="color: var(--text-primary);">${eventObj.title}</span>
        </div>
        <div style="margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
            <strong><i class="fa-solid fa-user-tie"></i> Solicitante:</strong> <span style="color: var(--text-primary);">${eventObj.prof}</span>
        </div>
        <div style="margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
            <strong><i class="fa-regular fa-clock"></i> Horário:</strong> <span style="color: var(--text-primary);">${eventObj.startTimeStr} às ${eventObj.endTimeStr}</span>
        </div>
        <div>
            <strong><i class="fa-solid fa-desktop"></i> Laboratório:</strong> <span style="color: var(--text-primary);">${eventObj.labName}</span>
        </div>
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
