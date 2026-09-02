<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="solicitacoesFinalizadasData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"					
					search
					:max-items="solicitacoesFinalizadasData.length"
					stripe
					:data="solicitacoesFinalizadasData"
				>
					<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
						<div class="flex mb-1">
							<span class="font-semibold">Solicitações Finalizadas</span>
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
						<vs-th class="whitespace-no-wrap" sort-key="nome">Cliente</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_coleta">Hora Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Hora Entrega</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Entrega</vs-th>
						<vs-th></vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="status">Status</vs-th>
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

							<vs-td :data="data[indextr].nome">{{data[indextr].nome}}</vs-td>

							<vs-td class="whitespace-no-wrap" :data="data[indextr].dt_prev_coleta">
								<div>{{exibirDia(data[indextr].dt_prev_coleta) | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min }}</div>
								<div v-if="data[indextr].hr_sai_coleta != null">
									<div class="flex items-center">
										<span
											style="font-size: 12px; color:gray"
										>{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM") }} {{data[indextr].hr_sai_coleta | hora_min }}</span>
										<feather-icon class="ml-1" icon="CheckCircleIcon" svgClasses="w-4 h-4 text-success" />
									</div>
								</div>
							</vs-td>

							<vs-td :data="data[indextr].local_coleta">
								<div>
									<span
										class="text-inherit hover:text-success stroke-current cursor-pointer"
										@click="fillSolicitacoesFinalizadas(data[indextr]);
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
								<div>{{exibirDia(data[indextr].dt_prev_entrega) | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
								<div v-if="data[indextr].hr_sai_entrega != null">
									<div class="flex items-center">
										<span
											style="font-size: 12px; color:gray"
										>{{exibirDia(data[indextr].dt_efet_entrega) | moment("DD MMM") }} {{data[indextr].hr_sai_entrega | hora_min }}</span>
										<feather-icon class="ml-1" icon="CheckCircleIcon" svgClasses="w-4 h-4 text-success" />
									</div>
								</div>
							</vs-td>

							<vs-td :data="data[indextr].local_entrega">
								<div>
									<span
										class="text-inherit hover:text-success stroke-current cursor-pointer"
										@click="fillSolicitacoesFinalizadas(data[indextr]);
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

							<vs-td :data="data[indextr].img_carga">
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
								<div :class="{'flex items-center': true, 'text-center': false}">
									<vs-avatar
										size="small"
										:color="corStatus(data[indextr].status)"
										:text="inicialStatus(data[indextr].status)"
									/>
									<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div class="flex flex-items-center">
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
					v-if="dadosSolicFin.dt_efet_coleta != null || dadosSolicFin.hr_partida_coleta != null || dadosSolicFin.hr_cheg_coleta != null || dadosSolicFin.hr_atend_coleta != null || dadosSolicFin.hr_sai_coleta != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosSolicFin.dt_efet_coleta != null">
							<span class="mr-1 font-semibold">Data da coleta:</span>
							{{dadosSolicFin.dt_efet_coleta | moment("DD MMM YYYY")}}
						</div>
						<div>
							<span v-if="dadosSolicFin.hr_partida_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Partida:</span>
								{{dadosSolicFin.hr_partida_coleta | hora_min}}
							</span>
							<span v-if="dadosSolicFin.hr_cheg_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Chegada:</span>
								{{dadosSolicFin.hr_cheg_coleta | hora_min}}
							</span>
							<span v-if="dadosSolicFin.hr_atend_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Atendimento:</span>
								{{dadosSolicFin.hr_atend_coleta | hora_min}}
							</span>
							<span v-if="dadosSolicFin.hr_sai_coleta != null" class="mr-4">
								<span class="mr-1 font-semibold">Saída:</span>
								{{dadosSolicFin.hr_sai_coleta | hora_min}}
							</span>
						</div>
					</template>
				</p>

				<p
					v-if="dadosSolicFin.placa_coleta != null || dadosSolicFin.nome_motorista_coleta != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosSolicFin.placa_coleta != null">
							<span class="mr-1 font-semibold">Veículo:</span>
							{{dadosSolicFin.placa_coleta}}
						</div>
						<div v-if="dadosSolicFin.nome_motorista_coleta != null">
							<span class="mr-1 font-semibold">Motorista:</span>
							{{dadosSolicFin.nome_motorista_coleta}}
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
					v-if="dadosSolicFin.dt_efet_entrega != null || dadosSolicFin.hr_partida_entrega != null || dadosSolicFin.hr_cheg_entrega != null || dadosSolicFin.hr_atend_entrega != null || dadosSolicFin.hr_sai_entrega != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosSolicFin.dt_efet_entrega != null">
							<span class="mr-1 font-semibold">Data da entrega:</span>
							{{dadosSolicFin.dt_efet_entrega | moment("DD MMM YYYY")}}
						</div>
						<div>
							<span v-if="dadosSolicFin.hr_partida_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Partida:</span>
								{{dadosSolicFin.hr_partida_entrega | hora_min}}
							</span>
							<span v-if="dadosSolicFin.hr_cheg_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Chegada:</span>
								{{dadosSolicFin.hr_cheg_entrega | hora_min}}
							</span>
							<span v-if="dadosSolicFin.hr_atend_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Atendimento:</span>
								{{dadosSolicFin.hr_atend_entrega | hora_min}}
							</span>
							<span v-if="dadosSolicFin.hr_sai_entrega != null" class="mr-4">
								<span class="mr-1 font-semibold">Saída:</span>
								{{dadosSolicFin.hr_sai_entrega | hora_min}}
							</span>
						</div>
					</template>
				</p>

				<p
					v-if="dadosSolicFin.placa_entrega != null || dadosSolicFin.nome_motorista_entrega != null"
					class="mt-4"
				>
					<template>
						<div v-if="dadosSolicFin.placa_entrega != null">
							<span class="mr-1 font-semibold">Veículo:</span>
							{{dadosSolicFin.placa_entrega}}
						</div>
						<div v-if="dadosSolicFin.nome_motorista_entrega != null">
							<span class="mr-1 font-semibold">Motorista:</span>
							{{dadosSolicFin.nome_motorista_entrega}}
						</div>
					</template>
				</p>

				<p v-if="dadosSolicFin.recebedor != null" class="mt-4">
					<span class="mr-1 font-semibold">Recebedor:</span>
					{{dadosSolicFin.recebedor}}
				</p>
			</template>
		</vs-popup>

		<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
			<div align="center">
				<img :src="foto" height="800px" />
			</div>
		</vs-popup>
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "solicitacoes-finalizadas",
	mixins: [controleMixins, procsMixins, coletaMixins],
	components: {
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

			//Dados da Coleta - Solicitações Finalizadas.
			dadosSolicFin: []
		};
	},
	async created() {
		await this.countSolicitacoesFinalizadas();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		solicitacoesFinalizadasData() {
			return this.$store.state.controle.solicitacoesFinalizadasData;
		},
		coletaUrlImgData() {
			return this.$store.state.controle.coletaUrlImgData;
		}
	},
	methods: {
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			await this.getDataAtual();
			await this.countSolicitacoesFinalizadas();
			await this.getSolicitacoesFinalizadas();

			await this.$vs.loading.close();
		},
		fillSolicitacoesFinalizadas(solicitacoesFinalizadas) {
			this.dadosSolicFin = solicitacoesFinalizadas;
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
