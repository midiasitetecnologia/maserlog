<template>
	<div>
		<!-- ALERTA VOLTAR PAINEL -->
		<div
			class="flex items-center text-center mb-4"
			v-if="windowWidth < 1200 || $store.state.reduceButton == false"
		>
			<feather-icon icon="ChevronsLeftIcon" svgClasses="w-5 h-5 text-primary" class="mr-2" />
			<span class="text-primary cursor-pointer" @click="fecharPainel">Voltar</span>
		</div>

		<div
			class="button-prev"
			type="flat"
			size="Large"
			v-if="windowWidth > 1200 && $store.state.reduceButton == true"
		>
			<vs-button class="p-0" size="small" type="border" @click="fecharPainel">
				<feather-icon icon="ChevronLeftIcon" svgClasses="h-12 w-12" class="cursor-pointer" />
			</vs-button>
		</div>

		<div>
			<vx-card class="mb-6">
				<div class="vx-row">
					<div class="vx-col md:w-1/6 w-full mb-1">
						<div>
							<span
								class="font-semibold whitespace-no-wrap"
								style="font-size: 18px"
							>{{veiculoCargaData.placa}}</span>
						</div>
						<div>
							<span class="font-semibold">{{veiculoCargaData.tipo_veiculo}}</span>
						</div>
					</div>
					<div class="vx-col md:w-1/6 w-full mb-1">
						<div>
							<span>Motorista</span>
						</div>
						<div>
							<span
								class="font-semibold"
								v-if="veiculoCargaData.motorista != null"
							>{{veiculoCargaData.motorista}}</span>
							<span class="font-semibold" v-else>Sem motorista</span>
						</div>
					</div>
					<div class="vx-col md:w-1/6 w-full mb-1">
						<div>
							<span>Capacidade</span>
						</div>
						<div>
							<span class="mr-2 font-semibold">{{veiculoCargaData.cap_kg}}</span>
							<span
								v-if="veiculoCargaData.dimensoes != '-'"
								class="font-semibold"
							>({{veiculoCargaData.dimensoes}})</span>
						</div>
					</div>
					<div class="vx-col md:w-1/6 w-full mb-1">
						<div class="vx-row">
							<div class="vx-col">
								<div>
									<span v-if="veiculoCargaData.qtde_coletas > 0">Solic.</span>
								</div>
								<div>
									<span
										v-if="veiculoCargaData.qtde_coletas > 0"
										class="font-semibold"
									>{{veiculoCargaData.qtde_coletas}}</span>
								</div>
							</div>
							<div class="vx-col">
								<div>
									<span>Peso total</span>
								</div>
								<div>
									<span class="mr-2 font-semibold">{{veiculoCargaData.peso_total_coletas}}</span>
									<span
										v-if="veiculoCargaData.peso_restante != '-'"
										:class="veiculoCargaData.capacid_peso_ok == 'S' ? 'text-success font-semibold whitespace-no-wrap' : 'text-danger font-semibold whitespace-no-wrap'"
									>({{veiculoCargaData.peso_restante}})</span>
								</div>
							</div>
						</div>
					</div>

					<div class="vx-col md:w-1/6 w-full mb-1">
						<div>
							<span>Ocupação</span>
						</div>
						<div>
							<div class="flex items-center text-center">
								<span
									class="mr-2 font-semibold"
								>{{veiculoCargaData.ocup_veiculo == null ? 0 : veiculoCargaData.ocup_veiculo}}%</span>
								<vs-progress
									:percent="veiculoCargaData.ocup_veiculo"
									:color="retCorPercOcup(veiculoCargaData.ocup_veiculo)"
									style="max-width: 100px"
								></vs-progress>
								<feather-icon
									v-if="!vueIgualTrimNull(veiculoCargaData.url_img_carga)"
									class="ml-4"
									icon="ImageIcon"
									svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
									@click="exibirFotoCarga(veiculoCargaData)"
								></feather-icon>
							</div>
						</div>
					</div>
					<div class="vx-col md:w-1/6 w-full mb-1">
						<div>
							<span class="text-sm opacity-0">Espaçamento</span>
						</div>
						<div>
							<div class="flex items-center">
								<div class="flex items-center flex-grow justify-between">
									<div>
										<feather-icon
											v-if="((veiculoCargaData.geo_lat != 0) & 
												(veiculoCargaData.geo_lat != null) & 
												(veiculoCargaData.geo_lng != 0) & 
												(veiculoCargaData.geo_lng != null))"
											icon="MapPinIcon"
											svgClasses="w-6 h-6 hover:text-danger stroke-current cursor-pointer"
											@click="exibirLocalizacao(veiculoCargaData)"
										/>
									</div>
									<feather-icon
										@click="refresh"
										icon="RotateCwIcon"
										svgClasses="w-4 h-4 cursor-pointer"
										class="ml-4"
									/>
								</div>
							</div>
						</div>
					</div>
				</div>
			</vx-card>
		</div>

		<!-- ENTREGAS PENDENTES - CARGA -->

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<div class="vx-row mb-6" v-show="(entregasPendCargaData.length > 0)">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="entregasPendCargaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						search
						:max-items="entregasPendCargaData.length"
						stripe
						:data="entregasPendCargaData"
					>
						<div slot="header" class="flex items-center flex-grow justify-between">
							<div class="mr-2">
								<span class="font-semibold">Entregas Pendentes</span>
							</div>

							<div>
								<span class="mr-1">Local de Saída:</span>
								<span class="font-semibold">Pavilhão / Outros</span>
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
							<vs-th sort-key="numero">Solicitação</vs-th>
							<vs-th sort-key="placa_coleta">Coleta</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Prev. Entrega</vs-th>
							<vs-th sort-key="local_entrega">Entrega</vs-th>
							<vs-th>Carga</vs-th>							
							<!-- Ações -->
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center text-center">
										<span v-if="data[indextr].numero != null">{{data[indextr].numero}}</span>
										<span v-else>ID: {{data[indextr].coleta_id}}</span>
										<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div>{{data[indextr].local_coleta | truncate(25)}}</div>
									<div>
										<div class="flex items-center">
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

								<vs-td class="whitespace-no-wrap">
									<div
										v-if="data[indextr].dt_prev_entrega != null"
										:style="emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)"
									>
										<span
											class="mr-1"
											v-if="data[indextr].dt_prev_entrega != data[indextr].data_cad"
										>{{data[indextr].dt_prev_entrega | moment("DD MMM")}}</span>
										<span>{{data[indextr].hr_prev_entrega | hora_min }}</span>
									</div>
									<div v-if="data[indextr].entrega_urgente == 'S'">
										<span class="badge_vermelho">urgente</span>
									</div>
									<div v-else style="font-size: 12px; font-weight: 500;">
										{{calcTempoRestante(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)}}
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div>{{data[indextr].local_entrega | truncate(25)}}</div>
									<div>
										<div class="flex items-center">
											<span v-if="data[indextr].placa_entrega != null" class="mr-2" style="font-size: 12px; color:gray">
												{{data[indextr].placa_entrega}}
											</span>
											<span style="font-size: 12px; color:gray">
												{{exibirDia(data[indextr].dt_efet_entrega) | moment("DD MMM") }} {{data[indextr].hr_sai_entrega | hora_min }}
											</span>
											<feather-icon
												v-if="data[indextr].hr_sai_entrega != null"
												class="ml-1 mr-2"
												icon="CheckCircleIcon"
												svgClasses="w-4 h-4 text-success"
											/>
											<span v-if="['R', 'D'].includes(data[indextr].reentrega)" class="badge_cinza">reentrega</span>
										</div>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center">
										<span>{{data[indextr].vol_carga}}</span>
										<feather-icon
											class="ml-1"
											:icon="data[indextr].capacid_peso_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].capacid_peso_ok == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-warning'"
										/>
									</div>
									<div class="flex items-center" v-if="data[indextr].dim_carga != '-'">
										<span style="font-size: 12px; color:gray">{{data[indextr].dim_carga}}</span>
										<feather-icon
											class="ml-1"
											:icon="data[indextr].dimensoes_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].dimensoes_ok == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-warning'"
										/>
									</div>
								</vs-td>
								
								<vs-td>
									<div v-if="['EN', 'EP'].includes(data[indextr].status)">
										<span class="ml-1 text-warning" style="font-size: 12px">Aguardando reentrega</span>
									</div>
									<div v-else>
										<div v-if="vueIgualTrimNull(data[indextr].placa_entrega)">
											<vs-button type="border" size="small" @click="definirVeiculo(data[indextr], veiculoCargaData)">ADICIONAR</vs-button>
										</div>
									</div>									
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
			<div align="center">
				<img :src="foto" height="800px" />
			</div>
		</vs-popup>

		<!-- COLETAS DO VEÍCULO - CARGA -->
		<coletas-veiculo-carga />
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import MapaPopUp from "@/components/rgsoft/MapaPopUp.vue";
import ColetasVeiculoCarga from "./ColetasVeiculoCarga.vue";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "veiculo-carga",
	mixins: [controleMixins, procsMixins],
	components: { MapaPopUp, ColetasVeiculoCarga, ColFixa },
	data() {
		return {};
	},
	mounted() {
		this.$store.commit("UPDATE_NAVBAR_TYPE", "hidden");
		window.scrollTo(0, 0);
	},
	beforeDestroy() {
		this.$store.commit("UPDATE_NAVBAR_TYPE", "floating");
	},
	computed: {
		veiculoCargaData() {
			return this.$store.state.controle.dadosVeiculoCargaData;
		},
		entregasPendCargaData() {
			return this.$store.state.controle.dadosEntregasPendCargaData;
		}
	},
	methods: {
		async fecharPainel() {
			await this.$vs.loading({ scale: 0.5 });

			await this.countColetasPendentes(
				this.$store.state.controle.coletasPendentesFiltros
			);
			await this.countColetasAndamento();
			await this.countEntregasPendentes();
			await this.countEntregasAndamento();

			await this.getVeiculosFrota(
				this.$store.state.controle.veiculosFrotaFiltros
			);
			await this.$store.commit("controle/EXIBIR_PAINEL_VEICULO_CARGA");

			await this.$vs.loading.close();
		},
		exibirLocalizacao(dados) {
			this.$store.commit("EXIBIR_MAPA_POPUP", dados);
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			await this.getDataAtual();
			await this.retornarDadosVeiculoCarga(this.veiculoCargaData.placa);
			await this.retornarEntregasPendentesCarga(
				this.veiculoCargaData.placa
			);

			const payload = {
				placa: this.veiculoCargaData.placa,
				local_saida_descr: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida_descr,
				local_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida,
				hora_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.hora_saida,
			};
			await this.retornarColetasVeiculoCarga(payload);

			await this.$vs.loading.close();
		},
		async definirVeiculo(dados, veiculo) {
			this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: dados.coleta_id,
				placa: veiculo.placa,
				autorizar: "N",
			};

			if (dados.status == 'C0') {
				await this.$store
					.dispatch("controle/definirVeiculoColeta", payload)
					.catch((err) => {
						console.error(err);
					});
			} 			

			if (dados.status == 'CR') {
				await this.$store
					.dispatch("controle/definirVeiculoEntrega", payload)
					.catch((err) => {
						console.error(err);
					});
			}

			await this.retornarDadosVeiculoCarga(this.veiculoCargaData.placa);
			await this.retornarEntregasPendentesCarga(veiculo.placa);

			const payloadCol = {
				placa: this.veiculoCargaData.placa,
				local_saida_descr: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida_descr,
				local_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida,
				hora_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.hora_saida,
			};
			await this.retornarColetasVeiculoCarga(payloadCol);

			await this.$vs.loading.close();
		},
		exibirFotoCarga(dados) {
			let titulo = "Carga do veículo: " + dados.placa;
			this.exibirFoto(dados.url_img_carga, titulo);
		},
	},
};
</script>

<style lang="scss">
.button-prev {
	position: fixed;
	bottom: 45%;
	left: 75px;
	z-index: 2500;
	background-color: transparent;
}
</style>

