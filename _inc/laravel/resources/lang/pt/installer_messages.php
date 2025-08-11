<?php

return [

	/**
	 *
	 * Traduções partilhadas.
	 *
	 */
	'title'   => 'Instalador',
	'next'    => 'Passo seguinte',
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
			'desc'           => 'Selecione como pretende configurar o ficheiro <code>.env</code> da aplicação.',
			'wizard-button'  => 'Assistente de configuração',
			'classic-button' => 'Editor de texto clássico',
		],
		'wizard' => [
			'templateTitle' => 'Passo 3 | Configurações de ambiente | Assistente guiado',
			'title'         => 'Assistente guiado do <code>.env</code>',
			'tabs'          => [
				'environment' => 'Ambiente',
				'database'    => 'Base de dados',
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
				'app_environment_placeholder_other' => 'Introduza o seu ambiente...',
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
				'db_connection_label'               => 'Ligação à base de dados',
				'db_connection_label_mysql'         => 'mysql',
				'db_connection_label_sqlite'        => 'sqlite',
				'db_connection_label_pgsql'         => 'pgsql',
				'db_connection_label_sqlsrv'        => 'sqlsrv',
				'db_host_label'                     => 'Host da base de dados',
				'db_host_placeholder'               => 'Host da base de dados',
				'db_port_label'                     => 'Porta da base de dados',
				'db_port_placeholder'               => 'Porta da base de dados',
				'db_name_label'                     => 'Nome da base de dados',
				'db_name_placeholder'               => 'Nome da base de dados',
				'db_username_label'                 => 'Utilizador da base de dados',
				'db_username_placeholder'           => 'Utilizador da base de dados',
				'db_password_label'                 => 'Password da base de dados',
				'db_password_placeholder'           => 'Password da base de dados',

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
					'redis_host'             => 'Host de Redis',
					'redis_password'         => 'Password de Redis',
					'redis_port'             => 'Porta de Redis',

					'mail_label'               => 'Correio',
					'mail_driver_label'        => 'Driver de correio',
					'mail_driver_placeholder'  => 'Driver de correio',
					'mail_host_label'          => 'Host de correio',
					'mail_host_placeholder'    => 'Host de correio',
					'mail_port_label'          => 'Porta de correio',
					'mail_port_placeholder'    => 'Porta de correio',
					'mail_username_label'      => 'Utilizador de correio',
					'mail_username_placeholder' => 'Utilizador de correio',
					'mail_password_label'      => 'Password de correio',
					'mail_password_placeholder' => 'Password de correio',
					'mail_encryption_label'    => 'Encriptação de correio',
					'mail_encryption_placeholder' => 'Encriptação de correio',

					'pusher_label'                 => 'Pusher',
					'pusher_app_id_label'          => 'Pusher App Id',
					'pusher_app_id_palceholder'    => 'Pusher App Id',
					'pusher_app_key_label'         => 'Pusher App Key',
					'pusher_app_key_palceholder'   => 'Pusher App Key',
					'pusher_app_secret_label'      => 'Pusher App Secret',
					'pusher_app_secret_palceholder' => 'Pusher App Secret',
				],

				'buttons' => [
					'setup_database'     => 'Configurar base de dados',
					'setup_application'  => 'Configurar aplicação',
					'install'            => 'Instalar',
				],
			],
		],
		'classic' => [
			'templateTitle' => 'Passo 3 | Configurações de ambiente | Editor clássico',
			'title'         => 'Editor clássico de ambiente',
			'save'          => 'Gravar .env',
			'back'          => 'Usar assistente',
			'install'       => 'Gravar e instalar',
		],
		'success' => 'As definições do ficheiro .env foram gravadas.',
		'errors'  => 'Não foi possível gravar o ficheiro .env, por favor crie-o manualmente.',
	],

	'install' => 'Instalar',

	/**
	 *
	 * Traduções dos registos instalados.
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
	'final' => [
		'title'         => 'Instalação concluída',
		'templateTitle' => 'Instalação concluída',
		'finished'      => 'A aplicação foi instalada com sucesso.',
		'migration'     => 'Saída da consola de migração e seed:',
		'console'       => 'Saída da consola da aplicação:',
		'log'           => 'Entrada de registo de instalação:',
		'env'           => 'Ficheiro final .env:',
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
		 * Traduções partilhadas.
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
			'message'         => 'Existe 1 atualização.|Existem :number atualizações.',
			'install_updates' => 'Instalar atualizações',
		],

		/**
		 *
		 * Página final do atualizador.
		 *
		 */
		'final' => [
			'title'    => 'Concluído',
			'finished' => 'A base de dados da aplicação foi atualizada com sucesso.',
			'exit'     => 'Clique aqui para sair',
		],

		'log' => [
			'success_message' => 'Atualizador atualizado com sucesso em ',
		],

	],

];
