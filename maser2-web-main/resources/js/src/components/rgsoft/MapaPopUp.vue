<template>
	<vs-popup class="width90" :title="titulo" :active.sync="mapaVisivel">
		<div>
			<gmap-map
				:center="center"
				:zoom="this.$store.state.dadosMapaPopUp.length > 0 ? 14: 16"
				style="width: 100%; height: 600px"
			>
				<gmap-info-window
					:options="infoOptions"
					:position="infoWindowPos"
					:opened="infoWinOpen"
					@closeclick="infoWinOpen=false"
				>					
					<div class="flex flex-items-center text-center">
						<span class="mt-1">{{infoContent}}</span>
						<feather-icon
							v-if="infoContentQtdeColetas > 0"
							:icon="'ClipboardIcon'"
							:svgClasses="infoContentSolicAtualId != '' ? 'ml-2 w-6 h-6 text-warning' : 'ml-2 w-6 h-6'"
						></feather-icon>
						<span v-if="infoContentQtdeColetas > 0" class="mt-1 ml-2">{{infoContentQtdeColetas}}</span>
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
						<feather-icon
							v-if="m.qtde_coletas > 0"
							:icon="'ClipboardIcon'"
							:svgClasses="m.solic_atual_id != '' ? 'ml-2 w-6 h-6 text-warning' : 'ml-2 w-6 h-6'"
						></feather-icon>
						<span v-if="m.qtde_coletas > 0" class="mt-1 ml-2">{{m.qtde_coletas}}</span>
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
	</vs-popup>
</template>

<script>
export default {
	name: "mapa-pop-up",
	props: {
		titulo: {
			type: String,
			default: " " //Aqui tem um espaço, se for null/trim, vai exibir o título "popup" default do component.
		}
	},
	data() {
		return {
			iconeLigado: { url: require('@assets/images/marker/ligado36px.png')},
			iconeDesligado: { url: require('@assets/images/marker/desligado36px.png')},

			infoContent: "",
			infoContentSolicAtualId: "",
			infoContentQtdeColetas: 0,
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
		mapaVisivel: {
			get: function() {
				return this.$store.state.exibirMapaPopUp;
			},
			set: function() {
				this.infoWinOpen = false;
				this.$store.commit("EXIBIR_MAPA_POPUP", []);
			}
		},
		center() {
			if (this.$store.state.dadosMapaPopUp.length > 0) {
				var center_lat = 0;
				var center_lng = 0;

				this.$store.state.dadosMapaPopUp.forEach(localizacao => {
					if (
						localizacao.geo_lat != null &&
						localizacao.geo_lng != null
					) {
						if (center_lat == 0 && center_lng == 0) {
							center_lat = localizacao.geo_lat;
							center_lng = localizacao.geo_lng;
						}
					}
				});
				return {
					lat: parseFloat(center_lat),
					lng: parseFloat(center_lng)
				};
			} else {
				if (
					this.$store.state.dadosMapaPopUp.geo_lat != null &&
					this.$store.state.dadosMapaPopUp.geo_lng != null
				) {
					return {
						lat: parseFloat(
							this.$store.state.dadosMapaPopUp.geo_lat
						),
						lng: parseFloat(
							this.$store.state.dadosMapaPopUp.geo_lng
						)
					};
				} else {
					return {
						lat: 0,
						lng: 0
					};
				}
			}
		},
		markers() {
			if (this.$store.state.dadosMapaPopUp.length > 0) {
				const array = this.$store.state.dadosMapaPopUp;
				var marcadores = [];
				var marcadoAtual = [];

				for (let i = 0; i < array.length; i++) {
					if (array[i].geo_lat != null && array[i].geo_lng != null) {
						marcadoAtual = {
							position: {
								lat: parseFloat(array[i].geo_lat),
								lng: parseFloat(array[i].geo_lng)
							},
							infoText: array[i].placa,
							qtde_coletas: array[i].qtde_coletas,
							solic_atual_id: array[i].solic_atual_id,
							ignicao: array[i].ignicao
						};
						marcadores.push(marcadoAtual);
					}
				}
				return marcadores;
			} else {
				if (
					this.$store.state.dadosMapaPopUp.geo_lat != null &&
					this.$store.state.dadosMapaPopUp.geo_lng != null
				) {
					return [
						{
							position: {
								lat: parseFloat(
									this.$store.state.dadosMapaPopUp.geo_lat
								),
								lng: parseFloat(
									this.$store.state.dadosMapaPopUp.geo_lng
								)
							},
							infoText: this.$store.state.dadosMapaPopUp.placa,
							qtde_coletas: 0,
							solic_atual_id: "",
							ignicao: this.$store.state.dadosMapaPopUp.ignicao
						}
					];
				}
			}
		}
	},
	methods: {
		toggleInfoWindow: function(marker, idx) {
			this.infoWindowPos = marker.position;
			this.infoContent = marker.infoText;
			this.infoContentQtdeColetas = marker.qtde_coletas;
			this.infoContentSolicAtualId = marker.solic_atual_id;
			//check if its the same marker that was selected if yes toggle
			if (this.currentMidx == idx) {
				this.infoWinOpen = !this.infoWinOpen;
			}
			//if different marker set infowindow to open and reset current marker index
			else {
				this.infoWinOpen = true;
				this.currentMidx = idx;
			}
		},
	}
};
</script>

<style lang="scss">
.con-vs-popup.width90 .vs-popup {
	width: 90%;
	/* height: 100%; */
}
</style>