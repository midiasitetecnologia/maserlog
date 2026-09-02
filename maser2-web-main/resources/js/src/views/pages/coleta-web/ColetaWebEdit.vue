<template>
	<div>
		<div v-if="podeSolicitarColetas == false">
			<bloqueio-solicitacoes />
		</div>
		<div v-if="podeSolicitarColetas == true" class="page-coleta-web-edit">
			<vs-alert color="danger" title="Coleta não encontrada" :active.sync="data_not_found">
				<span>Registro de coleta com id: {{$route.params.id}} não encontrada.</span>
				<span>
					<span>Verifique todas as</span>
					<router-link :to="{name:'coleta-web'}" class="text-inherit underline">Coletas</router-link>
				</span>
			</vs-alert>

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

			<br v-if="tem_erros" />

			<div class="vx-col w-full mb-base">
				<vx-card title="Solicitação" v-if="!data_not_found">
					<div class="vx-row" v-if="$acl.check('admin')">
						<div class="vx-col md:w-1/3 sm:w-1/3 w-full mb-6" v-if="coletaWebData.numero != null">
							<label for class="vs-input--label">Número</label>
							<input
								:disabled="true"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
								name="numero"
								v-model="coletaWebData.numero"
							/>
						</div>
						<div class="vx-col md:w-1/3 sm:w-1/3 w-full mb-6" v-else>
							<label for class="vs-input--label">ID</label>
							<input
								:disabled="true"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
								name="id"
								v-model="coletaWebData.id"
							/>
						</div>

						<div class="vx-col md:w-1/3 sm:w-1/3 w-full mb-6">
							<label class="vs-input--label">Data cadastro</label>
							<flat-pickr
								:disabled="true"
								:config="configdateTimePickerDate"
								v-model="coletaWebData.data_cad"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
							/>
						</div>

						<div class="vx-col md:w-1/3 sm:w-1/3 w-full mb-6">
							<label class="vs-input--label">Hora cadastro</label>
							<flat-pickr
								:disabled="true"
								:config="configdateTimePickerTime"
								v-model="coletaWebData.hora_cad"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
							/>
						</div>
					</div>

					<div class="vx-row mb-6" v-if="$acl.check('admin')">
						<div class="vx-col w-full">
							<label for class="vs-input--label">Empresa</label>
							<input
								:disabled="true"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
								name="nome_empresa"
								v-model="coletaWebData.nome_empresa"
							/>
						</div>
					</div>

					<div class="vx-row" v-if="$acl.check('admin')">
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
								v-model="coletaWebData.cod_cliente"
							/>
						</div>

						<div class="vx-col md:w-1/3 sm:w-1/2 w-full mb-6">
							<vs-input
								autocomplete="off"
								name="cpf_cnpj"
								class="w-full"
								label="CNPJ / CPF"
								disabled="true"
								v-model="coletaWebData.cpf_cnpj"
							/>
						</div>
					</div>

					<div class="vx-row mb-6" v-if="$acl.check('admin')">
						<div class="vx-col w-full">
							<label class="vs-input--label">Tipo de Coleta</label>
							<v-select
								class="w-full"
								label="label"
								:options="coletaFixaOptions"
								:clearable="false"
								v-model="coletaFixaCombo"
							>
								<div slot="no-options">Opção não disponível</div>
							</v-select>
						</div>
					</div>

					<div class="vx-row">
						<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
							<label class="vs-input--label">Data prevista para coleta</label>
							<flat-pickr
								:config="configdateTimePickerDate"
								v-model="coletaWebData.dt_prev_coleta"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
							/>
						</div>

						<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
							<label class="vs-input--label">Hora prevista para coleta</label>
							<flat-pickr
								:config="configdateTimePickerTime"
								v-model="coletaWebData.hr_prev_coleta"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
							/>
						</div>
					</div>

					<div class="vx-row">
						<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
							<label class="vs-input--label">Data prevista para entrega</label>
							<flat-pickr
								:config="configdateTimePickerDate"
								v-model="coletaWebData.dt_prev_entrega"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
							/>
						</div>

						<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
							<label class="vs-input--label">Hora prevista para entrega</label>
							<flat-pickr
								:config="configdateTimePickerTime"
								v-model="coletaWebData.hr_prev_entrega"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
							/>
						</div>
					</div>

					<div class="vx-row mb-6">
						<div class="vx-col">
							<vs-checkbox v-model="entregaUrgenteCheck" vs-value="S">Entrega Urgente</vs-checkbox>
						</div>
					</div>
				</vx-card>
			</div>

			<div class="vx-col w-full mb-base">
				<vx-card title="Coleta" v-if="!data_not_found">
					<div class="vx-row">
						<div :class="$acl.check('admin') ? 'vx-col md:w-2/3 w-full mb-6' : 'vx-col w-full mb-6'">
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

						<div class="vx-col md:w-1/3 w-full mb-6" v-if="$acl.check('admin')">
							<vs-input
								autocomplete="off"
								name="cod_loc_coleta"
								class="w-full"
								label="Código"
								disabled="true"
								v-model="coletaWebData.cod_loc_coleta"
							/>
						</div>
					</div>

					<div class="vx-row">
						<div :class="$acl.check('admin') ? 'vx-col md:w-2/3 w-full mb-6' : 'vx-col w-full mb-6'">
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

						<div class="vx-col md:w-1/3 w-full mb-6" v-if="$acl.check('admin')">
							<vs-input
								autocomplete="off"
								name="cod_loc_entrega"
								class="w-full"
								label="Código"
								disabled="true"
								v-model="coletaWebData.cod_loc_entrega"
							/>
						</div>
					</div>

					<div class="vx-row mb-6" v-if="this.exibirCamposColetaFixa()">
						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
							<vs-input
								autocomplete="off"
								data-vv-validate-on="blur"
								v-validate="'max:80'"
								data-vv-as="solicitante"
								name="solicitante"
								class="w-full"
								label="Solicitante"
								v-model="coletaWebData.solicitante"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('solicitante')"
							>{{ errors.first('solicitante') }}</span>
						</div>

						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
							<label for class="vs-input--label">Peso</label>
							<input
								autocomplete="off"
								data-vv-validate-on="blur"
								v-validate="'max:15'"
								data-vv-as="peso"
								maxlength="15"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
								name="peso"
								v-model="coletaWebData.peso"
								v-money="money"
							/>
							<span class="text-danger text-sm" v-show="errors.has('peso')">{{ errors.first('peso') }}</span>
						</div>

						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
							<vs-input
								class="w-full"
								type="number"
								min="1"
								autocomplete="off"
								data-vv-validate-on="blur"
								v-validate="'numeric|max:10|max_value:2147483647'"
								data-vv-as="volumes"
								name="volumes"
								label="Volumes"
								v-model.number="coletaWebData.volumes"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('volumes')"
							>{{ errors.first('volumes') }}</span>
						</div>

						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
							<vs-input
								data-vv-validate-on="blur"
								v-validate="'max:80'"
								data-vv-as="especie"
								name="especie"
								class="w-full"
								label="Especie"
								v-model="coletaWebData.especie"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('especie')"
							>{{ errors.first('especie') }}</span>
						</div>
					</div>

					<div class="vx-row mb-6" v-if="this.exibirCamposColetaFixa()">
						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
							<label for class="vs-input--label">Comprimento</label>
							<input
								autocomplete="off"
								data-vv-validate-on="blur"
								v-validate="'max:11'"
								data-vv-as="comprimento"
								maxlength="11"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
								name="comp_carga"
								v-model="coletaWebData.comp_carga"
								v-money="money"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('comp_carga')"
							>{{ errors.first('comp_carga') }}</span>
						</div>

						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
							<label for class="vs-input--label">Largura</label>
							<input
								autocomplete="off"
								data-vv-validate-on="blur"
								v-validate="'max:11'"
								data-vv-as="largura"
								maxlength="11"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
								name="larg_carga"
								v-model="coletaWebData.larg_carga"
								v-money="money"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('larg_carga')"
							>{{ errors.first('larg_carga') }}</span>
						</div>

						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
							<label for class="vs-input--label">Altura</label>
							<input
								autocomplete="off"
								data-vv-validate-on="blur"
								v-validate="'max:11'"
								data-vv-as="altura"
								maxlength="11"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
								name="altura"
								v-model="coletaWebData.alt_carga"
								v-money="money"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('alt_carga')"
							>{{ errors.first('alt_carga') }}</span>
						</div>

						<div class="vx-col md:w-1/4 sm:w-1/2 w-full">
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
								v-model="coletaWebData.caract_coleta"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('caract_coleta')"
							>{{ errors.first('caract_coleta') }}</span>
						</div>
					</div>

					<div class="vx-row">
						<div :class="$acl.check('admin') ? 'vx-col md:w-1/3 w-full mb-6' : 'vx-col md:w-1/2 w-full mb-6'">
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

						<div :class="$acl.check('admin') ? 'vx-col md:w-1/3 w-full mb-6' : 'vx-col md:w-1/2 w-full mb-6'">
							<label class="vs-input--label">Tipo de Veículo</label>
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

						<div class="vx-col md:w-1/3 w-full mb-6" v-if="$acl.check('admin')">
							<vs-input
								class="w-full"
								disabled
								label="Veículo"
								name="placa_coleta"
								v-model="coletaWebData.placa_coleta"
							/>
						</div>
					</div>

					<div class="vx-row mb-6" v-if="$acl.check('admin')">
						<div class="vx-col">
							<vs-checkbox
								v-model="receberNfCheck"
								vs-value="S"
							>Receber NF de frete junto com notas fiscais</vs-checkbox>
						</div>
					</div>

					<div class="vx-row mb-6" v-if="$acl.check('admin') && (coletaWebData.coleta_fixa != 'M')">
						<div class="vx-col">
							<vs-checkbox v-model="aceitarFotoRomCheck" vs-value="S">Romaneio como documento de carga</vs-checkbox>
						</div>
					</div>

					<div class="vx-row mb-6" v-if="$acl.check('admin')">
						<div class="vx-col">
							<vs-checkbox v-model="ocultarResumoCheck" vs-value="S">Não mostrar a solicitação no Resumo do Dia</vs-checkbox>
						</div>
					</div>

					<div class="vx-row">
						<div class="vx-col w-full">
							<label class="vs-input--label">Observações</label>
							<vs-textarea class="w-full" rows="3" v-model="coletaWebData.obs_coleta" />
						</div>
					</div>
				</vx-card>
			</div>

			<div class="vx-col w-full mb-base">
				<vx-card v-if="!data_not_found">
					<div class="vx-row">
						<div class="vx-col w-full">
							<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="updateRecord()">Salvar</vs-button>
							<vs-button type="border" color="danger" @click="voltar()">Cancelar</vs-button>
						</div>
					</div>
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
import labelsMixins from "@/mixins/labelsMixins";
import vSelect from "vue-select";
import { VMoney } from "v-money";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";
import BloqueioSolicitacoes from "./BloqueioSolicitacoes.vue";

