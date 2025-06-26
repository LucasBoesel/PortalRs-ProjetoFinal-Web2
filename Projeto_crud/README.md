# 📰 Portal RS – Notícias do Rio Grande do Sul

Este repositório contém o **Portal RS**, um sistema web para publicação e gerenciamento de notícias focado no estado do Rio Grande do Sul. Desenvolvido com **PHP orientado a objetos** e estilizado com **Bootstrap 5**.

Este projeto foi desenvolvido como trabalho final da disciplina **Programação Web II**, aplicando conceitos de banco de dados, segurança, boas práticas em PHP e usabilidade.

---

## 🛠️ Requisitos

- PHP 8.x+  
- MySQL ou MariaDB  
- Navegador moderno  
- Servidor local (XAMPP, Laragon, WAMP, etc.)

---

## 🚀 Funcionalidades

- Página inicial com carrossel animado de notícias em destaque  
- Notícias organizadas por temas: Geral, Política e Esportes  
- Cadastro e edição completa de usuário  
- Cadastro, visualização e edição de notícias com imagens  
- Perfil do usuário com suas notícias publicadas  
- Rodapé com redes sociais e informações de contato  

---

## 🧰 Tecnologias Utilizadas

O desenvolvimento do Portal RS contou com as seguintes tecnologias:

- **PHP** – Backend com orientação a objetos e integração com banco de dados via PDO  
- **MySQL/MariaDB** – Banco de dados relacional  
- **HTML5 & CSS3** – Estrutura e estilo personalizados com foco em layout escuro  
- **JavaScript** – Comportamentos dinâmicos e interações em partes da interface  
- **Bootstrap 5** – Framework CSS para responsividade e componentes visuais  

---

## 🎨 Estilo

O layout utiliza uma paleta escura personalizada com:

- Tons de preto, azul e branco  
- Gradientes suaves e botões com transparência  
- Fontes legíveis e ícones do *Bootstrap Icons*  

---

## 🗄️ Banco de Dados

O portal utiliza um banco relacional **MySQL/MariaDB**, com acesso via **PDO**, garantindo segurança e portabilidade. As principais tabelas incluem:

- `usuarios`: armazena nome, sexo, telefone, email, senha criptografada, código de verificação e imagem de perfil  
- `noticias`: contém título, notícia, data de publicação, local, autor, imagem, tema

O modelo relacional permite associar cada notícia ao autor responsável, garantindo controle e rastreabilidade.

---

## 📦 Estrutura do Banco de Dados

Abaixo está o script SQL para criar a base de dados `bdcrud` com as tabelas `usuarios` e `noticias`:

```sql
CREATE DATABASE bdcrud;
USE bdcrud;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    sexo CHAR(1) NOT NULL,
    fone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    codigo_verificacao VARCHAR(10) DEFAULT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL
);

CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    noticia TEXT NOT NULL,
    data DATETIME,
    local VARCHAR(255),
    autor INT,
    imagem VARCHAR(255),
    tema VARCHAR(50),
    FOREIGN KEY (autor) REFERENCES usuarios(id)
);
```

---

## 💡 Inspirações

O Portal RS foi inspirado visual e funcionalmente nos portais **G1** e **UOL**, adaptando as melhores práticas de navegação, categorização e design responsivo para um projeto acadêmico.

---

## 👨‍💻 Autores

**Lucas Boesel**  
**Daniel Jacob**  
Acadêmicos de Sistemas de Informação – ULBRA São Lucas  
Este projeto faz parte do Projeto Final da disciplina **Programação Web II**, com foco em desenvolvimento web orientado a objetos com PHP.

---

## ✅ Licença

Uso educacional e acadêmico.  
Permitida a modificação e reutilização para fins de aprendizado.
