<?php

return [

    /**
     *
     * Traduções compartilhadas.
     *
     */
    'title'   => 'Instalador',
    'next'    => 'Próximo passo',
    'back'    => 'Anterior',
    'finish'  => 'Instalar',
    'forms'   => [
        'errorTitle' => 'Ocorreram os seguintes erros:',
    ],

    /**
     *
     * Traduções da página inicial.
     *
     */
    'welcome' => [
        'templateTitle' => 'Bem-vindo',
        'title'         => 'Instalador',
        'message'       => 'Assistente de instalação e configuração fácil.',
        'next'          => 'Verificar requisitos',
    ],

    /**
     *
     * Traduções da página de requisitos.
     *
     */
    'requirements' => [
        'templateTitle' => 'Passo 1 | Requisitos do servidor',
        'title'         => 'Requisitos do servidor',
        'next'          => 'Verificar permissões',
    ],

    /**
     *
     * Traduções da página de permissões.
     *
     */
    'permissions' => [
        'templateTitle' => 'Passo 2 | Permissões',
        'title'         => 'Permissões',
        'next'          => 'Configurar ambiente',
    ],

    /**
     *
     * Traduções da página de ambiente.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle'  => 'Passo 3 | Configurações de ambiente',
            'title'          => 'Configurações de ambiente',
            'desc'           => 'Selecione como deseja configurar o arquivo <code>.env</code> da aplicação.',
            'wizard-button'  => 'Assistente de configuração',
            'classic-button' => 'Editor clássico de texto',
        ],
        'wizard' => [
            'templateTitle' => 'Passo 3 | Configurações de ambiente | Assistente guiado',
            'title'         => 'Assistente guiado de <code>.env</code>',
            'tabs'          => [
                'environment' => 'Ambiente',
                'database'    => 'Banco de dados',
                'application' => 'Aplicação',
            ],
            'form'          => [
                'name_required'                     => 'É necessário um nome de ambiente.',
                'app_name_label'                    => 'Nome da aplicação',
                'app_name_placeholder'              => 'Nome da aplicação',
                'app_environment_label'             => 'Ambiente da aplicação',
                'app_environment_label_local'       => 'Local',
                'app_environment_label_developement' => 'Desenvolvimento',
                'app_environment_label_qa'          => 'QA',
                'app_environment_label_production'  => 'Produção',
                'app_environment_label_other'       => 'Outro',
                'app_environment_placeholder_other' => 'Digite seu ambiente...',
                'app_debug_label'                   => 'Depuração da aplicação',
                'app_debug_label_true'              => 'Verdadeiro',
                'app_debug_label_false'             => 'Falso',
                'app_log_level_label'               => 'Nível de log da aplicação',
                'app_log_level_label_debug'         => 'debug',
                'app_log_level_label_info'          => 'info',
                'app_log_level_label_notice'        => 'notice',
                'app_log_level_label_warning'       => 'warning',
                'app_log_level_label_error'         => 'error',
                'app_log_level_label_critical'      => 'critical',
                'app_log_level_label_alert'         => 'alert',
                'app_log_level_label_emergency'     => 'emergency',
                'app_url_label'                     => 'URL da aplicação',
                'app_url_placeholder'               => 'URL da aplicação',
                'db_connection_label'               => 'Conexão com banco de dados',
                'db_connection_label_mysql'         => 'mysql',
                'db_connection_label_sqlite'        => 'sqlite',
                'db_connection_label_pgsql'         => 'pgsql',
                'db_connection_label_sqlsrv'        => 'sqlsrv',
                'db_host_label'                     => 'Servidor de banco de dados',
                'db_host_placeholder'               => 'Servidor de banco de dados',
                'db_port_label'                     => 'Porta do banco de dados',
                'db_port_placeholder'               => 'Porta do banco de dados',
                'db_name_label'                     => 'Nome do banco de dados',
                'db_name_placeholder'               => 'Nome do banco de dados',
                'db_username_label'                 => 'Usuário do banco de dados',
                'db_username_placeholder'           => 'Usuário do banco de dados',
                'db_password_label'                 => 'Senha do banco de dados',
                'db_password_placeholder'           => 'Senha do banco de dados',

                'app_tabs' => [
                    'more_info'              => 'Mais informações',
                    'broadcasting_title'     => 'Transmissão, Cache, Sessão e Fila',
                    'broadcasting_label'     => 'Driver de transmissão',
                    'broadcasting_placeholder' => 'Driver de transmissão',
                    'cache_label'            => 'Driver de cache',
                    'cache_placeholder'      => 'Driver de cache',
                    'session_label'          => 'Driver de sessão',
                    'session_placeholder'    => 'Driver de sessão',
                    'queue_label'            => 'Driver de fila',
                    'queue_placeholder'      => 'Driver de fila',
                    'redis_label'            => 'Driver de Redis',
                    'redis_host'             => 'Servidor Redis',
                    'redis_password'         => 'Senha do Redis',
                    'redis_port'             => 'Porta do Redis',

                    'mail_label'              => 'Email',
                    'mail_driver_label'       => 'Driver de email',
                    'mail_driver_placeholder' => 'Driver de email',
                    'mail_host_label'         => 'Servidor de email',
                    'mail_host_placeholder'   => 'Servidor de email',
                    'mail_port_label'         => 'Porta de email',
                    'mail_port_placeholder'   => 'Porta de email',
                    'mail_username_label'     => 'Usuário de email',
                    'mail_username_placeholder' => 'Usuário de email',
                    'mail_password_label'     => 'Senha de email',
                    'mail_password_placeholder' => 'Senha de email',
                    'mail_encryption_label'   => 'Criptografia de email',
                    'mail_encryption_placeholder' => 'Criptografia de email',

                    'pusher_label'                 => 'Pusher',
                    'pusher_app_id_label'          => 'ID do App Pusher',
                    'pusher_app_id_palceholder'    => 'ID do App Pusher',
                    'pusher_app_key_label'         => 'Chave do App Pusher',
                    'pusher_app_key_palceholder'   => 'Chave do App Pusher',
                    'pusher_app_secret_label'      => 'Segredo do App Pusher',
                    'pusher_app_secret_palceholder' => 'Segredo do App Pusher',
                ],
                'buttons' => [
                    'setup_database'    => 'Configurar banco de dados',
                    'setup_application' => 'Configurar aplicação',
                    'install'           => 'Instalar',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Passo 3 | Configurações de ambiente | Editor clássico',
            'title'         => 'Editor clássico de ambiente',
            'save'          => 'Salvar arquivo .env',
            'back'          => 'Usar assistente',
            'install'       => 'Salvar e instalar',
        ],
        'success' => 'Configurações do arquivo .env salvas com sucesso.',
        'errors'  => 'Não foi possível salvar o arquivo .env. Por favor, crie-o manualmente.',
    ],

    'install' => 'Instalar',

    /**
     *
     * Traduções do log de instalação.
     *
     */
    'installed' => [
        'success_log_message' => 'Instalador instalado com sucesso em ',
    ],

    /**
     *
     * Traduções da página final.
     *
     */
    'final'   => [
        'title'         => 'Instalação concluída',
        'templateTitle' => 'Instalação concluída',
        'finished'      => 'A aplicação foi instalada com sucesso.',
        'migration'     => 'Saída do console de migração e seed:',
        'console'       => 'Saída do console da aplicação:',
        'log'           => 'Registro de instalação:',
        'env'           => 'Arquivo .env final:',
        'exit'          => 'Clique aqui para sair',
    ],

    /**
     *
     * Traduções específicas do atualizador.
     *
     */
    'updater' => [
        /**
         *
         * Traduções compartilhadas.
         *
         */
        'title' => 'Atualizador',

        /**
         *
         * Página de boas-vindas do atualizador.
         *
         */
        'welcome' => [
            'title'   => 'Bem-vindo ao Atualizador',
            'message' => 'Bem-vindo ao assistente de atualização.',
        ],

        /**
         *
         * Página de visão geral.
         *
         */
        'overview' => [
            'title'           => 'Visão geral',
            'message'         => 'Há 1 atualização.|Há :number atualizações.',
            'install_updates' => 'Instalar atualizações',
        ],

        /**
         *
         * Página final do atualizador.
         *
         */
        'final' => [
            'title'    => 'Concluído',
            'finished' => 'Banco de dados da aplicação atualizado com sucesso.',
            'exit'     => 'Clique aqui para sair',
        ],

        'log' => [
            'success_message' => 'Atualizador atualizado com sucesso em ',
        ],
    ],

];
