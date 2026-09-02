import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { VeiculoProximosPage } from './veiculo-proximos.page';

const routes: Routes = [
  {
    path: '',
    component: VeiculoProximosPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class VeiculoProximosPageRoutingModule {}
