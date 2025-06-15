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

## 📂 Estrutura do Projeto

    sistema-gestao-chamados/
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
    │   │   ├── SetoresController.php
    │   │   └── UsuariosController.php
    │   │
    │   ├── models/
    │   │   ├── Chamado.php
    │   │   ├── ChamadoComentario.php
    │   │   ├── ChamadoHistorico.php
    │   │   ├── Empresa.php
    │   │   ├── HistoricoChamado.php
    │   │   ├── Licenca.php
    │   │   ├── Log.php
    │   │   ├── Model.php
    │   │   ├── Setor.php
    │   │   ├── StatusChamado.php
    │   │   └── Usuario.php
    │   │
    │   └── views/
    │       ├── auth/
    │       │   ├── login.php
    │       │   └── recuperar-senha.php
    │       ├── chamados/
    │       │   ├── dashboard.php
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
    │   └── database.php
    │
    ├── public/
    │   ├── css/
    │   │   └── style.css
    │   ├── img/
    │   │   ├── favicon.ico
    │   │   └── logo.png
    │   └── js/
    │       └── main.js
    │
    ├── scripts/
    │   ├── database/
    │   │   └── schema.sql
    │   └── setup-database.php
    │
    ├── .gitignore
    ├── .htaccess
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

## ⚙️ Instalação

1. **Clone o projeto:**

   ```
   git clone https://github.com/lucasandrefernando/sistema-gestao-chamados.git
   ```

2. **Configure o banco de dados:**

   - Edite `config/database.php` com suas credenciais
   - Execute o script de criação do banco:

   ```
   php scripts/setup-database.php
   ```

   - Ou importe manualmente o arquivo `scripts/database/schema.sql`

3. **Configure o servidor web:**

   - Aponte o DocumentRoot para a pasta `public/`
   - Certifique-se de que o mod_rewrite está habilitado (para .htaccess)

4. **Acesse no navegador:**
   - Exemplo: `http://localhost/sistema-gestao-chamados/public/`
   - Faça login com as credenciais padrão:
     - Email: `admin@sistema.com`
     - Senha: `admin123`

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

### Gestão de Setores

- **Cadastro**: Criação e configuração de setores
- **Usuários**: Associação de usuários a setores
- **Visualização**: Detalhes e estatísticas por setor
- **Administração**: Configurações avançadas por setor

### Gestão de Empresas

- **Cadastro**: Criação e configuração de empresas
- **Licenças**: Controle de licenças e períodos de uso
- **Configurações**: Personalização por empresa

## 📊 Relatórios e Análises

O sistema oferece diversos relatórios e visualizações para análise de dados:

- **Chamados por Status**: Distribuição visual dos chamados por situação
- **Chamados por Setor**: Análise de volume por departamento
- **Tempo Médio de Atendimento**: Métricas de eficiência por setor e tipo
- **Chamados por Período**: Análise temporal de volume de solicitações
- **Exportação CSV**: Dados completos para análise em ferramentas externas

## ✨ Personalização

- **Adicionar campos**: Edite o model, controller e formulários correspondentes
- **Novo status de chamado**: Adicione no banco de dados e atualize as views
- **Personalizar relatórios**: Modifique as consultas e visualizações em ChamadosController
- **Alterar layout**: Edite os templates em `views/templates/`
- **Adicionar módulos**: Siga o padrão MVC para implementar novas funcionalidades

## 🔒 Segurança

O sistema implementa diversas medidas de segurança:

- **Autenticação segura**: Senhas armazenadas com hash
- **Controle de sessão**: Timeout e validação de sessões
- **Validação de entradas**: Prevenção contra injeção SQL e XSS
- **Controle de acesso**: Verificação de permissões por módulo e ação
- **Logs de atividades**: Registro de ações importantes no sistema

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature:

   ```
   git checkout -b minha-feature
   ```

3. Commit suas alterações:

   ```
   git commit -m 'feat: Minha nova funcionalidade'
   ```

4. Push para a branch:

   ```
   git push origin minha-feature
   ```

5. Abra um Pull Request

## 🧑‍💻 Guia para Desenvolvedores

### 1. Entenda a Estrutura

- **controllers/**: Lógica de negócio e fluxo da aplicação
- **models/**: Acesso ao banco de dados e regras de negócio
- **views/**: Templates e componentes visuais
- **config/**: Configurações da aplicação e banco de dados
- **public/**: Arquivos acessíveis diretamente (CSS, JS, imagens)

### 2. Fluxo de Trabalho

- **Adicionar funcionalidade**: Implemente no controller e model correspondentes
- **Criar nova visualização**: Adicione em `views/` e atualize o controller
- **Alterar banco de dados**: Modifique o model e atualize o schema
- **Ajustar layout**: Edite os templates e arquivos CSS

### 3. Boas Práticas

- Siga o padrão MVC
- Use prepared statements para consultas SQL
- Valide entradas de usuários
- Mantenha a separação de responsabilidades
- Documente seu código
- Teste em diferentes dispositivos e navegadores

## 📱 Compatibilidade

O sistema é totalmente responsivo e compatível com:

- **Navegadores**: Chrome, Firefox, Safari, Edge (versões recentes)
- **Dispositivos**: Desktop, Tablets e Smartphones
- **Sistemas**: Windows, macOS, Linux, Android, iOS

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

---

Desenvolvido por [Lucas André Fernando](https://github.com/lucasandrefernando)
