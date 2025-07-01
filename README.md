# Sistema de Gestão de Chamados

O Sistema de Gestão de Chamados é uma aplicação web completa para gerenciamento de chamados técnicos, com foco em usabilidade, organização e eficiência. Permite cadastrar, acompanhar e gerenciar chamados de forma intuitiva, com recursos avançados de filtragem, relatórios e histórico completo.

## 🚀 Funcionalidades

- **Dashboard intuitivo** com estatísticas e visão geral dos chamados
- **Gestão completa de chamados** com status, prioridades e categorias
- **Sistema de comentários** para acompanhamento e comunicação
- **Histórico detalhado** de todas as alterações em chamados
- **Transferência entre setores** com rastreabilidade
- **Relatórios e exportação** para análise de dados
- **Impressão de chamados** em formato adequado para documentação
- **Gestão de usuários e permissões** por setor
- **Gestão de empresas e licenças** para ambiente multi-tenant
- **Interface responsiva e moderna**
- **Sistema de notificações** para acompanhamento em tempo real
- **Perfil de usuário** com histórico de atividades
- **Verificação de sessão** para maior segurança

## 📂 Estrutura do Projeto

    sistema-gestao-chamados/
    │
    ├── .github/
    │   └── workflows/
    │       └── deploy.yml
    │
    ├── app/
    │   ├── controllers/
    │   │   ├── AuthController.php
    │   │   ├── ChamadosController.php
    │   │   ├── Controller.php
    │   │   ├── DashboardController.php
    │   │   ├── EmpresasController.php
    │   │   ├── HomeController.php
    │   │   ├── LicencasController.php
    │   │   ├── NotificacoesController.php
    │   │   ├── PerfilController.php
    │   │   ├── RelatoriosController.php
    │   │   ├── SetoresController.php
    │   │   └── UsuariosController.php
    │   │
    │   ├── models/
    │   │   ├── Chamado.php
    │   │   ├── ChamadoComentario.php
    │   │   ├── ChamadoHistorico.php
    │   │   ├── DashboardModel.php
    │   │   ├── EmailService.php
    │   │   ├── Empresa.php
    │   │   ├── HistoricoChamado.php
    │   │   ├── Licenca.php
    │   │   ├── Log.php
    │   │   ├── Model.php
    │   │   ├── Notificacao.php
    │   │   ├── Relatorio.php
    │   │   ├── Setor.php
    │   │   ├── StatusChamado.php
    │   │   └── Usuario.php
    │   │
    │   └── views/
    │       ├── auth/
    │       │   ├── confirmar_sessao.php
    │       │   ├── login.php
    │       │   ├── recuperar-senha.php
    │       │   ├── redefinir-senha.php
    │       │   └── solicitar-recuperacao.php
    │       ├── chamados/
    │       │   ├── form.php
    │       │   ├── imprimir.php
    │       │   ├── index.php
    │       │   ├── listar.php
    │       │   ├── relatorio.php
    │       │   └── visualizar.php
    │       ├── dashboard/
    │       │   └── index.php
    │       ├── empresas/
    │       │   ├── confirmar_exclusao.php
    │       │   ├── form.php
    │       │   └── index.php
    │       ├── licencas/
    │       │   ├── form.php
    │       │   └── index.php
    │       ├── notificacoes/
    │       │   └── index.php
    │       ├── perfil/
    │       │   ├── atividade.php
    │       │   ├── editar.php
    │       │   └── index.php
    │       ├── setores/
    │       │   ├── admin/
    │       │   │   ├── form.php
    │       │   │   ├── index.php
    │       │   │   └── usuarios.php
    │       │   ├── detalhes.php
    │       │   ├── form.php
    │       │   ├── index.php
    │       │   ├── usuarios.php
    │       │   └── visualizacao.php
    │       ├── templates/
    │       │   ├── footer.php
    │       │   ├── header.php
    │       │   └── sidebar.php
    │       └── usuarios/
    │           ├── confirmar-restauracao.php
    │           ├── form.php
    │           └── index.php
    │
    ├── config/
    │   ├── app.php
    │   ├── database.php
    │   └── middlewares/
    │       └── AuthMiddleware.php
    │
    ├── public/
    │   ├── css/
    │   │   ├── auth.css
    │   │   ├── base.css
    │   │   ├── chamado-visualizar.css
    │   │   ├── chamados-form.css
    │   │   ├── chamados-imprimir.css
    │   │   ├── chamados-index.css
    │   │   ├── chamados-listar.css
    │   │   ├── chamados-relatorio.css
    │   │   ├── components.css
    │   │   ├── dashboard.css
    │   │   ├── header.css
    │   │   ├── layout.css
    │   │   ├── licencas.css
    │   │   ├── main.css
    │   │   ├── relatorios.css
    │   │   ├── responsive.css
    │   │   ├── setores-admin.css
    │   │   ├── setores-detalhes.css
    │   │   ├── setores-form-admin.css
    │   │   ├── setores-form.css
    │   │   ├── setores-usuarios.css
    │   │   ├── setores-visualizacao.css
    │   │   ├── setores.css
    │   │   ├── usuario-form.css
    │   │   ├── usuario-restore.css
    │   │   ├── usuarios-setor.css
    │   │   ├── usuarios.css
    │   │   └── utilities.css
    │   ├── img/
    │   │   ├── favicon.ico
    │   │   └── logo.png
    │   └── js/
    │       ├── auth.js
    │       ├── chamado-visualizar.js
    │       ├── chamados-form.js
    │       ├── chamados-imprimir.js
    │       ├── chamados-index.js
    │       ├── chamados-listar.js
    │       ├── chamados-relatorio.js
    │       ├── chamados.js
    │       ├── charts.js
    │       ├── dashboard.js
    │       ├── header.js
    │       ├── licencas.js
    │       ├── main.js
    │       ├── relatorios.js
    │       ├── session-check.js
    │       ├── setores-admin.js
    │       ├── setores-detalhes.js
    │       ├── setores-form-admin.js
    │       ├── setores-form.js
    │       ├── setores-usuarios.js
    │       ├── setores-visualizacao.js
    │       ├── setores.js
    │       ├── usuario-form.js
    │       ├── usuarios-setor.js
    │       └── usuarios.js
    │
    ├── scripts/
    │   ├── database/
    │   │   └── schema.sql
    │   └── setup-database.php
    │
    ├── vendor/
    │
    ├── .gitignore
    ├── .htaccess
    ├── add_admin.php
    ├── composer.json
    ├── composer.lock
    ├── debug_auth.php
    ├── index.php
    └── README.md

