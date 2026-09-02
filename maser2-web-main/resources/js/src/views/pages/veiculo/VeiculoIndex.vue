<template>
	<div>		
		<div class="flex flex-wrap-reverse items-center data-list-btn-container">
			<div
				class="btn-add-new p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-center text-lg font-medium text-base text-primary border border-solid border-primary"
				@click="addNewData"
			>
				<feather-icon icon="PlusIcon" svgClasses="h-4 w-4" />
				<span class="ml-2 text-base text-primary">Adicionar</span>
			</div>
		</div>

		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="veiculoData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="veiculoData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
								<!-- div para alinhar o dropdown a direita. Se tiver algum filtro simples de uma linha, pode ser colocado aqui.
								Ex: Filtro de ativos dos motoristas-->
							</div>

							<div class="flex flex-wrap-reverse items-center">
								<feather-icon
									@click="refresh"
									icon="RotateCwIcon"
									svgClasses="h-4 w-4"
									class="cursor-pointer mr-4"
								/>
								<vs-dropdown vs-trigger-click class="cursor-pointer mr-4 items-per-page-handler">
									<div
										class="p-2 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium whitespace-no-wrap"
									>
										<span
											class
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ veiculoData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : veiculoData.length }} de {{ queriedItems }}</span>
										<feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
									</div>

									<vs-dropdown-menu>
										<vs-dropdown-item @click="itemsPerPage=10">
											<span>10</span>
										</vs-dropdown-item>
										<vs-dropdown-item @click="itemsPerPage=15">
											<span>15</span>
										</vs-dropdown-item>
										<vs-dropdown-item @click="itemsPerPage=20">
											<span>20</span>
										</vs-dropdown-item>
									</vs-dropdown-menu>
								</vs-dropdown>
							</div>
						</div>

						<template slot="thead">
							<vs-th sort-key="placa">Placa</vs-th>
							<vs-th sort-key="descricao_tipo">Tipo de Veículo</vs-th>
							<vs-th sort-key="nivel_cons">
								<span class="pl-2">NC</span>
							</vs-th>
							<vs-th sort-key="usar_gps">GPS</vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
							<vs-th sort-key="ocup_veiculo">Ocupação</vs-th>
							<vs-th></vs-th>
							<vs-th sort-key="ativo">Ativo</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap" :data="data[indextr].placa">
									<router-link
										:to="url(tr.placa)"
										@click.stop.prevent
										class="text-inherit hover:underline"
									>{{data[indextr].placa}}</router-link>
								</vs-td>
								
								<vs-td :data="data[indextr].descricao_tipo">{{data[indextr].descricao_tipo}}</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].consumo">
									<vs-avatar
										v-if="data[indextr].nivel_cons == '1'"
										size="small"
										color="rgb(90, 225, 155)"
										text="1"
									/>
									<vs-avatar v-if="data[indextr].nivel_cons == '2'" size="small" color="success" text="2" />
									<vs-avatar
										v-if="data[indextr].nivel_cons == '3'"
										size="small"
										color="rgb(255, 200, 100)"
										text="3"
									/>
									<vs-avatar v-if="data[indextr].nivel_cons == '4'" size="small" color="warning" text="4" />
									<vs-avatar
										v-if="data[indextr].nivel_cons == '5'"
										size="small"
										color="rgb(240, 145, 140)"
										text="5"
									/>
									<vs-avatar v-if="data[indextr].nivel_cons == '6'" size="small" color="danger" text="6" />
								</vs-td>

								<vs-td :data="data[indextr].usar_gps">
									<span class="mr-1">{{ data[indextr].usar_gps | gpsLabel }}</span>
									<span v-if="!vueIgualTrimNull(data[indextr].placa_cavalo)">({{data[indextr].placa_cavalo}})</span>									
								</vs-td>

								<vs-td align="center">
									<vx-tooltip :text="data[indextr].ignicao == 'S' ? 'Veículo ligado' : 'Veículo desligado'" position="top">
										<feather-icon
											icon="TruckIcon"
											:svgClasses="data[indextr].ignicao == 'S' ? 'w-5 h-5 text-success' : 'w-5 h-5 text-danger'"
											class="ml-2"
										/>
									</vx-tooltip>
								</vs-td>
								<vs-td>
									<feather-icon
										v-if="((data[indextr].geo_lat != 0) & 
										       (data[indextr].geo_lat != null) & 
											   (data[indextr].geo_lng != 0) & 
											   (data[indextr].geo_lng != null))"
										icon="MapPinIcon"
										svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										class="ml-2"
										@click="exibirLocalizacao(data[indextr])"
									/>
								</vs-td>
								<vs-td :data="data[indextr].ocup_veiculo">
									<div v-if="data[indextr].ocup_veiculo != null && data[indextr].ocup_veiculo != 0">
										<span>{{data[indextr].ocup_veiculo}}%</span>
										<vs-progress
											:percent="data[indextr].ocup_veiculo"
											:color="retCorPercOcup(data[indextr].ocup_veiculo)"
										></vs-progress>
									</div>
								</vs-td>

								<vs-td :data="data[indextr].img_carga" align="center">
									<div v-if="data[indextr].img_carga != null && data[indextr].ocup_veiculo != 0">
										<vx-tooltip text="Clique para visualizar a foto da carga" position="top">
											<feather-icon
												icon="ImageIcon"
												svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
												@click="exibirFotoCarga(data[indextr])"
											></feather-icon>
										</vx-tooltip>
									</div>
								</vs-td>

								<vs-td :data="data[indextr].ativo">
									<vs-chip transparent :color="chipColor(data[indextr].ativo)">
										<span>{{data[indextr].ativo | sim_nao }}</span>
									</vs-chip>
								</vs-td>

								<vs-td class="whitespace-no-wrap" align="center">
									<div class="flex flex-items-center">
										<feather-icon
											icon="EditIcon"
											svgClasses="w-5 h-5 hover:text-primary stroke-current cursor-pointer"
											@click="editRecord(tr.placa)"
										/>
										<feather-icon
											icon="TrashIcon"
											svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
											class="ml-2"
											@click="confirmDeleteRecord(tr.placa)"
										/>
										<vx-tooltip
											v-if="data[indextr].motorista_id != null"
											:title="'Motorista: ' + data[indextr].nome"
											text="Clique para desvincular o motorista do veículo"
											position="left"
											class="mt-2"
										>
											<feather-icon
												icon="UserXIcon"
												svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
												class="ml-3"
												@click="desvincularMotorista(data[indextr])"
											/>
										</vx-tooltip>
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

		<vs-prompt
			class="max550"
			color="danger"
			@accept="desvincular(promptDados)"
			title="Desvincular motorista"
			accept-text="SIM"
			cancel-text="NÃO"
			:active.sync="activePrompt"
		>
			<div class="con-exemple-prompt">
				<p class="mb-4">{{promptText}}</p>
				<p>Está certo disso?</p>
			</div>
		</vs-prompt>
				
		<mapa-pop-up :titulo="tituloPopMapa"/>
	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import MapaPopUp from "@/components/rgsoft/MapaPopUp.vue";

