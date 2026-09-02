/*=========================================================================================
  File Name: sidebarItems.js
  Description: Sidebar Items list. Add / Remove menu items from here.
  Strucutre:
		  url     => router path
		  name    => name to display in sidebar
		  slug    => router path name
		  icon    => Feather Icon component/icon name
		  tag     => text to display on badge
		  tagColor  => class to apply on badge element
		  i18n    => Internationalization
		  submenu   => submenu of current item (current item will become dropdown )
				NOTE: Submenu don't have any icon(you can add icon if u want to display)
		  isDisabled  => disable sidebar item/group
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/

import store from '@/store/store'

let navAdmin = [];
let navCliente = [];
let navMenuItems = [];

//Painel do Adminitrador.
if (store.state.AppActiveUser.userRole == 'admin') {

	navAdmin = [
		{
			url: '/',
			name: "Home",
			icon: "HomeIcon",
			slug: "home",
		},
		{
			url: null,
			name: "Cadastros",
			icon: "ArchiveIcon",
			submenu: [
				{
					url: "/cliente",
					name: "Clientes",
					slug: "cadastros-clientes",
				},
				{
					url: '/motorista',
					name: "Motoristas",
					slug: "cadastros-motoristas",
				},
				{
					url: '/tipo-veiculo',
					name: "Tipos de Veículo",
					slug: "cadastros-tipos-veiculos",
				},
				{
					url: '/veiculo',
					name: "Veículos",
					slug: "cadastros-veiculos",
				},
			]
		},
		{
			url: null,
			name: "Atendimento",
			icon: "HeadphonesIcon",
			submenu: [
				{
					url: "/buscar-veiculos",
					name: "Buscar Veículos",
					slug: "atendimento-buscar-veiculos",
				},
				{
					url: '/coleta-web',
					name: "Solicitar Coletas",
					slug: "atendimento-solicitar-coletas",
				},
				{
					url: '/distribuicao-entregas',
					name: "Distribuição",
					slug: "atendimento-distribuicao-entregas",
				},
				{
					url: '/definir-reentrega',
					name: "Reentrega",
					slug: "atendimento-definir-reentrega",
				},
			]
		},
		{
			url: '/controle',
			name: "Controle",
			slug: "controle",
			icon: "MonitorIcon",
		},
		{
			url: '/operacao',
			name: "Operação",
			slug: "operacao",
			icon: "PackageIcon",
		},
		{
			url: '/resumo-dia',
			name: "Resumo do Dia",
			slug: "resumo-dia",
			icon: "LayersIcon",
		},
		{
			url: null,
			name: "Solicitações",
			icon: "ClipboardIcon",
			submenu: [
				{
					url: '/coleta-fixa',
					name: "Coletas Fixas",
					slug: "solicitacoes-coletas-fixas",
				},
				{
					url: '/coleta',
					name: "Arquivadas",
					slug: "solicitacoes-gerenciar-coletas",
				},
				{
					url: '/resumo-km-tempo',
					name: "Resumos Km + Hrs",
					slug: "solicitacoes-resumo-km-tempo",
				}
			]
		},
		{
			url: null,
			name: "Administrador",
			icon: "SettingsIcon",
			submenu: [
				{
					url: '/configuracao',
					name: "Configurações",
					slug: "administrador-configuracoes",
				},
				{
					url: '/distance-matrix',
					name: "Serviços API",
					slug: "administrador-distance-matrix",
				},
				{
					url: '/users',
					name: "Contas de Usuários",
					slug: "administrador-contas-de-usuarios",
				},
				{
					url: '/empresa',
					name: "Empresas",
					slug: "administrador-empresas",
				},
				{
					url: "/log-pro",
					name: "Logs",
					slug: "log-pro",
				},
				{
					url: "/sabedoria",
					name: "Sabedoria",
					slug: "wisdom",
				},
			]
		},
	];

	navMenuItems = navAdmin

}

//Painel do Cliente
if (store.state.AppActiveUser.userRole == 'cliente') {

	navCliente = [
		{
			url: '/dashboard-cliente',
			name: "Home",
			icon: "HomeIcon",
			slug: "home",
		},
		{
			url: '/coleta-web',
			name: "Solicitar Coletas",
			icon: "FilePlusIcon",
			slug: "atendimento-solicitar-coletas",
		},
		{
			url: '/coleta',
			name: "Arquivadas",
			icon: "ClipboardIcon",
			slug: "solicitacoes-gerenciar-coletas",
		},
		{
			url: '/users/' + store.state.AppActiveUser.uid,
			name: "Perfil",
			icon: "UserIcon",
			slug: "perfil",
		},
	];

	navMenuItems = navCliente

}

export default navMenuItems

