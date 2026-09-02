import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ModeloPage } from './modelo.page';

const routes: Routes = [
  {
    path: '',
    component: ModeloPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ModeloPageRoutingModule {}