export default {
	mixins: [procsMixins],
	components: { MapaPopUp },
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false,		

			activePrompt: false,
			promptText: null,
			promptDados: [],

			tituloPopMapa: ' ',
		};
	},
	created() {
		this.getVeiculo();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		veiculoUrlImgData() {
			return this.$store.state.veiculo.veiculoUrlImgData;
		},
		veiculoData() {
			return this.$store.state.veiculo.veiculoData;
		},
		chipColor() {
			return value => {
				if (value === "S") return "success";
				else if (value === "N") return "danger";
				else "primary";
			};
		},
		currentPage() {
			if (this.isMounted) {
				return this.$refs.table.currentx;
			}
			return 0;
		},
		queriedItems() {
			return this.$refs.table
				? this.$refs.table.queriedResults.length
				: this.veiculoData.length;
		}
	},
	filters: {
		gpsLabel(str) {
			if (str === "N") return "Não utilizar";
			else if (str === "V") return "Veículo";
			else return str;
		}
	},
	methods: {
		async getVeiculo() {
			await this.$store.dispatch("veiculo/indexVeiculo").catch(err => {
				console.error(err);
			});
		},
		addNewData() {
			this.$router.push("/veiculo/create/").catch(() => {});
		},
		editRecord(placa) {
			this.$router.push("/veiculo/" + placa + "/edit").catch(() => {});
		},
		confirmDeleteRecord(placa) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este veículo "${placa}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { placa: placa }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("veiculo/destroyVeiculo", parameters["placa"])
				.then(res => {
					if (res.data.status) {
						this.showDeleteSuccess();
					} else {
						this.showDeleteFail(res.data.erros["message"][0]);
					}
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Veículo deletado",
				text: "O veículo selecionado foi excluído com sucesso"
			});
		},
		showDeleteFail(msg) {
			this.$vs.notify({
				color: "danger",
				title: "Ops!",
				text: msg
			});
		},
		url(placa) {
			return "/veiculo/" + placa;
		},		
		exibirLocalizacao(dados) {

			var moment = require("moment");
			var hora_formatada = moment(dados.dt_geopos).format('DD MMM YYYY HH:mm:ss');
			this.tituloPopMapa = 'Localização do veículo: ' + dados.geo_lat + ',' + dados.geo_lng + ' (' + hora_formatada + ')';
			this.$store.commit("EXIBIR_MAPA_POPUP", dados);
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.getVeiculo();
			await this.$vs.loading.close();
		},
		desvincularMotorista(dados) {
			this.promptText = `O motorista "${dados.nome}" não terá mais acesso às solicitações alocadas para a placa "${dados.placa}" e... o veículo ficará disponível para outro motorista.`;
			this.promptDados = dados;
			this.activePrompt = true;
		},
		async desvincular(dados) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				placa: dados.placa,
				motorista_id: dados.motorista_id
			};

			await this.$store
				.dispatch("veiculo/desvincularMotoristaVeiculo", payload)
				.catch(err => {
					console.error(err);
				});

			await this.getVeiculo();
			await this.$vs.loading.close();
		},
		exibirFotoCarga(dados) {
			let titulo = 'Carga do veículo: ' + dados.placa;
			this.exibirFoto(this.veiculoUrlImgData + dados.img_carga, titulo);
		}
	}
};
</script>

<style lang="scss">
.con-vs-dialog.max550 .vs-dialog {
	max-width: 550px;
}

.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}
</style>

