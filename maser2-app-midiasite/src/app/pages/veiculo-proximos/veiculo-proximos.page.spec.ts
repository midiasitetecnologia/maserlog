import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { VeiculoProximosPage } from './veiculo-proximos.page';

describe('VeiculoProximosPage', () => {
  let component: VeiculoProximosPage;
  let fixture: ComponentFixture<VeiculoProximosPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VeiculoProximosPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(VeiculoProximosPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
