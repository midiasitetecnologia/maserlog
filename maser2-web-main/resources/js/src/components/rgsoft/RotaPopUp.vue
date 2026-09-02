<template>
	<vs-popup
		class="holamundo width60"
		:title="'Rota: ' + dadosRotaPopUp.placa + ' (' + dadosRotaPopUp.hora_atual + ')' "
		:active.sync="rotaVisivel"
	>
		<div class="vx-row mb-base">
			<div class="vx-col w-full">
				<gmap-map :center="center" :zoom="16" style="width: 100%; height: 250px">
					<gmap-info-window
						:options="infoOptions"
						:position="infoWindowPos"
						:opened="infoWinOpen"
						@closeclick="infoWinOpen=false"
					>
						<div class="flex flex-items-center text-center">
							<span class="mt-1">{{infoContent}}</span>
						</div>
					</gmap-info-window>

					<gmap-info-window
						:key="i"
						v-for="(m,i) in markers"
						:options="infoOptions"
						:position="m.position"
						:opened="true"
					>
						<div class="flex flex-items-center text-center">
							<span class="mt-1">{{m.infoText}}</span>
						</div>
					</gmap-info-window>

					<!-- //marker + i, é o indice do marcador, tive que fazer isso para evitar o erro "Duplicate keys detected: 0. This may cause an update error" -->
					<gmap-marker
						:key="'marker' + i"
						v-for="(m,i) in markers"
						:position="m.position"
						:icon="m.ignicao == 'S' ? iconeLigado : iconeDesligado"
						:clickable="true"
						@click="toggleInfoWindow(m,i)"
					>
						<!-- :icon="m.qtde_coletas > 0 ? iconeLigado : iconeDesligado" -->
					</gmap-marker>
				</gmap-map>
			</div>
		</div>

		<div class="vx-row">
			<div class="vx-col w-full">
				<vs-table
					v-show="coletasRotaPopUp.length > 0"
					class="mb-4"
					ref="table"
					:noDataText="coletasRotaPopUp.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					max-items="100"
					stripe
					:data="coletasRotaPopUp"
				>
					<template slot="thead">
						<vs-th>Solicitação</vs-th>
						<vs-th>Etapa</vs-th>
						<vs-th>Previsão</vs-th>
						<vs-th>Destino</vs-th>
						<vs-th>Distância</vs-th>
						<vs-th class="whitespace-no-wrap">Prev. Chegada</vs-th>
						<vs-th>Status</vs-th>
					</template>

					<template slot-scope="{data}">						
						<vs-tr
							:data="tr"
							:state="data[indextr].coleta_id == dadosRotaPopUp.coleta_id ? 'primary': null"
							:key="indextr"
							v-for="(tr, indextr) in data"
						>
							<vs-td class="whitespace-no-wrap">
								<div class="flex items-center text-center">
									<span v-if="data[indextr].numero != null">{{data[indextr].numero}}</span>
									<span v-else>ID: {{data[indextr].coleta_id}}</span>
									<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
								</div>
							</vs-td>

							<vs-td>
								<span>{{data[indextr].etapa}}</span>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div
									v-if="data[indextr].dt_prev_col_ent == data[indextr].data_cad"
								>{{data[indextr].hr_prev_col_ent | hora_min }}</div>
								<div
									v-else
								>{{data[indextr].dt_prev_col_ent | moment("DD MMM")}} {{data[indextr].hr_prev_col_ent | hora_min }}</div>
							</vs-td>

							<vs-td>
								<span>{{data[indextr].destino | truncate(25)}}</span>
							</vs-td>

							<vs-td class="whitespace-no-wrap">{{data[indextr].distancia_destino}}</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div
									class="flex items-center"
									:class="data[indextr].hr_prev_chegada > data[indextr].hr_prev_col_ent ? 'text-danger': ''"
								>
									<span
										v-if="data[indextr].tempo_rota != ''"
										:class="data[indextr].coleta_id == dadosRotaPopUp.coleta_id ? 'font-semibold mr-1': 'mr-1'"
										:style="data[indextr].coleta_id == dadosRotaPopUp.coleta_id ? 'font-size: 16px': ''"
									>{{data[indextr].tempo_rota}}</span>
									<span
										:class="data[indextr].coleta_id == dadosRotaPopUp.coleta_id ? 'font-semibold': ''"
										:style="data[indextr].coleta_id == dadosRotaPopUp.coleta_id ? 'font-size: 16px': ''"
									>({{data[indextr].hr_prev_chegada | hora_min}})</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div class="flex items-center text-center">
									<vs-avatar
										size="small"
										:color="corStatus(data[indextr].status)"
										:text="inicialStatus(data[indextr].status)"
									/>
									<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
								</div>
							</vs-td>
						</vs-tr>
					</template>
				</vs-table>
			</div>
		</div>
	</vs-popup>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "rota-pop-up",
	mixins: [procsMixins],
	components: {		
		ColFixa
	},
	data() {
		return {
			iconeLigado: {
				url: require("@assets/images/marker/ligado36px.png")
			},
			iconeDesligado: {
				url: require("@assets/images/marker/desligado36px.png")
			},

			infoContent: "",
			infoWindowPos: null,
			infoWinOpen: false,
			currentMidx: null,
			//optional: offset infowindow so it visually sits nicely on top of our marker
			infoOptions: {
				pixelOffset: { width: 0, height: -35 }
			}
		};
	},
	computed: {
		rotaVisivel: {
			get: function() {
				return this.$store.state.exibirRotaPopUp;
			},
			set: function() {
				this.$store.commit("EXIBIR_ROTA_POPUP", []);
			}
		},
		coletasVeiculoCargaData() {
			return this.$store.state.controle.dadosColetasVeiculoCargaData;
		},
		dadosRotaPopUp() {
			return this.$store.state.dadosRotaPopUp;
		},
		coletasRotaPopUp() {
			return this.$store.state.coletasRotaPopUp;
		},
		center() {
			if (
				this.dadosRotaPopUp.geo_lat != null &&
				this.dadosRotaPopUp.geo_lng != null
			) {
				return {
					lat: parseFloat(this.dadosRotaPopUp.geo_lat),
					lng: parseFloat(this.dadosRotaPopUp.geo_lng)
				};
			} else {
				return {
					lat: 0,
					lng: 0
				};
			}
		},
		markers() {
			if (
				this.dadosRotaPopUp.geo_lat != null &&
				this.dadosRotaPopUp.geo_lng != null
			) {
				return [
					{
						position: {
							lat: parseFloat(this.dadosRotaPopUp.geo_lat),
							lng: parseFloat(this.dadosRotaPopUp.geo_lng)
						},
						infoText: this.dadosRotaPopUp.placa,

						ignicao: this.dadosRotaPopUp.ignicao
					}
				];
			}
		}
	},
	methods: {
		toggleInfoWindow: function(marker, idx) {
			this.infoWindowPos = marker.position;
			this.infoContent = marker.infoText;
			//check if its the same marker that was selected if yes toggle
			if (this.currentMidx == idx) {
				this.infoWinOpen = !this.infoWinOpen;
			}
			//if different marker set infowindow to open and reset current marker index
			else {
				this.infoWinOpen = true;
				this.currentMidx = idx;
			}
		}
	}
};
</script>

<style lang="scss">
.con-vs-popup.width60 .vs-popup {
	width: 60%;
	/* height: 100%; */
}
</style>