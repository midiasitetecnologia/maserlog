import { IonicModule } from '@ionic/angular';
import { RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HomePage } from './home.page';
import { ExploreContainerComponentModule } from '../../components/explore-container/explore-container.module';
import { SelecionarVeiculoComponentModule } from '../../components/selecionar-veiculo/selecionar-veiculo.module';
import { CardSolicitacaoComponentModule } from '../../components/card-solicitacao/card-solicitacao.module';

@NgModule({
  imports: [
    IonicModule,
    CommonModule,
    FormsModule,
    ExploreContainerComponentModule,
    SelecionarVeiculoComponentModule,
    CardSolicitacaoComponentModule,
    RouterModule.forChild([{ path: '', component: HomePage }])
  ],
  declarations: [HomePage]
})
export class HomePageModule {}
