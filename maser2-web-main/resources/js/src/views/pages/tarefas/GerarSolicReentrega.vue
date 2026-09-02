<template>
	<div>
		<!-- Dados da coleta -->
		<div class="vx-row w-full mb-base">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="coleta_atual.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						:data="coleta_atual"
					>
						<template>
							<vs-tr>
								<vs-td class="whitespace-no-wrap">
									<div>
										<span class="font-semibold" style="font-size: 12px">Empresa</span>
									</div>
									<div>
										<span>{{coleta_atual_index.nome_empresa}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Solicitação</span>
									</div>
									<div>
										<span v-if="coleta_atual_index.numero != null">{{coleta_atual[0].numero}}</span>
										<span v-else>ID: {{coleta_atual_index.coleta_id}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Local Coleta</span>
									</div>
									<div>
										<span>{{coleta_atual_index.local_coleta | truncate(25)}}</span>
									</div>									
									<div class="flex items-center justify-center">
										<span class="mr-2" style="font-size: 12px; color: gray">{{coleta_atual_index.placa_coleta}}</span>
										<span style="font-size: 12px; color: gray">
											{{exibirDia(coleta_atual_index.dt_efet_coleta) | moment("DD MMM")}} {{coleta_atual_index.hr_sai_coleta | hora_min}}
										</span>										
										<feather-icon
											v-if="coleta_atual_index.hr_sai_coleta != null"
											class="ml-1"
											icon="CheckCircleIcon"
											svgClasses="w-4 h-4 text-success"
										/>		
									</div>																
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Local Entrega</span>
									</div>
									<div>
										<span>{{coleta_atual_index.local_entrega | truncate(25)}}</span>
									</div>											
									<div class="flex items-center justify-center">
										<span class="mr-2" style="font-size: 12px; color: gray">{{coleta_atual_index.placa_entrega}}</span>
										<span style="font-size: 12px; color: gray">
											{{exibirDia(coleta_atual_index.dt_efet_entrega) | moment("DD MMM")}} {{coleta_atual_index.hr_sai_entrega | hora_min}}
										</span>										
										<feather-icon
											v-if="coleta_atual_index.hr_sai_entrega != null"
											class="ml-1"
											icon="CheckCircleIcon"
											svgClasses="w-4 h-4 text-success"
										/>																
									</div>											
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Motivo</span>
									</div>
									<div>
										<span>{{coleta_atual_index.mot_nao_entrega | mot_nao_entrega}}</span>
									</div>
									<div>
										<span>{{coleta_atual_index.obs_nao_entrega}}</span>
									</div>
								</vs-td>								
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<!-- Notas -->
		<div class="vx-row w-full mb-base" v-show="(notas.length > 0)">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="notas.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						stripe
						:data="notas"
					>
						<div slot="header" class="flex items-center flex-grow justify-between mb-4">
							<div class="flex items-center">
								<span class="font-semibold">Definir o que fazer com cada nota</span>
							</div>							
						</div>

						<template slot="thead">
							<vs-th>Número</vs-th>
							<vs-th>Volumes</vs-th>
							<vs-th>Valor</vs-th>
							<vs-th>Histórico</vs-th>
							<vs-th class="whitespace-no-wrap">O que fazer?</vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap">{{data[indextr].numero}}</vs-td>
								<vs-td>{{data[indextr].volumes}}</vs-td>
								<vs-td class="whitespace-no-wrap">
									{{data[indextr].valor | currency('', 2, { thousandsSeparator: '.', decimalSeparator: ',' })}}
								</vs-td>																
								<vs-td class="whitespace-no-wrap">				
									<span v-if="!vueIgualTrimNull(data[indextr].mot_nao_entrega)" class="badge_vermelho">{{data[indextr].mot_nao_entrega | toLowerCase}}</span>
									<span v-if="['R', 'D'].includes(data[indextr].reentrega)" class="badge_cinza">reentrega</span>									
									<span v-if="data[indextr].substituida == 'S'" class="badge_cinza">substituída</span>
									<span v-if="!vueIgualTrimNull(data[indextr].img_recibo)" class="badge_verde">entregue</span>
								</vs-td>								
								<vs-td class="whitespace-no-wrap">
									<template v-if="(vueIgualZeroNull(data[indextr].solic_destino_id) && (data[indextr].substituida != 'S') && 
									  (!vueIgualTrimNull(data[indextr].mot_nao_entrega) || 
									  (vueIgualTrimNull(data[indextr].img_recibo) && vueIgualTrimNull(data[indextr].mot_nao_entrega))))">										
										<span class="text-primary hover:underline cursor-pointer"
											style="font-size: 12px;" @click="editRecord(indextr, data[indextr])">
											<span v-if="data[indextr].acao == null">Nenhuma</span>
											<span v-if="data[indextr].acao == 'A'">Adicionar na reentrega</span>
											<span v-if="data[indextr].acao == 'S'">Marcar como substituida</span>
										</span>										
									</template>
									<template v-else>
										<feather-icon											
											icon="LockIcon"
											svgClasses="w-5 h-5'"
										/>
									</template>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<!-- Solicitação -->
		<div class="vx-row w-full mb-base">
			<div class="vx-col w-full">
				<vx-card>
					<div class="flex items-center mb-4">
						<span class="font-semibold">Criar solicitação de reentrega</span>
					</div>

					<div class="vx-row mb-6">
						<div class="vx-col w-full">
							<label class="vs-input--label">Reentrega ou Devolução?</label>
							<v-select
								class="w-full"
								label="label"
								:options="reentregaOptions"
								:clearable="false"
								v-model="reentregaCombo"
							>
								<div slot="no-options">Opção não disponível</div>
							</v-select>
						</div>
					</div>

					<div class="vx-row">
						<div class="vx-col md:w-1/2 w-full">
							<label class="vs-input--label">Coleta</label>
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

						<div class="vx-col md:w-1/2 w-full">
							<div class="vx-row">
								<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
									<label class="vs-input--label">Data prev. coleta</label>
									<flat-pickr
										:config="configdateTimePickerDate"
										v-model="reentregaData[0].dt_prev_coleta"
										class="w-full vs-inputx vs-input--input normal hasValue"
										style="border: 1px solid rgba(0, 0, 0, 0.2);"
									/>
								</div>

								<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
									<label class="vs-input--label">Hora prev. coleta</label>
									<flat-pickr
										:config="configdateTimePickerTime"
										v-model="reentregaData[0].hr_prev_coleta"
										class="w-full vs-inputx vs-input--input normal hasValue"
										style="border: 1px solid rgba(0, 0, 0, 0.2);"
									/>
								</div>							
							</div>
						</div>						
					</div>				

					<div class="vx-row">
						<div class="vx-col md:w-1/2 w-full">
							<label class="vs-input--label">Entrega</label>
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

						<div class="vx-col md:w-1/2 w-full">
							<div class="vx-row">
								<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
									<label class="vs-input--label">Data prev. entrega</label>
									<flat-pickr
										:config="configdateTimePickerDate"
										v-model="reentregaData[0].dt_prev_entrega"
										class="w-full vs-inputx vs-input--input normal hasValue"
										style="border: 1px solid rgba(0, 0, 0, 0.2);"
									/>
								</div>

								<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
									<label class="vs-input--label">Hora prev. entrega</label>
									<flat-pickr
										:config="configdateTimePickerTime"
										v-model="reentregaData[0].hr_prev_entrega"
										class="w-full vs-inputx vs-input--input normal hasValue"
										style="border: 1px solid rgba(0, 0, 0, 0.2);"
									/>
								</div>
							</div>	
						</div>						
					</div>					

					<div class="vx-row mb-6">
						<div class="vx-col">
							<vs-checkbox v-model="entregaUrgenteCheck" vs-value="S">Entrega Urgente</vs-checkbox>
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
								v-model="reentregaData[0].solicitante"
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
								v-model="reentregaData[0].peso"
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
								v-model.number="reentregaData[0].volumes"
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
								v-model="reentregaData[0].especie"
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
								v-model="reentregaData[0].comp_carga"
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
								v-model="reentregaData[0].larg_carga"
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
								v-model="reentregaData[0].alt_carga"
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
								label="Características"
								v-model="reentregaData[0].caract_coleta"
							/>
							<span
								class="text-danger text-sm"
								v-show="errors.has('caract_coleta')"
							>{{ errors.first('caract_coleta') }}</span>
						</div>
					</div>

					<div class="vx-row">
						<div :class="'vx-col md:w-1/2 w-full mb-6'">
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

						<div :class="'vx-col md:w-1/2 w-full mb-6'">
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
					</div>					

					<div class="vx-row">
						<div class="vx-col w-full">
							<label class="vs-input--label">Observações</label>
							<vs-textarea class="w-full" rows="3" v-model="reentregaData[0].obs_coleta" />
						</div>
					</div>
				</vx-card>
			</div>
		</div>		

		<vs-popup class="holamundo" :title="'Nota: ' + nro_nota" :active.sync="popupEdit">
			<div class="vx-row mb-6">
				<div class="vx-col w-full">
					<label class="vs-input--label">Escolha a ação para esta nota fiscal</label>
					<v-select
						class="w-full"
						label="label"
						:options="acaoOptions"
						:clearable="false"
						v-model="acaoCombo"
					>
						<div slot="no-options">Opção não disponível</div>
					</v-select>					
				</div>
			</div>

			<br />
			<br />
			<br />
			<br />
			<br />

			<div class="vx-col w-full">
				<vs-button class="mr-3" type="border" @click="salvar()">Salvar</vs-button>
				<vs-button type="border" color="danger" @click="popupEdit=false">Cancelar</vs-button>
			</div>
		</vs-popup>

		<!-- Confirmação -->
		<div class="vx-row w-full mb-4">
			<div class="vx-col w-full">
				<vx-card>
					<div class="mb-6">
						<label class="font-semibold">Confirmar reentrega</label>
					</div>					
					<div class="vx-row">
						<div class="vx-col">
							<vs-checkbox
								class="mb-6"
								v-model="confirmar"
								vs-value="S"
							>Eu conferi os dados e confirmo a criação da solicitação</vs-checkbox>
							<vs-button
								:disabled="!validateForm || (validateForm && confirmar == null)"
								@click="gerarSolicReentrega()"
							>Gerar Solicitação</vs-button>
						</div>
					</div>
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import controleMixins from "@/mixins/controleMixins";
import labelsMixins from "@/mixins/labelsMixins";
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";

export default {
	mixins: [procsMixins, controleMixins, labelsMixins],
	components: {
		"v-select": vSelect,
		flatPickr
	},
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
				locale: Portuguese
			},

			coleta_atual: [],
			coleta_atual_index: [],
			notas: [],

			clienteData: [],

			popupEdit: false,
			nro_nota: null,
			acaoNota: null,			

			confirmar: null,

			reentregaData: [
				{					
					reentrega: 'R',
					dt_prev_coleta: this.dataHoraInsert("data"),
					hr_prev_coleta: this.dataHoraInsert("hora"),
					dt_prev_entrega: this.dataHoraInsert("data"),
					hr_prev_entrega: this.dataHoraInsert("hora"),					
					nome_coleta: null,
					cod_loc_coleta: null,
					nome_entrega: null,
					cod_loc_entrega: null,
					entrega_urgente: null,
					solicitante: null,
					peso: null,
					volumes: null,
					especie: null,
					comp_carga: null,
					larg_carga: null,
					alt_carga: null,
					sis_carga: null,
					caract_coleta: null,
					tipo_frete: "N",
					cod_tipo_veiculo: null,
					descricao_veiculo: null,
					obs_coleta: null,
				},
			],

			reentregaOptions: [
				{ label: "Reentrega", value: "R" }, 
				{ label: "Devolução", value: "D" }
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

			acaoOptions: [
				{ label: "Nenhuma", value: null },
				{ label: "Adicionar na reentrega", value: "A" },
				{ label: "Marcar como substituida", value: "S" }
			],
		};
	},
	async created() {
		await this.retornarEntregasNaoRealizadasReentregaAtual();		
		this.getCliente();
		this.lerTipoVeiculo();
	},
	computed: {
		validateForm() {
			return !this.errors.any();
		},
		tipoVeiculo() {
			return this.$store.state.tipoVeiculo.tipoVeiculoPesqData;
		},
		veiculo() {
			return this.$store.state.veiculo.veiculoPesqData;
		},
		clienteCombo: {
			get() {
				if (this.local_entrega == null) {
					return "Selecione o local de entrega";
				} else {
					return {
						nome_cnpj: this.local_entrega
					};
				}
			},
			set(obj) {
				this.cod_loc_entrega = obj.codigo;
				this.local_entrega = obj.nome;
				this.cpf_cnpj = obj.cpf_cnpj;
			}
		},
		reentregaCombo: {
			get() {
				if (this.reentregaData[0].reentrega == null) {
					return "Selecione o tipo de solicitação";
				} else {
					return {
						label: this.reentregaLabel(
							this.reentregaData[0].reentrega
						),
						value: this.reentregaData[0].reentrega,
					};
				}
			},
			set(obj) {
				this.reentregaData[0].reentrega = obj.value;
			},
		},
		sisCargaCombo: {
			get() {
				if (this.reentregaData[0].sis_carga == null) {
					return "Selecione...";
				} else {
					return {
						label: this.sisCargaLabel(
							this.reentregaData[0].sis_carga
						),
						value: this.reentregaData[0].sis_carga,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.reentregaData[0].sis_carga = obj.value;
				} else {
					this.reentregaData[0].sis_carga = null;
				}
			},
		},
		tipoFreteCombo: {
			get() {
				if (this.reentregaData[0].tipo_frete == null) {
					return "Selecione o tipo de frete";
				} else {
					return {
						label: this.tipoFreteLabel(
							this.reentregaData[0].tipo_frete
						),
						value: this.reentregaData[0].tipo_frete,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.reentregaData[0].tipo_frete = obj.value;
				} else {
					this.reentregaData[0].tipo_frete = null;
				}
			},
		},
		LocalColetaCombo: {
			get() {
				if (this.reentregaData[0].nome_coleta == null) {
					return "Selecione o local de coleta";
				} else {
					return {
						nome: this.reentregaData[0].nome_coleta,
					};
				}
			},
			set(obj) {
				this.reentregaData[0].cod_loc_coleta = obj.codigo;
				this.reentregaData[0].nome_coleta = obj.nome;
			},
		},
		LocalEntregaCombo: {
			get() {
				if (this.reentregaData[0].nome_entrega == null) {
					return "Selecione o local de entrega";
				} else {
					return {
						nome: this.reentregaData[0].nome_entrega,
					};
				}
			},
			set(obj) {
				this.reentregaData[0].cod_loc_entrega = obj.codigo;
				this.reentregaData[0].nome_entrega = obj.nome;
			},
		},
		tipoVeiculoCombo: {
			get() {
				if (this.reentregaData[0].descricao_veiculo == null) {
					return "Selecione o tipo de veiculo";
				} else {
					return {
						descricao: this.reentregaData[0].descricao_veiculo,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.reentregaData[0].cod_tipo_veiculo = obj.codigo;
					this.reentregaData[0].descricao_veiculo = obj.descricao;
				} else {
					this.reentregaData[0].cod_tipo_veiculo = null;
					this.reentregaData[0].descricao_veiculo = null;
				}
			},
		},		
		entregaUrgenteCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.reentregaData[0].entrega_urgente
				);
			},
			set(obj) {
				this.reentregaData[0].entrega_urgente = obj;
			},
		},
		acaoCombo: {
			get() {
				if (this.acaoNota == null) {
					return {
						label: 'Nenhuma',
						value: null,
					};
				} else if (this.acaoNota == 'A') {
					return {
						label: 'Adicionar na reentrega',
						value: 'A',
					};					
				} else if (this.acaoNota == 'S') {
					return {
						label: 'Marcar como substituida',
						value: 'S',
					};
				}
			},
			set(obj) {				
				this.acaoNota = obj.value;
			},
		},
	},
	methods: {
		async retornarEntregasNaoRealizadasReentregaAtual() {
			await this.$http
				.get(`api/RetornarEntregasNaoRealizadasReentrega`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						coleta_id: this.$route.params.coleta_id
					}
				})
				.then(async response => {
					this.coleta_atual = response.data.dados.coletas;
					this.coleta_atual_index = response.data.dados.coletas[0];
					this.notas = response.data.dados.coletas[0].notas;

					await this.inicializarValores(this.coleta_atual_index);					
				})
				.catch();
		},
		async getCliente() {
			await this.$http
				.post(
					`api/getDadosCliente`,
					{
						empresa: this.coleta_atual[0].empresa
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
		async lerTipoVeiculo() {
			this.$store.dispatch("tipoVeiculo/lerTipoVeiculo").catch((err) => {
				console.error(err);
			});
		},
		async editRecord(index, dados) {
			this.nro_nota = dados.numero;			
			this.acaoNota = dados.acao;
			this.nota_index = index;			
			this.popupEdit = !this.popupEdit;
		},
		async salvar() {
			if (this.acaoNota == null) {
				this.notas[this.nota_index].acao = null;
			} else if (this.acaoNota == 'A') {
				this.notas[this.nota_index].acao = 'A';
			} else if (this.acaoNota == 'S') {
				this.notas[this.nota_index].acao = 'S';
			}
			this.popupEdit = false;
		},
		async gerarSolicReentrega() {
			await this.$vs.loading({ scale: 0.5 });
			await this.$http
				.post(
					`api/GerarSolicReentrega`,
					{
						coleta_id: this.coleta_atual_index.coleta_id,
						cod_loc_coleta: this.reentregaData[0].cod_loc_coleta,
						dt_prev_coleta: this.reentregaData[0].dt_prev_coleta,
						hr_prev_coleta: this.reentregaData[0].hr_prev_coleta,
						cod_loc_entrega: this.reentregaData[0].cod_loc_entrega,
						dt_prev_entrega: this.reentregaData[0].dt_prev_entrega,
						hr_prev_entrega: this.reentregaData[0].hr_prev_entrega,
						entrega_urgente:
							this.reentregaData[0].entrega_urgente == null
								? "N"
								: this.reentregaData[0].entrega_urgente,
						solicitante: this.reentregaData[0].solicitante,
						peso: this.reentregaData[0].peso,
						volumes: this.reentregaData[0].volumes,
						especie: this.reentregaData[0].especie,
						sis_carga: this.reentregaData[0].sis_carga,
						alt_carga: this.reentregaData[0].alt_carga,
						larg_carga: this.reentregaData[0].larg_carga,
						comp_carga: this.reentregaData[0].comp_carga,
						tipo_frete: this.reentregaData[0].tipo_frete,
						cod_tipo_veiculo: this.reentregaData[0].cod_tipo_veiculo,						
						caract_coleta: this.reentregaData[0].caract_coleta,
						obs_coleta: this.reentregaData[0].obs_coleta,
						reentrega: this.reentregaData[0].reentrega,						
						lista_notas: this.notas
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(async response => {
					await this.$vs.loading.close();

					if (response.data.retorno.cod_retorno != "Z100") {
						await this.$vs.notify({
							time: 10000,
							text: response.data.erros[0].msg_retorno,
							iconPack: "feather",
							icon: "icon-alert-circle",
							color: "danger"
						});
					} else {
						this.$router
							.push("/definir-reentrega")
							.catch(() => {});
					}
				})
				.catch(async error => {
					await this.$vs.loading.close();
				});
		},
		retValorCheckBoxSimNao(value) {
			if (value === "S") {
				return "S";
			} else {
				return null;
			}
		},
		inicializarValores(dados) {
			this.reentregaData[0].solicitante 	   = dados.solicitante;
			this.reentregaData[0].especie 		   = dados.especie;
			this.reentregaData[0].peso 	           = dados.peso;
			this.reentregaData[0].volumes 	       = dados.volumes;
			this.reentregaData[0].comp_carga 	   = dados.comp_carga;
			this.reentregaData[0].larg_carga 	   = dados.larg_carga;
			this.reentregaData[0].alt_carga 	   = dados.alt_carga;
			this.reentregaData[0].caract_coleta    = dados.caract_coleta;
			this.reentregaData[0].cod_tipo_veiculo = dados.cod_tipo_veiculo;
			this.reentregaData[0].sis_carga        = dados.sis_carga;
			this.reentregaData[0].tipo_frete       = dados.tipo_frete;
		},
		dataHoraInsert(dataHora = "data") {
			var moment = require("moment");
			var data = new Date();

			if (dataHora == "data") {
				var retorno = moment(data).format("YYYY-MM-DD");
			} else {
				var retorno = moment(data).format("HH:mm:ss");
			}
			return retorno;
		},
		exibirCamposColetaFixa() {
			var retorno = false;

			// Solicitaçoes do tipo "M"(Multi-Destinos) e "C"(contrato) não validamos: peso, volumes, especie,
			// comp_carga, larg_carga, alt_carga e sis_carga
			if (this.coleta_atual_index.coleta_fixa == "D") {
				retorno = true;
			}
			return retorno;
		},
	}
};
</script>

<style lang="scss" scoped>
</style>

