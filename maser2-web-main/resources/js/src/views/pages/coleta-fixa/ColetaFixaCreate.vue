<template>
	<div class="page-coleta-fixa-create">
		<vs-alert
			color="danger"
			:active.sync="tem_erros"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<ul>
				<li v-for="(value, i) in erros_form" :key="i">{{ value[0] }}</li>
			</ul>
		</vs-alert>

		<br v-show="tem_erros" />

		<div class="vx-col w-full mb-base">
			<vx-card title="Contrato">
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">Empresa</label>
						<v-select
							class="w-full"
							label="nome"
							:options="empresa"
							:clearable="false"
							v-model="empresaCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row">
					<div class="vx-col md:w-1/2 w-full mb-6">
						<label class="vs-input--label">Cliente</label>
						<v-select
							class="w-full"
							label="nome"
							:options="clienteData"
							:clearable="false"
							v-model="clienteCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>

					<div class="vx-col md:w-1/6 sm:w-1/2 w-full mb-6">
						<vs-input
							autocomplete="off"
							name="cod_cliente"
							class="w-full"
							label="Código"
							disabled="true"
							v-model="coletaFixaData[0].cod_cliente"
						/>
					</div>

					<div class="vx-col md:w-1/3 sm:w-1/2 w-full mb-6">
						<vs-input
							autocomplete="off"
							name="cpf_cnpj"
							class="w-full"
							label="CNPJ / CPF"
							disabled="true"
							v-model="coletaFixaData[0].cpf_cnpj"
						/>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">Tipo de Coleta</label>
						<v-select
							class="w-full"
							label="label"
							:options="tipoColetaOptions"
							:clearable="false"
							v-model="tipoColetaCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Data Inicial</label>
						<flat-pickr
							:config="configdateTimePickerDate"
							v-model="coletaFixaData[0].dt_ini"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>

					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Data Final</label>
						<flat-pickr
							:config="configdateTimePickerDate"
							v-model="coletaFixaData[0].dt_fim"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card title="Coleta">
				<div class="vx-row">
					<div class="vx-col md:w-2/3 w-full mb-6">
						<label class="vs-input--label">Local de Coleta</label>
						<v-select
							class="w-full"
							label="nome"
							:options="clienteData"
							:clearable="false"
							v-model="LocalColetaCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>

					<div class="vx-col md:w-1/3 w-full mb-6">
						<vs-input
							autocomplete="off"
							name="cod_loc_coleta"
							class="w-full"
							label="Código"
							disabled="true"
							v-model="coletaFixaData[0].cod_loc_coleta"
						/>
					</div>
				</div>

				<div class="vx-row">
					<div class="vx-col md:w-2/3 w-full mb-6">
						<label class="vs-input--label">Local de Entrega</label>
						<v-select
							class="w-full"
							label="nome"
							:options="clienteData"
							:clearable="false"
							v-model="LocalEntregaCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>

					<div class="vx-col md:w-1/3 w-full mb-6">
						<vs-input
							autocomplete="off"
							name="cod_loc_entrega"
							class="w-full"
							label="Código"
							disabled="true"
							v-model="coletaFixaData[0].cod_loc_entrega"
						/>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:150'"
							data-vv-as="características"
							name="caract_coleta"
							class="w-full"
							label="Características da coleta"
							v-model="coletaFixaData[0].caract_coleta"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('caract_coleta')"
						>{{ errors.first('caract_coleta') }}</span>
					</div>
				</div>

				<div class="vx-row">
					<div class="vx-col md:w-1/2 w-full mb-6">
						<label class="vs-input--label">Sistema de Carga</label>
						<v-select
							class="w-full"
							label="label"
							:options="sisCargaOptions"
							clearable
							v-model="sisCargaCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>

					<div class="vx-col md:w-1/2 w-full mb-6">
						<label class="vs-input--label">Tipo de Frete</label>
						<v-select
							class="w-full"
							label="label"
							:options="tipoFreteOptions"
							:clearable="false"
							v-model="tipoFreteCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row">
					<div class="vx-col md:w-1/2 w-full mb-6">
						<label class="vs-input--label">Veículo</label>
						<v-select class="w-full" label="placa" :options="veiculo" clearable v-model="veiculoCombo">
							<template v-slot:option="option">{{ option.placa }}</template>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>

					<div class="vx-col md:w-1/2 w-full mb-6">
						<label class="vs-input--label">Tipo de Veículo Contratado</label>
						<v-select
							class="w-full"
							label="descricao"
							:options="tipoVeiculo"
							clearable
							v-model="tipoVeiculoCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col">
						<vs-checkbox v-model="receberNfCheck" vs-value="S">Receber NF de frete junto com notas fiscais</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col">
						<vs-checkbox v-model="aceitarFotoRomCheck" vs-value="S">Romaneio como documento de carga</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col">
						<vs-checkbox v-model="autorizaColetaCheck" vs-value="S">Autorizar automaticamente a coleta quando tiver um veículo definido</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col">
						<vs-checkbox v-model="ocultarResumoCheck" vs-value="S">Não mostrar a solicitação no Resumo do Dia</vs-checkbox>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card title="Dias">
				<div class="vx-row mb-6">
					<div class="vx-col">
						<vs-checkbox v-model="segundaCheck" vs-value="S">Segunda</vs-checkbox>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="tercaCheck" vs-value="S">Terça</vs-checkbox>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="quartaCheck" vs-value="S">Quarta</vs-checkbox>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="quintaCheck" vs-value="S">Quinta</vs-checkbox>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="sextaCheck" vs-value="S">Sexta</vs-checkbox>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="sabadoCheck" vs-value="S">Sábado</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="coletaFixaData[0].tipo_coleta != 'C'">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Hora da Coleta</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="coletaFixaData[0].hr_prev_coleta"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Hora da Entrega</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="coletaFixaData[0].hr_prev_entrega"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="coletaFixaData[0].tipo_coleta == 'C'">
					<div class="vx-col w-full">
						<vs-checkbox v-model="doisTurnosCheck" vs-value="S">Dois Turnos</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="coletaFixaData[0].tipo_coleta == 'C'">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
						<label class="vs-input--label">Turno #1 - Início</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="coletaFixaData[0].t1_hora_ini"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>

					<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
						<label class="vs-input--label">Turno #1 - Fim</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="coletaFixaData[0].t1_hora_fim"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="coletaFixaData[0].dois_turnos == 'S'">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
						<label class="vs-input--label">Turno #2 - Início</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="coletaFixaData[0].t2_hora_ini"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>

					<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
						<label class="vs-input--label">Turno #2 - Fim</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="coletaFixaData[0].t2_hora_fim"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card title="Cancelamento">
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-checkbox v-model="contCancelCheck" vs-value="S">Cancelar este contrato</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">Data de Cancelamento</label>
						<flat-pickr
							:config="configdateTimePickerDate"
							v-model="coletaFixaData[0].dt_cancel"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card>
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="addNewRecord()">Salvar</vs-button>
						<vs-button type="border" color="danger" @click="voltar()">Cancelar</vs-button>
					</div>
				</div>
			</vx-card>
		</div>
	</div>
