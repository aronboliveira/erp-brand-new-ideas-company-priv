<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BanksConstants as BKC,
	DatabaseConstants as DC
};
use App\Enums\ConsumableType;
use App\Models\ProductServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Log};
use Faker\Factory as Faker;

class ProductServiceCategorySeeder extends Seeder
{
	public function run(): void
	{
		$faker = Faker::create('pt_BR');

		// Catálogo principal informado
		$catalog = [
			[
				'name'        => 'Desenvolvimento de Software',
				'code'        => 'SWDEV',
				DC::COL_TP_LB => ConsumableType::Service->value,
				'color'       => '#2563eb',
				'icon'        => 'lucide:code',
			],
			[
				'name'        => 'Infraestrutura de TI',
				'code'        => 'ITINFRA',
				DC::COL_TP_LB => ConsumableType::Service->value,
				'color'       => '#0ea5e9',
				'icon'        => 'lucide:server',
			],
			[
				'name'        => 'Suporte / Help Desk',
				'code'        => 'HELPDESK',
				DC::COL_TP_LB => ConsumableType::Service->value,
				'color'       => '#10b981',
				'icon'        => 'lucide:headphones',
			],
			[
				'name'        => 'Revenda de Produtos de Tecnologia',
				'code'        => 'RETAIL',
				DC::COL_TP_LB => ConsumableType::Product->value,
				'color'       => '#f59e0b',
				'icon'        => 'lucide:shopping-cart',
			],
		];

		// Technology Categories (40)
		$techCategories = [
			['Desenvolvimento Web', 'WEBDEV', ConsumableType::Service->value, '#3b82f6', 'lucide:globe'],
			['Desenvolvimento Mobile', 'MOBDEV', ConsumableType::Service->value, '#3b82f6', 'lucide:smartphone'],
			['Desenvolvimento Backend', 'BACKEND', ConsumableType::Service->value, '#3b82f6', 'lucide:database'],
			['Desenvolvimento Frontend', 'FRONTEND', ConsumableType::Service->value, '#3b82f6', 'lucide:layout'],
			['Inteligência Artificial', 'AI', ConsumableType::Service->value, '#3b82f6', 'lucide:brain'],
			['Machine Learning', 'ML', ConsumableType::Service->value, '#3b82f6', 'lucide:cpu'],
			['Análise de Dados', 'DATA_ANAL', ConsumableType::Service->value, '#3b82f6', 'lucide:bar-chart'],
			['Ciência de Dados', 'DATA_SCI', ConsumableType::Service->value, '#3b82f6', 'lucide:chart-bar'],
			['Segurança da Informação', 'INFOSEC', ConsumableType::Service->value, '#3b82f6', 'lucide:shield'],
			['Cybersecurity', 'CYBERSEC', ConsumableType::Service->value, '#3b82f6', 'lucide:shield-check'],
			['Cloud Computing', 'CLOUD', ConsumableType::Service->value, '#3b82f6', 'lucide:cloud'],
			['DevOps', 'DEVOPS', ConsumableType::Service->value, '#3b82f6', 'lucide:git-branch'],
			['SRE', 'SRE', ConsumableType::Service->value, '#3b82f6', 'lucide:activity'],
			['Redes de Computadores', 'NETWORKS', ConsumableType::Service->value, '#3b82f6', 'lucide:network'],
			['Telecomunicações', 'TELECOM', ConsumableType::Service->value, '#3b82f6', 'lucide:phone'],
			['Administração de Sistemas', 'SYSADMIN', ConsumableType::Service->value, '#3b82f6', 'lucide:terminal'],
			['Banco de Dados', 'DB', ConsumableType::Service->value, '#3b82f6', 'lucide:hard-drive'],
			['Virtualização', 'VIRTUAL', ConsumableType::Service->value, '#3b82f6', 'lucide:box'],
			['Contêineres', 'CONTAINERS', ConsumableType::Service->value, '#3b82f6', 'lucide:package'],
			['Automação', 'AUTOMATION', ConsumableType::Service->value, '#3b82f6', 'lucide:robot'],
			['Hardware', 'HARDWARE', ConsumableType::Product->value, '#3b82f6', 'lucide:monitor'],
			['Servidores', 'SERVERS', ConsumableType::Product->value, '#3b82f6', 'lucide:server'],
			['Storage', 'STORAGE', ConsumableType::Product->value, '#3b82f6', 'lucide:hard-drive'],
			['Redes Físicas', 'PHY_NET', ConsumableType::Product->value, '#3b82f6', 'lucide:router'],
			['Periféricos', 'PERIPHERALS', ConsumableType::Product->value, '#3b82f6', 'lucide:mouse'],
			['Equipamentos de TI', 'IT_EQP', ConsumableType::Product->value, '#3b82f6', 'lucide:printer'],
			['Licenças de Software', 'SOFT_LIC', ConsumableType::Product->value, '#3b82f6', 'lucide:key'],
			['Software Empresarial', 'BUS_SOFT', ConsumableType::Product->value, '#3b82f6', 'lucide:package'],
			['Sistemas Operacionais', 'OS', ConsumableType::Product->value, '#3b82f6', 'lucide:layers'],
			['Ferramentas de Desenvolvimento', 'DEV_TOOLS', ConsumableType::Product->value, '#3b82f6', 'lucide:wrench'],
			['QA e Testes', 'QA', ConsumableType::Service->value, '#3b82f6', 'lucide:check-circle'],
			['Arquitetura de Software', 'SW_ARCH', ConsumableType::Service->value, '#3b82f6', 'lucide:git-merge'],
			['Gestão de Projetos de TI', 'IT_PM', ConsumableType::Service->value, '#3b82f6', 'lucide:clipboard-list'],
			['Consultoria em TI', 'IT_CONS', ConsumableType::Service->value, '#3b82f6', 'lucide:message-square'],
			['Treinamento em TI', 'IT_TRAIN', ConsumableType::Service->value, '#3b82f6', 'lucide:book-open'],
			['Suporte Técnico', 'TECH_SUP', ConsumableType::Service->value, '#3b82f6', 'lucide:help-circle'],
			['Field Service', 'FIELD_SVC', ConsumableType::Service->value, '#3b82f6', 'lucide:truck'],
			['Monitoramento', 'MONITORING', ConsumableType::Service->value, '#3b82f6', 'lucide:eye'],
			['Backup e DR', 'BACKUP', ConsumableType::Service->value, '#3b82f6', 'lucide:save'],
			['Sustentação de Sistemas', 'SUSTAIN', ConsumableType::Service->value, '#3b82f6', 'lucide:refresh-cw'],
		];

		// Digital Creative Categories (25)
		$digitalCategories = [
			['Design Gráfico', 'GRAPH_DES', ConsumableType::Service->value, '#8b5cf6', 'lucide:palette'],
			['UI/UX Design', 'UIUX', ConsumableType::Service->value, '#8b5cf6', 'lucide:layout'],
			['Motion Design', 'MOTION', ConsumableType::Service->value, '#8b5cf6', 'lucide:film'],
			['Edição de Vídeo', 'VIDEO_EDIT', ConsumableType::Service->value, '#8b5cf6', 'lucide:video'],
			['Produção de Áudio', 'AUDIO_PROD', ConsumableType::Service->value, '#8b5cf6', 'lucide:music'],
			['Fotografia', 'PHOTO', ConsumableType::Service->value, '#8b5cf6', 'lucide:camera'],
			['Ilustração', 'ILLUSTRATION', ConsumableType::Service->value, '#8b5cf6', 'lucide:pen-tool'],
			['Marketing Digital', 'DIGITAL_MKT', ConsumableType::Service->value, '#8b5cf6', 'lucide:megaphone'],
			['Mídias Sociais', 'SOCIAL_MEDIA', ConsumableType::Service->value, '#8b5cf6', 'lucide:share-2'],
			['SEO', 'SEO', ConsumableType::Service->value, '#8b5cf6', 'lucide:search'],
			['Tráfego Pago', 'PPC', ConsumableType::Service->value, '#8b5cf6', 'lucide:dollar-sign'],
			['E-commerce', 'ECOMMERCE', ConsumableType::Service->value, '#8b5cf6', 'lucide:shopping-bag'],
			['Conteúdo Digital', 'DIGITAL_CONT', ConsumableType::Service->value, '#8b5cf6', 'lucide:file-text'],
			['Copywriting', 'COPYWRITING', ConsumableType::Service->value, '#8b5cf6', 'lucide:type'],
			['Branding', 'BRANDING', ConsumableType::Service->value, '#8b5cf6', 'lucide:star'],
			['Web Design', 'WEB_DESIGN', ConsumableType::Service->value, '#8b5cf6', 'lucide:globe'],
			['Animação', 'ANIMATION', ConsumableType::Service->value, '#8b5cf6', 'lucide:play-circle'],
			['Realidade Virtual/Aumentada', 'VR_AR', ConsumableType::Service->value, '#8b5cf6', 'lucide:glasses'],
			['Game Design', 'GAME_DESIGN', ConsumableType::Service->value, '#8b5cf6', 'lucide:gamepad-2'],
			['3D Modeling', '3D_MODEL', ConsumableType::Service->value, '#8b5cf6', 'lucide:box'],
			['Arte Digital', 'DIGITAL_ART', ConsumableType::Service->value, '#8b5cf6', 'lucide:brush'],
			['Produção de Conteúdo', 'CONTENT_PROD', ConsumableType::Service->value, '#8b5cf6', 'lucide:feather'],
			['Gestão de Comunidade', 'COMM_MGMT', ConsumableType::Service->value, '#8b5cf6', 'lucide:users'],
			['Analytics Digital', 'DIGITAL_ANAL', ConsumableType::Service->value, '#8b5cf6', 'lucide:bar-chart'],
			['Estratégia Digital', 'DIGITAL_STRAT', ConsumableType::Service->value, '#8b5cf6', 'lucide:target'],
		];

		// HR Categories (20)
		$hrCategories = [
			['Recrutamento e Seleção', 'RECRUIT', ConsumableType::Service->value, '#10b981', 'lucide:users'],
			['Treinamento e Desenvolvimento', 'TRAINING', ConsumableType::Service->value, '#10b981', 'lucide:book-open'],
			['Departamento Pessoal', 'DP', ConsumableType::Service->value, '#10b981', 'lucide:file-text'],
			['Benefícios', 'BENEFITS', ConsumableType::Service->value, '#10b981', 'lucide:gift'],
			['Remuneração', 'COMPENSATION', ConsumableType::Service->value, '#10b981', 'lucide:dollar-sign'],
			['Clima Organizacional', 'ORG_CLIMATE', ConsumableType::Service->value, '#10b981', 'lucide:thermometer'],
			['Compliance Trabalhista', 'LABOR_COMP', ConsumableType::Service->value, '#10b981', 'lucide:scale'],
			['Saúde Ocupacional', 'OCC_HEALTH', ConsumableType::Service->value, '#10b981', 'lucide:heart'],
			['Segurança do Trabalho', 'WORK_SAFETY', ConsumableType::Service->value, '#10b981', 'lucide:shield'],
			['Gestão de Talentos', 'TALENT_MGMT', ConsumableType::Service->value, '#10b981', 'lucide:award'],
			['Desenvolvimento Organizacional', 'ORG_DEV', ConsumableType::Service->value, '#10b981', 'lucide:trending-up'],
			['RH Estratégico', 'STRAT_HR', ConsumableType::Service->value, '#10b981', 'lucide:target'],
			['Plano de Carreira', 'CAREER_PLAN', ConsumableType::Service->value, '#10b981', 'lucide:map'],
			['Avaliação de Desempenho', 'PERF_REVIEW', ConsumableType::Service->value, '#10b981', 'lucide:chart-bar'],
			['Onboarding', 'ONBOARDING', ConsumableType::Service->value, '#10b981', 'lucide:user-plus'],
			['Offboarding', 'OFFBOARDING', ConsumableType::Service->value, '#10b981', 'lucide:user-minus'],
			['Folha de Pagamento', 'PAYROLL', ConsumableType::Service->value, '#10b981', 'lucide:calculator'],
			['Consultoria em RH', 'HR_CONS', ConsumableType::Service->value, '#10b981', 'lucide:message-square'],
			['Eventos Corporativos', 'CORP_EVENTS', ConsumableType::Service->value, '#10b981', 'lucide:calendar'],
			['Uniformização', 'UNIFORMS', ConsumableType::Product->value, '#10b981', 'lucide:shirt'],
		];

		// Business & Finance Categories (44)
		$businessCategories = [
			['Consultoria Empresarial', 'BUS_CONS', ConsumableType::Service->value, '#f59e0b', 'lucide:briefcase'],
			['Planejamento Estratégico', 'STRAT_PLAN', ConsumableType::Service->value, '#f59e0b', 'lucide:target'],
			['Análise de Mercado', 'MARKET_ANAL', ConsumableType::Service->value, '#f59e0b', 'lucide:bar-chart'],
			['Gestão de Projetos', 'PROJ_MGMT', ConsumableType::Service->value, '#f59e0b', 'lucide:clipboard-list'],
			['Gestão de Processos', 'PROC_MGMT', ConsumableType::Service->value, '#f59e0b', 'lucide:git-merge'],
			['Inovação', 'INNOVATION', ConsumableType::Service->value, '#f59e0b', 'lucide:lightbulb'],
			['Transformação Digital', 'DIGITAL_TRANS', ConsumableType::Service->value, '#f59e0b', 'lucide:refresh-cw'],
			['Contabilidade', 'ACCOUNTING', ConsumableType::Service->value, '#84cc16', 'lucide:calculator'],
			['Auditoria', 'AUDIT', ConsumableType::Service->value, '#84cc16', 'lucide:search'],
			['Controladoria', 'CONTROLLER', ConsumableType::Service->value, '#84cc16', 'lucide:pie-chart'],
			['Tesouraria', 'TREASURY', ConsumableType::Service->value, '#84cc16', 'lucide:wallet'],
			['Finanças Corporativas', 'CORP_FIN', ConsumableType::Service->value, '#84cc16', 'lucide:trending-up'],
			['Análise Financeira', 'FIN_ANAL', ConsumableType::Service->value, '#84cc16', 'lucide:chart-line'],
			['Planejamento Financeiro', 'FIN_PLAN', ConsumableType::Service->value, '#84cc16', 'lucide:calendar'],
			['Orçamento', 'BUDGET', ConsumableType::Service->value, '#84cc16', 'lucide:dollar-sign'],
			['Custos', 'COSTING', ConsumableType::Service->value, '#84cc16', 'lucide:file-text'],
			['Investimentos', 'INVESTMENTS', ConsumableType::Service->value, '#84cc16', 'lucide:trending-up'],
			['Crédito', 'CREDIT', ConsumableType::Service->value, '#84cc16', 'lucide:credit-card'],
			['Câmbio', 'FOREX', ConsumableType::Service->value, '#84cc16', 'lucide:globe'],
			['Comércio Exterior', 'FOREIGN_TRADE', ConsumableType::Service->value, '#84cc16', 'lucide:package'],
			['Tributação', 'TAX', ConsumableType::Service->value, '#84cc16', 'lucide:scale'],
			['Fiscal', 'FISCAL', ConsumableType::Service->value, '#84cc16', 'lucide:file-text'],
			['Legalização', 'LEGALIZATION', ConsumableType::Service->value, '#84cc16', 'lucide:file-check'],
			['Certificações', 'CERTIFICATIONS', ConsumableType::Service->value, '#84cc16', 'lucide:award'],
			['Qualidade', 'QUALITY', ConsumableType::Service->value, '#84cc16', 'lucide:check-circle'],
			['Conformidade', 'COMPLIANCE', ConsumableType::Service->value, '#84cc16', 'lucide:shield'],
			['Riscos', 'RISK', ConsumableType::Service->value, '#84cc16', 'lucide:alert-triangle'],
			['Fusões e Aquisições', 'M_A', ConsumableType::Service->value, '#84cc16', 'lucide:git-merge'],
			['Due Diligence', 'DUE_DILIG', ConsumableType::Service->value, '#84cc16', 'lucide:search'],
			['Reestruturação', 'RESTRUCT', ConsumableType::Service->value, '#84cc16', 'lucide:refresh-cw'],
			['ERP', 'ERP', ConsumableType::Service->value, '#84cc16', 'lucide:database'],
			['CRM', 'CRM', ConsumableType::Service->value, '#84cc16', 'lucide:users'],
			['BI', 'BI', ConsumableType::Service->value, '#84cc16', 'lucide:bar-chart'],
			['BPO Financeiro', 'BPO_FIN', ConsumableType::Service->value, '#84cc16', 'lucide:external-link'],
			['Consultoria Fiscal', 'TAX_CONS', ConsumableType::Service->value, '#84cc16', 'lucide:message-square'],
			['Assessoria Financeira', 'FIN_ADV', ConsumableType::Service->value, '#84cc16', 'lucide:briefcase'],
			['Análise de Crédito', 'CREDIT_ANAL', ConsumableType::Service->value, '#84cc16', 'lucide:credit-card'],
			['Recuperação de Créditos', 'CREDIT_RECOV', ConsumableType::Service->value, '#84cc16', 'lucide:refresh-ccw'],
			['Planejamento Tributário', 'TAX_PLAN', ConsumableType::Service->value, '#84cc16', 'lucide:calendar'],
			['Controle Interno', 'INT_CONTROL', ConsumableType::Service->value, '#84cc16', 'lucide:shield'],
			['Auditoria Interna', 'INT_AUDIT', ConsumableType::Service->value, '#84cc16', 'lucide:search'],
			['Relatórios Gerenciais', 'MGMT_REPORTS', ConsumableType::Service->value, '#84cc16', 'lucide:file-text'],
			['FP&A', 'FP_A', ConsumableType::Service->value, '#84cc16', 'lucide:pie-chart'],
			['Contabilidade Gerencial', 'MGMT_ACC', ConsumableType::Service->value, '#84cc16', 'lucide:calculator'],
		];

		// Sales Categories (20)
		$salesCategories = [
			['Vendas Diretas', 'DIRECT_SALES', ConsumableType::Service->value, '#ef4444', 'lucide:megaphone'],
			['Vendas Indiretas', 'INDIRECT_SALES', ConsumableType::Service->value, '#ef4444', 'lucide:users'],
			['Vendas Corporativas', 'CORP_SALES', ConsumableType::Service->value, '#ef4444', 'lucide:building'],
			['Vendas no Varejo', 'RETAIL_SALES', ConsumableType::Service->value, '#ef4444', 'lucide:store'],
			['Vendas no Atacado', 'WHOLESALE', ConsumableType::Service->value, '#ef4444', 'lucide:package'],
			['Pré-vendas', 'PRESALES', ConsumableType::Service->value, '#ef4444', 'lucide:message-square'],
			['Pós-vendas', 'POSTSALES', ConsumableType::Service->value, '#ef4444', 'lucide:headphones'],
			['Customer Success', 'CUST_SUCCESS', ConsumableType::Service->value, '#ef4444', 'lucide:thumbs-up'],
			['Gestão de Contas', 'ACCOUNT_MGMT', ConsumableType::Service->value, '#ef4444', 'lucide:user-check'],
			['Business Development', 'BIZ_DEV', ConsumableType::Service->value, '#ef4444', 'lucide:trending-up'],
			['Inside Sales', 'INSIDE_SALES', ConsumableType::Service->value, '#ef4444', 'lucide:phone'],
			['Field Sales', 'FIELD_SALES', ConsumableType::Service->value, '#ef4444', 'lucide:truck'],
			['Channel Sales', 'CHANNEL_SALES', ConsumableType::Service->value, '#ef4444', 'lucide:git-branch'],
			['Key Accounts', 'KEY_ACCOUNTS', ConsumableType::Service->value, '#ef4444', 'lucide:key'],
			['Sales Ops', 'SALES_OPS', ConsumableType::Service->value, '#ef4444', 'lucide:settings'],
			['Sales Enablement', 'SALES_ENABLE', ConsumableType::Service->value, '#ef4444', 'lucide:book-open'],
			['Prospecção', 'PROSPECTING', ConsumableType::Service->value, '#ef4444', 'lucide:search'],
			['Negociação', 'NEGOTIATION', ConsumableType::Service->value, '#ef4444', 'lucide:handshake'],
			['Fechamento', 'CLOSING', ConsumableType::Service->value, '#ef4444', 'lucide:check-circle'],
			['Relacionamento', 'RELATIONSHIP', ConsumableType::Service->value, '#ef4444', 'lucide:heart'],
		];

		// Administration Categories (20)
		$adminCategories = [
			['Administração Geral', 'GEN_ADMIN', ConsumableType::Service->value, '#6b7280', 'lucide:clipboard'],
			['Facilities', 'FACILITIES', ConsumableType::Service->value, '#6b7280', 'lucide:building'],
			['Recepção', 'RECEPTION', ConsumableType::Service->value, '#6b7280', 'lucide:user'],
			['Telefonia', 'TELEPHONY', ConsumableType::Service->value, '#6b7280', 'lucide:phone'],
			['Correspondência', 'MAIL', ConsumableType::Service->value, '#6b7280', 'lucide:mail'],
			['Arquivo', 'ARCHIVE', ConsumableType::Service->value, '#6b7280', 'lucide:folder'],
			['Protocolo', 'PROTOCOL', ConsumableType::Service->value, '#6b7280', 'lucide:file-text'],
			['Suprimentos', 'SUPPLIES', ConsumableType::Service->value, '#6b7280', 'lucide:package'],
			['Almoxarifado', 'WAREHOUSE', ConsumableType::Service->value, '#6b7280', 'lucide:warehouse'],
			['Transporte', 'TRANSPORT', ConsumableType::Service->value, '#6b7280', 'lucide:truck'],
			['Frota', 'FLEET', ConsumableType::Service->value, '#6b7280', 'lucide:car'],
			['Manutenção Predial', 'BUILD_MAINT', ConsumableType::Service->value, '#6b7280', 'lucide:wrench'],
			['Limpeza', 'CLEANING', ConsumableType::Service->value, '#6b7280', 'lucide:sparkles'],
			['Jardinagem', 'GARDENING', ConsumableType::Service->value, '#6b7280', 'lucide:tree'],
			['Segurança Patrimonial', 'PATRIM_SEC', ConsumableType::Service->value, '#6b7280', 'lucide:shield'],
			['Controle de Acesso', 'ACCESS_CTRL', ConsumableType::Service->value, '#6b7280', 'lucide:lock'],
			['Copa', 'PANTRY', ConsumableType::Service->value, '#6b7280', 'lucide:coffee'],
			['Eventos Internos', 'INT_EVENTS', ConsumableType::Service->value, '#6b7280', 'lucide:calendar'],
			['Viagens', 'TRAVEL', ConsumableType::Service->value, '#6b7280', 'lucide:plane'],
			['Serviços Gerais', 'GEN_SERVICES', ConsumableType::Service->value, '#6b7280', 'lucide:tool'],
		];

		// Clerks Categories (15)
		$clerkCategories = [
			['Escritório', 'OFFICE', ConsumableType::Product->value, '#f97316', 'lucide:package'],
			['Papelaria', 'STATIONERY', ConsumableType::Product->value, '#f97316', 'lucide:pen-tool'],
			['Material de Escritório', 'OFFICE_SUP', ConsumableType::Product->value, '#f97316', 'lucide:clipboard'],
			['Arquivamento', 'FILING', ConsumableType::Product->value, '#f97316', 'lucide:folder'],
			['Organização', 'ORGANIZATION', ConsumableType::Product->value, '#f97316', 'lucide:layout'],
			['Comunicação Interna', 'INT_COMM', ConsumableType::Service->value, '#f97316', 'lucide:message-square'],
			['Atendimento', 'SERVICE_DESK', ConsumableType::Service->value, '#f97316', 'lucide:headphones'],
			['Processamento', 'PROCESSING', ConsumableType::Service->value, '#f97316', 'lucide:file-text'],
			['Digitação', 'TYPING', ConsumableType::Service->value, '#f97316', 'lucide:type'],
			['Conferência', 'CHECKING', ConsumableType::Service->value, '#f97316', 'lucide:check-square'],
			['Almoxarife', 'STOREKEEPER', ConsumableType::Service->value, '#f97316', 'lucide:package'],
			['Auxiliar Administrativo', 'ADMIN_ASSIST', ConsumableType::Service->value, '#f97316', 'lucide:user'],
			['Secretariado', 'SECRETARIAL', ConsumableType::Service->value, '#f97316', 'lucide:file-text'],
			['Assistente Executivo', 'EXEC_ASSIST', ConsumableType::Service->value, '#f97316', 'lucide:briefcase'],
			['Office Boy', 'OFFICE_BOY', ConsumableType::Service->value, '#f97316', 'lucide:truck'],
		];

		// Cleaning Categories (15)
		$cleaningCategories = [
			['Limpeza Comercial', 'COMM_CLEAN', ConsumableType::Service->value, '#14b8a6', 'lucide:sparkles'],
			['Limpeza Industrial', 'IND_CLEAN', ConsumableType::Service->value, '#14b8a6', 'lucide:factory'],
			['Limpeza Residencial', 'RES_CLEAN', ConsumableType::Service->value, '#14b8a6', 'lucide:home'],
			['Higienização', 'SANITIZATION', ConsumableType::Service->value, '#14b8a6', 'lucide:droplets'],
			['Desinfecção', 'DISINFECTION', ConsumableType::Service->value, '#14b8a6', 'lucide:shield'],
			['Limpeza de Vidros', 'GLASS_CLEAN', ConsumableType::Service->value, '#14b8a6', 'lucide:window'],
			['Limpeza de Estofados', 'UPHOLSTERY', ConsumableType::Service->value, '#14b8a6', 'lucide:sofa'],
			['Limpeza de Tapetes', 'CARPET_CLEAN', ConsumableType::Service->value, '#14b8a6', 'lucide:grid'],
			['Produtos de Limpeza', 'CLEAN_PROD', ConsumableType::Product->value, '#14b8a6', 'lucide:droplet'],
			['Equipamentos de Limpeza', 'CLEAN_EQUIP', ConsumableType::Product->value, '#14b8a6', 'lucide:vacuum-cleaner'],
			['EPIs para Limpeza', 'CLEAN_PPE', ConsumableType::Product->value, '#14b8a6', 'lucide:shield'],
			['Coleta de Lixo', 'WASTE_COLLECT', ConsumableType::Service->value, '#14b8a6', 'lucide:trash-2'],
			['Descarte de Resíduos', 'WASTE_DISPOSAL', ConsumableType::Service->value, '#14b8a6', 'lucide:trash'],
			['Zeladoria', 'CUSTODIAL', ConsumableType::Service->value, '#14b8a6', 'lucide:building'],
			['Conservação', 'CONSERVATION', ConsumableType::Service->value, '#14b8a6', 'lucide:shield'],
		];

		// Healthcare Categories (20)
		$healthCategories = [
			['Consultas Médicas', 'MED_CONSULT', ConsumableType::Service->value, '#ec4899', 'lucide:heart'],
			['Exames', 'EXAMS', ConsumableType::Service->value, '#ec4899', 'lucide:activity'],
			['Laboratório', 'LAB', ConsumableType::Service->value, '#ec4899', 'lucide:flask'],
			['Imagens', 'IMAGING', ConsumableType::Service->value, '#ec4899', 'lucide:scan'],
			['Fisioterapia', 'PHYSIOTHERAPY', ConsumableType::Service->value, '#ec4899', 'lucide:activity'],
			['Nutrição', 'NUTRITION', ConsumableType::Service->value, '#ec4899', 'lucide:apple'],
			['Psicologia', 'PSYCHOLOGY', ConsumableType::Service->value, '#ec4899', 'lucide:brain'],
			['Odontologia', 'DENTISTRY', ConsumableType::Service->value, '#ec4899', 'lucide:tooth'],
			['Farmácia', 'PHARMACY', ConsumableType::Service->value, '#ec4899', 'lucide:pills'],
			['Enfermagem', 'NURSING', ConsumableType::Service->value, '#ec4899', 'lucide:stethoscope'],
			['Primeiros Socorros', 'FIRST_AID', ConsumableType::Service->value, '#ec4899', 'lucide:plus-circle'],
			['Check-up', 'CHECKUP', ConsumableType::Service->value, '#ec4899', 'lucide:check-square'],
			['Vacinação', 'VACCINATION', ConsumableType::Service->value, '#ec4899', 'lucide:syringe'],
			['Saúde Ocupacional', 'OCC_HEALTH', ConsumableType::Service->value, '#ec4899', 'lucide:briefcase'],
			['Ginástica Laboral', 'WORK_GYM', ConsumableType::Service->value, '#ec4899', 'lucide:dumbbell'],
			['Medicamentos', 'MEDICINES', ConsumableType::Product->value, '#ec4899', 'lucide:pills'],
			['Equipamentos Médicos', 'MED_EQUIP', ConsumableType::Product->value, '#ec4899', 'lucide:thermometer'],
			['Instrumentos', 'INSTRUMENTS', ConsumableType::Product->value, '#ec4899', 'lucide:scalpel'],
			['Materiais Hospitalares', 'HOSP_SUPPLIES', ConsumableType::Product->value, '#ec4899', 'lucide:package'],
			['EPIs Hospitalares', 'HOSP_PPE', ConsumableType::Product->value, '#ec4899', 'lucide:shield'],
		];

		// Legal Categories (20)
		$legalCategories = [
			['Direito Trabalhista', 'LABOR_LAW', ConsumableType::Service->value, '#a855f7', 'lucide:scale'],
			['Direito Civil', 'CIVIL_LAW', ConsumableType::Service->value, '#a855f7', 'lucide:gavel'],
			['Direito Empresarial', 'BUSINESS_LAW', ConsumableType::Service->value, '#a855f7', 'lucide:building'],
			['Direito Tributário', 'TAX_LAW', ConsumableType::Service->value, '#a855f7', 'lucide:dollar-sign'],
			['Direito Contratual', 'CONTRACT_LAW', ConsumableType::Service->value, '#a855f7', 'lucide:file-text'],
			['Direito Imobiliário', 'REAL_ESTATE_LAW', ConsumableType::Service->value, '#a855f7', 'lucide:home'],
			['Propriedade Intelectual', 'IP_LAW', ConsumableType::Service->value, '#a855f7', 'lucide:lightbulb'],
			['Compliance', 'COMPLIANCE', ConsumableType::Service->value, '#a855f7', 'lucide:shield'],
			['LGPD', 'LGPD', ConsumableType::Service->value, '#a855f7', 'lucide:lock'],
			['Contencioso', 'LITIGATION', ConsumableType::Service->value, '#a855f7', 'lucide:gavel'],
			['Consultoria Jurídica', 'LEGAL_CONS', ConsumableType::Service->value, '#a855f7', 'lucide:message-square'],
			['Assessoria Jurídica', 'LEGAL_ADV', ConsumableType::Service->value, '#a855f7', 'lucide:briefcase'],
			['Due Diligence Jurídica', 'LEGAL_DD', ConsumableType::Service->value, '#a855f7', 'lucide:search'],
			['Registro de Marcas', 'TRADEMARK', ConsumableType::Service->value, '#a855f7', 'lucide:registered'],
			['Patentes', 'PATENTS', ConsumableType::Service->value, '#a855f7', 'lucide:lightbulb'],
			['Direitos Autorais', 'COPYRIGHTS', ConsumableType::Service->value, '#a855f7', 'lucide:pen-tool'],
			['Certidões', 'CERTIFICATES', ConsumableType::Service->value, '#a855f7', 'lucide:file-check'],
			['Autenticações', 'AUTHENTICATIONS', ConsumableType::Service->value, '#a855f7', 'lucide:stamp'],
			['Procurações', 'POWERS_ATTORNEY', ConsumableType::Service->value, '#a855f7', 'lucide:user-check'],
			['Legalização de Documentos', 'DOC_LEGAL', ConsumableType::Service->value, '#a855f7', 'lucide:file-text'],
		];

		// Gastronomy Categories (16)
		$gastronomyCategories = [
			['Restaurante', 'RESTAURANT', ConsumableType::Service->value, '#f97316', 'lucide:utensils'],
			['Catering', 'CATERING', ConsumableType::Service->value, '#f97316', 'lucide:truck'],
			['Coffee Break', 'COFFEE_BREAK', ConsumableType::Service->value, '#f97316', 'lucide:coffee'],
			['Alimentação', 'FOOD', ConsumableType::Service->value, '#f97316', 'lucide:apple'],
			['Bebidas', 'BEVERAGES', ConsumableType::Service->value, '#f97316', 'lucide:glass-water'],
			['Confeitaria', 'CONFECTIONERY', ConsumableType::Service->value, '#f97316', 'lucide:cake'],
			['Panificação', 'BAKERY', ConsumableType::Service->value, '#f97316', 'lucide:bread'],
			['Buffet', 'BUFFET', ConsumableType::Service->value, '#f97316', 'lucide:chef-hat'],
			['Alimentos', 'FOOD_PROD', ConsumableType::Product->value, '#f97316', 'lucide:carrot'],
			['Ingredientes', 'INGREDIENTS', ConsumableType::Product->value, '#f97316', 'lucide:flask'],
			['Utensílios de Cozinha', 'KITCHEN_TOOLS', ConsumableType::Product->value, '#f97316', 'lucide:utensils-crossed'],
			['Equipamentos de Cozinha', 'KITCHEN_EQUIP', ConsumableType::Product->value, '#f97316', 'lucide:microwave'],
			['Mobiliário para Restaurante', 'REST_FURN', ConsumableType::Product->value, '#f97316', 'lucide:chair'],
			['Descartáveis', 'DISPOSABLES', ConsumableType::Product->value, '#f97316', 'lucide:cup'],
			['Água e Bebidas', 'DRINKS', ConsumableType::Product->value, '#f97316', 'lucide:bottle'],
			['Serviços de Mesa', 'TABLE_SERVICE', ConsumableType::Service->value, '#f97316', 'lucide:users'],
		];

		// Combine all categories
		$allCategories = array_merge(
			$techCategories,
			$digitalCategories,
			$hrCategories,
			$businessCategories,
			$salesCategories,
			$adminCategories,
			$clerkCategories,
			$cleaningCategories,
			$healthCategories,
			$legalCategories,
			$gastronomyCategories
		);

		// Add all categories to catalog
		foreach ($allCategories as $category) {
			$catalog[] = [
				'name'        => $category[0],
				'code'        => $category[1],
				DC::COL_TP_LB => $category[2],
				'color'       => $category[3],
				'icon'        => $category[4],
			];
		}

		// Add typed generics
		$typedGenerics = [
			[
				'name'        => 'Receitas Diversas',
				'code'        => 'INCOME_MISC',
				DC::COL_TP_LB => ConsumableType::Income->value,
				'color'       => '#22c55e',
				'icon'        => 'lucide:trending-up',
			],
			[
				'name'        => 'Despesas Operacionais',
				'code'        => 'OPER_EXP',
				DC::COL_TP_LB => ConsumableType::Expense->value,
				'color'       => '#ef4444',
				'icon'        => 'lucide:trending-down',
			],
			[
				'name'        => 'Ativos',
				'code'        => 'ASSETS',
				DC::COL_TP_LB => ConsumableType::Asset->value,
				'color'       => '#14b8a6',
				'icon'        => 'lucide:wallet',
			],
			[
				'name'        => 'Passivos',
				'code'        => 'LIABIL',
				DC::COL_TP_LB => ConsumableType::Liability->value,
				'color'       => '#a855f7',
				'icon'        => 'lucide:scale',
			],
			[
				'name'        => 'Patrimônio Líquido',
				'code'        => 'EQUITY',
				DC::COL_TP_LB => ConsumableType::Equity->value,
				'color'       => '#8b5cf6',
				'icon'        => 'lucide:banknote',
			],
			[
				'name'        => 'Custos de Mercadorias/Serviços Vendidos',
				'code'        => 'COGS',
				DC::COL_TP_LB => ConsumableType::CostsOfGoodsSold->value,
				'color'       => '#f97316',
				'icon'        => 'lucide:factory',
			],
			[
				'name'        => 'Outros',
				'code'        => 'OTHER',
				DC::COL_TP_LB => ConsumableType::Other->value,
				'color'       => '#6b7280',
				'icon'        => 'lucide:circle',
			],
		];

		foreach ($typedGenerics as $item) {
			$catalog[] = $item;
		}

		// Ensure we have exactly 256 items
		$totalItems = count($catalog);
		if ($totalItems < 256) {
			$additional = 256 - $totalItems;
			for ($i = 0; $i < $additional; $i++) {
				$catalog[] = [
					'name'        => 'Categoria Adicional ' . ($i + 1),
					'code'        => 'CAT_' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
					DC::COL_TP_LB => ConsumableType::Other->value,
					'color'       => '#9ca3af',
					'icon'        => 'lucide:plus',
				];
			}
		} elseif ($totalItems > 256) {
			$catalog = array_slice($catalog, 0, 256);
		}

		// Verify structure
		echo "Total de categorias: " . count($catalog) . "\n";
		echo "Estrutura válida: " . (count($catalog) === 256 ? 'Sim' : 'Não') . "\n";

		foreach (array_merge($catalog, $typedGenerics) as $row) {
			try {
				(new \Symfony\Component\Console\Output\ConsoleOutput
				)->writeln("Criando Categoria de Produto/Serviço: {$row['name']}");
				$label = $row[DC::COL_TP_LB];
				$typeIndex = $labelIndex[$label] ?? 0;

				ProductServiceCategory::updateOrCreate(
					['code' => $row['code']],
					[
						// Mantém ID estável em primeira criação; em updates, o DB ignorará mudança de PK
						'id'            => (string) Str::uuid(),
						'name'          => $row['name'],
						// Índice compatível (0..9) alinhado ao label escolhido
						'type'          => $typeIndex,
						DC::COL_TP_LB   => $label,
						BKC::COL_COA    => null,
						'color'         => $row['color'] ?? '#fc544b',
						'icon'          => $row['icon'] ?? null,
						'attributes'    => [
							'visibility'  => 'public',
							'label_key'   => $label,
							'label_index' => $typeIndex,
							'tags'        => [$row['code'], 'catalog'],
						],
						'description'   => $faker->sentences(2, true),
						DC::COL_RL_CAT  => [],
						'notes'         => $faker->sentence(),
						AC::COL_IA      => true,
					]
				);
			} catch (\Exception $e) {
				Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
				continue;
			}
		}
	}
}
