# Sistema de Agendamento de Laboratórios

Este é um sistema web completo desenvolvido para o gerenciamento, solicitação e agendamento de laboratórios em instituições de ensino. O sistema conta com controle de acesso por níveis de privilégio (Administradores e Técnicos), dashboard interativo, visualização de calendário dinâmico e design moderno.

## 🚀 Funcionalidades Principais

*   **Autenticação e Controle de Acesso:** Login seguro com níveis de acesso diferenciados (Administrador Geral, Técnico de Saúde, Técnico de TI/Engenharia).
*   **Visão Geral (Dashboard):** Gráficos interativos (Chart.js) que mostram o status em tempo real das solicitações e a ocupação por laboratórios.
*   **Gestão de Solicitações:** Administradores e técnicos responsáveis podem Aprovar, Recusar, Editar e Excluir solicitações de reserva de laboratórios, tudo via AJAX sem recarregar a página.
*   **Calendário Interativo:** Visualização semanal e mensal de agendamentos utilizando o FullCalendar, com separação de cores baseada no status e no tipo do laboratório.
*   **Gerenciamento de Laboratórios:** Tela dedicada para o Administrador adicionar novos laboratórios, definindo capacidade e setor (Informática, Saúde, Engenharia, etc.).
*   **Gerenciamento de Técnicos:** Criação e configuração de perfil para o corpo técnico, permitindo designar logins e senhas com segurança.
*   **Interface Premium (UI/UX):** Design responsivo, com modais overlay animadas, alertas (toasts) e tipografia moderna.

## 🛠️ Tecnologias Utilizadas

*   **Frontend:** HTML5, CSS3 Nativo (Variáveis CSS, Flexbox/Grid), Vanilla JavaScript.
*   **Bibliotecas JS:** 
    *   [FullCalendar](https://fullcalendar.io/) (Visualização do calendário).
    *   [Chart.js](https://www.chartjs.org/) (Gráficos do dashboard).
    *   [FontAwesome](https://fontawesome.com/) (Ícones).
*   **Backend:** PHP 8+ com PDO.
*   **Banco de Dados:** MySQL.

## ⚙️ Como Executar o Projeto (Localmente com XAMPP)

1.  **Clone ou Baixe o Repositório**
    Coloque a pasta do projeto (`agendamento-lab`) dentro do diretório `htdocs` do seu XAMPP.
    Ex: `C:\xampp\htdocs\agendamento-lab`

2.  **Inicie os Serviços**
    Abra o painel de controle do XAMPP e inicie o **Apache** e o **MySQL**.

3.  **Configuração do Banco de Dados**
    *   Acesse o PHPMyAdmin pelo navegador: `http://localhost/phpmyadmin`
    *   Crie um banco de dados vazio chamado `agendamento_lab`.
    *   Importe o arquivo **`database.sql`** (encontrado na raiz do projeto) para dentro desse banco recém-criado. Isso criará toda a estrutura de tabelas e preencherá os dados iniciais.

4.  **Acessando o Sistema**
    *   Abra o seu navegador e acesse: `http://localhost/agendamento-lab`
    *   O sistema te redirecionará para a tela de login.

## 🔐 Credenciais Padrão (Ambiente de Teste)

Ao importar o banco de dados fornecido (`database.sql`), as seguintes credenciais estarão disponíveis para você testar as funcionalidades:

*   **Administrador Geral:**
    *   **Usuário:** `admin`
    *   **Senha:** `123`

*(Nota: Dentro do sistema, o Administrador pode criar novos técnicos e definir as senhas de forma segura, com criptografia hash no banco de dados).*

## 📁 Estrutura de Diretórios

```
agendamento-lab/
│
├── assets/
│   ├── css/          # Arquivos de estilização geral (style.css, login.css)
│   ├── js/           # Scripts Globais e de páginas específicas (app_global.js, etc.)
│   └── img/          # Imagens estáticas
│
├── config/
│   └── database.php  # Classe PDO para conexão com o banco de dados
│
├── controllers/      # Arquivos PHP que processam as requisições (AJAX/Forms)
│   ├── auth_controller.php
│   ├── create_user.php
│   ├── lab_controller.php
│   └── schedule_controller.php
│
├── includes/         # Componentes repetitivos (Header, Sidebar, Footer, Auth_functions)
│
├── views/            # As telas visuais do sistema (Dashboard, Admin, Calendário, etc)
│
├── index.php         # Roteador/Home padrão
├── database.sql      # Dump do banco de dados completo (estrutura + inserts)
└── README.md         # Documentação
```

---
*Desenvolvido como projeto de gestão de laboratórios institucionais.*
