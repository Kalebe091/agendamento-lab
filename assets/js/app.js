document.addEventListener('DOMContentLoaded', () => {
    
    // Configurações
    const startHour = 17; // 17:00 (Ocultar amanhã e tarde)
    const endHour = 22; // 22:00
    const slotHeight = 70; // 70px por hora (combinando com CSS)
    const daysOfWeek = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    
    // Começa na semana atual baseada na data de hoje
    let currentWeekStart = new Date();
    // Ajustar para a Segunda-feira da semana atual
    const day = currentWeekStart.getDay();
    const diff = currentWeekStart.getDate() - day + (day === 0 ? -6 : 1);
    currentWeekStart = new Date(currentWeekStart.setDate(diff));
    currentWeekStart.setHours(0, 0, 0, 0);
    
    // Processa os agendamentos reais vindos do PHP
    let allEvents = [];
    if (window.DB_EVENTS && window.DB_EVENTS.length > 0) {
        allEvents = window.DB_EVENTS.map(dbEvent => {
            const startStr = dbEvent.start_time.replace(' ', 'T');
            const endStr = dbEvent.end_time.replace(' ', 'T');
            
            const startDate = new Date(startStr);
            const endDate = new Date(endStr);
            
            return {
                id: dbEvent.id,
                title: dbEvent.subject,
                prof: dbEvent.requester_name,
                lab: 'lab' + dbEvent.lab_id,
                labId: dbEvent.lab_id,
                labName: dbEvent.lab_name,
                startDate: startDate,
                endDate: endDate,
                rawStart: dbEvent.start_time,
                rawEnd: dbEvent.end_time
            };
        });
    }

    const daysContainer = document.getElementById('days-container');
    const labFilter = document.getElementById('lab-filter');

    // 2. Gerar colunas de dias e slots
    function renderDaysAndEvents(filterLab = 'all') {
        daysContainer.innerHTML = '';
        
        // Determina o fim da semana visualizada
        const weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(weekEnd.getDate() + 5); // Segunda a Sábado (5 dias)
        
        // Atualiza a UI com as datas
        const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        const dateStr = `${currentWeekStart.getDate()} - ${weekEnd.getDate()} de ${months[currentWeekStart.getMonth()]}, ${currentWeekStart.getFullYear()}`;
        document.getElementById('current-date-display').textContent = dateStr;
        document.getElementById('week-display').textContent = `Semana Atual (${currentWeekStart.getDate()} ${months[currentWeekStart.getMonth()].substring(0,3)} - ${weekEnd.getDate()} ${months[weekEnd.getMonth()].substring(0,3)})`;
        
        daysOfWeek.forEach((dayName, index) => {
            const dayDate = new Date(currentWeekStart);
            dayDate.setDate(currentWeekStart.getDate() + index);
            const dateNum = dayDate.getDate();
            
            const today = new Date();
            const isToday = dayDate.toDateString() === today.toDateString();

            const dayCol = document.createElement('div');
            dayCol.className = 'day-col';

            const dayHeader = document.createElement('div');
            dayHeader.className = `day-header ${isToday ? 'today' : ''}`;
            dayHeader.innerHTML = `
                <span class="day-name">${dayName}</span>
                <span class="day-date">${dateNum.toString().padStart(2, '0')}</span>
            `;
            dayCol.appendChild(dayHeader);

            const daySlots = document.createElement('div');
            daySlots.className = 'day-slots';
            
            // Filtra os eventos globais para os que ocorrem neste dia específico
            const dayEvents = allEvents.filter(e => {
                return e.startDate.toDateString() === dayDate.toDateString() &&
                       (filterLab === 'all' || e.lab === filterLab);
            });

            // 1. Sort events by start time
            dayEvents.sort((a, b) => a.startDate.getTime() - b.startDate.getTime());

            dayEvents.forEach(event => {
                const eventEl = document.createElement('div');
                eventEl.className = `event ${event.lab}`;
                
                const startTimeStr = `${event.startDate.getHours().toString().padStart(2, '0')}:${event.startDate.getMinutes().toString().padStart(2, '0')}`;
                const endTimeStr = `${event.endDate.getHours().toString().padStart(2, '0')}:${event.endDate.getMinutes().toString().padStart(2, '0')}`;

                eventEl.innerHTML = `
                    <div class="event-title" title="${event.title}">${event.title}</div>
                    <div class="event-time"><i class="fa-regular fa-clock"></i> ${startTimeStr} - ${endTimeStr} | ${event.labName}</div>
                    <div class="event-prof"><i class="fa-solid fa-user-tie"></i> ${event.prof}</div>
                `;
                
                eventEl.addEventListener('click', () => {
                    const eventObj = {
                        id: event.id,
                        title: event.title,
                        prof: event.prof,
                        startTimeStr: startTimeStr,
                        endTimeStr: endTimeStr,
                        labName: event.labName,
                        labId: event.labId,
                        rawStart: event.rawStart,
                        rawEnd: event.rawEnd
                    };
                    showEventDetails(eventObj);
                });

                daySlots.appendChild(eventEl);
            });

            dayCol.appendChild(daySlots);
            daysContainer.appendChild(dayCol);
        });
    }

    // Event listener para o filtro
    labFilter.addEventListener('change', (e) => {
        renderDaysAndEvents(e.target.value);
    });

    // Event listeners para navegação de semanas
    document.getElementById('prev-week-btn').addEventListener('click', () => {
        currentWeekStart.setDate(currentWeekStart.getDate() - 7);
        renderDaysAndEvents(labFilter.value);
    });

    document.getElementById('next-week-btn').addEventListener('click', () => {
        currentWeekStart.setDate(currentWeekStart.getDate() + 7);
        renderDaysAndEvents(labFilter.value);
    });


    renderDaysAndEvents();
    

    // Modal de Solicitação de Agendamento
    const btnRequestSchedule = document.getElementById('btn-request-schedule');
    const requestModal = document.getElementById('request-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal, .cancel-modal');
    const scheduleForm = document.getElementById('schedule-request-form');

    const reqLabSelect = document.getElementById('req-lab');
    const labResponsibleInfo = document.getElementById('lab-responsible-info');
    const labResponsibleName = document.getElementById('lab-responsible-name');

    // Mapeamento dinâmico do responsável baseado no tipo do laboratório
    reqLabSelect?.addEventListener('change', (e) => {
        const selectedLabId = e.target.value;
        const labs = window.DB_LABS || [];
        const labInfo = labs.find(l => l.id == selectedLabId);
        
        if (labInfo) {
            let respText = '';
            if (labInfo.type === 'saude') {
                respText = 'Técnica Saúde';
            } else if (labInfo.type === 'informatica') {
                respText = 'TI Responsável / Admin';
            } else if (labInfo.type === 'engenharia') {
                respText = 'Técnico Engenharia';
            } else {
                respText = 'Não Atribuído';
            }
            
            labResponsibleName.textContent = respText;
            labResponsibleInfo.style.display = 'block';
        } else {
            labResponsibleInfo.style.display = 'none';
        }
    });

    if (btnRequestSchedule && requestModal) {
        btnRequestSchedule.addEventListener('click', () => {
            requestModal.classList.add('active');
        });


        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                requestModal.classList.remove('active');
                scheduleForm.reset();
                if (labResponsibleInfo) labResponsibleInfo.style.display = 'none';
            });
        });

        // Fechar clicando fora do modal
        requestModal.addEventListener('click', (e) => {
            if (e.target === requestModal) {
                requestModal.classList.remove('active');
                scheduleForm.reset();
                if (labResponsibleInfo) labResponsibleInfo.style.display = 'none';
            }
        });

        // Enviar formulário
        scheduleForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('requester_name', document.getElementById('req-name').value);
            formData.append('lab_id', document.getElementById('req-lab').value);
            formData.append('date', document.getElementById('req-date').value);
            formData.append('time_start', document.getElementById('req-time-start').value);
            formData.append('time_end', document.getElementById('req-time-end').value);
            formData.append('subject', document.getElementById('req-subject').value);
            formData.append('notes', document.getElementById('req-notes').value);
            
            const submitBtn = scheduleForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';
            submitBtn.disabled = true;

            fetch('controllers/schedule_controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    requestModal.classList.remove('active');
                    scheduleForm.reset();
                    if (labResponsibleInfo) labResponsibleInfo.style.display = 'none';
                } else {
                    showToast('Erro: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Ocorreu um erro ao enviar a solicitação.', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
