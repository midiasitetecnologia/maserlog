import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ProximasPage } from './proximas.page';

const routes: Routes = [
  {
    path: '',
    component: ProximasPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ProximasPageRoutingModule {}
