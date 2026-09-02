import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { VeiculoPage } from './veiculo.page';

describe('VeiculoPage', () => {
  let component: VeiculoPage;
  let fixture: ComponentFixture<VeiculoPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VeiculoPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(VeiculoPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