## 🛠️ Tecnologias Utilizadas

- **PHP 7+** com arquitetura MVC
- **MySQL/MariaDB** para banco de dados
- **HTML5, CSS3, JavaScript**
- **Bootstrap 5** para interface responsiva
- **Font Awesome** para ícones
- **jQuery** para interações dinâmicas
- **AJAX** para requisições assíncronas
- **PDO** para conexão segura com banco de dados
- **Composer** para gerenciamento de dependências
- **GitHub Actions** para CI/CD

## 📝 Arquivos CSS

O sistema utiliza uma estrutura modular de CSS para melhor organização e manutenção:

- **auth.css**: Estilos específicos para telas de autenticação
- **base.css**: Estilos base e reset para garantir consistência entre navegadores
- **chamado-visualizar.css**: Estilos para a visualização detalhada de chamados
- **chamados-form.css**: Estilos para o formulário de criação/edição de chamados
- **chamados-imprimir.css**: Estilos otimizados para impressão de chamados
- **chamados-index.css**: Estilos para a página principal de chamados
- **chamados-listar.css**: Estilos para a listagem de chamados
- **chamados-relatorio.css**: Estilos para relatórios de chamados
- **components.css**: Componentes reutilizáveis como botões, cards, alertas
- **dashboard.css**: Estilos para o painel de controle e estatísticas
- **header.css**: Estilos para o cabeçalho da aplicação
- **layout.css**: Estrutura de layout, grid e posicionamento de elementos
- **licencas.css**: Estilos para o módulo de licenças
- **main.css**: Arquivo principal que importa os demais módulos CSS
- **relatorios.css**: Estilos para o módulo de relatórios
- **responsive.css**: Ajustes responsivos para diferentes tamanhos de tela
- **setores-\*.css**: Conjunto de estilos para o módulo de setores
- **usuario-\*.css**: Conjunto de estilos para o módulo de usuários
- **utilities.css**: Classes utilitárias para espaçamento, cores, tipografia

## 📜 Arquivos JavaScript

O sistema utiliza uma estrutura modular de JavaScript para melhor organização e manutenção:

- **auth.js**: Funcionalidades de autenticação e validação de login
- **chamado-visualizar.js**: Interações na visualização detalhada de chamados
- **chamados-form.js**: Validações e interações do formulário de chamados
- **chamados-imprimir.js**: Funcionalidades para impressão de chamados
- **chamados-index.js**: Funcionalidades da página principal de chamados
- **chamados-listar.js**: Filtros e ordenação na listagem de chamados
- **chamados-relatorio.js**: Geração e exportação de relatórios
- **chamados.js**: Funcionalidades gerais do módulo de chamados
- **charts.js**: Geração de gráficos e visualizações de dados
- **dashboard.js**: Interações e atualizações do painel de controle
- **header.js**: Funcionalidades do cabeçalho e navegação
- **licencas.js**: Gerenciamento de licenças
- **main.js**: Funcionalidades globais e inicialização
- **relatorios.js**: Geração e exportação de relatórios avançados
- **session-check.js**: Verificação de sessão e timeout
- **setores-\*.js**: Conjunto de scripts para o módulo de setores
- **usuario-\*.js**: Conjunto de scripts para o módulo de usuários

## 🔒 Middleware de Autenticação

O sistema implementa um middleware de autenticação (`AuthMiddleware.php`) para proteger rotas e garantir que apenas usuários autorizados acessem determinadas funcionalidades:

- **Verificação de sessão**: Valida se o usuário está autenticado
- **Controle de acesso**: Verifica permissões específicas por rota
- **Redirecionamento**: Encaminha para login quando necessário
- **Timeout de sessão**: Gerencia expiração de sessões inativas
- **Confirmação de sessão**: Verifica a autenticidade da sessão do usuário

## 📱 Novos Módulos

### Sistema de Notificações

O sistema agora conta com um módulo completo de notificações:

- **Notificações em tempo real**: Alertas sobre novos chamados, comentários e transferências
- **Centro de notificações**: Interface centralizada para visualização
- **Marcação de leitura**: Controle de notificações lidas/não lidas
- **Filtros**: Organização por tipo, data e origem
- **Preferências**: Configuração de tipos de notificações recebidas

### Perfil de Usuário

Novo módulo para gerenciamento de perfil pessoal:

- **Visualização de dados**: Informações do usuário logado
- **Edição de perfil**: Atualização de dados pessoais e preferências
- **Histórico de atividades**: Registro de ações realizadas no sistema
- **Alteração de senha**: Funcionalidade segura para troca de senha
- **Preferências de notificação**: Configuração de alertas recebidos

### Verificação de Sessão

Implementação de segurança adicional:

- **Verificação periódica**: Checagem automática de validade da sessão
- **Renovação automática**: Extensão de sessão durante uso ativo
- **Alerta de expiração**: Notificação antes do timeout da sessão
- **Confirmação de identidade**: Verificação adicional para operações sensíveis

## ⚙️ Instalação

1. **Clone o projeto:**
   git clone https://github.com/lucasandrefernando/sistema-gestao-chamados.git

2. **Instale as dependências:**
   composer install

3. **Configure o banco de dados:**

- Edite `config/database.php` com suas credenciais
- Execute o script de criação do banco:

php scripts/setup-database.php

- Ou importe manualmente o arquivo `scripts/database/schema.sql`

4. **Configure o servidor web:**

- Aponte o DocumentRoot para a pasta `public/`
- Certifique-se de que o mod_rewrite está habilitado (para .htaccess)

5. **Adicione um usuário administrador (opcional):**
   php add_admin.php

6. **Acesse no navegador:**

- Exemplo: `http://localhost/sistema-gestao-chamados/public/`


## 🖥️ Módulos do Sistema

### Gestão de Chamados

- **Dashboard**: Visão geral com estatísticas e gráficos
- **Listagem**: Filtros avançados, busca e ordenação
- **Visualização**: Detalhes completos, histórico e comentários
- **Criação/Edição**: Formulário intuitivo com campos contextuais
- **Transferência**: Movimentação entre setores com rastreabilidade
- **Alteração de Status**: Fluxo de trabalho configurável
- **Comentários**: Sistema de comunicação integrado
- **Relatórios**: Análise de desempenho e tempo de atendimento
- **Exportação**: Dados em formato CSV para análise externa
- **Impressão**: Formato adequado para documentação física

### Gestão de Usuários

- **Cadastro**: Criação e edição de usuários
- **Permissões**: Controle de acesso por função e setor
- **Ativação/Desativação**: Gerenciamento de contas ativas
- **Recuperação de Senha**: Processo seguro de redefinição
- **Restauração**: Recuperação de contas desativadas

### Gestão de Setores

- **Cadastro**: Criação e configuração de setores
- **Usuários**: Associação de usuários a setores
- **Visualização**: Detalhes e estatísticas por setor
- **Administração**: Configurações avançadas por setor
- **Relatórios**: Análise de desempenho por setor

### Gestão de Empresas

- **Cadastro**: Criação e configuração de empresas
- **Licenças**: Controle de licenças e períodos de uso
- **Configurações**: Personalização por empresa

### Notificações

