import { Injectable } from '@angular/core';
import { Router } from '@angular/router';

import { GlobalService } from './global.service';
import { MotoristaService } from './motorista.service';
import { ColetaService } from './coleta.service';

@Injectable({
  providedIn: 'root'
})
export class OnesignalService {

  constructor(
    private router: Router,
    private global: GlobalService,
    private motorista: MotoristaService,
    private coleta: ColetaService) { 
    console.log('OnesignalService -> constructor');
  }

  inicializar() {
    console.log('OnesignalService -> inicializar');

    // Configura o OneSignal com o ID do App.
    window["plugins"].OneSignal.setAppId(this.global.getAppIdOneSignal());
    
    // Executa quando a notificação for recebida.
    window["plugins"].OneSignal.setNotificationWillShowInForegroundHandler(data => {
      console.log("OnesignalService -> OneSignal.setNotificationWillShowInForegroundHandler", data);
      this.coleta.atualizarColetasPendentes = true;
      this.coleta.apiGetColetasPendentes();
      this.motorista.apiGetNotificacoesMotorista();
    });

    // Executa quando a notificação for aberta (clicada).
    window["plugins"].OneSignal.setNotificationOpenedHandler(data => {
      console.log("OnesignalService -> OneSignal.setNotificationOpenedHandler", data);
      this.coleta.atualizarColetasPendentes = true;
      this.coleta.apiGetColetasPendentes();
      this.motorista.apiGetNotificacoesMotorista();
      this.router.navigate(['/tabs/notificacoes']);
    });

    // Solicita ao usuário permissões de notificação.
    window["plugins"].OneSignal.promptForPushNotificationsWithUserResponse(accepted => {
      console.log("OnesignalService -> OneSignal.promptForPushNotificationsWithUserResponse", accepted);
    });

  }

}
