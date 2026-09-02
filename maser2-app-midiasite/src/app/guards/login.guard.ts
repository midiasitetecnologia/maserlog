import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { GlobalService } from './../services/global.service';

@Injectable({
  providedIn: 'root'
})
export class LoginGuard implements CanActivate {

  constructor(
    private router: Router,
    private global: GlobalService) {
  }

  canActivate(): boolean {
    if (this.global.motoristaAutenticado() == true) {
      this.router.navigate(['']);
      return false;
    } else {
      return true;
    }        
  }
  
}
