import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { ColetaNotasFiscaisPage } from './coleta-notas-fiscais.page';

describe('ColetaNotasFiscaisPage', () => {
  let component: ColetaNotasFiscaisPage;
  let fixture: ComponentFixture<ColetaNotasFiscaisPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ColetaNotasFiscaisPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(ColetaNotasFiscaisPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
