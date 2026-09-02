<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="entregasPendentesData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"
					search
					:max-items="entregasPendentesData.length"
					stripe
					:data="entregasPendentesData"
				>
					<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
						<div class="flex mb-1">
							<span class="font-semibold">Entregas Pendentes</span>
						</div>

						<div class="flex flex-wrap-reverse items-center">
							<feather-icon
								@click="refresh"
								icon="RotateCwIcon"
								svgClasses="h-4 w-4"
								class="cursor-pointer mr-4"
							/>
						</div>
					</div>

					<template slot="thead">
						<vs-th class="whitespace-no-wrap" sort-key="numero">Solicitação</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Prev. Entrega</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Entrega</vs-th>												
						<vs-th class="whitespace-no-wrap" sort-key="placa_entrega">
							<span class="pl-5">Placa</span>
						</vs-th>
						<!-- Baldeação -->
						<vs-th></vs-th>
						<!-- Foto -->
						<vs-th></vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="status">Status</vs-th>
						<!-- Ações -->
						<vs-th></vs-th>
						<vs-th></vs-th>
						<vs-th></vs-th>
					</template>

					<template slot-scope="{data}">
						<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">

							<vs-td class="whitespace-no-wrap" :data="data[indextr].numero">
								<div>
									<div :class="{'flex items-center': true, 'text-center': true}">
										<router-link
											v-if="data[indextr].numero != null"
											class="text-inherit hover:underline"
											target="_blank"
											:to="url(tr.id)"
										>{{data[indextr].numero}}</router-link>
										<router-link
											v-else
											:to="url(tr.id)"
											target="_blank"
											class="text-inherit hover:underline"
										>ID: {{data[indextr].id}}</router-link>
										<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
									</div>
								</div>
								<div>
									<vx-tooltip
										:color="data[indextr].cor_fonte"
										:text="data[indextr].nome_empresa"
										position="left"
									>
										<span
											style="font-size: 12px; color:gray"
										>{{exibirDia(data[indextr].data_cad) | moment("DD MMM") }} {{data[indextr].hora_cad | hora_min }}</span>
									</vx-tooltip>
								</div>
							</vs-td>

							<vs-td :data="data[indextr].local_coleta">
								<div>
									<span
										class="text-inherit hover:text-success stroke-current cursor-pointer"
										@click="fillEntregasPendentes(data[indextr]);
													exibirDados('coleta', data[indextr], data[indextr].local_coleta, data[indextr].cod_loc_coleta,
												data[indextr].solicitante, data[indextr].caract_coleta,
												data[indextr].endereco_coleta, data[indextr].bairro_coleta, 
												data[indextr].cidade_coleta, data[indextr].uf_coleta, 
												data[indextr].cep_coleta, data[indextr].fone_coleta, 
												data[indextr].hr_ini_coleta_man, data[indextr].hr_fim_coleta_man,
												data[indextr].hr_ini_coleta_tar, data[indextr].hr_fim_coleta_tar)"
									>{{data[indextr].local_coleta}}</span>
								</div>
								<div>
									<div class="flex items-center whitespace-no-wrap">
										<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_coleta}}</span>
										<span
											style="font-size: 12px; color:gray"
										>{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM") }} {{data[indextr].hr_sai_coleta | hora_min }}</span>
										<feather-icon
											v-if="data[indextr].hr_sai_coleta != null"
											class="ml-1"
											icon="CheckCircleIcon"
											svgClasses="w-4 h-4 text-success"
										/>
									</div>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap" :data="data[indextr].dt_prev_entrega">
								<div
									v-if="data[indextr].dt_prev_entrega != null"
									:style="emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)"
								>{{exibirDia(data[indextr].dt_prev_entrega) | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
								<div v-if="data[indextr].entrega_urgente == 'S'">
									<span class="badge_vermelho">urgente</span>
								</div>
								<div v-else style="font-size: 12px; font-weight: 500;">
									{{calcTempoRestante(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)}}
								</div>
							</vs-td>

							<vs-td :data="data[indextr].local_entrega">
								<div>
									<span class="text-inherit hover:text-success stroke-current cursor-pointer"
										@click="exibirDados('entrega', data[indextr], data[indextr].local_entrega, data[indextr].cod_loc_entrega, null, null, 
												data[indextr].endereco_entrega, data[indextr].bairro_entrega, 
												data[indextr].cidade_entrega, data[indextr].uf_entrega, 
												data[indextr].cep_entrega, data[indextr].fone_entrega, 
												data[indextr].hr_ini_entrega_man, data[indextr].hr_fim_entrega_man,
												data[indextr].hr_ini_entrega_tar, data[indextr].hr_fim_entrega_tar)"
									>{{data[indextr].local_entrega}}</span>
								</div>
								<div>
									<div class="flex items-center whitespace-no-wrap">										
										<span
											style="font-size: 12px; color:gray"
										>{{exibirDia(data[indextr].dt_efet_entrega) | moment("DD MMM") }} {{data[indextr].hr_sai_entrega | hora_min }}</span>
										<feather-icon
											v-if="data[indextr].hr_sai_entrega != null"
											class="ml-1 mr-2"
											icon="CheckCircleIcon"
											svgClasses="w-4 h-4 text-success"
										/>
										<span v-if="data[indextr].carga_pavilhao == 'S'" class="badge_cinza mr-2">pavilhão</span>
										<span v-if="['R', 'D'].includes(data[indextr].reentrega)" class="badge_cinza">reentrega</span>
									</div>
								</div>								
							</vs-td>

							<!-- Veículo Entrega -->
							<vs-td v-if="data[indextr].placa_entrega != null" class="whitespace-no-wrap" align="center">
								<div v-if="podeTrocarVeiculo(data[indextr])">
									<vx-tooltip
										:color="data[indextr].cor_fonte"
										text="Clique aqui para alterar o veículo"
										position="top"
									>
										<span
											class="text-inherit underline hover:text-success stroke-current cursor-pointer"
											@click="escolherVeiculo(data[indextr])"
										>{{data[indextr].placa_entrega}}</span>
									</vx-tooltip>
								</div>								
								<div v-else>
									{{data[indextr].placa_entrega}}
								</div>								
							</vs-td>
							<vs-td v-if="data[indextr].placa_entrega == null" class="whitespace-no-wrap" align="center">
								<div v-if="podeTrocarVeiculo(data[indextr])">
									<vx-tooltip
									:color="data[indextr].cor_fonte"
									text="Clique aqui para definir o veículo"
									position="top"
									>
										<feather-icon
											icon="TruckIcon"
											svgClasses="w-6 h-6 hover:text-success stroke-current cursor-pointer"
											@click="escolherVeiculo(data[indextr])"
										/>
									</vx-tooltip>
								</div>
								<div v-else>
									{{data[indextr].placa_entrega}}
								</div>								
							</vs-td>

							<vs-td align="center">
								<div v-if="data[indextr].placa_baldeacao != null">
									<vx-tooltip v-if="data[indextr].baldeada == 'S'" text="Baldeação realizada" position="top">
										<feather-icon
											icon="RepeatIcon"
											svgClasses="w-5 h-5 text-success stroke-current cursor-pointer"
										></feather-icon>
									</vx-tooltip>
									<vx-tooltip v-else :text="'Baldear para ' + data[indextr].placa_baldeacao" position="top">
										<feather-icon
											icon="RepeatIcon"
											svgClasses="w-5 h-5 text-warning stroke-current cursor-pointer"
										></feather-icon>
									</vx-tooltip>
								</div>
							</vs-td>

							<vs-td :data="data[indextr].img_carga" align="center">
								<div v-if="data[indextr].img_carga != null">
									<vx-tooltip text="Clique para visualizar a foto da carga" position="top">
										<feather-icon
											icon="ImageIcon"
											svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
											@click="exibirFotoCarga(data[indextr])"
										></feather-icon>
									</vx-tooltip>
								</div>
							</vs-td>

							<!-- Status -->
							<vs-td class="whitespace-no-wrap" :data="data[indextr].status">
								<div class="flex items-center">
									<vs-avatar
										size="small"
										:color="corStatus(data[indextr].status)"
										:text="inicialStatus(data[indextr].status)"
									/>
									<div>
										<div>
											<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
										</div>
										<div
											v-if="data[indextr].coleta_fixa == 'M' && vueIgualZeroNull(data[indextr].solic_origem_id) && data[indextr].status == 'CR'"
										>
											<span
												v-if="data[indextr].qtde_notas_distrib > 0"
												class="ml-1 text-warning"
												style="font-size: 12px"
											>Aguardando distribuição</span>
											<span v-else class="ml-1 text-success" style="font-size: 12px">Distribuída</span>
										</div>
										<div v-if="['EN', 'EP'].includes(data[indextr].status)">
											<span class="ml-1 text-warning" style="font-size: 12px">Aguardando reentrega</span>
										</div>
									</div>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div class="flex flex-items-center text-center">
									<vx-tooltip
										v-if="podeAutorizar(data[indextr])"
										:text="data[indextr].placa_entrega == null ? 'Autorizar entrega para veículo que fez coleta: ' + data[indextr].placa_coleta : 'Autorizar entrega para veículo: ' + data[indextr].placa_entrega"
										position="left"
									>
										<vs-button
											color="primary"
											type="flat"
											size="small"
											@click="autorizar(data[indextr])"
										>AUTORIZAR</vs-button>
									</vx-tooltip>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div class="flex flex-items-center text-center">
									<vx-tooltip
										v-if="podeEnviarInstrucao(data[indextr])"
										:text="data[indextr].nome_motorista != null ? 'Enviar instrução para ' + data[indextr].nome_motorista : 'Enviar instrução'"
										position="left"
									>
										<feather-icon
											icon="MessageSquareIcon"
											:svgClasses="'w-5 h-5 hover:text-success'"
											@click="informarInstrucao(data[indextr])"
										/>
									</vx-tooltip>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div class="flex flex-items-center text-center">
									<!-- Colocamos somente status 'E0' porque está na rota. o status 'CR' está na carga e não está na rota. -->
									<vx-tooltip
										v-if="data[indextr].status == 'E0'"
										class="mr-2"
										text="Visualizar rota do veículo"
										position="left"
									>
										<feather-icon
											icon="MapIcon"
											:svgClasses="'w-5 h-5 hover:text-success'"
											@click="rotaPopUp(data[indextr])"
										/>
									</vx-tooltip>

									<span v-if="data[indextr].notas_fiscais > 0">
										<vx-tooltip class="mr-2" text="Notas fiscais" position="left">
											<feather-icon
												icon="FileIcon"
												:svgClasses="'w-5 h-5 hover:text-success'"
												@click="notasFiscais(data[indextr].id)"
											/>
										</vx-tooltip>
									</span>
									<span v-else>
										<vx-tooltip
											v-if="data[indextr].img_rom_coleta != null"
											class="mr-2"
											text="Romaneios"
											position="left"
										>
											<feather-icon
												icon="FileIcon"
												:svgClasses="'w-5 h-5 hover:text-success'"
												@click="romaneios(coletaUrlImgData, data[indextr])"
											/>
										</vx-tooltip>
									</span>

									<vx-tooltip
										v-if="data[indextr].coleta_pos > 0 || data[indextr].coleta_log > 0"
										text="Auditoria"
										position="left"
									>
										<feather-icon
											icon="FileTextIcon"
											:svgClasses="'w-5 h-5 hover:text-success'"
											@click="auditoria(data[indextr].id)"
										/>
									</vx-tooltip>
								</div>
							</vs-td>

						</vs-tr>
					</template>
				</vs-table>
			</vx-card>
		</div>

		<!-- Foto -->
		<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
			<div align="center">
				<img :src="foto" height="800px" />
			</div>
		</vs-popup>

		<!-- Endereços -->
		<vs-popup class="holamundo" :title="tituloPopup" :active.sync="popupActive">
			<p>
				<template>
					<div class="flex items-center">
						<span class="mr-1 font-semibold">Local {{funcaoPopup}}:</span>
						<span>{{localPopup}}</span>
						<router-link
							v-if="((localGeo_lat != 0) & 
								   (localGeo_lat != null) & 
								   (localGeo_lng != 0) & 
								   (localGeo_lng != null))"
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
			<p v-if="(funcaoPopup == 'coleta') && (carga != null || dimensao != null)" class="mt-4">
				<span class="mr-1 font-semibold">Carga:</span>
				<span v-if="carga != null" class="mr-1">{{carga}}</span>
				<span v-if="dimensao != null">{{dimensao}}</span>
			</p>
			<p v-if="caracteristicas != null" class="mt-4">
				<span class="mr-1 font-semibold">Características:</span>
				{{caracteristicas}}
			</p>

			<template v-if="(funcaoPopup == 'coleta')">
				<p
					v-if="dadosEntrPend.dt_efet_coleta != null || dadosEntrPend.hr_partida_coleta != null || dadosEntrPend.hr_cheg_coleta != null || dadosEntrPend.hr_atend_coleta != null || dadosEntrPend.hr_sai_coleta != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosEntrPend.dt_efet_coleta != null">
							<span class="mr-1 font-semibold">Data da coleta:</span>
							{{dadosEntrPend.dt_efet_coleta | moment("DD MMM YYYY")}}
						</div>
						<div>
							<span v-if="dadosEntrPend.hr_partida_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Partida:</span>
								{{dadosEntrPend.hr_partida_coleta | hora_min}}
							</span>
							<span v-if="dadosEntrPend.hr_cheg_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Chegada:</span>
								{{dadosEntrPend.hr_cheg_coleta | hora_min}}
							</span>
							<span v-if="dadosEntrPend.hr_atend_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Atendimento:</span>
								{{dadosEntrPend.hr_atend_coleta | hora_min}}
							</span>
							<span v-if="dadosEntrPend.hr_sai_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Saída:</span>
								{{dadosEntrPend.hr_sai_coleta | hora_min}}
							</span>
						</div>
					</template>
				</p>

				<p
					v-if="dadosEntrPend.placa_coleta != null || dadosEntrPend.nome_motorista != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosEntrPend.placa_coleta != null">
							<span class="mr-1 font-semibold">Veículo:</span>
							{{dadosEntrPend.placa_coleta}}
						</div>
						<div v-if="dadosEntrPend.nome_motorista != null">
							<span class="mr-1 font-semibold">Motorista:</span>
							{{dadosEntrPend.nome_motorista}}
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
		</vs-popup>

		<!-- Definir Veículo -->
		<vs-popup class="holamundo" :title="'Definir veículo: ENTREGA'" :active.sync="popupVeiculo">

			<div class="vx-row mb-6">
				<div class="vx-col w-full">					
					<label class="vs-input--label font-semibold">Veículo para Entrega</label>

					<v-select class="w-full" label="placa" :options="veiculo" clearable :disabled="false" v-model="veiculoCombo">
						<template v-slot:option="option">{{ option.placa }}</template>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<div v-if="status == 'E0'" class="vx-row mb-6">
				<div class="vx-col">
					<vs-checkbox
						v-model="autorizarDesvincular"
						vs-value="S"
					>Desvincular do veículo "{{placaOld[0].placa}}" e atribuir ao veículo selecionado.</vs-checkbox>
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
			<br />
			<br />

			<vs-button
				type="border"
				:disabled="validarTrocaVeiculo"
				@click="definirVeiculo(coleta_id, status, placaOld[0].placa, placaSel[0].placa)"
			>Confirmar</vs-button>
		</vs-popup>		

		<vs-prompt
			class="max550"
			@accept="autorizado(promptDados)"
			title="Autorizar entrega"
			accept-text="Autorizar"
			cancel-text="Cancelar"
			:active.sync="activePrompt"
		>
			<div class="con-exemple-prompt">
				<p class="mb-4">{{promptText}}</p>
				<p>Você autoriza a entrega e o envio de uma notificação ao motorista?</p>
			</div>
		</vs-prompt>

		<!-- Instruções -->
		<vs-popup class="holamundo minh450" title="Enviar Instrução" :active.sync="popupInstrucao">
			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<vs-input class="w-full" disabled label="Última instrução enviada" v-model="ultimaInstrucao" />
				</div>
			</div>
			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<label class="vs-input--label">Nova instrução</label>
					<v-select v-model="instrucao_local" :options="instrucaoOptions" clearable>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<div class="vx-row mb-3" v-if="instrucaoValue == '99'">
				<div class="vx-col w-full">
					<vs-input
						maxlength="255"
						class="w-full"
						label="Texto da instrução"
						v-model="digitarInstrucao"
					/>
				</div>
			</div>
			<br v-if="instrucaoValue != '05' && instrucaoValue != '99'" />
			<br v-if="instrucaoValue != '05' && instrucaoValue != '99'" />
			<vs-alert icon-pack="feather" icon="icon-info" class="h-full my-4 mt-8 mb-6" color="warning">
				<div>Uma notificação será enviada para o celular do motorista{{motoristaInstrucao != null ? ':' : '.'}}</div>
				<div class="font-semibold">{{motoristaInstrucao}}</div>
			</vs-alert>
			<vs-button
				type="border"
				:disabled="validarInstrucao"
				@click="enviarInstrucao(coletaIdInstrucao, instrucaoValue, digitarInstrucao)"
			>Enviar instrução</vs-button>
		</vs-popup>

	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";

import vSelect from "vue-select";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "entregas-pendentes",
	mixins: [controleMixins, procsMixins, coletaMixins],
	components: {
		vSelect,
		ColFixa
	},
	data() {
		return {
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
			carga: null,
			dimensao: null,
			caracteristicas: null,
			veicSolic: null,
			veicNec: null,

			activePrompt: false,
			promptText: null,
			promptDados: [],

			popupInstrucao: false,
			coletaIdInstrucao: 0,
			ultimaInstrucao: null,
			digitarInstrucao: null,
			instrucaoValue: null,
			motoristaInstrucao: null,

			instrucaoOptions: [
				{ label: "Manter carga no veículo", value: "02" },
				{ label: "Descarregar no pavilhão", value: "03" },
				{ label: "Ir para pavilhão", value: "04" },
				{ label: "Digitar instrução", value: "99" }
			],

			//Dados da Coleta - Entregas Pendentes.
			dadosEntrPend: [],

			popupVeiculo: false,
			coleta_id: 0,			
			status: 0,
			placaSel: [{placa: null}],
			placaOld: [{placa: null}],
			autorizarDesvincular: null
		};
	},
	async created() {
		this.countEntregasPendentes();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		entregasPendentesData() {
			return this.$store.state.controle.entregasPendentesData;
		},
		coletaUrlImgData() {
			return this.$store.state.controle.coletaUrlImgData;
		},
		instrucao_local: {
			get() {
				if (this.instrucaoValue == null) {
					return "Selecione a instrução";
				} else {
					return {
						label: this.instrucaoLabel(this.instrucaoValue),
						value: this.instrucaoValue
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.instrucaoValue = obj.value;
				} else {
					this.instrucaoValue = null;
				}
			}
		},
		validarInstrucao() {
			let desabilitado = true;			

			if (this.instrucaoValue != null) {
				desabilitado = false;

				if (this.instrucaoValue == "99" && (this.digitarInstrucao == "" || this.digitarInstrucao == null)) {
					desabilitado = true;
				}
			}

			return desabilitado;
		},
		validarTrocaVeiculo() {
			let retorno = true;

			if ((this.status == "CR" && this.placaSel[0].placa != null) || 
			    (this.autorizarDesvincular != null && this.placaSel[0].placa != this.placaOld[0].placa)) {
				retorno = false;
			}
			return retorno;
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
		}
	},
	methods: {
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			await this.getDataAtual();
			await this.countEntregasPendentes();
			await this.getEntregasPendentes();

			await this.$vs.loading.close();
		},
		fillEntregasPendentes(entregasPendentes) {
			this.dadosEntrPend = entregasPendentes;
		},
		exibirDados(
			funcao,
			dadosColeta,
			local,
			cod,
			solicitante,
			caracteristicas,
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
			this.caracteristicas = caracteristicas;
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

			this.popupActive = !this.popupActive;
		},
		autorizar(dados) {
			if (dados.placa_entrega == null) {
				this.promptText =
					'Esta entrega será realizada pelo veículo que fez a coleta: "' +
					dados.placa_coleta +
					'".';
			} else {
				this.promptText =
					'Esta entrega será realizada pelo veículo: "' +
					dados.placa_entrega +
					'".';
			}

			this.promptDados = dados;
			this.activePrompt = true;
		},
		async autorizado(dados) {
			const payload = {
				coleta_id: dados.id,
				placa: "",
				autorizar: "S"
			};

			await this.$store
				.dispatch("controle/definirVeiculoEntrega", payload)
				.catch(err => {
					console.error(err);
				});

			await this.countEntregasPendentes();
			await this.getEntregasPendentes();
			await this.countEntregasAndamento();
		},
		informarInstrucao(dados) {
			this.coletaIdInstrucao = dados.id;
			this.ultimaInstrucao = dados.txt_instrucao;
			this.instrucaoValue = null;
			this.digitarInstrucao = null;
			this.motoristaInstrucao = dados.nome_motorista;
			this.popupInstrucao = !this.popupInstrucao;
		},
		async enviarInstrucao(col_id, instr, txt_instr) {
			const payload = {
				coleta_id: col_id,
				instrucao: instr,
				txt_instrucao: txt_instr
			};

			await this.$store
				.dispatch("controle/enviarInstrucaoColeta", payload)
				.catch(err => {
					console.error(err);
				});

			await this.getEntregasPendentes();
			this.popupInstrucao = false;
		},
		exibirFotoCarga(dados) {
			let titulo;
			if (dados.numero != null) {
				titulo = "Carga da coleta: " + dados.numero;
			} else {
				titulo = "Carga da coleta: ID " + dados.id;
			}
			this.exibirFoto(this.coletaUrlImgData + dados.img_carga, titulo);
		},
		podeAutorizar(dados) {
			let retorno = false;
			if ((dados.status == "CR" || dados.status == "E0") && (dados.carga_pavilhao != "S")) {
				retorno = true;
				if (dados.coleta_fixa == "M" && this.vueIgualZeroNull(dados.solic_origem_id)) {
					retorno = false;
				}
			}
			return retorno;
		},
		podeEnviarInstrucao(dados) {
			let retorno = false;
			if (dados.carga_pavilhao != "S") {
				retorno = true;
				if (dados.coleta_fixa == "M" && this.vueIgualZeroNull(dados.solic_origem_id)) {
					if (this.vueIgualZeroNull(dados.qtde_notas_distrib)) {
						retorno = false;
					}
				}
			}
			return retorno;
		},
		async lerVeiculo() {
			this.$store.dispatch("veiculo/lerVeiculo").catch(err => {
				console.error(err);
			});
		},
		podeTrocarVeiculo(dados) {
			let retorno = false;
			if (dados.status == "CR" || dados.status == "E0") {
				retorno = true;
				if (dados.coleta_fixa == "M" && this.vueIgualZeroNull(dados.solic_origem_id)) {
					retorno = false;
				}
			}
			return retorno;
		},
		async escolherVeiculo(dados) {			
			await this.lerVeiculo();			
			this.coleta_id = dados.id;
			this.placaOld[0].placa = dados.placa_entrega;
			this.placaSel[0].placa = dados.placa_entrega;			
			this.status = dados.status;			
			this.autorizarDesvincular = null;
			this.popupVeiculo = !this.popupVeiculo;
		},
		async definirVeiculo(col_id, status, placa_anterior, placa_selecionada) {
			await this.$vs.loading({ scale: 0.5 });						

			if (status == 'E0') {				

				const payload = {
					coleta_id: col_id,
					placa: placa_anterior,
				};

				await this.$store
					.dispatch("controle/desvincularVeiculoSolicitacao", payload)
					.catch((err) => {
						console.error(err);
					});

			};

			if (placa_selecionada != null) {

				const payloadVeiculo = {
					coleta_id: col_id,
					placa: placa_selecionada,
					autorizar: "N",
				};					

				await this.$store
						.dispatch("controle/definirVeiculoEntrega", payloadVeiculo)
						.catch((err) => {
							console.error(err);
						});

			};

			this.popupVeiculo = false;

			await this.countEntregasPendentes();
			await this.getEntregasPendentes();

			await this.$vs.loading.close();
		}		
	}
};
</script>

<style lang="scss">
</style>
