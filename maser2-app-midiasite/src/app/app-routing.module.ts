import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';
import { AuthGuard } from './guards/auth.guard';
import { LoginGuard } from './guards/login.guard';

const routes: Routes = [
  {
    path: '',
    loadChildren: () => import('./tabs/tabs.module').then(m => m.TabsPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'login',
    loadChildren: () => import('./pages/login/login.module').then( m => m.LoginPageModule),
    canActivate: [LoginGuard]
  },
  {
    path: 'modelo',
    loadChildren: () => import('./pages/modelo/modelo.module').then( m => m.ModeloPageModule)
    // Para essa página não precisa canActivate. Ela serve para efetuar testes e é acessada 
    // apenas através da barra de navegação no ambiente de desenvolvimento (ionic serve).
  },
  {
    path: 'veiculo',
    loadChildren: () => import('./pages/veiculo/veiculo.module').then( m => m.VeiculoPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'coleta',
    loadChildren: () => import('./pages/coleta/coleta.module').then( m => m.ColetaPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'proximas',
    loadChildren: () => import('./pages/proximas/proximas.module').then( m => m.ProximasPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'coleta-notas-fiscais',
    loadChildren: () => import('./pages/coleta-notas-fiscais/coleta-notas-fiscais.module').then( m => m.ColetaNotasFiscaisPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'nota-fiscal',
    loadChildren: () => import('./pages/nota-fiscal/nota-fiscal.module').then( m => m.NotaFiscalPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'comanda',
    loadChildren: () => import('./pages/comanda/comanda.module').then( m => m.ComandaPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'veiculo-ocupacao',
    loadChildren: () => import('./pages/veiculo-ocupacao/veiculo-ocupacao.module').then( m => m.VeiculoOcupacaoPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'coleta-finalizar',
    loadChildren: () => import('./pages/coleta-finalizar/coleta-finalizar.module').then( m => m.ColetaFinalizarPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'foto',
    loadChildren: () => import('./pages/foto/foto.module').then( m => m.FotoPageModule)
    // Para essa página não precisa canActivate.
  },
  {
    path: 'veiculo-proximos',
    loadChildren: () => import('./pages/veiculo-proximos/veiculo-proximos.module').then( m => m.VeiculoProximosPageModule),
    canActivate: [AuthGuard]
  },
  {
    path: 'notificacoes',
    loadChildren: () => import('./pages/notificacoes/notificacoes.module').then( m => m.NotificacoesPageModule),
    canActivate: [AuthGuard]
  }
];
@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules })
  ],
  exports: [RouterModule]
})
export class AppRoutingModule {}
