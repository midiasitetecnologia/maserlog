import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';
import { ProximasPageRoutingModule } from './proximas-routing.module';
import { ProximasPage } from './proximas.page';
import { CardSolicitacaoComponentModule } from '../../components/card-solicitacao/card-solicitacao.module';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ProximasPageRoutingModule,
    CardSolicitacaoComponentModule
  ],
  declarations: [ProximasPage]
})
export class ProximasPageModule {}
