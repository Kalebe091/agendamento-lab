# 🔬 LabSchedule (AgendaEventos v2)

O **LabSchedule** é um sistema web moderno, responsivo e intuitivo para gestão e agendamento de laboratórios institucionais/acadêmicos. O sistema atende tanto ao público geral (professores/alunos) para solicitação e consulta de reservas, quanto aos administradores e equipe técnica para controle de solicitações, gestão de laboratórios e geração de relatórios.

---

## 🚀 Principais Funcionalidades

### 🌐 Área Pública (Sem necessidade de login)
- **Agenda Semanal Interativa**: Consulta visual da disponibilidade dos laboratórios por semana.
- **Filtro por Laboratório**: Filtragem rápida da agenda por laboratório específico.
- **Solicitação de Agendamento Online**: Formulário modal para solicitação de reserva informando o solicitante, laboratório, data, horário, disciplina e observações.
- **Validação Automática de Conflitos**: Bloqueio automático de solicitações para horários que já possuem agendamentos aprovados.

### 🔐 Área Restrita (Técnicos e Administradores)
- **Dashboard Analítico**:
  - Indicadores em tempo real: Total de agendamentos, laboratórios cadastrados, técnicos ativos e total de horas alocadas.
  - Solicitações pendentes recentes para rápida aprovação/rejeição.
  - Gráficos de distribuição por status e uso de laboratórios.
- **Gestão de Solicitações (`Admin`)**:
  - Aprovação e rejeição em tempo real com notificações flutuantes (Toasts).
  - Filtro de solicitações com base na área de atuação do técnico logado.
  - Edição de horários e exclusão de reservas.
- **Agenda Mensal em Grade**:
  - Visão global dos eventos aprovados do mês em grade responsiva de 7 dias (Domingo a Sábado).
  - Destaque automático do dia atual ("Hoje").
  - Mapeamento dinâmico de cores por laboratório.
  - Clique em qualquer agendamento para abrir detalhes (e opções de Edição/Exclusão para Administradores).
- **Gestão de Laboratórios**:
  - Cadastro, edição e exclusão de laboratórios (com capacidade e categoria: Saúde, Engenharia, Informática/TI e Geral).
- **Gestão de Técnicos (Exclusivo Admin)**:
  - Cadastro de novos técnicos atribuindo seus respetivos cargos e setores.
- **Exportação de Relatórios para Excel/CSV (Exclusivo Admin)**:
  - Exportação de dados formatados em UTF-8 com BOM e separador de ponto e vírgula (`;`), 100% compatível com Microsoft Excel.
  - Filtros customizáveis por **Status**, **Laboratório** e **Intervalo de Datas**.

---

## 🛠️ Arquitetura & Tecnologias

- **Backend**: PHP Nativo (com instrução PDO e prepared statements).
- **Banco de Dados**: MySQL / MariaDB (charset `utf8mb4`).
- **Frontend**: HTML5, CSS3 Nativo (Variáveis CSS, Flexbox, CSS Grid) e JavaScript Vanilla.
- **Bibliotecas & Recursos Visuais**: FontAwesome 6, Google Fonts (*Inter*).
- **Autenticação**: Sessões PHP (`PHP_SESSION`) com hash seguro de senhas (`password_hash` / `password_verify`).

---

## 📂 Estrutura de Arquivos e Pastas

```text
AgendaEventos v2/
├── assets/
│   ├── css/
│   │   └── style.css            # Estilos globais, temas, layout e componentes
│   └── js/
│       ├── admin.js             # Scripts específicos do painel administrativo
│       ├── app.js               # Lógica da agenda semanal pública
│       ├── app_admin.js         # Lógica adicional da área restrita
│       ├── app_global.js        # Modais de confirmação, toasts e detalhes do evento
│       └── global.js            # Utilitários gerais
├── config/
│   └── database.php             # Classe de conexão PDO com o MySQL
├── controllers/
│   ├── auth_controller.php      # Processamento de Login e Logout
│   ├── create_user.php          # Cadastro de novos técnicos/usuários
│   ├── edit_schedule.php        # Edição e exclusão de agendamentos
│   ├── export_schedules.php     # Gerador de relatórios em formato CSV/Excel
│   ├── lab_controller.php       # CRUD de laboratórios
│   ├── schedule_controller.php   # Cadastro de solicitações públicas e validação de choque
│   └── update_schedule_status.php # Aprovação e rejeição de solicitações
├── includes/
│   ├── auth_functions.php       # Funções de verificação de sessão e permissões
│   ├── footer.php               # Rodapé padrão da área restrita
│   ├── header.php               # Cabeçalho padrão da área restrita
│   └── sidebar.php              # Menu lateral estático com contador de pendências
├── views/
│   ├── admin.php                # Tela de gerenciamento e aprovação de solicitações
│   ├── agenda-mensal.php        # Visualização da agenda mensal em grade
│   ├── configuracoes.php        # Parâmetros do sistema e exportação Excel
│   ├── dashboard.php            # Painel com gráficos e relatórios
│   ├── laboratorios.php         # Cadastro e edição de laboratórios
│   ├── login.php                # Tela de login da área restrita
│   └── tecnicos.php             # Gestão da equipe técnica
├── index.php                    # Página inicial pública (Agenda Semanal)
└── README.md                    # Documentação do projeto
```

