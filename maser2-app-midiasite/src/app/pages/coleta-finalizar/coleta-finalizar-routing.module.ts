import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ColetaFinalizarPage } from './coleta-finalizar.page';

const routes: Routes = [
  {
    path: '',
    component: ColetaFinalizarPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ColetaFinalizarPageRoutingModule {}
