import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { TabsPage } from './tabs.page';

const routes: Routes = [
  {
    path: 'tabs',
    component: TabsPage,
    children: [
      {
        path: 'home',
        children: [
          {
            path: '',
            loadChildren: () =>
              import('../pages/home/home.module').then(m => m.HomePageModule)
          }
        ]
      },
      {
        path: 'proximas',
        children: [
          {
            path: '',
            loadChildren: () =>
              import('../pages/proximas/proximas.module').then(m => m.ProximasPageModule)
          }
        ]
      },
      {
        path: 'carga',
        children: [
          {
            path: '',
            loadChildren: () =>
              import('../pages/carga/carga.module').then(m => m.CargaPageModule)
          }
        ]
      },
      {
        path: 'notificacoes',
        children: [
          {
            path: '',
            loadChildren: () =>
              import('../pages/notificacoes/notificacoes.module').then(m => m.NotificacoesPageModule)
          }
        ]
      },
      {
        path: 'perfil',
        children: [
          {
            path: '',
            loadChildren: () =>
              import('../pages/perfil/perfil.module').then(m => m.PerfilPageModule)
          }
        ]
      },
      {
        path: '',
        redirectTo: '/tabs/home',
        pathMatch: 'full'
      }
    ]
  },
  {
    path: '',
    redirectTo: '/tabs/home',
    pathMatch: 'full'
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TabsPageRoutingModule {}
