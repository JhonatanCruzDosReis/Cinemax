# Cinemax

Plataforma web de streaming de filmes desenvolvida em PHP e MySQL como projeto acadêmico do curso de Análise e Desenvolvimento de Sistemas (UNISUAM), em 2025.

## Funcionalidades

- **Catálogo de filmes** com capa, gênero, ano e descrição.
- **Player de vídeo:** cada filme abre o arquivo correspondente da pasta `filmes/`.
- **Cadastro e login** com senha protegida por hash bcrypt e escolha de **plano mensal ou anual**.
- **Pagamento simulado** por cartão ou Pix. O sistema guarda só os 4 últimos dígitos do cartão e nunca o CVV.
- **Avaliação de 1 a 5 estrelas** por filme.
- **Triggers no MySQL:** ao avaliar um filme, ele entra automaticamente no histórico de **filmes assistidos** com a nota, sem código extra no PHP.
- **Lista de desejos** de filmes para assistir depois.
- **Gráfico das notas** dos filmes assistidos com Chart.js, com ordenação crescente e decrescente.
- **Painel do mestre (administrador):** cadastro e remoção de filmes e gestão de usuários.
- Consultas com *prepared statements* (mysqli), protegendo contra SQL Injection.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP 8, mysqli, sessões |
| Banco de dados | MySQL / MariaDB (chaves estrangeiras, `CHECK`, triggers) |
| Front-end | HTML5, CSS3, JavaScript, Bootstrap 5, Chart.js, Font Awesome |

## Estrutura

```
├── index.php, login.php, cadastro.php, planos.php
├── Home_filme.php          # Catálogo
├── video.php               # Player e avaliação
├── salvar_avaliacao.php
├── filmes_assistidos.php, lista_desejos.php, grafico.php
├── perfil.php              # Painel do mestre
├── filmes/                 # Coloque aqui os vídeos (não versionados)
└── database/cinemax.sql    # Estrutura, triggers e dados de exemplo
```

## Como rodar (XAMPP)

1. Copie a pasta do projeto para `C:\xampp\htdocs\cinemax`.
2. Inicie o **Apache** e o **MySQL** no XAMPP.
3. No phpMyAdmin, importe `database/cinemax.sql` (o script cria o banco `cinemax`).
4. Coloque vídeos `.mp4` na pasta `filmes/`. O nome do arquivo deve ser igual ao título do filme no banco (ex.: `Os incríveis 2.mp4`).
5. Acesse `http://localhost/cinemax/`.

### Logins de teste

| Perfil | E-mail | Senha |
|---|---|---|
| Mestre (admin) | `mestre@cinemax.com` | `admin123` |
| Usuário | `usuario@exemplo.com` | `teste123` |

Todos os dados de usuários do script são fictícios.

## Autor

**Jhonatan Cruz** · [GitHub](https://github.com/JhonatanCruzDosReis) · [LinkedIn](https://www.linkedin.com/in/jhonatan-cruz-279a113b8)
