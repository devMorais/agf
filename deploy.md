# Fluxo de Deploy — Associação Grande Família (AGF)
> PHP puro (sem framework), com router próprio (Pecee\SimpleRouter) + Twig.
> Ambiente local: Laragon (Windows), servido via servidor embutido do PHP.
> Domínio produção: associacaograndefamilia.devmorais.com.br

## 1. Antes de tudo: testar local
cd C:\laragon\www\agf
C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64\php.exe -S localhost:8000 router-dev.php
# Testar em http://localhost:8000

## 2. Subir mudanças pro GitHub
cd C:\laragon\www\agf
git add .
git commit -m "descrição da mudança"
git push origin main

## 3. Conectar no servidor (Hostinger)
ssh -p 65002 u846585591@147.93.39.159

## 4. Ir até a pasta do projeto em produção
cd ~/domains/devmorais.com.br/public_html/associacaograndefamilia

## 5. Puxar as mudanças
git pull origin main

## 6. Rodar pós-deploy (se necessário)
composer install --no-dev
# Sem sistema de migrations — mudanças de estrutura de banco aplicadas
# manualmente via SQL direto em produção.

## Notas
- IP servidor: 147.93.39.159
- Porta SSH: 65002
- Usuário SSH: u846585591
- Banco local: agf (usuário root, senha vazia)
- Banco produção: u846585591_agf (usuário u846585591_agf)
- Domínio produção: associacaograndefamilia.devmorais.com.br
- Caminho completo produção: ~/domains/devmorais.com.br/public_html/associacaograndefamilia
- Remote git: https://github.com/devMorais/agf.git
- Config real fica em `sistema/configuracao.php` (não versionado — template
  em `sistema/configuracao.example.php`). Confirmado que produção e local
  usam o mesmo arquivo de config, cada um com seus próprios valores.

### Peculiaridades deste projeto
- SEM sistema de migrations. Mas, diferente do sistema de votação, este
  projeto já vem com um dump SQL versionado dentro da pasta `_BD/agf (1).sql`
  — não precisa puxar do servidor de produção via SSH, é só importar direto:
    mysql -u root -e "CREATE DATABASE agf"
    mysql -u root agf < "_BD\agf (1).sql"
  (esse dump pode ficar desatualizado com o tempo — se precisar de dados mais
  recentes, puxar novo dump de produção como fizemos no sistema de votação:
    ssh -p 65002 u846585591@147.93.39.159
    mysqldump -u u846585591_agf -p'^t0HAsM@yXGA' u846585591_agf > ~/agf_completo.sql
  depois baixar com scp e importar local.)

- O `vendor/` estava versionado no git mas veio INCOMPLETO no clone (pacotes
  como `verot/class.upload.php`, `pecee/simple-router`, `twig/twig` vieram
  como pastas vazias). Sempre que clonar este repo do zero, apagar `vendor`
  inteiro e rodar `composer install` de novo para reconstruir corretamente:
    rmdir /s /q vendor
    php composer.phar install

- O `index.php` deste projeto NÃO tinha `error_reporting(E_ALL & ~E_DEPRECATED)`
  (diferente do sistema de votação, que já tinha). Isso causa uma tela cheia
  de warnings "Deprecated" de bibliotecas antigas (SimpleRouter, php-ref) ao
  rodar em PHP 8.4. Adicionado manualmente no topo do `index.php` local —
  vale confirmar se essa linha também existe em produção, senão replicar lá.

- Mesmo fix do `router-dev.php` do sistema de votação é necessário aqui
  (servidor embutido do PHP não serve CSS/imagens sem ele). Sempre rodar com
  `-S localhost:8000 router-dev.php`, nunca `index.php` sozinho.

- A constante `URL_DESENVOLVIMENTO` em `sistema/configuracao.php` precisa
  apontar para `http://localhost:8000/` (não para o valor padrão do
  `.example`, que é `https://agf.test/` — esse domínio não existe sem vhost
  configurado).

- `DB_SENHA` no bloco de ambiente local do `.example` vem como `'root'`,
  mas o MySQL do Laragon usa senha vazia por padrão — trocar para `''`.