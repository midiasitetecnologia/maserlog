import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { ColetaFinalizarPage } from './coleta-finalizar.page';

describe('ColetaFinalizarPage', () => {
  let component: ColetaFinalizarPage;
  let fixture: ComponentFixture<ColetaFinalizarPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ColetaFinalizarPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(ColetaFinalizarPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
