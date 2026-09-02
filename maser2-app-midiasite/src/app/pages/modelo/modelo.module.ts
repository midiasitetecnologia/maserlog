import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ModeloPageRoutingModule } from './modelo-routing.module';
import { SelecionarVeiculoComponentModule } from '../../components/selecionar-veiculo/selecionar-veiculo.module';
import { CardSolicitacaoComponentModule } from '../../components/card-solicitacao/card-solicitacao.module';
import { PopoverColetaComponentModule } from '../../components/popover-coleta/popover-coleta.module';

import { ModeloPage } from './modelo.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ModeloPageRoutingModule,
    SelecionarVeiculoComponentModule,
    CardSolicitacaoComponentModule,
    PopoverColetaComponentModule
  ],
  declarations: [ModeloPage]
})
export class ModeloPageModule {}
