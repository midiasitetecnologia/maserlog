import { NgModule, LOCALE_ID } from '@angular/core';
import { registerLocaleData } from '@angular/common';
import localePt from '@angular/common/locales/pt';

import { BrowserModule } from '@angular/platform-browser';
import { RouteReuseStrategy } from '@angular/router';

import { IonicModule, IonicRouteStrategy } from '@ionic/angular';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

import { ComponentsModule } from './components/components.module';
import { ReactiveFormsModule } from '@angular/forms';

import { HttpClientModule } from '@angular/common/http';

import { AppVersion } from '@awesome-cordova-plugins/app-version/ngx';
import { Device } from '@awesome-cordova-plugins/device/ngx';
import { HTTP } from '@awesome-cordova-plugins/http/ngx';
import { OneSignal } from '@awesome-cordova-plugins/onesignal/ngx';

registerLocaleData(localePt);

@NgModule({
  declarations: [AppComponent],
  imports: [
    BrowserModule,
    IonicModule.forRoot({
      backButtonText: '',
      innerHTMLTemplatesEnabled: true
    }),
    ReactiveFormsModule,
    AppRoutingModule,
    ComponentsModule,
    HttpClientModule
  ],
  providers: [
    { provide: LOCALE_ID, useValue: 'pt-BR' },
    { provide: RouteReuseStrategy, useClass: IonicRouteStrategy },
    AppVersion,
    Device,
    HTTP,
    OneSignal
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
