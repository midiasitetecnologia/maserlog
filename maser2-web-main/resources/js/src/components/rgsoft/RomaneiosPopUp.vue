<template>
	<vs-popup class="holamundo minh400" :title="titulo" :active.sync="romaneiosVisivel">
		<div class="vx-row">
			<div class="vx-col w-full">
				<vs-table ref="table" max-items="100" stripe :data="fotosRomaneios">
					<vs-tr>
						<vs-td>Coleta</vs-td>
						<vs-td align="center">
							<div>
								<feather-icon
									v-if="fotosRomaneios[0].img_rom_coleta != null"
									icon="ImageIcon"
									svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
									@click="exibirFoto(fotosRomaneios[0].img_rom_coleta, 'Romaneio da coleta')"
								></feather-icon>
							</div>
						</vs-td>
					</vs-tr>
					<vs-tr>
						<vs-td>Entrega</vs-td>
						<vs-td align="center">
							<div>
								<feather-icon
									v-if="fotosRomaneios[0].img_rom_entrega != null"
									icon="ImageIcon"
									svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
									@click="exibirFoto(fotosRomaneios[0].img_rom_entrega, 'Romaneio da entrega')"
								></feather-icon>
							</div>
						</vs-td>
					</vs-tr>
				</vs-table>
			</div>
		</div>

		<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
			<div align="center">
				<img :src="foto" height="800px" />
			</div>
		</vs-popup>
	</vs-popup>
</template>

<script>
export default {
	name: "romaneios-pop-up",
	props: {
		titulo: {
			type: String,
			default: "Romaneios"
		}
	},
	data() {
		return {
			popupFoto: false,
			foto: null,
			fotoTitulo: " " //Aqui tem um espaço, se for null/trim, vai exibir o título "popup" default do component.
		};
	},
	computed: {
		romaneiosVisivel: {
			get: function() {
				return this.$store.state.exibirRomaneios;
			},
			set: function() {
				this.$store.commit("EXIBIR_ROMANEIOS");
			}
		},
		fotosRomaneios() {
			return this.$store.state.fotosRomaneios;
		}
	},
	methods: {
		exibirFoto(src, titulo) {
			this.foto = src;
			this.fotoTitulo = titulo;
			this.popupFoto = !this.popupFoto;
		}
	}
};
</script>

<style lang="scss">
.con-vs-popup.minh400 .vs-popup {
	min-height: 400px;
}
.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}
</style>