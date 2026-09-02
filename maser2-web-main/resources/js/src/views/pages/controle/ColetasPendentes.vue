<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="coletasPendentesData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"
					search
					:max-items="coletasPendentesData.length"
					stripe
					:data="coletasPendentesData"
				>
					<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
						<div class="flex mb-1">
							<label class="font-semibold mr-3">Novas Coletas</label>
							<vs-switch class="mr-2" color="primary" v-model="somenteHoje" />
							<label class="mr-2">Hoje</label>
							<template v-if="somenteHoje == true">
								<vs-switch class="mr-2" color="primary" v-model="antesTarde" />
								<label class="mr-2">Tarde</label>
							</template>
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
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_coleta">Prev. Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Local de Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Prev. Entrega</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Local de Entrega</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="descricao_tipo_veiculo">Tipo Veículo</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="placa_coleta">
							<span class="pl-5">Placa</span>
						</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="status">Status</vs-th>
						<!-- Ações -->
						<vs-th></vs-th>
						<vs-th></vs-th>
					</template>

					<template slot-scope="{data}">
						<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
							<vs-td class="whitespace-no-wrap" :data="data[indextr].numero">
								<div>
									<div class="flex items-center text-center">
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

							<vs-td
								class="whitespace-no-wrap"
								:style="emAtraso(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta)"
								:data="data[indextr].dt_prev_coleta"
							>
								<div>{{exibirDia(data[indextr].dt_prev_coleta) | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min }}</div>
								<div
									style="font-size: 12px; font-weight: 500;"
								>{{calcTempoRestante(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta)}}</div>
							</vs-td>

							<vs-td :data="data[indextr].local_coleta">
								<span
									class="text-inherit hover:text-success stroke-current cursor-pointer"
									@click="exibirDados('coleta', data[indextr], data[indextr].local_coleta, data[indextr].cod_loc_coleta,
												data[indextr].solicitante, data[indextr].caract_coleta,
												data[indextr].endereco_coleta, data[indextr].bairro_coleta, 
												data[indextr].cidade_coleta, data[indextr].uf_coleta, 
												data[indextr].cep_coleta, data[indextr].fone_coleta, 
												data[indextr].hr_ini_coleta_man, data[indextr].hr_fim_coleta_man,
												data[indextr].hr_ini_coleta_tar, data[indextr].hr_fim_coleta_tar)"
								>{{data[indextr].local_coleta}}</span>
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
									<span
										class="text-inherit hover:text-success stroke-current cursor-pointer"
										@click="exibirDados('entrega', data[indextr], data[indextr].local_entrega, data[indextr].cod_loc_entrega, null, null, 
												data[indextr].endereco_entrega, data[indextr].bairro_entrega, 
												data[indextr].cidade_entrega, data[indextr].uf_entrega, 
												data[indextr].cep_entrega, data[indextr].fone_entrega, 
												data[indextr].hr_ini_entrega_man, data[indextr].hr_fim_entrega_man,
												data[indextr].hr_ini_entrega_tar, data[indextr].hr_fim_entrega_tar)"
									>{{data[indextr].local_entrega}}</span>
								</div>								
								<div class="flex items-center whitespace-no-wrap">								
									<span v-if="['R', 'D'].includes(data[indextr].reentrega)" class="badge_cinza">reentrega</span>
								</div>								
							</vs-td>

							<vs-td :data="data[indextr].descricao_tipo_veiculo">{{data[indextr].descricao_tipo_veiculo}}</vs-td>

							<vs-td
								v-if="data[indextr].placa_coleta != null"
								class="whitespace-no-wrap"
								:data="data[indextr].placa_coleta"
								align="center"
							>
								<vx-tooltip
									:color="data[indextr].cor_fonte"
									text="Clique aqui para alterar o veículo"
									position="top"
								>
									<span
										class="text-inherit underline hover:text-success stroke-current cursor-pointer"
										@click="definirVeiculos(data[indextr].id)"
									>{{data[indextr].placa_coleta}}</span>
								</vx-tooltip>
							</vs-td>
							<vs-td v-else class="whitespace-no-wrap" align="center">
								<vx-tooltip
									:color="data[indextr].cor_fonte"
									text="Clique aqui para definir o veículo"
									position="top"
								>
									<feather-icon
										icon="TruckIcon"
										svgClasses="w-6 h-6 hover:text-success stroke-current cursor-pointer"
										@click="definirVeiculos(data[indextr].id)"
									/>
								</vx-tooltip>
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
										<div v-if="(data[indextr].mot_nao_coleta == '03')">
											<span class="ml-1 font-semibold text-warning" style="font-size: 12px;">Devolvida</span>
										</div>
									</div>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<vx-tooltip
									v-if="data[indextr].status == 'C0' && data[indextr].placa_coleta != null"
									:text="'Autorizar coleta para: ' + data[indextr].placa_coleta"
									position="left"
								>
									<vs-button
										color="primary"
										type="flat"
										size="small"
										@click="autorizar(data[indextr])"
									>AUTORIZAR</vs-button>
								</vx-tooltip>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
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
			<p v-if="veicSolic != null" class="mt-4">
				<span class="mr-1 font-semibold">Veículo solicitado:</span>
				{{veicSolic}}
			</p>
			<p v-if="veicNec != null">
				<span class="mr-1 font-semibold">Veículo necessário:</span>
				{{veicNec}}
			</p>
		</vs-popup>

		<vs-prompt
			class="max550"
			@accept="autorizado(promptDados)"
			title="Autorizar coleta"
			accept-text="Autorizar"
			cancel-text="Cancelar"
			:active.sync="activePrompt"
		>
			<div class="con-exemple-prompt">
				<p class="mb-4">{{promptText}}</p>
				<p>Você autoriza a coleta e o envio de uma notificação ao motorista?</p>
			</div>
		</vs-prompt>
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "coletas-pendentes",
	mixins: [controleMixins, procsMixins, coletaMixins],
	components: {		
		ColFixa
	},
	data() {
		return {
			somenteHoje: true,
			antesTarde: this.statusSwitchTarde(),
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
			promptDados: []
		};
	},
	async created() {
		const payload = {
			somenteHoje: this.somenteHoje,
			antesTarde: this.antesTarde
		};
		await this.$store.commit(
			"controle/SET_COLETAS_PENDENTES_FILTROS",
			payload
		);
		await this.countColetasPendentes(payload);
		await this.getColetasPendentes(payload);
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		coletasPendentesData() {
			return this.$store.state.controle.coletasPendentesData;
		}		
	},
	watch: {
		somenteHoje: function(newValue, oldValue) {
			this.refresh();
		},
		antesTarde: function(newValue, oldValue) {
			this.refresh();
		}
	},
	methods: {
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				somenteHoje: this.somenteHoje,
				antesTarde: this.antesTarde
			};
			await this.$store.commit(
				"controle/SET_COLETAS_PENDENTES_FILTROS",
				payload
			);

			await this.getDataAtual();
			await this.countColetasPendentes(payload);
			await this.getColetasPendentes(payload);

			await this.$vs.loading.close();
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
		async definirVeiculos(coleta_id) {
			await this.$vs.loading({ scale: 0.5 });
			const payloadVC = {
				coleta_id: coleta_id,
				com_motorista: "S"
			};
			await this.retornarVeiculosColeta(payloadVC);
			await this.$vs.loading.close();

			const payload = {
				somenteHoje: this.somenteHoje,
				antesTarde: this.antesTarde
			};
			await this.$store.commit(
				"controle/SET_COLETAS_PENDENTES_FILTROS",
				payload
			);

			await this.$store.commit("controle/SET_DEFINIR_VEICULOS");
		},
		autorizar(dados) {
			this.promptText = `Esta coleta será realizada pelo veículo: "${dados.placa_coleta}".`;
			this.promptDados = dados;
			this.activePrompt = true;
		},
		async autorizado(dados) {
			const payload = {
				coleta_id: dados.id,
				placa: dados.placa_coleta,
				autorizar: "S"
			};

			await this.$store
				.dispatch("controle/definirVeiculoColeta", payload)
				.catch(err => {
					console.error(err);
				});

			await this.countColetasPendentes(
				this.$store.state.controle.coletasPendentesFiltros
			);
			await this.getColetasPendentes(
				this.$store.state.controle.coletasPendentesFiltros
			);

			await this.countColetasAndamento();
		},
		statusSwitchTarde() {
			var moment = require("moment");
			var hora_atual = moment(this.dataHoraAtual).format("HH:mm:ss");
			return hora_atual <= "12:00:00" ? false : true;
		},
		map(nome, lat, lng) {
			this.$router
				.push(
					"/cliente/mapa/" +
						encodeURIComponent(nome) +
						"/" +
						lat +
						"/" +
						lng
				)
				.catch(() => {});
		}
	}
};
</script>

<style lang="scss">
</style>