---

## 🔑 Perfis de Acesso (`roles`)

| Perfil | Permissões |
| :--- | :--- |
| **`admin`** | Acesso total: gerencia usuários, laboratórios, aprova/rejeita/edita/exclui agendamentos e exporta relatórios. |
| **`tech_saude`** | Acesso restrito a agendamentos e estatísticas dos laboratórios da área de **Saúde**. |
| **`tech_eng`** | Acesso restrito a agendamentos e estatísticas dos laboratórios da área de **Engenharia**. |
| **`tech_info`** | Acesso restrito a agendamentos e estatísticas dos laboratórios de **Informática / TI**. |

---

## 🗄️ Estrutura do Banco de Dados

### Tabela `users`
- `id` (INT, PK, AUTO_INCREMENT)
- `username` (VARCHAR 50, UNIQUE)
- `password_hash` (VARCHAR 255)
- `role` (ENUM: `admin`, `tech_saude`, `tech_eng`, `tech_info`)
- `name` (VARCHAR 100)
- `title` (VARCHAR 100)

### Tabela `laboratories`
- `id` (INT, PK, AUTO_INCREMENT)
- `name` (VARCHAR 100)
- `capacity` (INT)
- `type` (ENUM: `saude`, `engenharia`, `informatica`, `geral`)

### Tabela `schedules`
- `id` (INT, PK, AUTO_INCREMENT)
- `requester_name` (VARCHAR 100)
- `lab_id` (INT, FK -> `laboratories.id`)
- `start_time` (DATETIME)
- `end_time` (DATETIME)
- `subject` (VARCHAR 150)
- `notes` (TEXT)
- `status` (ENUM: `pending`, `approved`, `rejected`)
- `created_at` (TIMESTAMP)

---

## 💻 Como Rodar o Projeto Localmente

### Pré-requisitos
- PHP 7.4 ou superior (Recomendado **PHP 8.x**).
- Banco de dados MySQL / MariaDB (ex: XAMPP, WampServer ou Laragon).

### Passo a Passo

1. **Configurar o Banco de Dados**:
   - Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`) ou o seu cliente MySQL.
   - Crie um banco de dados chamado `agenda_lab` (ou o nome de sua preferência).
   - Crie as tabelas `users`, `laboratories` e `schedules` conforme a estrutura acima.

2. **Ajustar as credenciais em [`config/database.php`](file:///c:/Users/TI/Documents/Antigravity/AgendaEventos%20v2/config/database.php)**:
   ```php
   private $host = "localhost";
   private $db_name = "agenda_lab";
   private $username = "root";
   private $password = "";
   ```

3. **Iniciar o Servidor Local**:
   - **Opção A (Servidor Embutido do PHP)**:
     Navegue até a pasta do projeto e execute no terminal:
     ```powershell
     C:\xampp\php\php.exe -S localhost:8000
     ```
     Acesse no navegador: `http://localhost:8000`

   - **Opção B (Apache do XAMPP)**:
     Copie a pasta `AgendaEventos v2` para `C:\xampp\htdocs\` e acesse: `http://localhost/AgendaEventos v2`

---

## 📊 Exportação de Dados
Na tela de **Configurações** (`views/configuracoes.php`), administradores podem gerar arquivos de relatórios em `.csv` selecionando:
- **Status desejado**: Todos, Aprovados, Pendentes ou Rejeitados.
- **Laboratório específico**: Ou todos os laboratórios.
- **Intervalo de datas**: Filtro por período inicial e final.

---

Desenvolvido para gestão eficiente de laboratórios. 🚀
