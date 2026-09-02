<template>
	<div id="invoice-page">

		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card id="invoice-container">
					<vs-table						
						ref="table"
						:noDataText="coletasResumoDiaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"						
						search
						:max-items="coletasResumoDiaData.length"
						stripe
						:data="coletasResumoDiaData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
								<!-- div para alinhar o dropdown a direita. Se tiver algum filtro simples de uma linha, pode ser colocado aqui.
								Ex: Filtro de ativos dos motoristas-->																
								<div class="flex items-center">
									<span @click="setToday" class="cursor-pointer mr-4" 									  
									  :style="dataSetada == 'hoje' ? 
									   'display: inline-block; border: 1px solid #ccc; padding: 5px; border-radius: 20px; border-color: #174083; line-height: 25px;' : 
									   'display: inline-block; border: 1px solid #ccc; padding: 5px; border-radius: 20px; border-color: #cccccc; line-height: 25px;'">
    									<span :class="dataSetada == 'hoje' ? 'ml-6 mr-6 font-semibold text-primary' : 'ml-6 mr-6'">
											Hoje
										</span>
									</span>
									<span @click="setTomorrow" class="cursor-pointer mr-4" 
									  :style="dataSetada == 'amanha' ? 
									   'display: inline-block; border: 1px solid #ccc; padding: 5px; border-radius: 20px; border-color: #174083; line-height: 25px;' : 
									   'display: inline-block; border: 1px solid #ccc; padding: 5px; border-radius: 20px; border-color: #cccccc; line-height: 25px;'">
    									<span :class="dataSetada == 'amanha' ? 'ml-2 mr-2 font-semibold text-primary' : 'ml-2 mr-2'">
											Amanhâ
										</span>
									</span>
									<flat-pickr 
										:config="configdateTimePickerDate"
										v-model="filtros[0].data_prevista"
										class="w-full vs-inputx vs-input--input normal hasValue text-center font-bold mr-6 dataBorderRadius"
									/>									
								</div>								
							</div>

							<div class="invoice-hidden flex flex-wrap-reverse items-center">
								<feather-icon
									@click="refresh"
									icon="RotateCwIcon"
									svgClasses="h-4 w-4"
									class="cursor-pointer mr-4"
								/>
								<feather-icon
										@click="printInvoice"
										class="ml-4 mr-4"
										icon="PrinterIcon"
										svgClasses="mr-4 w-5 h-5 hover:text-primary stroke-current cursor-pointer"
								></feather-icon>
							</div>
						</div>											

						<template slot="thead">
							<vs-th sort-key="dt_prev_col_ent">Horário</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="etapa">Etapa</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Local de Coleta</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Local de Entrega</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="descricao_tipo_veiculo">Tipo Veículo</vs-th>							
							<vs-th class="invoice-hidden"></vs-th> <!-- Editar -->
							<vs-th class="whitespace-no-wrap" sort-key="placa">Placa</vs-th>														
							<vs-th class="whitespace-no-wrap" sort-key="nome_motorista">Motorista
								<feather-icon v-if="windowWidth <= 1024" icon="ChevronsRightIcon" svgClasses="w-4 h-4 text-primary" class="ml-24" />
							</vs-th>							
							<vs-th class="invoice-hidden" sort-key="volumes">Carga</vs-th>																	
						</template>

						<template slot-scope="{data}">							
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">

								<!-- Hora Previsão -->
								<vs-td class="whitespace-no-wrap">									
									<div :style="emAtraso(data[indextr].dt_prev_col_ent, data[indextr].hr_prev_col_ent)">
										<span :style="emAtraso(data[indextr].dt_prev_col_ent, data[indextr].hr_prev_col_ent)">
											{{data[indextr].dt_prev_col_ent | moment("DD MMM") | toUpperCase}}
										</span>
									</div>
									<div :style="emAtraso(data[indextr].dt_prev_col_ent, data[indextr].hr_prev_col_ent)">
										<span :style="windowWidth <= 1024 ? 'font-size: 22px' : 'font-size: 24px'">
											{{data[indextr].hr_prev_col_ent | hora_min }}
										</span>
									</div>
								</vs-td>

								<!-- Etapa -->
								<vs-td>
									<span v-if="data[indextr].etapa == 'C'" class="badge_azul" :style="windowWidth <= 1024 ? 'font-size: 80%' : 'font-size: 85%'">
										Coleta
									</span>
									<span v-if="data[indextr].etapa == 'E'" class="badge_verde invoice-bold" :style="windowWidth <= 1024 ? 'font-size: 80%' : 'font-size: 85%'">
										Entrega
									</span>
								</vs-td>

								<!-- Local de Coleta -->
								<vs-td>
									<div>
										<span
											class="text-inherit hover:text-success stroke-current cursor-pointer"
											@click="fillSolicitacoes(data[indextr]);
														exibirDados('coleta', data[indextr], data[indextr].local_coleta, data[indextr].cod_loc_coleta,
													data[indextr].solicitante, 
													data[indextr].endereco_coleta, data[indextr].bairro_coleta, 
													data[indextr].cidade_coleta, data[indextr].uf_coleta, 
													data[indextr].cep_coleta, data[indextr].fone_coleta, 
													data[indextr].hr_ini_coleta_man, data[indextr].hr_fim_coleta_man,
													data[indextr].hr_ini_coleta_tar, data[indextr].hr_fim_coleta_tar)"
											>{{data[indextr].local_coleta | truncate(25)}}
										</span>
									</div>
									<span v-if="data[indextr].carga_pavilhao == 'S'" class="badge_cinza mr-2 mb-2 invoice-bold">pavilhão</span>
								</vs-td>
								
								<!-- Local de Entrega -->
								<vs-td>
									<div>
										<span
											class="text-inherit hover:text-success stroke-current cursor-pointer"
											@click="fillSolicitacoes(data[indextr]);
												exibirDados('entrega', data[indextr], data[indextr].local_entrega, data[indextr].cod_loc_entrega, null, 
													data[indextr].endereco_entrega, data[indextr].bairro_entrega, 
													data[indextr].cidade_entrega, data[indextr].uf_entrega, 
													data[indextr].cep_entrega, data[indextr].fone_entrega, 
													data[indextr].hr_ini_entrega_man, data[indextr].hr_fim_entrega_man,
													data[indextr].hr_ini_entrega_tar, data[indextr].hr_fim_entrega_tar)"
										>{{data[indextr].local_entrega | truncate(25)}}</span>
									</div>									
									<span v-if="data[indextr].entrega_urgente == 'S'" class="badge_vermelho mr-2 mb-2 invoice-bold">urgente</span>									
									<span v-if="['R', 'D'].includes(data[indextr].reentrega)" class="badge_cinza invoice-bold">reentrega</span>
								</vs-td>

								<!-- Tipo Veículo -->
								<vs-td class="whitespace-no-wrap">
									<div v-if="data[indextr].descricao_tipo_veiculo != null">
										<span :style="windowWidth <= 1024 ? 'font-size: 12px' : ''">{{data[indextr].descricao_tipo_veiculo}}</span>										
									</div>
								</vs-td>

								<!-- Edit -->
								<vs-td class="invoice-hidden">
									<feather-icon
										v-if="(data[indextr].definir_veiculo_previsto == 'S' || data[indextr].definir_motorista_previsto == 'S')"
										icon="EditIcon"										
										svgClasses="w-5 h-5 hover:text-primary stroke-current cursor-pointer"
										@click="definirVeiculoMotorista(data[indextr])"
									/>
								</vs-td>

								<!-- Veículo -->
								<vs-td class="whitespace-no-wrap">
									<div v-if="data[indextr].placa != null">										
										<span :style="windowWidth <= 1024 ? 'font-size: 12px' : ''">{{data[indextr].placa}}</span>
									</div>
								</vs-td>									

								<!-- Motorista -->
								<vs-td>
									<div>{{data[indextr].nome_motorista | truncate(15)}}</div>
									<div v-if="data[indextr].nome_motorista != null" class="flex items-center">
										<span v-if="((data[indextr].hr_ini_exped != null) && (data[indextr].hr_fim_exped != null))" 
										  :style="data[indextr].exped_ok == 'S' ? 'font-size: 12px; color:gray' : 'font-size: 12px; color:red'" class="whitespace-no-wrap">
											{{data[indextr].hr_ini_exped | hora_min }} - {{data[indextr].hr_fim_exped | hora_min }}
										</span>
										<span v-else style="font-size: 12px; color:red" class="whitespace-no-wrap">
											{{data[indextr].msg_exped}}
										</span>										
										<vx-tooltip
											v-if="data[indextr].msg_exped != ''"
											:text="data[indextr].msg_exped"
											position="top"
										>
											<feather-icon
												class="ml-1 mt-1"
												:icon="data[indextr].exped_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
												:svgClasses="data[indextr].exped_ok == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-danger'"
											></feather-icon>
										</vx-tooltip>
										<feather-icon
											class="ml-1"
											v-else
											:icon="data[indextr].exped_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].exped_ok == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-danger'"
										></feather-icon>
									</div>									
								</vs-td>								

								<!-- Carga -->
								<vs-td class="invoice-hidden whitespace-no-wrap">									
									<div class="flex flex-items-center">
										<span>
											<vx-tooltip class="mr-4" text="Informações da carga" position="top">
												<feather-icon
													icon="PackageIcon"
													svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
													@click="fillSolicitacoes(data[indextr]); exibirDadosCarga(data[indextr])"
												></feather-icon>
											</vx-tooltip>
										</span>
										<span v-if="!vueIgualTrimNull(data[indextr].url_imagem)">
											<vx-tooltip text="Clique para visualizar a foto da carga" position="top">
												<feather-icon
													icon="ImageIcon"
													svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
													@click="exibirFotoCarga(data[indextr])"
												></feather-icon>
											</vx-tooltip>
										</span>
									</div>
								</vs-td>

							</vs-tr>

						</template>
					</vs-table>

					<!-- Rodapé -->													
					<div class="invoice-hidden flex mt-6">
						<div class="flex items-center whitespace-no-wrap mr-4">																														
							<feather-icon icon="CheckCircleIcon" svgClasses="h-4 w-4" class="mr-2" />
							<span>Novas coletas</span>
						</div>
						<div class="flex items-center whitespace-no-wrap mr-4">																														
							<feather-icon icon="CheckCircleIcon" svgClasses="h-4 w-4" class="mr-2" />
							<span>Coletas andamento</span>
						</div>
						<div class="flex items-center whitespace-no-wrap">																														
							<feather-icon icon="CheckCircleIcon" svgClasses="h-4 w-4" class="mr-2" />
							<span>Entregas pendentes</span>
						</div>
					</div>

				</vx-card>
			</div>
		</div>

		<!-- Endereços -->
		<vs-popup class="holamundo" :title="tituloPopup" :active.sync="popupActive">
			<p>
				<template>
					<div class="flex items-center">
						<span class="mr-1 font-semibold">Local {{funcaoPopup}}:</span>
						<span>{{localPopup}}</span>
						<router-link
							v-if="((localGeo_lat != 0) & (localGeo_lat != null) & 
								   (localGeo_lng != 0) & (localGeo_lng != null))"
							class="ml-2"
							:to="{ name: 'cliente-mapa', params: { nome: encodeURIComponent(localPopup), lat: localGeo_lat, lng: localGeo_lng }}"
							target="_blank"
						>
							<feather-icon
								icon="MapPinIcon"
								svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
							/>
						</router-link>
						<span v-else>
							<feather-icon class="ml-2" icon="MapPinIcon" svgClasses="w-5 h-5" />
						</span>
					</div>
				</template>
			</p>

			<p v-if="enderecoPopup != null || fonePopup != null" class="mt-4">
				<template>
					<div v-if="enderecoPopup != null">
						<span class="mr-1 font-semibold">Endereço:</span>
						{{enderecoPopup}}
					</div>
					<div v-if="fonePopup != null">
						<span class="mr-1 font-semibold">Telefone:</span>
						{{fonePopup}}
					</div>
				</template>
			</p>

			<p v-if="horario != null" class="mt-4">
				<span class="mr-1 font-semibold">Horários para {{funcaoPopup}}:</span>
				{{horario}}
			</p>
			<p v-if="solicitante != null" class="mt-4">
				<span class="mr-1 font-semibold">Solicitante:</span>
				{{solicitante}}
			</p>

			<template v-if="(funcaoPopup == 'coleta')">
				<p v-if="dadosSolic.dt_efet_coleta != null || dadosSolic.hr_partida_coleta != null || dadosSolic.hr_cheg_coleta != null || dadosSolic.hr_atend_coleta != null || dadosSolic.hr_sai_coleta != null"
					class="mt-4">
					<template>
						<div v-if="dadosSolic.dt_efet_coleta != null">
							<span class="mr-1 font-semibold">Data da coleta:</span>
							{{dadosSolic.dt_efet_coleta | moment("DD MMM YYYY")}}
						</div>
						<div>
							<span v-if="dadosSolic.hr_partida_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Partida:</span>
								{{dadosSolic.hr_partida_coleta | hora_min}}
							</span>
							<span v-if="dadosSolic.hr_cheg_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Chegada:</span>
								{{dadosSolic.hr_cheg_coleta | hora_min}}
							</span>
							<span v-if="dadosSolic.hr_atend_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Atendimento:</span>
								{{dadosSolic.hr_atend_coleta | hora_min}}
							</span>
							<span v-if="dadosSolic.hr_sai_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Saída:</span>
								{{dadosSolic.hr_sai_coleta | hora_min}}
							</span>
						</div>
					</template>
				</p>

				<p v-if="dadosSolic.placa_coleta != null" class="mt-4">
					<template>
						<div v-if="dadosSolic.placa_coleta != null">
							<span class="mr-1 font-semibold">Veículo:</span>
							{{dadosSolic.placa_coleta}}
						</div>
					</template>
				</p>
				<p v-if="veicSolic != null" class="mt-4">
					<span class="mr-1 font-semibold">Veículo solicitado:</span>
					{{veicSolic}}
				</p>
				<p v-if="veicNec != null">
					<span class="mr-1 font-semibold">Veículo necessário:</span>
					{{veicNec}}
				</p>
			</template>

			<template v-if="(funcaoPopup == 'entrega')">
				<p v-if="dadosSolic.dt_efet_entrega != null || dadosSolic.hr_partida_entrega != null || dadosSolic.hr_cheg_entrega != null || dadosSolic.hr_atend_entrega != null || dadosSolic.hr_sai_entrega != null"
					class="mt-4">
					<template>
						<div v-if="dadosSolic.dt_efet_entrega != null">
							<span class="mr-1 font-semibold">Data da entrega:</span>
							{{dadosSolic.dt_efet_entrega | moment("DD MMM YYYY")}}
						</div>
						<div>
							<span v-if="dadosSolic.hr_partida_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Partida:</span>
								{{dadosSolic.hr_partida_entrega | hora_min}}
							</span>
							<span v-if="dadosSolic.hr_cheg_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Chegada:</span>
								{{dadosSolic.hr_cheg_entrega | hora_min}}
							</span>
							<span v-if="dadosSolic.hr_atend_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Atendimento:</span>
								{{dadosSolic.hr_atend_entrega | hora_min}}
							</span>
							<span v-if="dadosSolic.hr_sai_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Saída:</span>
								{{dadosSolic.hr_sai_entrega | hora_min}}
							</span>
						</div>
					</template>
				</p>

				<p v-if="dadosSolic.placa_entrega != null" class="mt-4">
					<template>
						<div v-if="dadosSolic.placa_entrega != null">
							<span class="mr-1 font-semibold">Veículo:</span>
							{{dadosSolic.placa_entrega}}
						</div>						
					</template>
				</p>

				<p v-if="dadosSolic.recebedor != null" class="mt-4">
					<span class="mr-1 font-semibold">Recebedor:</span>
					{{dadosSolic.recebedor}}
				</p>
			</template>
		</vs-popup>

		<!-- Carga -->
		<vs-popup class="holamundo" :title="tituloPopup" :active.sync="popupCargaActive">						
			<p v-if="(carga != null || dimensao != null)">
				<span class="mr-1 font-semibold">Carga:</span>
				<span v-if="carga != null" class="mr-1">{{carga}}</span>
				<span v-if="dimensao != null">{{dimensao}}</span>
			</p>
			<p v-if="caracteristicas != null" class="mt-4">
				<span class="mr-1 font-semibold">Características:</span>
				{{caracteristicas}}
			</p>
			<p class="mt-4">
				<span class="mr-1 font-semibold">Status:</span>				
				{{status | coleta_status}}
				<span v-if="coleta_fixa == 'M' && vueIgualZeroNull(solic_origem_id) && status == 'CR'">
					<span v-if="qtde_notas_distrib > 0">(Aguardando distribuição)</span>
					<span v-else>(Distribuída)</span>
				</span>				
				<span v-if="['EN', 'EP'].includes(status)">(Aguardando reentrega)</span>
			</p>
		</vs-popup>

		<!-- Foto -->
		<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
			<div align="center">
				<img :src="foto" height="800px" />
			</div>
		</vs-popup>

		<!-- Organizar Resumo -->
		<vs-popup class="holamundo" :title="etapa == 'C' ? 'Definir veículo e motorista: COLETA' : 'Definir veículo e motorista: ENTREGA'" :active.sync="popupResumo">
			<div class="vx-row mb-6">
				<div class="vx-col w-full">
					<label v-if="etapa == 'C'" class="vs-input--label font-semibold">Veículo previsto para Coleta</label>
					<label v-if="etapa == 'E'" class="vs-input--label font-semibold">Veículo previsto para Entrega</label>

					<v-select class="w-full" label="placa" :options="veiculo" clearable :disabled="definir_veiculo_previsto == 'S' ? false : true" v-model="veiculoCombo">
						<template v-slot:option="option">{{ option.placa }}</template>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<vs-alert v-if="definir_veiculo_previsto == 'N'" icon-pack="feather" icon="icon-info" class="h-full my-4 mb-6" color="warning">				
				<div>{{status | coleta_status}}: Veículo não pode ser alterado.</div>
			</vs-alert>

			<div class="vx-row mb-6">
				<div class="vx-col w-full">
					<label v-if="etapa == 'C'" class="vs-input--label font-semibold">Motorista previsto para Coleta</label>
					<label v-if="etapa == 'E'" class="vs-input--label font-semibold">Motorista previsto para Entrega</label>

					<v-select class="w-full" label="nome" :options="motoristaData" clearable v-model="motoristaCombo">
						<template v-slot:option="option">{{ option.nome }}</template>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<br />
			<br />
			<br />
			<br />
			<br />
			<br />			
			<br />
			<br />

			<vs-button
				type="border"				
				@click="definirRoteiroPrevisto(coleta_id, placaSel[0].placa, status, motoristaSel[0].id, etapa)"
			>Confirmar</vs-button>
		</vs-popup>		

	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";

