import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit {

  public fGroup: FormGroup;
  private click: number = 0;

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService,
    public coleta: ColetaService) {
    console.log('LoginPage -> constructor');

    this.fGroup = this.formBuilder.group({
      'email': [null, Validators.compose([
        Validators.required
      ])],
      'password': [null, Validators.compose([
        Validators.required
      ])]
    });
  }

  ngOnInit() {
    console.log('LoginPage -> ngOnInit');
  }

  ionViewWillEnter() {
    console.log('LoginPage -> ionViewWillEnter');
    this.fGroup.get('email').setValue(this.motorista.getEmail());
    this.fGroup.get('password').setValue('');
  }

  async fazerLogin() {
    console.log('LoginPage -> fazerLogin', this.fGroup.value);

    await this.procs.iniciarLoading();
    await this.motorista.apiAutenticarMotorista(this.fGroup.get('email').value, this.fGroup.get('password').value);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.procs.iniciarLoading();
      this.coleta.atualizarColetasPendentes = true;
      await this.coleta.apiGetColetasPendentes();
      await this.motorista.apiGetNotificacoesMotorista();
      await this.procs.finalizarLoading();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

  clickLogo() {
    console.log('HomePage -> clickLogo', this.click);

    this.click++;

    if ((this.click == 5) && (this.global.getModoDeveloper() == 'S')) {
      this.router.navigate(['/modelo']);
    }

    if (this.click >= 10) {

      // Reinicializa o contador de clicks.
      this.click = 0;

      let modoDeveloper: string = '';

      if (this.global.getModoDeveloper() == 'S') {
        modoDeveloper = 'N';
        this.procs.exibirToastOk('', 'Modo de desenvolvedor desativado. Para aplicar as mudanças, por favor, feche o aplicativo e abra-o novamente.');
      }
      else {
        modoDeveloper = 'S';
        this.procs.exibirToastOk('', 'Modo de desenvolvedor ATIVADO. Para aplicar as mudanças, por favor, feche o aplicativo e abra-o novamente.');
      }

      // Apaga tudo que salvamos em 'LocalStorage'.
      this.procs.limparLocalStorage();

      // Gravamos o novo valor para o modo developer.
      this.global.setModoDeveloper(modoDeveloper);

    }
  }

}