- **Centro de Notificações**: Interface centralizada
- **Configurações**: Personalização de alertas
- **Marcação de Leitura**: Controle de notificações lidas/não lidas

### Perfil de Usuário

- **Visualização**: Dados do usuário logado
- **Edição**: Atualização de informações pessoais
- **Atividades**: Histórico de ações no sistema
- **Preferências**: Configurações personalizadas

## 📊 Relatórios e Análises

O sistema oferece diversos relatórios e visualizações para análise de dados:

- **Chamados por Status**: Distribuição visual dos chamados por situação
- **Chamados por Setor**: Análise de volume por departamento
- **Tempo Médio de Atendimento**: Métricas de eficiência por setor e tipo
- **Chamados por Período**: Análise temporal de volume de solicitações
- **Desempenho de Usuários**: Métricas individuais de atendimento
- **Relatórios Personalizados**: Geração de análises específicas
- **Exportação CSV**: Dados completos para análise em ferramentas externas
- **Gráficos Interativos**: Visualizações dinâmicas para melhor compreensão

## ✨ Personalização

- **Adicionar campos**: Edite o model, controller e formulários correspondentes
- **Novo status de chamado**: Adicione no banco de dados e atualize as views
- **Personalizar relatórios**: Modifique as consultas e visualizações em RelatoriosController
- **Alterar layout**: Edite os templates em `views/templates/`
- **Adicionar módulos**: Siga o padrão MVC para implementar novas funcionalidades
- **Configurar notificações**: Personalize tipos e frequência em NotificacoesController

## 🔒 Segurança

O sistema implementa diversas medidas de segurança:

- **Autenticação segura**: Senhas armazenadas com hash
- **Controle de sessão**: Timeout e validação de sessões
- **Validação de entradas**: Prevenção contra injeção SQL e XSS
- **Controle de acesso**: Verificação de permissões por módulo e ação
- **Logs de atividades**: Registro de ações importantes no sistema
- **Verificação periódica**: Checagem automática de validade da sessão
- **Confirmação de identidade**: Verificação adicional para operações sensíveis
- **Recuperação segura de senha**: Processo protegido com tokens únicos

## 🚀 CI/CD

O projeto utiliza GitHub Actions para automação de deploy:

- **Integração contínua**: Verificação automática de código
- **Deploy automático**: Atualização do ambiente de produção após commits na branch principal
- **Notificações**: Alertas sobre status do deploy
- **Rollback**: Possibilidade de reverter alterações problemáticas

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature:

git checkout -b minha-feature

3. Commit suas alterações:

git commit -m 'feat: Minha nova funcionalidade'

4. Push para a branch:

git push origin minha-feature

5. Abra um Pull Request

## 🧑‍💻 Guia para Desenvolvedores

### 1. Entenda a Estrutura

- **controllers/**: Lógica de negócio e fluxo da aplicação
- **models/**: Acesso ao banco de dados e regras de negócio
- **views/**: Templates e componentes visuais
- **config/**: Configurações da aplicação e banco de dados
- **config/middlewares/**: Middlewares para controle de acesso e processamento de requisições
- **public/**: Arquivos acessíveis diretamente (CSS, JS, imagens)
- **scripts/**: Utilitários para configuração e manutenção
- **vendor/**: Dependências gerenciadas pelo Composer

### 2. Fluxo de Trabalho

- **Adicionar funcionalidade**: Implemente no controller e model correspondentes
- **Criar nova visualização**: Adicione em `views/` e atualize o controller
- **Alterar banco de dados**: Modifique o model e atualize o schema
- **Ajustar layout**: Edite os templates e arquivos CSS
- **Implementar middleware**: Adicione em `config/middlewares/` e registre na aplicação
- **Adicionar scripts JS**: Crie arquivos modulares em `public/js/`
- **Estilizar componentes**: Adicione estilos específicos em `public/css/`

### 3. Boas Práticas

- Siga o padrão MVC
- Use prepared statements para consultas SQL
- Valide entradas de usuários
- Mantenha a separação de responsabilidades
- Documente seu código
- Teste em diferentes dispositivos e navegadores
- Mantenha os arquivos CSS e JS modulares
- Utilize o sistema de logs para rastrear problemas
- Implemente testes unitários quando possível

## 📱 Compatibilidade

O sistema é totalmente responsivo e compatível com:

- **Navegadores**: Chrome, Firefox, Safari, Edge (versões recentes)
- **Dispositivos**: Desktop, Tablets e Smartphones
- **Sistemas**: Windows, macOS, Linux, Android, iOS

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

---

Desenvolvido por [Lucas André Fernando](https://github.com/lucasandrefernando)
