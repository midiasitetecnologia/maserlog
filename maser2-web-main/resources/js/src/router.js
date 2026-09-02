/*=========================================================================================
  File Name: router.js
  Description: Routes for vue-router. Lazy loading is enabled.
  Object Strucutre:
                    path => router path
                    name => router name
                    component(lazy loading) => component to load
                    meta : {
                      rule => which user can have access (ACL)
                      breadcrumb => Add breadcrumb to specific page
                      pageTitle => Display title besides breadcrumb
                    }
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/

import Vue from 'vue'
import Router from 'vue-router'
import store from './store/store'

Vue.use(Router)

const router = new Router({
    store,
    mode: 'history',
    base: process.env.BASE_URL,
    scrollBehavior() {
        return { x: 0, y: 0 }
    },
    routes: [

        {
            // =============================================================================
            // MAIN LAYOUT ROUTES
            // =============================================================================
            path: '',
            component: () => import('./layouts/main/Main.vue'),
            children: [
                {
                    path: '/',
                    redirect: '/dashboard'
                },
                {
                    path: '/dashboard',
                    name: 'dashboard',
                    component: () => import('./views/pages/dashboard/Dashboard.vue'),
                    meta: {
                        parent: "Home",
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/dashboard-cliente',
                    name: 'dashboard-cliente',
                    component: () => import('./views/pages/dashboard/DashboardCliente.vue'),
                    meta: {
                        parent: "Home",
                        rule: 'cliente',
                        authRequired: true
                    }
                },
                {
                    path: '/emitir-notas-fiscais',
                    name: 'emitir-notas-fiscais',
                    component: () => import('./views/pages/tarefas/EmitirNotasFiscais.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Emitir Notas Fiscais', active: true },
                        ],
                        pageTitle: 'Emitir Notas Fiscais',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/corrigir-cadastros',
                    name: 'corrigir-cadastros',
                    component: () => import('./views/pages/tarefas/CorrigirCadastros.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Corrigir Cadastros', active: true },
                        ],
                        pageTitle: 'Corrigir Cadastros',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/cliente',
                    name: 'cliente',
                    component: () => import('./views/pages/cliente/ClienteIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Clientes', active: true },
                        ],
                        pageTitle: 'Clientes',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/cliente/:id',
                    name: 'cliente-view',
                    component: () => import('./views/pages/cliente/ClienteView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Clientes', url: '/cliente' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "cliente",
                        pageTitle: 'Clientes',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/cliente/mapa/:nome/:lat/:lng',
                    name: 'cliente-mapa',
                    component: () => import('./views/pages/cliente/ClienteMap.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Clientes', url: '/cliente' },
                            { title: 'Localização', active: true },
                        ],
                        parent: "cliente",
                        pageTitle: 'Localização',
                        rule: 'admin'
                    },
                },
                {
                    path: '/motorista',
                    name: 'motorista',
                    component: () => import('./views/pages/motorista/MotoristaIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Motoristas', active: true },
                        ],
                        pageTitle: 'Motoristas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/motorista/:id/edit',
                    name: 'motorista-edit',
                    component: () => import('./views/pages/motorista/MotoristaEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Motoristas', url: '/motorista' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "cadastros-motoristas",
                        pageTitle: 'Motoristas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/motorista/:id',
                    name: 'motorista-view',
                    component: () => import('./views/pages/motorista/MotoristaView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Motoristas', url: '/motorista' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "cadastros-motoristas",
                        pageTitle: 'Motoristas',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/tipo-veiculo',
                    name: 'tipo-veiculo',
                    component: () => import('./views/pages/tipo-veiculo/TipoVeiculoIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Tipos de Veículo', active: true },
                        ],
                        pageTitle: 'Tipos de Veículo',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/tipo-veiculo/create',
                    name: 'tipo-veiculo-create',
                    component: () => import('./views/pages/tipo-veiculo/TipoVeiculoCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Tipos de Veículo', url: '/tipo-veiculo' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "cadastros-tipos-veiculos",
                        pageTitle: 'Tipos de Veículo',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/tipo-veiculo/:codigo/edit',
                    name: 'tipo-veiculo-edit',
                    component: () => import('./views/pages/tipo-veiculo/TipoVeiculoEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Tipos de Veículo', url: '/tipo-veiculo' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "cadastros-tipos-veiculos",
                        pageTitle: 'Tipos de Veículo',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/tipo-veiculo/:codigo',
                    name: 'tipo-veiculo-view',
                    component: () => import('./views/pages/tipo-veiculo/TipoVeiculoView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Tipos de Veículo', url: '/tipo-veiculo' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "cadastros-tipos-veiculos",
                        pageTitle: 'Tipos de Veículo',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/veiculo',
                    name: 'veiculo',
                    component: () => import('./views/pages/veiculo/VeiculoIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Veículos', active: true },
                        ],
                        pageTitle: 'Veículos',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/veiculo/create',
                    name: 'veiculo-create',
                    component: () => import('./views/pages/veiculo/VeiculoCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Veículos', url: '/veiculo' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "cadastros-veiculos",
                        pageTitle: 'Veículos',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/veiculo/:placa/edit',
                    name: 'veiculo-edit',
                    component: () => import('./views/pages/veiculo/VeiculoEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Veículos', url: '/veiculo' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "cadastros-veiculos",
                        pageTitle: 'Veículos',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/veiculo/:placa',
                    name: 'veiculo-view',
                    component: () => import('./views/pages/veiculo/VeiculoView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Veículos', url: '/veiculo' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "cadastros-veiculos",
                        pageTitle: 'Veículos',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/coleta-web',
                    name: 'coleta-web',
                    component: () => import('./views/pages/coleta-web/ColetaWebIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Solicitar Coletas', active: true },
                        ],
                        pageTitle: 'Solicitar Coletas',
                        rule: 'cliente',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-web/create',
                    name: 'coleta-web-create',
                    component: () => import('./views/pages/coleta-web/ColetaWebCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Solicitar Coletas', url: '/coleta-web' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "atendimento-solicitar-coletas",
                        pageTitle: 'Solicitar Coletas',
                        rule: 'cliente',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-web/:id/edit',
                    name: 'coleta-web-edit',
                    component: () => import('./views/pages/coleta-web/ColetaWebEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Solicitar Coletas', url: '/coleta-web' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "atendimento-solicitar-coletas",
                        pageTitle: 'Solicitar Coletas',
                        rule: 'cliente',
                        authRequired: true
                    }
                },
                {
                    path: '/distribuicao-entregas',
                    name: 'distribuicao-entregas',
                    component: () => import('./views/pages/tarefas/DistribuicaoEntregas.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Distribuição de Entregas', active: true },
                        ],
                        parent: "atendimento-distribuicao-entregas",
                        pageTitle: 'Distribuição de Entregas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/distribuir-entregas/:coleta_id',
                    name: 'distribuir-entregas',
                    component: () => import('./views/pages/tarefas/GerarSolicAuxMultiDestino.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Distribuição de Entregas', url: '/distribuicao-entregas' },
                            { title: 'Distribuir Entregas', active: true },
                        ],
                        parent: "atendimento-distribuicao-entregas",
                        pageTitle: 'Distribuir Entregas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/definir-reentrega',
                    name: 'definir-reentrega',
                    component: () => import('./views/pages/tarefas/ReentregaDevolucao.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Reentrega', active: true },
                        ],
                        parent: "atendimento-definir-reentrega",
                        pageTitle: 'Reentrega',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/definir-reentrega/:coleta_id',
                    name: 'definir-reentrega',
                    component: () => import('./views/pages/tarefas/GerarSolicReentrega.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Reentrega', url: '/definir-reentrega' },
                            { title: 'Definir Reentrega', active: true },
                        ],
                        parent: "atendimento-definir-reentrega",
                        pageTitle: 'Definir Reentrega',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-fixa',
                    name: 'coleta-fixa',
                    component: () => import('./views/pages/coleta-fixa/ColetaFixaIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', active: true },
                        ],
                        pageTitle: 'Coletas Fixas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-fixa/create',
                    name: 'coleta-fixa-create',
                    component: () => import('./views/pages/coleta-fixa/ColetaFixaCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', url: '/coleta-fixa' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "solicitacoes-coletas-fixas",
                        pageTitle: 'Coletas Fixas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-fixa/:id/edit',
                    name: 'coleta-fixa-edit',
                    component: () => import('./views/pages/coleta-fixa/ColetaFixaEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', url: '/coleta-fixa' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "solicitacoes-coletas-fixas",
                        pageTitle: 'Coletas Fixas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-fixa/:id',
                    name: 'coleta-fixa-view',
                    component: () => import('./views/pages/coleta-fixa/ColetaFixaView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', url: '/coleta-fixa' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "solicitacoes-coletas-fixas",
                        pageTitle: 'Coletas Fixas',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/coleta-fixa-bloq',
                    name: 'coleta-fixa-bloq',
                    component: () => import('./views/pages/coleta-fixa-bloq/ColetaFixaBloqIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', url: '/coleta-fixa' },
                            { title: 'Bloqueios', active: true },
                        ],
                        parent: "solicitacoes-coletas-fixas",
                        pageTitle: 'Coletas Fixas - Bloqueios',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-fixa-bloq/create/:coleta_fixa_id',
                    name: 'coleta-fixa-bloq-create',
                    component: () => import('./views/pages/coleta-fixa-bloq/ColetaFixaBloqCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', url: '/coleta-fixa' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "solicitacoes-coletas-fixas",
                        pageTitle: 'Coletas Fixas - Bloqueios',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-fixa-bloq/:id/edit',
                    name: 'coleta-fixa-bloq-edit',
                    component: () => import('./views/pages/coleta-fixa-bloq/ColetaFixaBloqEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', url: '/coleta-fixa' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "solicitacoes-coletas-fixas",
                        pageTitle: 'Coletas Fixas - Bloqueios',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta-fixa-bloq/:id',
                    name: 'coleta-fixa-bloq-view',
                    component: () => import('./views/pages/coleta-fixa-bloq/ColetaFixaBloqView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Coletas Fixas', url: '/coleta-fixa' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "solicitacoes-coletas-fixas",
                        pageTitle: 'Coletas Fixas - Bloqueios',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/coleta',
                    name: 'coleta',
                    component: () => import('./views/pages/coleta/ColetaIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Arquivadas', active: true },
                        ],
                        pageTitle: 'Arquivadas',
                        rule: 'cliente',
                        authRequired: true
                    }
                },
                {
                    path: '/coleta/:id',
                    name: 'coleta-view',
                    component: () => import('./views/pages/coleta/ColetaView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Gerenciar Coletas', url: '/coleta' },
                            { title: 'Visualização', active: true },
                        ],
                        //Deixar desabilitado, porque pode abrir a view pelo painel e pode confundir.
                        //parent: "solicitacoes-gerenciar-coletas",
                        pageTitle: 'Gerenciar Coletas',
                        rule: 'cliente',
                        authRequired: true
                    },
                },
                {
                    path: '/resumo-km-tempo',
                    name: 'resumo-km-tempo',
                    component: () => import('./views/pages/coleta/ResumoKmTempo.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Resumos Km+Hrs', active: true },
                        ],
                        //Deixar desabilitado, porque pode abrir a view pelo painel e pode confundir.
                        //parent: "solicitacoes-gerenciar-coletas",
                        pageTitle: 'Resumos Km + Hrs',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/buscar-veiculos',
                    name: 'buscar-veiculos',
                    component: () => import('./views/pages/atendimento/BuscarVeiculos.vue'),
                    meta: {
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/controle',
                    name: 'controle',
                    component: () => import('./views/pages/controle/Controle.vue'),
                    meta: {
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/operacao',
                    name: 'operacao',
                    component: () => import('./views/pages/operacao/Operacao.vue'),
                    meta: {
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/resumo-dia',
                    name: 'resumo-dia',
                    component: () => import('./views/pages/resumo-dia/ResumoDia.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },                            
                            { title: 'Resumo do Dia', active: true },
                        ],
                        pageTitle: 'Resumo do Dia',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/empresa',
                    name: 'empresa',
                    component: () => import('./views/pages/empresa/EmpresaIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Empresas', active: true },
                        ],
                        pageTitle: 'Empresas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/empresa/create',
                    name: 'empresa-create',
                    component: () => import('./views/pages/empresa/EmpresaCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Empresas', url: '/empresa' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "administrador-empresas",
                        pageTitle: 'Empresas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/empresa/:codigo/edit',
                    name: 'empresa-edit',
                    component: () => import('./views/pages/empresa/EmpresaEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Empresas', url: '/empresa' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "administrador-empresas",
                        pageTitle: 'Empresas',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/empresa/:codigo',
                    name: 'empresa-view',
                    component: () => import('./views/pages/empresa/EmpresaView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Empresas', url: '/empresa' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "administrador-empresas",
                        pageTitle: 'Empresas',
                        rule: 'admin',
                        authRequired: true
                    },
                },
                {
                    path: '/users',
                    name: 'users',
                    component: () => import('./views/pages/users/UsersIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Contas de Usuários', active: true },
                        ],
                        pageTitle: 'Contas de Usuários',
                        rule: 'cliente',
                        authRequired: true
                    }
                },
                {
                    path: '/users/create',
                    name: 'users-create',
                    component: () => import('./views/pages/users/UsersCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Contas de Usuários', url: '/users' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "administrador-contas-de-usuarios",
                        pageTitle: 'Contas de Usuários',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/users/:id/edit',
                    name: 'users-edit',
                    component: () => import('./views/pages/users/UsersEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Contas de Usuários', url: '/users' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "administrador-contas-de-usuarios",
                        pageTitle: 'Contas de Usuários',
                        rule: 'cliente',
                        authRequired: true
                    }
                },
                {
                    path: '/users/:id',
                    name: 'users-view',
                    component: () => import('./views/pages/users/UsersView.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Contas de Usuários', url: '/users' },
                            { title: 'Visualização', active: true },
                        ],
                        parent: "administrador-contas-de-usuarios",
                        pageTitle: 'Contas de Usuários',
                        rule: 'cliente',
                        authRequired: true
                    },
                },
                {
                    path: '/users_alterar_senha/:id',
                    name: 'users-alterar-senha',
                    component: () => import('./views/pages/users/UsersAlterarSenha.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Contas de Usuários', url: '/users' },
                            { title: 'Alterar Senha', active: true },
                        ],
                        parent: "administrador-contas-de-usuarios",
                        pageTitle: 'Contas de Usuários',
                        rule: 'cliente',
                        authRequired: true
                    },
                },                            
                {
                    path: '/log-pro',
                    name: 'log-pro',
                    component: () => import('./views/pages/log-pro/LogProIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Logs de Processamento', active: true },
                        ],
                        pageTitle: 'Logs de Processamento',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/sabedoria',
                    name: 'wisdom',
                    component: () => import('./views/pages/wisdom/WisdomIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Sabedoria', active: true },
                        ],
                        pageTitle: 'Sabedoria',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/sabedoria/create',
                    name: 'wisdom-create',
                    component: () => import('./views/pages/wisdom/WisdomCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Sabedoria', url: '/sabedoria' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "administrador-sabedoria",
                        pageTitle: 'Sabedoria',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/sabedoria/:id/edit',
                    name: 'wisdom-edit',
                    component: () => import('./views/pages/wisdom/WisdomEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Sabedoria', url: '/sabedoria' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "administrador-sabedoria",
                        pageTitle: 'Sabedoria',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/distance-matrix',
                    name: 'distance-matrix',
                    component: () => import('./views/pages/distance-matrix/DistanceMatrixIndex.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Serviços API', active: true },
                        ],
                        pageTitle: 'Serviços API',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/distance-matrix/create',
                    name: 'distance-matrix-create',
                    component: () => import('./views/pages/distance-matrix/DistanceMatrixCreate.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Serviços API', url: '/distance-matrix' },
                            { title: 'Novo', active: true },
                        ],
                        parent: "administrador-distance-matrix",
                        pageTitle: 'Serviços API',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/distance-matrix/:id/edit',
                    name: 'distance-matrix-edit',
                    component: () => import('./views/pages/distance-matrix/DistanceMatrixEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Serviços API', url: '/distance-matrix' },
                            { title: 'Editando', active: true },
                        ],
                        parent: "administrador-distance-matrix",
                        pageTitle: 'Serviços API',
                        rule: 'admin',
                        authRequired: true
                    }
                },
                {
                    path: '/configuracao',
                    name: 'configuracao',
                    component: () => import('./views/pages/sys-cfg/SysCfgCreateEdit.vue'),
                    meta: {
                        breadcrumb: [
                            { title: 'Home', url: '/' },
                            { title: 'Configurações', active: true },
                        ],
                        pageTitle: 'Configurações',
                        rule: 'admin',
                        authRequired: true
                    }
                },
            ],
        },

        // =============================================================================
        // FULL PAGE LAYOUTS
        // =============================================================================
        {
            path: '',
            component: () => import('@/layouts/full-page/FullPage.vue'),
            children: [
                // =============================================================================
                // PAGES
                // =============================================================================                
                {
                    path: '/login',
                    name: 'page-login',
                    component: () => import('@/views/pages/login/Login.vue'),
                    meta: {
                        rule: 'cliente'
                    }
                },
                {
                    path: '/exibirNotificacao/:tipo_mensagem/:texto_mensagem/:titulo',
                    name: 'page-exibir-notificacao',
                    component: () => import('@/views/pages/ExibirNotificacao.vue'),
                    meta: {
                        rule: 'cliente'
                    }
                },

                // Não estamos utilizando.
                // {
                //     path: '/pages/forgot-password',
                //     name: 'page-forgot-password',
                //     component: () => import('@/views/pages/ForgotPassword.vue'),
                //     meta: {
                //         rule: 'cliente'
                //     }
                // },

                // {
                //     path: '/pages/reset-password',
                //     name: 'page-reset-password',
                //     component: () => import('@/views/pages/ResetPassword.vue'),
                //     meta: {
                //         rule: 'cliente'
                //     }
                // },

                {
                    path: '/pages/error-404',
                    name: 'page-error-404',
                    component: () => import('@/views/pages/Error404.vue'),
                    meta: {
                        rule: 'cliente'
                    }
                },
                {
                    path: '/pages/error-500',
                    name: 'page-error-500',
                    component: () => import('@/views/pages/Error500.vue'),
                    meta: {
                        rule: 'cliente'
                    }
                },
                {
                    path: '/pages/not-authorized',
                    name: 'page-not-authorized',
                    component: () => import('@/views/pages/NotAuthorized.vue'),
                    meta: {
                        rule: 'cliente'
                    }
                },
                {
                    path: '/pages/maintenance',
                    name: 'page-maintenance',
                    component: () => import('@/views/pages/Maintenance.vue'),
                    meta: {
                        rule: 'cliente'
                    }
                },
            ]
        },
        // Redirect to 404 page, if no match found
        {
            path: '*',
            redirect: '/pages/error-404'
        }
    ],
})

router.afterEach(() => {
    // Remove initial loading
    const appLoading = document.getElementById('loading-bg')
    if (appLoading) {
        appLoading.style.display = "none";
    }
})

router.beforeEach((to, from, next) => {

    // If auth required, check login. If login fails redirect to login page
    if (to.meta.authRequired) {

        if (!(store.state.auth.isUserLoggedIn())) {

            if (from.path !== '/login') {

                router.push({ path: '/login' }).catch(e => {
                    console.log(e);
                })

            }
        }
    }

    //Esta foi a solução para redirecionar para o local correto de acordo com o tipo de usuário.
    if (store.state.AppActiveUser.userRole == 'cliente') {

        if (to.path == '/dashboard') {
            if (from.path !== '/dashboard-cliente') {
                router.push({ path: '/dashboard-cliente' }).catch(e => {
                    console.log(e);
                })
            } else {
                return //Não vai para rota nenhuma, porque já está na página. sem este teste ocorre erro de DuplicateNavigation.
            }
        }
        return next()
    }

    return next()

});

export default router
