import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { VeiculoOcupacaoPage } from './veiculo-ocupacao.page';

const routes: Routes = [
  {
    path: '',
    component: VeiculoOcupacaoPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class VeiculoOcupacaoPageRoutingModule {}
