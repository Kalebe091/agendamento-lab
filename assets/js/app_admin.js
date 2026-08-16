// A página de admin agora usa seções fixas (Pendentes e Aprovados).
// O filtro via JS foi removido.

// Ações de modais conectadas ao backend via AJAX
window.openApproveModal = function(id) {
    try {
        if (typeof showConfirm !== 'function') {
            alert("ERRO GRAVE: showConfirm não existe!");
            return;
        }
        showConfirm('Tem certeza que deseja APROVAR a solicitação #' + id + '?', () => {
            updateScheduleStatus(id, 'approved');
        });
    } catch(e) {
        alert('JS Error in openApproveModal: ' + e.message);
    }
};

window.openRejectModal = function(id) {
    try {
        showConfirm('Tem certeza que deseja RECUSAR a solicitação #' + id + '?', () => {
            updateScheduleStatus(id, 'rejected');
        });
    } catch(e) {
        alert('JS Error in openRejectModal: ' + e.message);
    }
};

function updateScheduleStatus(id, status) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);

    fetch('../controllers/update_schedule_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Em vez de recarregar a página toda, podemos apenas recarregar a janela
            // ou atualizar o DOM, mas o mais seguro aqui é dar reload para garantir os dados
            window.location.reload();
        } else {
            showToast('Erro: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Ocorreu um erro ao atualizar a solicitação.', 'error');
    });
}
