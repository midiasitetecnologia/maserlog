import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { lastValueFrom } from 'rxjs';
import { Platform } from '@ionic/angular';
//import { CapacitorHttp, HttpOptions } from '@capacitor/core';
import { HTTP as CordovaHttp } from '@awesome-cordova-plugins/http/ngx';

@Injectable({
  providedIn: 'root',
})
export class HttpService {

  constructor(
    private platform: Platform,
    private cordovaHttp: CordovaHttp,
    private angularHttp: HttpClient) {
    console.log('HttpService -> constructor');
  }

  private async isNative() {
    return this.platform.is('capacitor');
  }

  async post(url: string, body: any, headers: any) {

    //if (await this.isNative()) {
    //  const options: HttpOptions = {
    //    url,
    //    headers,
    //    data: body
    //  }
    //  const response = await CapacitorHttp.post(options);
    //  console.log('HttpService | response capacitorHttp -> ' + url, JSON.stringify(response));
    //  return response.data;
    //}

    if (await this.isNative()) {
      const response = await this.cordovaHttp.post(url, body, headers);
      console.log('HttpService | response cordovaHttp -> ' + url, JSON.stringify(response));
      return JSON.parse(response.data);
    }

    const options = (headers) ? { headers: headers } : null;
    // 1. Obter o Observable
    const observable = this.angularHttp.post(url, body, options);
    // 2. Converter o Observable para Promise e usar 'await'
    const data = await lastValueFrom(observable);
    console.log('HttpService | response httpAngular -> ' + url, JSON.stringify(data));
    return data;

  }

}