</template>

<script>
import labelsMixins from "@/mixins/labelsMixins";
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";

export default {
	mixins: [labelsMixins],
	components: {
		"v-select": vSelect,
		flatPickr
	},
	data() {
		return {
			configdateTimePickerDate: {
				altInput: true,
				altFormat: "d/m/Y",
				dateFormat: "Y-m-d",
				locale: Portuguese
			},

			configdateTimePickerTime: {
				enableTime: true,
				enableSeconds: false,
				noCalendar: true,
				locale: Portuguese
			},

			coletaFixaData: [
				{
					empresa: null,
					empresa_nome: null,
					nome: null,
					cod_cliente: null,
					cpf_cnpj: null,
					tipo_coleta: null,
					dt_ini: null,
					dt_fim: null,
					nome_coleta: null,
					cod_loc_coleta: null,
					nome_entrega: null,
					cod_loc_entrega: null,
					placa_coleta: null,
					caract_coleta: null,
					cod_tipo_veiculo: null,
					sis_carga: null,
					tipo_frete: "N",
					descricao_veiculo: null,
					receber_nf_frete: null,
					aceitar_foto_rom: null,
					autoriza_coleta: null,
					ocultar_resumo: null,
					segunda: null,
					terca: null,
					quarta: null,
					quinta: null,
					sexta: null,
					sabado: null,
					dois_turnos: null,
					cont_cancel: null
				}
			],

			clienteData: [],

			tipoColetaOptions: [
				{
					label:
						"Diária (solicitação normal; veículo pode atender vários clientes)",
					value: "D"
				},
				{
					label:
						"Contrato (por expediente; veículo dedicado a um único cliente)",
					value: "C"
				},
				{
					label:
						"Multi-destinos (coleta na mesma origem para vários destinos; veículo pode atender vários clientes)",
					value: "M"
				}
			],

			sisCargaOptions: [
				{ label: "Nenhum", value: "N" },
				{ label: "Empilhadeira", value: "E" },
				{ label: "Ponte Rolante", value: "P" },
				{ label: "Manual", value: "M" }
			],

			tipoFreteOptions: [
				{ label: "Normal", value: "N" },
				{ label: "Retorno Embalagem / Beneficiamento", value: "R" },
			],

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.getEmpresa();
		this.lerVeiculo();
		this.lerTipoVeiculo();
	},
	computed: {
		validateForm() {
			return !this.errors.any();
		},
		empresa() {
			return this.$store.state.empresa.empresaData;
		},
		veiculo() {
			return this.$store.state.veiculo.veiculoPesqData;
		},
		tipoVeiculo() {
			return this.$store.state.tipoVeiculo.tipoVeiculoPesqData;
		},
		empresaCombo: {
			get() {
				if (this.coletaFixaData[0].empresa_nome == null) {
					return "Selecione a empresa";
				} else {
					return {
						nome: this.coletaFixaData[0].empresa_nome
					};
				}
			},
			set(obj) {
				this.coletaFixaData[0].empresa = obj.codigo;
				this.coletaFixaData[0].empresa_nome = obj.nome;

				this.getCliente();
			}
		},
		clienteCombo: {
			get() {
				if (this.coletaFixaData[0].nome == null) {
					return "Selecione o cliente";
				} else {
					return {
						nome: this.coletaFixaData[0].nome
					};
				}
			},
			set(obj) {
				this.coletaFixaData[0].cod_cliente = obj.codigo;
				this.coletaFixaData[0].cpf_cnpj = obj.cpf_cnpj;
				this.coletaFixaData[0].nome = obj.nome;
			}
		},
		tipoColetaCombo: {
			get() {
				if (this.coletaFixaData[0].tipo_coleta == null) {
					return "Selecione o tipo de coleta";
				} else {
					return {
						label: this.tipoColetaLabel(
							this.coletaFixaData[0].tipo_coleta
						),
						value: this.coletaFixaData[0].tipo_coleta
					};
				}
			},
			set(obj) {
				this.coletaFixaData[0].tipo_coleta = obj.value;
				if (this.coletaFixaData[0].tipo_coleta != "C") {
					this.coletaFixaData[0].dois_turnos = "N";
				}
			}
		},
		sisCargaCombo: {
			get() {
				if (this.coletaFixaData[0].sis_carga == null) {
					return "Selecione...";
				} else {
					return {
						label: this.sisCargaLabel(
							this.coletaFixaData[0].sis_carga
						),
						value: this.coletaFixaData[0].sis_carga
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.coletaFixaData[0].sis_carga = obj.value;
				} else {
					this.coletaFixaData[0].sis_carga = null;
				}
			}
		},
		tipoFreteCombo: {
			get() {
				if (this.coletaFixaData[0].tipo_frete == null) {
					return "Selecione o tipo de frete";
				} else {
					return {
						label: this.tipoFreteLabel(
							this.coletaFixaData[0].tipo_frete
						),
						value: this.coletaFixaData[0].tipo_frete,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.coletaFixaData[0].tipo_frete = obj.value;
				} else {
					this.coletaFixaData[0].tipo_frete = null;
				}
			},
		},
		LocalColetaCombo: {
			get() {
				if (this.coletaFixaData[0].nome_coleta == null) {
					return "Selecione o local de coleta";
				} else {
					return {
						nome: this.coletaFixaData[0].nome_coleta
					};
				}
			},
			set(obj) {
				this.coletaFixaData[0].cod_loc_coleta = obj.codigo;
				this.coletaFixaData[0].nome_coleta = obj.nome;
			}
		},
		LocalEntregaCombo: {
			get() {
				if (this.coletaFixaData[0].nome_entrega == null) {
					return "Selecione o local de entrega";
				} else {
					return {
						nome: this.coletaFixaData[0].nome_entrega
					};
				}
			},
			set(obj) {
				this.coletaFixaData[0].cod_loc_entrega = obj.codigo;
				this.coletaFixaData[0].nome_entrega = obj.nome;
			}
		},
		veiculoCombo: {
			get() {
				if (this.coletaFixaData[0].placa_coleta == null) {
					return "";
				} else {
					return {
						placa: this.coletaFixaData[0].placa_coleta
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.coletaFixaData[0].placa_coleta = obj.placa;
				} else {
					this.coletaFixaData[0].placa_coleta = null;
				}
			}
		},
		tipoVeiculoCombo: {
			get() {
				if (this.coletaFixaData[0].descricao_veiculo == null) {
					return "";
				} else {
					return {
						descricao: this.coletaFixaData[0].descricao_veiculo
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.coletaFixaData[0].cod_tipo_veiculo = obj.codigo;
					this.coletaFixaData[0].descricao_veiculo = obj.descricao;
				} else {
					this.coletaFixaData[0].cod_tipo_veiculo = null;
					this.coletaFixaData[0].descricao_veiculo = null;
				}
			}
		},
		receberNfCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].receber_nf_frete
				);
			},
			set(obj) {
				this.coletaFixaData[0].receber_nf_frete = obj;
			}
		},
		aceitarFotoRomCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].aceitar_foto_rom
				);
			},
			set(obj) {
				this.coletaFixaData[0].aceitar_foto_rom = obj;
			}
		},
		autorizaColetaCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].autoriza_coleta
				);
			},
			set(obj) {
				this.coletaFixaData[0].autoriza_coleta = obj;
			}
		},
		ocultarResumoCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].ocultar_resumo
				);
			},
			set(obj) {
				this.coletaFixaData[0].ocultar_resumo = obj;
			}
		},
		segundaCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].segunda
				);
			},
			set(obj) {
				this.coletaFixaData[0].segunda = obj;
			}
		},
		tercaCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].terca
				);
			},
			set(obj) {
				this.coletaFixaData[0].terca = obj;
			}
		},
		quartaCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].quarta
				);
			},
			set(obj) {
				this.coletaFixaData[0].quarta = obj;
			}
		},
		quintaCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].quinta
				);
			},
			set(obj) {
				this.coletaFixaData[0].quinta = obj;
			}
		},
		sextaCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].sexta
				);
			},
			set(obj) {
				this.coletaFixaData[0].sexta = obj;
			}
		},
		sabadoCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].sabado
				);
			},
			set(obj) {
				this.coletaFixaData[0].sabado = obj;
			}
		},
		doisTurnosCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].dois_turnos
				);
			},
			set(obj) {
				this.coletaFixaData[0].dois_turnos = obj;
			}
		},
		contCancelCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaFixaData[0].cont_cancel
				);
			},
			set(obj) {
				this.coletaFixaData[0].cont_cancel = obj;
			}
		}
	},
	methods: {
		getEmpresa() {
			this.$store.dispatch("empresa/indexEmpresa").catch(err => {
				console.error(err);
			});
		},
		lerVeiculo() {
			this.$store.dispatch("veiculo/lerVeiculo").catch(err => {
				console.error(err);
			});
		},
		lerTipoVeiculo() {
			this.$store.dispatch("tipoVeiculo/lerTipoVeiculo").catch(err => {
				console.error(err);
			});
		},
		getCliente() {
			this.coletaFixaData[0].cod_cliente = null;
			this.coletaFixaData[0].nome = null;
			this.coletaFixaData[0].cod_loc_coleta = null;
			this.coletaFixaData[0].nome_coleta = null;
			this.coletaFixaData[0].cod_loc_entrega = null;
			this.coletaFixaData[0].nome_entrega = null;

			this.$http
				.post(
					`api/getDadosCliente`,
					{
						empresa: this.coletaFixaData[0].empresa
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(response => {
					if (response.data.status) {
						this.clienteData = response.data.cliente;
					}
				})
				.catch();
		},
		addNewRecord() {
			this.$http
				.post(
					`api/coleta-fixa`,
					{
						empresa: this.coletaFixaData[0].empresa,
						cod_cliente: this.coletaFixaData[0].cod_cliente,
						tipo_coleta: this.coletaFixaData[0].tipo_coleta,
						dt_ini: this.coletaFixaData[0].dt_ini,
						dt_fim: this.coletaFixaData[0].dt_fim,
						cod_loc_coleta: this.coletaFixaData[0].cod_loc_coleta,
						cod_loc_entrega: this.coletaFixaData[0].cod_loc_entrega,
						placa_coleta: this.coletaFixaData[0].placa_coleta,
						cod_tipo_veiculo: this.coletaFixaData[0].cod_tipo_veiculo,
						caract_coleta: this.coletaFixaData[0].caract_coleta,
						sis_carga: this.coletaFixaData[0].sis_carga,
						tipo_frete: this.coletaFixaData[0].tipo_frete,
						receber_nf_frete:
							this.coletaFixaData[0].receber_nf_frete == null
								? "N"
								: this.coletaFixaData[0].receber_nf_frete,
						aceitar_foto_rom:
							this.coletaFixaData[0].aceitar_foto_rom == null
								? "N"
								: this.coletaFixaData[0].aceitar_foto_rom,
						autoriza_coleta:
							this.coletaFixaData[0].autoriza_coleta == null
								? "N"
								: this.coletaFixaData[0].autoriza_coleta,
						ocultar_resumo:
							this.coletaFixaData[0].ocultar_resumo == null
								? "N"
								: this.coletaFixaData[0].ocultar_resumo,
						segunda:
							this.coletaFixaData[0].segunda == null
								? "N"
								: this.coletaFixaData[0].segunda,
						terca:
							this.coletaFixaData[0].terca == null
								? "N"
								: this.coletaFixaData[0].terca,
						quarta:
							this.coletaFixaData[0].quarta == null
								? "N"
								: this.coletaFixaData[0].quarta,
						quinta:
							this.coletaFixaData[0].quinta == null
								? "N"
								: this.coletaFixaData[0].quinta,
						sexta:
							this.coletaFixaData[0].sexta == null
								? "N"
								: this.coletaFixaData[0].sexta,
						sabado:
							this.coletaFixaData[0].sabado == null
								? "N"
								: this.coletaFixaData[0].sabado,
						hr_prev_coleta: this.coletaFixaData[0].hr_prev_coleta,
						hr_prev_entrega: this.coletaFixaData[0].hr_prev_entrega,
						dois_turnos:
							this.coletaFixaData[0].dois_turnos == null
								? "N"
								: this.coletaFixaData[0].dois_turnos,
						t1_hora_ini: this.coletaFixaData[0].t1_hora_ini,
						t1_hora_fim: this.coletaFixaData[0].t1_hora_fim,
						t2_hora_ini:
							this.coletaFixaData[0].dois_turnos == null
								? null
								: this.coletaFixaData[0].t2_hora_ini,
						t2_hora_fim:
							this.coletaFixaData[0].dois_turnos == null
								? null
								: this.coletaFixaData[0].t2_hora_fim,
						cont_cancel:
							this.coletaFixaData[0].cont_cancel == null
								? "N"
								: this.coletaFixaData[0].cont_cancel,
						dt_cancel:
							this.coletaFixaData[0].cont_cancel == null
								? null
								: this.coletaFixaData[0].dt_cancel
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(response => {
					if (response.data.status) {
						this.$router.push("/coleta-fixa/").catch(() => {});
					}
					if (response.data.status === false) {
						this.erros_form = response.data.erros;
						this.tem_erros = true;
						console.log(response.data.erros);
					}
				})
				.catch(e => {
					this.erros_form = e.response.data.errors;
					this.tem_erros = true;
					console.log(e);
				});
		},
		voltar() {
			this.$router.back();
		},
		retValorCheckBoxSimNao(value) {
			if (value === "S") {
				return "S";
			} else {
				return null;
			}
		}
	}
};
</script>

<style lang="scss">
</style>