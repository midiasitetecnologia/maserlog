import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { VeiculoOcupacaoPage } from './veiculo-ocupacao.page';

describe('VeiculoOcupacaoPage', () => {
  let component: VeiculoOcupacaoPage;
  let fixture: ComponentFixture<VeiculoOcupacaoPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VeiculoOcupacaoPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(VeiculoOcupacaoPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
