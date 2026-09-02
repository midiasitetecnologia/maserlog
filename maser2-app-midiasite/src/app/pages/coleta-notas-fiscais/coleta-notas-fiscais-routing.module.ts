import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ColetaNotasFiscaisPage } from './coleta-notas-fiscais.page';

const routes: Routes = [
  {
    path: '',
    component: ColetaNotasFiscaisPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ColetaNotasFiscaisPageRoutingModule {}
