WPCM Redirect Manager
Um plugin WordPress que gerencia automaticamente redirecionamentos 301 quando os slugs (URLs) de posts ou páginas são alterados ou quando posts/páginas são excluídos.

Índice
Descrição
Funcionalidades
Instalação
Como Usar
Estrutura de Pastas
Contribuição
Licença
Descrição
O WPCM Redirect Manager é um plugin que garante que qualquer alteração nos slugs (URLs) de seus posts ou páginas não resultem em erros 404 para os visitantes ou motores de busca. Ele cria automaticamente redirecionamentos 301 sempre que um slug é alterado ou um post/página é excluído, mantendo a integridade dos links em seu site.

Funcionalidades
Redirecionamento Automático em Alterações de Slug:

Monitora alterações nos slugs de posts e páginas.
Cria automaticamente um redirecionamento 301 do URL antigo para o novo.
Redirecionamento em Exclusão de Post/Página:

Ao excluir um post ou página, o plugin solicita que você configure um redirecionamento para outro URL existente.
Se nenhum redirecionamento for especificado, o URL antigo não estará mais acessível.
Interface de Gerenciamento:

Interface amigável no painel do WordPress para visualizar, editar e excluir redirecionamentos.
Possibilidade de adicionar redirecionamentos manualmente.
Otimização e Segurança:

Otimizado para desempenho, evitando impactos na velocidade do site.
Compatível com as versões mais recentes do WordPress.
Inclui medidas de segurança para evitar acesso não autorizado.
Instalação
Siga os passos abaixo para instalar o plugin a partir do GitHub:

Download:

Faça o download do plugin diretamente do repositório do GitHub ou clone o repositório usando o comando:
bash
Copiar código
git clone https://github.com/centralmidia-wordpress/-wpcm-redirect-manager.git
Estrutura de Pastas:

Certifique-se de que a estrutura de pastas esteja organizada da seguinte forma:
css
Copiar código
wp-content
└── plugins
    └── wpcm-redirect-manager
        ├── css
        │   └── admin-style.css
        ├── js
        │   └── admin-script.js
        ├── languages
        ├── templates
        │   └── admin-page.php
        └── wpcm-redirect-manager.php
Upload via FTP (opcional):

Se necessário, use um cliente FTP para enviar a pasta wpcm-redirect-manager para o diretório wp-content/plugins/ do seu site WordPress.
Ativação do Plugin:

No painel administrativo do WordPress, navegue até Plugins > Plugins Instalados.
Encontre o WPCM Redirect Manager na lista e clique em Ativar.
Como Usar
Após a instalação e ativação, o plugin começará a funcionar automaticamente. Veja como aproveitar ao máximo suas funcionalidades:

Redirecionamento Automático
Alteração de Slug:

Quando você alterar o slug de um post ou página, o plugin criará automaticamente um redirecionamento 301 do URL antigo para o novo.
Exclusão de Post/Página:

Ao mover um post ou página para a lixeira, uma notificação aparecerá solicitando que você configure um redirecionamento.
Clique no link fornecido para especificar o URL de destino para o redirecionamento.
Gerenciando Redirecionamentos
Acessando a Interface:

No painel administrativo, vá até Redirecionamentos no menu lateral.
Adicionando um Novo Redirecionamento:

Clique em Adicionar Novo.
Insira o URL de Origem e o URL de Destino.
Salve o redirecionamento.
Visualizando Redirecionamentos Existentes:

A lista de redirecionamentos exibirá todos os redirecionamentos configurados.
Você pode editar ou excluir redirecionamentos conforme necessário.
Estrutura de Pastas
Abaixo está a descrição de cada pasta e arquivo do plugin:

graphql
Copiar código
wpcm-redirect-manager
├── css
│   └── admin-style.css         # Estilos CSS para o painel administrativo
├── js
│   └── admin-script.js         # Scripts JavaScript para funcionalidades no admin
├── languages                    # Pasta reservada para arquivos de tradução
├── templates
│   └── admin-page.php          # Template da página administrativa do plugin
└── wpcm-redirect-manager.php    # Arquivo principal do plugin
css/admin-style.css: Contém estilos personalizados para a interface administrativa do plugin.
js/admin-script.js: Inclui scripts que aprimoram a usabilidade na área administrativa.
languages/: Pasta destinada a arquivos de tradução para internacionalização.
templates/admin-page.php: Template utilizado para renderizar a página de gerenciamento de redirecionamentos.
wpcm-redirect-manager.php: O arquivo principal que contém o código funcional do plugin.
Contribuição
Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou pull requests no repositório do GitHub.

Repositório GitHub: centralmidia-wordpress/-wpcm-redirect-manager
Licença
Este projeto está licenciado sob a Licença MIT.