export default {
	mixins: [labelsMixins],
	components: {
		"v-select": vSelect,
		flatPickr,
		BloqueioSolicitacoes,
	},
	directives: { money: VMoney },
	data() {
		return {
			money: {
				decimal: ",",
				thousands: ".",
				precision: 3,
			},

			configdateTimePickerDate: {
				altInput: true,
				altFormat: "d/m/Y",
				dateFormat: "Y-m-d",
				locale: Portuguese,
			},

			configdateTimePickerTime: {
				enableTime: true,
				enableSeconds: false,
				noCalendar: true,
				locale: Portuguese,
			},

			coletaWebData: [],

			clienteData: [],

			coletaFixaOptions: [
				{
					label:
						"Diária (solicitação normal; veículo pode atender vários clientes)",
					value: "D",
				},
				{
					label:
						"Contrato (por expediente; veículo dedicado a um único cliente)",
					value: "C",
				},
				{
					label:
						"Multi-destinos (coleta na mesma origem para vários destinos; veículo pode atender vários clientes)",
					value: "M",
				},
			],

			sisCargaOptions: [
				{ label: "Nenhum", value: "N" },
				{ label: "Empilhadeira", value: "E" },
				{ label: "Ponte Rolante", value: "P" },
				{ label: "Manual", value: "M" },
			],

			tipoFreteOptions: [
				{ label: "Normal", value: "N" },
				{ label: "Retorno Embalagem / Beneficiamento", value: "R" },
			],

			data_not_found: false,

			tem_erros: false,
			erros_form: [],

			//Inicializar com null, assim não da o efeito de "Piscação" com os dados ocultados.
			//A variável trata oque pode exibir ou não.
			podeSolicitarColetas: null,
			clienteFromUserData: [],
		};
	},
	async created() {
		if (this.$store.state.AppActiveUser.userRole == "cliente") {
			await this.lerClienteFromUser();
		} else {
			this.podeSolicitarColetas = true;
		}

		this.$store
			.dispatch("coleta/editColeta", this.$route.params.id)
			.then((res) => {
				if (res.data.coleta.length > 0) {
					this.coletaWebData = res.data.coleta[0];
					this.getCliente();
				} else {
					this.data_not_found = true;
				}
			})
			.catch((err) => {
				console.error(err);
			});
		this.lerTipoVeiculo();
	},
	computed: {
		validateForm() {
			return !this.errors.any();
		},
		tipoVeiculo() {
			return this.$store.state.tipoVeiculo.tipoVeiculoPesqData;
		},
		clienteCombo: {
			get() {
				if (this.coletaWebData.nome == null) {
					return "Selecione o cliente";
				} else {
					return {
						nome: this.coletaWebData.nome,
					};
				}
			},
			set(obj) {
				this.coletaWebData.cod_cliente = obj.codigo;
				this.coletaWebData.cpf_cnpj = obj.cpf_cnpj;
				this.coletaWebData.nome = obj.nome;
			},
		},
		coletaFixaCombo: {
			get() {
				if (this.coletaWebData.coleta_fixa == null) {
					return "Selecione o tipo de coleta";
				} else {
					return {
						label: this.tipoColetaLabel(
							this.coletaWebData.coleta_fixa
						),
						value: this.coletaWebData.coleta_fixa,
					};
				}
			},
			set(obj) {
				this.coletaWebData.coleta_fixa = obj.value;
			},
		},
		sisCargaCombo: {
			get() {
				if (this.coletaWebData.sis_carga == null) {
					return "Selecione...";
				} else {
					return {
						label: this.sisCargaLabel(this.coletaWebData.sis_carga),
						value: this.coletaWebData.sis_carga,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.coletaWebData.sis_carga = obj.value;
				} else {
					this.coletaWebData.sis_carga = null;
				}
			},
		},
		tipoFreteCombo: {
			get() {
				if (this.coletaWebData.tipo_frete == null) {
					return "Selecione o tipo de frete";
				} else {
					return {
						label: this.tipoFreteLabel(this.coletaWebData.tipo_frete),
						value: this.coletaWebData.tipo_frete,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.coletaWebData.tipo_frete = obj.value;
				} else {
					this.coletaWebData.tipo_frete = null;
				}
			},
		},
		LocalColetaCombo: {
			get() {
				if (this.coletaWebData.local_coleta == null) {
					return "Selecione o local de coleta";
				} else {
					return {
						nome: this.coletaWebData.local_coleta,
					};
				}
			},
			set(obj) {
				this.coletaWebData.cod_loc_coleta = obj.codigo;
				this.coletaWebData.local_coleta = obj.nome;
			},
		},
		LocalEntregaCombo: {
			get() {
				if (this.coletaWebData.local_entrega == null) {
					return "Selecione o local de entrega";
				} else {
					return {
						nome: this.coletaWebData.local_entrega,
					};
				}
			},
			set(obj) {
				this.coletaWebData.cod_loc_entrega = obj.codigo;
				this.coletaWebData.local_entrega = obj.nome;
			},
		},
		tipoVeiculoCombo: {
			get() {
				if (this.coletaWebData.descr_veiculo == null) {
					return "Selecione o tipo de veiculo";
				} else {
					return {
						descricao: this.coletaWebData.descr_veiculo,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.coletaWebData.cod_tipo_veiculo = obj.codigo;
					this.coletaWebData.descr_veiculo = obj.descricao;
				} else {
					this.coletaWebData.cod_tipo_veiculo = null;
					this.coletaWebData.descr_veiculo = null;
				}
			},
		},
		receberNfCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaWebData.receber_nf_frete
				);
			},
			set(obj) {
				this.coletaWebData.receber_nf_frete = obj;
			},
		},
		aceitarFotoRomCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaWebData.aceitar_foto_rom
				);
			},
			set(obj) {
				this.coletaWebData.aceitar_foto_rom = obj;
			},
		},
		ocultarResumoCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaWebData.ocultar_resumo
				);
			},
			set(obj) {
				this.coletaWebData.ocultar_resumo = obj;
			},
		},
		entregaUrgenteCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.coletaWebData.entrega_urgente
				);
			},
			set(obj) {
				this.coletaWebData.entrega_urgente = obj;
			},
		},
	},
	methods: {
		async lerClienteFromUser() {
			await this.$store
				.dispatch(
					"cliente/lerClienteFromUser",
					this.$store.state.AppActiveUser.uid
				)
				.then((res) => {
					if (res.data.cliente.length > 0) {
						this.clienteFromUserData = res.data.cliente[0];
						this.podeSolicitarColetas =
							this.clienteFromUserData.solicitar_coletas == "S"
								? true
								: false;
					}
				})
				.catch((err) => {
					console.error(err);
				});
		},
		async lerTipoVeiculo() {
			this.$store.dispatch("tipoVeiculo/lerTipoVeiculo").catch((err) => {
				console.error(err);
			});
		},
		getCliente() {
			this.$http
				.post(
					`api/getDadosCliente`,
					{
						empresa: this.coletaWebData.empresa,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then((response) => {
					if (response.data.status) {
						this.clienteData = response.data.cliente;
					}
				})
				.catch();
		},
		getTipoVeiculo() {
			this.$store
				.dispatch("tipoVeiculo/indexTipoVeiculo")
				.catch((err) => {
					console.error(err);
				});
		},
		updateRecord() {
			this.$http
				.put(
					`api/coleta/${this.$route.params.id}`,
					{
						data_cad: this.coletaWebData.data_cad,
						hora_cad: this.coletaWebData.hora_cad,
						empresa: this.coletaWebData.empresa,
						cod_cliente: this.coletaWebData.cod_cliente,
						coleta_fixa: this.coletaWebData.coleta_fixa,
						dt_prev_coleta: this.coletaWebData.dt_prev_coleta,
						hr_prev_coleta: this.coletaWebData.hr_prev_coleta,
						dt_prev_entrega: this.coletaWebData.dt_prev_entrega,
						hr_prev_entrega: this.coletaWebData.hr_prev_entrega,
						entrega_urgente:
							this.coletaWebData.entrega_urgente == null
								? "N"
								: this.coletaWebData.entrega_urgente,
						cod_loc_coleta: this.coletaWebData.cod_loc_coleta,
						cod_loc_entrega: this.coletaWebData.cod_loc_entrega,
						solicitante: this.coletaWebData.solicitante,
						peso: this.coletaWebData.peso,
						volumes: this.coletaWebData.volumes,
						especie: this.coletaWebData.especie,
						comp_carga: this.coletaWebData.comp_carga,
						larg_carga: this.coletaWebData.larg_carga,
						alt_carga: this.coletaWebData.alt_carga,
						sis_carga: this.coletaWebData.sis_carga,
						caract_coleta: this.coletaWebData.caract_coleta,
						tipo_frete: this.coletaWebData.tipo_frete,
						cod_tipo_veiculo: this.coletaWebData.cod_tipo_veiculo,
						receber_nf_frete:
							this.coletaWebData.receber_nf_frete == null
								? "N"
								: this.coletaWebData.receber_nf_frete,
						aceitar_foto_rom:
							this.coletaWebData.aceitar_foto_rom == null
								? "N"
								: this.coletaWebData.aceitar_foto_rom,
						ocultar_resumo:
							this.coletaWebData.ocultar_resumo == null
								? "N"
								: this.coletaWebData.ocultar_resumo,
						obs_coleta: this.coletaWebData.obs_coleta,
						solic_origem_id: this.coletaWebData.solic_origem_id,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then((response) => {
					if (response.data.status) {
						//Vamos voltar ao salvar, por temos a edição na view das coletas que podem ser chamadas de qualquer local.
						this.$router.back();
					}
					if (response.data.status === false) {
						this.erros_form = response.data.erros;
						this.tem_erros = true;
						console.log(response.data.erros);
					}
				})
				.catch((e) => {
					this.erros_form = e.response.data.errors;
					this.tem_erros = true;
					console.log(e.response.data.errors);
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
		},
		exibirCamposColetaFixa() {
			var retorno = false;

			// Solicitaçoes do tipo "M"(Multi-Destinos) e "C"(contrato) não validamos: peso, volumes, especie,
			// comp_carga, larg_carga, alt_carga e sis_carga
			if (this.coletaWebData.coleta_fixa == "D") {
				//Para solicitações Diárias, só vamos exigir a validação se não for uma coleta fixa.
				//Não temos estes campos na geração de coleta fixas automatica e sempre irá gravar um coleta_fixa_id
				if (this.coletaWebData.coleta_fixa_id == null) {
					retorno = true;
				}
			}
			return retorno;
		},
	},
};
</script>

<style lang="scss">
</style>
