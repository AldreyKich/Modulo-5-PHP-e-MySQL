# 📚 Sistema de Biblioteca - Módulo 5

Sistema completo de gerenciamento de biblioteca desenvolvido em PHP com MySQL para fins educacionais.

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Como Usar](#como-usar)
- [Banco de Dados](#banco-de-dados)
- [Conceitos Aplicados](#conceitos-aplicados)

---

## 🎯 Sobre o Projeto

Este é um sistema completo de gerenciamento de biblioteca que permite:
- Cadastro e controle de livros
- Gerenciamento de clientes
- Registro e controle de empréstimos
- Cálculo automático de multas
- Relatórios e estatísticas

O sistema foi desenvolvido com **fins educacionais** para o Módulo 5 - Banco de Dados II, aplicando conceitos de:
- PDO (PHP Data Objects)
- Prepared Statements
- Transações
- Relacionamentos entre tabelas
- Validações
- Segurança

---

## ✨ Funcionalidades

### 📖 Gerenciamento de Livros
- ✅ Cadastrar novos livros
- ✅ Editar informações de livros
- ✅ Excluir livros (quando não há empréstimos)
- ✅ Filtrar por título, autor, categoria
- ✅ Controle de estoque (quantidade disponível)
- ✅ Localização física na biblioteca

### 👥 Gerenciamento de Clientes
- ✅ Cadastrar novos clientes
- ✅ Editar informações de clientes
- ✅ Excluir clientes (quando não há empréstimos ativos)
- ✅ Status (Ativo, Inativo, Bloqueado)
- ✅ Histórico de empréstimos
- ✅ Validação de CPF e e-mail

### 📋 Gerenciamento de Empréstimos
- ✅ Registrar novos empréstimos
- ✅ Validação de disponibilidade
- ✅ Limite de 3 empréstimos por cliente
- ✅ Bloqueio de clientes com atraso
- ✅ Renovação de empréstimos
- ✅ Devolução com cálculo de multa
- ✅ Prazo de 7 dias (configurável)
- ✅ Multa de R$ 2,50 por dia de atraso

### ✍️ Gerenciamento de Autores
- ✅ Cadastrar autores
- ✅ Editar informações
- ✅ Listar livros por autor

### 📊 Dashboard e Relatórios
- ✅ Estatísticas gerais do sistema
- ✅ Livros mais emprestados
- ✅ Alertas de empréstimos atrasados
- ✅ Últimos livros cadastrados

---

## 🛠️ Tecnologias Utilizadas

- **PHP 7.4+** - Linguagem de programação
- **MySQL 5.7+** - Banco de dados
- **PDO** - Interface de acesso ao banco
- **HTML5 & CSS3** - Interface do usuário
- **JavaScript** - Validações e interatividade

---

## 📦 Requisitos

Antes de instalar, certifique-se de ter:

- **XAMPP**, **WAMP**, **LAMP** ou servidor com:
  - PHP 7.4 ou superior
  - MySQL 5.7 ou superior
  - Apache
- Extensões PHP habilitadas:
  - `pdo_mysql`
  - `mysqli`
  - `mbstring`

---

## 🚀 Instalação

### Passo 1: Clonar/Baixar o Projeto

Baixe os arquivos do projeto e coloque na pasta do seu servidor web:
- **XAMPP**: `C:\xampp\htdocs\biblioteca`
- **WAMP**: `C:\wamp64\www\biblioteca`
- **Linux**: `/var/www/html/biblioteca`

### Passo 2: Criar o Banco de Dados

1. Abra o **phpMyAdmin** (http://localhost/phpmyadmin)
2. Clique em "Novo" ou "New"
3. Crie um banco chamado `biblioteca`
4. Selecione o banco criado
5. Clique em "SQL"
6. Copie todo o conteúdo do arquivo `database.sql`
7. Cole na área de texto e clique em "Executar" ou "Go"

**OU** execute via linha de comando:

```bash
mysql -u root -p < database.sql
```

### Passo 3: Configurar a Conexão

Abra o arquivo `config/database.php` e ajuste se necessário:

```php
private $host = "localhost";
private $db = "biblioteca";
private $user = "root";
private $pass = "";  // Sua senha do MySQL
```

### Passo 4: Acessar o Sistema

Abra seu navegador e acesse:
- http://localhost/biblioteca/

---

## 📁 Estrutura do Projeto

```
biblioteca/
│
├── config/                      # Configurações
│   ├── config.php              # Constantes do sistema
│   └── database.php            # Conexão PDO (Singleton)
│
├── includes/                    # Arquivos incluídos
│   ├── header.php              # Cabeçalho e menu
│   ├── footer.php              # Rodapé
│   └── funcoes.php             # Funções auxiliares
│
├── index.php                    # Dashboard principal
│
├── clientes.php                 # Listagem de clientes
├── cliente_novo.php            # Formulário de cadastro
├── cliente_salvar.php          # Processa cadastro
├── cliente_editar.php          # Formulário de edição
├── cliente_atualizar.php       # Processa edição
├── cliente_excluir.php         # Exclui cliente
│
├── livros.php                   # Listagem de livros
├── livro_novo.php              # Formulário de cadastro
├── livro_salvar.php            # Processa cadastro
│
├── autores.php                  # Listagem de autores
├── autor_novo.php              # Formulário de cadastro
├── autor_salvar.php            # Processa cadastro
│
├── emprestimos.php              # Listagem de empréstimos
├── emprestimo_novo.php         # Formulário de novo empréstimo
├── emprestimo_registrar.php    # Processa empréstimo
├── emprestimo_devolver.php     # Processa devolução
├── emprestimo_renovar.php      # Renova empréstimo
│
└── database.sql                 # Script de criação do BD
```

---

## 💻 Como Usar

### 1. Primeira Execução

Após a instalação, o sistema já vem com dados de exemplo:
- 10 autores cadastrados
- 15 livros no acervo
- 5 clientes cadastrados
- Alguns empréstimos de exemplo

### 2. Fluxo de Uso

#### Cadastrar um Autor
1. Acesse **Autores** no menu
2. Clique em "Cadastrar Novo Autor"
3. Preencha o nome (obrigatório)
4. Preencha nacionalidade e biografia (opcional)
5. Clique em "Cadastrar Autor"

#### Cadastrar um Livro
1. Acesse **Livros** no menu
2. Clique em "Cadastrar Novo Livro"
3. Preencha:
   - Título (obrigatório)
   - Autor (obrigatório)
   - Quantidade total e disponível
   - Outros dados opcionais
4. Clique em "Cadastrar Livro"

#### Cadastrar um Cliente
1. Acesse **Clientes** no menu
2. Clique em "Cadastrar Novo Cliente"
3. Preencha:
   - Nome completo (obrigatório)
   - E-mail (obrigatório e único)
   - Telefone (obrigatório)
   - Outros dados opcionais
4. Clique em "Cadastrar Cliente"

#### Registrar um Empréstimo
1. Acesse **Empréstimos** no menu
2. Clique em "Registrar Novo Empréstimo"
3. Selecione o cliente
4. Selecione o livro
5. Clique em "Registrar Empréstimo"

**Validações automáticas:**
- Verifica se o livro está disponível
- Verifica se o cliente não atingiu o limite (3 empréstimos)
- Verifica se o cliente não tem atrasos
- Atualiza automaticamente o estoque

#### Devolver um Livro
1. Acesse **Empréstimos** no menu
2. Localize o empréstimo ativo
3. Clique em "Devolver"
4. O sistema:
   - Calcula automaticamente se há atraso
   - Calcula a multa (R$ 2,50 por dia)
   - Devolve o livro ao estoque
   - Exibe o resumo da devolução

#### Renovar um Empréstimo
1. Acesse **Empréstimos** no menu
2. Localize o empréstimo ativo (sem atraso)
3. Clique em "Renovar"
4. O sistema adiciona mais 7 dias ao prazo

---

## 🗄️ Banco de Dados

### Tabelas Principais

#### `autores`
- **id**: Chave primária
- **nome**: Nome do autor
- **nacionalidade**: País de origem
- **data_nascimento**: Data de nascimento
- **biografia**: Biografia do autor

#### `livros`
- **id**: Chave primária
- **titulo**: Título do livro
- **autor_id**: FK para autores
- **isbn**: Código ISBN
- **ano_publicacao**: Ano de publicação
- **quantidade_total**: Total de exemplares
- **quantidade_disponivel**: Exemplares disponíveis
- **categoria**: Gênero do livro
- **localizacao**: Localização física

#### `clientes`
- **id**: Chave primária
- **nome**: Nome completo
- **email**: E-mail (único)
- **telefone**: Telefone de contato
- **cpf**: CPF (único)
- **status**: Ativo, Inativo ou Bloqueado

#### `emprestimos`
- **id**: Chave primária
- **cliente_id**: FK para clientes
- **livro_id**: FK para livros
- **data_emprestimo**: Data do empréstimo
- **data_devolucao_prevista**: Data prevista
- **data_devolucao_real**: Data real da devolução
- **status**: Ativo ou Devolvido
- **multa**: Valor da multa

---

## 📚 Conceitos Aplicados

### 1. PDO (PHP Data Objects)
- Utilização de prepared statements
- Proteção contra SQL Injection
- Tratamento de exceções

### 2. Padrão Singleton
- Classe Database com instância única
- Economia de recursos
- Conexão centralizada

### 3. Transações
- Usado em empréstimos e devoluções
- Garante consistência dos dados
- ACID compliance

### 4. Relacionamentos
- **1:N** - Um autor pode ter vários livros
- **1:N** - Um cliente pode ter vários empréstimos
- **1:N** - Um livro pode ter vários empréstimos
- Uso de JOINs para buscar dados relacionados

### 5. Validações
- **Server-side** (PHP)
- **Client-side** (JavaScript)
- Validação de CPF, e-mail, telefone
- Regras de negócio (limites, prazos)

### 6. Segurança
- htmlspecialchars() para prevenir XSS
- Prepared statements para prevenir SQL Injection
- Validação e sanitização de inputs
- Controle de sessões

---

## ⚙️ Configurações

### Alterar Prazo de Empréstimo

Edite o arquivo `config/config.php`:

```php
define('PRAZO_EMPRESTIMO_DIAS', 7);  // Altere para o prazo desejado
```

### Alterar Valor da Multa

Edite o arquivo `config/config.php`:

```php
define('VALOR_MULTA_DIA', 2.50);  // Altere para o valor desejado
```

### Alterar Limite de Empréstimos

Edite o arquivo `config/config.php`:

```php
define('LIMITE_EMPRESTIMOS_CLIENTE', 3);  // Altere para o limite desejado
```

---

## 🐛 Solução de Problemas

### Erro de Conexão com Banco

1. Verifique se o MySQL está rodando
2. Confirme usuário e senha em `config/database.php`
3. Verifique se o banco `biblioteca` foi criado

### Página em Branco

1. Ative a exibição de erros em `config/config.php`:
   ```php
   define('DEBUG_MODE', true);
   ```
2. Verifique os logs de erro do PHP

### Mensagens não aparecem

1. Verifique se iniciou a sessão
2. Confirme se as funções em `includes/funcoes.php` estão sendo carregadas

---

## 📝 Licença

Este projeto é de código aberto e está disponível para fins educacionais.

---

## 👨‍💻 Autor

**Módulo 5 - Banco de Dados II**  
Sistema desenvolvido para fins didáticos

---

## 🤝 Contribuindo

Este é um projeto educacional. Sugestões e melhorias são bem-vindas!

---

## 📧 Suporte

Para dúvidas ou problemas:
- Consulte este README
- Revise os comentários no código
- Verifique as mensagens de erro exibidas

---

**Versão:** 1.0.0  
**Última Atualização:** Novembro de 2025
