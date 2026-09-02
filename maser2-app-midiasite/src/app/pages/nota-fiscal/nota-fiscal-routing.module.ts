import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { NotaFiscalPage } from './nota-fiscal.page';

const routes: Routes = [
  {
    path: '',
    component: NotaFiscalPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class NotaFiscalPageRoutingModule {}
