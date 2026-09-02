<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="coletasAndamentoData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"
					search
					:max-items="coletasAndamentoData.length"
					stripe
					:data="coletasAndamentoData"
				>
					<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
						<div class="flex mb-1">
							<span class="font-semibold">Coletas Andamento</span>
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
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_coleta">Hora Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Hora Entrega</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Entrega</vs-th>
						<!-- Baldeação -->
						<vs-th></vs-th>
						<vs-th></vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="status">Status</vs-th>
						<!-- Ações -->
						<vs-th></vs-th>
						<vs-th></vs-th>
					</template>

					<template slot-scope="{data}">
						<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
							<vs-td class="whitespace-no-wrap" :data="data[indextr].numero">
								<div>
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

							<vs-td class="whitespace-no-wrap" :data="data[indextr].dt_prev_coleta">
								<div
									:style="data[indextr].dt_efet_coleta == null ? emAtraso(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta) : ''"
								>{{exibirDia(data[indextr].dt_prev_coleta) | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min }}</div>
								<div v-if="data[indextr].hr_sai_coleta != null">
									<div class="flex items-center">
										<span
											style="font-size: 12px; color:gray"
										>{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM") }} {{data[indextr].hr_sai_coleta | hora_min }}</span>
										<feather-icon class="ml-1" icon="CheckCircleIcon" svgClasses="w-4 h-4 text-success" />
									</div>
								</div>
								<div
									v-else
									style="font-size: 12px; font-weight: 500;"
								>{{calcTempoRestante(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta)}}</div>
							</vs-td>

							<vs-td :data="data[indextr].local_coleta">
								<div>
									<span
										class="text-inherit hover:text-success stroke-current cursor-pointer"
										@click="fillColetasAndamento(data[indextr]);
													exibirDados('coleta', data[indextr], data[indextr].local_coleta, data[indextr].cod_loc_coleta,
												data[indextr].solicitante, data[indextr].caract_coleta,
												data[indextr].endereco_coleta, data[indextr].bairro_coleta, 
												data[indextr].cidade_coleta, data[indextr].uf_coleta, 
												data[indextr].cep_coleta, data[indextr].fone_coleta, 
												data[indextr].hr_ini_coleta_man, data[indextr].hr_fim_coleta_man,
												data[indextr].hr_ini_coleta_tar, data[indextr].hr_fim_coleta_tar)"
									>{{data[indextr].local_coleta}}</span>
								</div>
								<div v-if="data[indextr].placa_coleta != null">
									<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_coleta}}</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap" :data="data[indextr].dt_prev_entrega">
								<div
									:style="data[indextr].dt_efet_entrega == null ? emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega) : ''"
								>{{exibirDia(data[indextr].dt_prev_entrega) | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
								<div v-if="data[indextr].hr_sai_entrega != null">
									<span
										style="font-size: 12px; color:gray"
									>{{exibirDia(data[indextr].dt_efet_entrega) | moment("DD MMM") }} {{data[indextr].hr_sai_entrega | hora_min }}</span>
								</div>
								<div
									v-else
									style="font-size: 12px; font-weight: 500;"
								>{{calcTempoRestante(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)}}</div>
							</vs-td>

							<vs-td :data="data[indextr].local_entrega">
								<div>
									<span
										class="text-inherit hover:text-success stroke-current cursor-pointer"
										@click="fillColetasAndamento(data[indextr]);
                                            exibirDados('entrega', data[indextr], data[indextr].local_entrega, data[indextr].cod_loc_entrega, null, null, 
												data[indextr].endereco_entrega, data[indextr].bairro_entrega, 
												data[indextr].cidade_entrega, data[indextr].uf_entrega, 
												data[indextr].cep_entrega, data[indextr].fone_entrega, 
												data[indextr].hr_ini_entrega_man, data[indextr].hr_fim_entrega_man,
												data[indextr].hr_ini_entrega_tar, data[indextr].hr_fim_entrega_tar)"
									>{{data[indextr].local_entrega}}</span>
								</div>
								<div v-if="data[indextr].placa_entrega != null">
									<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_entrega}}</span>
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
											v-if="((data[indextr].status == 'C3') || (data[indextr].status == 'C4') || (data[indextr].status == 'E3') || (data[indextr].status == 'E4'))"
										>
											<span
												v-if="data[indextr].coleta_fixa != 'C'"
												class="ml-1"
												style="font-size: 12px; color:gray"
											>Previsão saída: {{dataPrevSaida(data[indextr])}}</span>
										</div>
									</div>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div class="flex flex-items-center text-center">
									<vx-tooltip
										:text="data[indextr].nome_motorista_coleta != null ? 'Enviar instrução para ' + data[indextr].nome_motorista_coleta : 'Enviar instrução'"
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
									<vx-tooltip class="mr-2" text="Visualizar rota do veículo" position="left">
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
					v-if="dadosColetasAnd.dt_efet_coleta != null || dadosColetasAnd.hr_partida_coleta || dadosColetasAnd.hr_cheg_coleta != null || dadosColetasAnd.hr_atend_coleta != null || dadosColetasAnd.hr_sai_coleta != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosColetasAnd.dt_efet_coleta != null">
							<span class="mr-1 font-semibold">Data da coleta:</span>
							{{dadosColetasAnd.dt_efet_coleta | moment("DD MMM YYYY")}}
						</div>
						<div>
							<span v-if="dadosColetasAnd.hr_partida_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Partida:</span>
								{{dadosColetasAnd.hr_partida_coleta | hora_min}}
							</span>
							<span v-if="dadosColetasAnd.hr_cheg_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Chegada:</span>
								{{dadosColetasAnd.hr_cheg_coleta | hora_min}}
							</span>
							<span v-if="dadosColetasAnd.hr_atend_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Atendimento:</span>
								{{dadosColetasAnd.hr_atend_coleta | hora_min}}
							</span>
							<span v-if="dadosColetasAnd.hr_sai_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Saída:</span>
								{{dadosColetasAnd.hr_sai_coleta | hora_min}}
							</span>
						</div>
					</template>
				</p>

				<p
					v-if="dadosColetasAnd.placa_coleta != null || dadosColetasAnd.nome_motorista_coleta != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosColetasAnd.placa_coleta != null">
							<span class="mr-1 font-semibold">Veículo:</span>
							{{dadosColetasAnd.placa_coleta}}
						</div>
						<div v-if="dadosColetasAnd.nome_motorista_coleta != null">
							<span class="mr-1 font-semibold">Motorista:</span>
							{{dadosColetasAnd.nome_motorista_coleta}}
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
				<p
					v-if="dadosColetasAnd.dt_efet_entrega != null || dadosColetasAnd.hr_partida_entrega != null || dadosColetasAnd.hr_cheg_entrega != null || dadosColetasAnd.hr_atend_entrega != null || dadosColetasAnd.hr_sai_entrega != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosColetasAnd.dt_efet_entrega != null">
							<span class="mr-1 font-semibold">Data da entrega:</span>
							{{dadosColetasAnd.dt_efet_entrega | moment("DD MMM YYYY")}}
						</div>
						<div>
							<span v-if="dadosColetasAnd.hr_partida_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Partida:</span>
								{{dadosColetasAnd.hr_partida_entrega | hora_min}}
							</span>
							<span v-if="dadosColetasAnd.hr_cheg_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Chegada:</span>
								{{dadosColetasAnd.hr_cheg_entrega | hora_min}}
							</span>
							<span v-if="dadosColetasAnd.hr_atend_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Atendimento:</span>
								{{dadosColetasAnd.hr_atend_entrega | hora_min}}
							</span>
							<span v-if="dadosColetasAnd.hr_sai_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Saída:</span>
								{{dadosColetasAnd.hr_sai_entrega | hora_min}}
							</span>
						</div>
					</template>
				</p>

				<p
					v-if="dadosColetasAnd.placa_entrega != null || dadosColetasAnd.nome_motorista_entrega != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosColetasAnd.placa_entrega != null">
							<span class="mr-1 font-semibold">Veículo:</span>
							{{dadosColetasAnd.placa_entrega}}
						</div>
						<div v-if="dadosColetasAnd.nome_motorista_entrega != null">
							<span class="mr-1 font-semibold">Motorista:</span>
							{{dadosColetasAnd.nome_motorista_entrega}}
						</div>
					</template>
				</p>

				<p v-if="dadosColetasAnd.recebedor != null" class="mt-4">
					<span class="mr-1 font-semibold">Recebedor:</span>
					{{dadosColetasAnd.recebedor}}
				</p>
			</template>
		</vs-popup>

		<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
			<div align="center">
				<img :src="foto" height="800px" />
			</div>
		</vs-popup>

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
	name: "solicitacoes-andamento",
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

			//Dados da Coleta - Coletas Andamento.
			dadosColetasAnd: [],

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
			]
		};
	},
	async created() {
		await this.countColetasAndamento();
		// await this.getColetasAndamento();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		coletasAndamentoData() {
			return this.$store.state.controle.coletasAndamentoData;
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

				if (
					this.instrucaoValue == "99" &&
					(this.digitarInstrucao == "" ||
						this.digitarInstrucao == null)
				) {
					desabilitado = true;
				}
			}

			return desabilitado;
		}
	},
	methods: {
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			await this.getDataAtual();
			await this.countColetasAndamento();
			await this.getColetasAndamento();

			await this.$vs.loading.close();
		},
		fillColetasAndamento(coletasAndamento) {
			this.dadosColetasAnd = coletasAndamento;
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
		informarInstrucao(dados) {
			this.coletaIdInstrucao = dados.id;
			this.ultimaInstrucao = dados.txt_instrucao;
			this.instrucaoValue = null;
			this.digitarInstrucao = null;
			this.motoristaInstrucao = dados.nome_motorista_coleta;
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

			await this.getColetasAndamento();
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
		}
	}
};
</script>

<style lang="scss">
</style>