import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";
import vSelect from "vue-select";

export default {
	mixins: [controleMixins, procsMixins],
	components: { flatPickr, vSelect },
	data() {
		return {			
			isMounted: false,						

			//Dados da Coleta - Solicitações Pendentes.
			dadosSolic: [],

			popupCargaActive: false,
			popupActive: false,
			tituloPopup: null,
			funcaoPopup: null,
			localPopup: null,
			localGeo_lat: null,
			localGeo_lng: null,
			enderecoPopup: null,
			fonePopup: null,
			horario: null,
			solicitante: null,			
			veicSolic: null,
			veicNec: null,	
			carga: null,
			dimensao: null,
			caracteristicas: null,		
			status: null,
			coleta_fixa: null,
			solic_origem_id: null,
			qtde_notas_distrib: null,

			dataSetada: 'hoje',

			filtros: [
				{
					data_prevista: new Date()
				}
			],

			configdateTimePickerDate: {
				altInput: true,
				altFormat: "d/m/Y",
				dateFormat: "Y-m-d",
				locale: Portuguese
			},

			popupResumo: false,
			coleta_id: 0,
			motoristaSel: [{id: null, nome: null}],
			placaSel: [{placa: null}],
			placaOld: [{placa: null}],
			etapa: null,
			definir_veiculo_previsto: null
		};
	},
	async created() {
		const payload = {
			data_prevista: this.filtros[0].data_prevista,
		};
		await this.retornarColetasResumoDia(payload);
		await this.getDataAtual();
	},
	mounted() {
		this.wasSidebarOpen = this.$store.state.reduceButton;
		this.$store.commit("TOGGLE_REDUCE_BUTTON", true);
		this.$emit("setAppClasses", "invoice-page")
		this.$nextTick(() => {document.title = "MASER - Resumo do Dia";});
		this.isMounted = true;
	},
	beforeDestroy() {
		document.title = "MASER";
		if (!this.wasSidebarOpen) {
			this.$store.commit("TOGGLE_REDUCE_BUTTON", false);
		}
	},
	computed: {
		coletasResumoDiaData() {
			return this.$store.state.controle.dadosColetasResumoDiaData;
		},
		dataAtual() {
			return this.$store.state.dataAtual.dataAtual;
		},
		motoristaData() {
			return this.$store.state.motorista.motoristaData;
		},
		motoristaCombo: {
			get() {
				if (this.motoristaSel[0].id == null) {
					return "Selecione o motorista";
				} else {
					return {
						nome: this.motoristaSel[0].nome,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.motoristaSel[0].id = obj.id;
					this.motoristaSel[0].nome = obj.nome;
				} else {
					this.motoristaSel[0].id = null;
					this.motoristaSel[0].nome = null;
				}
			},
		},
		veiculo() {
			return this.$store.state.veiculo.veiculoPesqData;
		},
		veiculoCombo: {
			get() {
				if (this.placaSel[0].placa == null) {
					return "Selecione o veículo";
				} else {
					return {
						placa: this.placaSel[0].placa
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.placaSel[0].placa = obj.placa;
				} else {
					this.placaSel[0].placa = this.placaOld[0].placa;
				}
			}
		},
	},
	watch: {
		filtros: {
			handler: async function(newValue, oldValue) {
				await this.refresh();
				await this.botaoDataSelecionado();
			},
			deep: true
		}
	},
	methods: {
		async getDataAtual() {
			await this.$store
				.dispatch("dataAtual/getDataAtual")
				.catch((err) => {
					console.error(err);
				});
		},		
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			const payload = {
				data_prevista: this.filtros[0].data_prevista,
			};
			await this.retornarColetasResumoDia(payload);
			await this.getDataAtual();		
			await this.$vs.loading.close();
		},
		exibirFotoCarga(dados) {
			let titulo;
			if (dados.numero != null) {
				titulo = "Carga da coleta: " + dados.numero;
			} else {
				titulo = "Carga da coleta: ID " + dados.coleta_id;
			}
			this.exibirFoto(dados.url_imagem, titulo);
		},
		fillSolicitacoes(solicitacoes) {
			this.dadosSolic = solicitacoes;
		},
		exibirDados(
			funcao,
			dadosColeta,
			local,
			cod,
			solicitante,			
			endereco,
			bairro,
			cidade,
			uf,
			cep,
			fone,
			horaIniMan,
			horaFimMan,
			horaIniTar,
			horaFimTar
		) {
			if (funcao == "coleta") {
				this.tituloPopup = "Dados da coleta";
				this.funcaoPopup = "coleta";
				this.localGeo_lat = dadosColeta.geo_lat_coleta;
				this.localGeo_lng = dadosColeta.geo_lng_coleta;
			} else {
				this.tituloPopup = "Dados da entrega";
				this.funcaoPopup = "entrega";
				this.localGeo_lat = dadosColeta.geo_lat_entrega;
				this.localGeo_lng = dadosColeta.geo_lng_entrega;
			}

			this.localPopup = local + " (" + cod + ")";
			this.solicitante = solicitante;			
			this.veicSolic = dadosColeta.descricao_tipo_veiculo;
			this.veicNec = dadosColeta.descricao_tipo_veiculo_nec;

			this.enderecoPopup = this.montarEnderecoCompleto(
				endereco,
				bairro,
				cidade,
				uf,
				cep
			);

			this.fonePopup = fone;

			this.horario = this.montarHorario(
				horaIniMan,
				horaFimMan,
				horaIniTar,
				horaFimTar
			);

			this.popupActive = !this.popupActive;
		},
		exibirDadosCarga(dadosColeta) {
			this.tituloPopup = "Informações da carga";
			this.caracteristicas = dadosColeta.caract_coleta;			
			this.carga = this.montarCarga(
				dadosColeta.volumes,
				dadosColeta.especie,
				dadosColeta.peso
			);			
			this.dimensao = this.montarDimensao(
				dadosColeta.comp_carga,
				dadosColeta.larg_carga,
				dadosColeta.alt_carga
			);
			this.status = dadosColeta.status,
			this.coleta_fixa = dadosColeta.coleta_fixa,
			this.solic_origem_id = dadosColeta.solic_origem_id,
			this.qtde_notas_distrib = dadosColeta.qtde_notas_distrib,
			this.popupCargaActive = !this.popupCargaActive;
		},
		async setToday() {
			var today = new Date();
			this.setDate(today);			
		},
		async setTomorrow() {
			var tomorrow = this.getTomorrow();			
			this.setDate(tomorrow);			
		},
		async setDate(date) {
			const formattedDate = this.formatDate(date);

			if (this.filtros[0].data_prevista !== formattedDate) {
				this.filtros[0].data_prevista = date;		
			}
		},
		async botaoDataSelecionado() {
			this.dataSetada = null;
			
			const today = new Date();
			const formattedToday = this.formatDate(today);
			const formattedTomorrow = this.formatDate(this.getTomorrow());

			if (this.filtros[0].data_prevista === formattedToday) {
				this.dataSetada = 'hoje';
			} else if (this.filtros[0].data_prevista === formattedTomorrow) {
				this.dataSetada = 'amanha';
			}
		},
		getTomorrow() {
			const tomorrow = new Date();
			tomorrow.setDate(tomorrow.getDate() + 1);
			return tomorrow;
		},
		formatDate(date) {
			const year = date.getFullYear();
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const day = String(date.getDate()).padStart(2, '0');
			return `${year}-${month}-${day}`;
		},
		async lerVeiculo() {
			this.$store.dispatch("veiculo/lerVeiculo").catch(err => {
				console.error(err);
			});
		},
		async getMotorista() {
			this.motoristaSel[0].id = null;
			const payload = {
				ativos: true,
			};			
			await this.$store
				.dispatch("motorista/indexMotorista", payload)
				.catch(err => {
					console.error(err);
				});
		},		
		async definirVeiculoMotorista(dados) {			
			await this.lerVeiculo();
			await this.getMotorista();
			this.coleta_id = dados.coleta_id;
			this.definir_veiculo_previsto = dados.definir_veiculo_previsto;
			this.placaOld[0].placa = dados.placa;
			this.placaSel[0].placa = dados.placa;
			this.etapa = dados.etapa;
			this.status = dados.status;
			if (this.etapa == 'C') {
				this.motoristaSel[0].id = dados.motor_coleta_id;				
			} else {
				this.motoristaSel[0].id = dados.motor_entrega_id;
			}
			this.motoristaSel[0].nome = dados.nome_motorista;						
			this.popupResumo = !this.popupResumo;
		},		
		async definirRoteiroPrevisto(coleta_id, placa, status, motorista_id, etapa) {
			await this.$vs.loading({ scale: 0.5 });						

			if (placa != null) {

				const payloadVeiculo = {
					coleta_id: coleta_id,
					placa: placa,
					autorizar: "N",
				};

				if (status == 'C0') {
				await this.$store
					.dispatch("controle/definirVeiculoColeta", payloadVeiculo)
					.catch((err) => {
						console.error(err);
					});
				} 			

				if (status == 'CR') {
					await this.$store
						.dispatch("controle/definirVeiculoEntrega", payloadVeiculo)
						.catch((err) => {
							console.error(err);
						});
				}	

			};

			const payloadMotorista = {
				coleta_id: coleta_id,
				motorista_id: motorista_id,
				etapa: etapa,
			};

			await this.$store
				.dispatch("controle/definirMotoristaPrevisto", payloadMotorista)
				.then(async response => {
					await this.$vs.loading.close();

					if (response.data.cod_retorno != "Z100") {
						await this.$vs.notify({
							time: 10000,
							//text: response.msg_retorno,
							text: response.data.msg_retorno,
							iconPack: "feather",
							icon: "icon-alert-circle",
							color: "danger"
						});
					} 
				})
				.catch((err) => {
					console.error(err);
				});

			this.popupResumo = false;

			const payload = {
				data_prevista: this.filtros[0].data_prevista,
			};
			await this.retornarColetasResumoDia(payload);
			await this.getDataAtual();

			await this.$vs.loading.close();
		},
		printInvoice() {
        	window.print()
      	}		
	}
};
</script>

<style lang="scss">
.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}

.dataBorderRadius {
	border-radius: 20px !important;
}

@media print {		
	/* Define a orientação da página para paisagem */
	@page {
		size: A4;
		size: landscape;
		margin: 5px !important;
	}

  	.invoice-page {
    * {
      visibility: hidden;
    }	

	#content-area {
      margin: 0 !important;
    }

    #invoice-container,
    #invoice-container * {
      visibility: visible;
    }
    #invoice-container {
      position: absolute;
      left: 0;
      top: 0;
      box-shadow: none;
    }
	
	/* Oculta um elemento específico dentro do #invoice-container */
    #invoice-container .invoice-hidden {
      display: none !important;
      visibility: hidden !important;
    }

	/* Oculta a barra de pesquisa */
	.vs-con-table .vs-table--header .vs-table--search .vs-table--search-input	{
		display: none !important;
      	visibility: hidden !important;
	}

	/* Oculta ícone de pesquisa */
	.vs-con-table .vs-table--header .vs-table--search i {
		display: none !important;
      	visibility: hidden !important;
	}

	/* Negrita a fonte do elemento específico dentro do #invoice-container */
	#invoice-container .invoice-bold {
      font-weight: bold !important;
    }
  }
}
</style>