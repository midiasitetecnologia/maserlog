<template>
	<div>
		<vs-popup class="holamundo" title="Localização do motorista" :active.sync="popupActive">
			<p>{{endereco}}</p>
			<br />
			<p>{{coord}}</p>
			<br />
			<p v-if="dataLocalizacao != null">{{dataLocalizacao | moment("DD MMM YYYY HH:mm:ss") }}</p>
		</vs-popup>
		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="motoristaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="motoristaData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
								<vs-switch color="success" v-model="motoristasAtivos" />&nbsp;
								<label>Motoristas ativos</label>
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ motoristaData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : motoristaData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="id">ID</vs-th>
							<vs-th sort-key="nome">Nome</vs-th>
							<vs-th sort-key="cpf">CPF</vs-th>							
							<vs-th sort-key="ativo">Ativo</vs-th>
							<vs-th></vs-th>
							<vs-th sort-key="placa">Veículo</vs-th>
							<vs-th sort-key="user_id">ID Login</vs-th>
							<vs-th sort-key="logado">Login</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td :data="data[indextr].id">{{data[indextr].id}}</vs-td>

								<vs-td :data="data[indextr].nome">
									<div>
										<router-link :to="url(tr.id)" @click.stop.prevent class="text-inherit hover:underline">{{data[indextr].nome}}</router-link>
									</div>
									<div v-if="data[indextr].user_id != null" class="flex items-center">
										<span v-if="((data[indextr].hr_ini_exped != null) && (data[indextr].hr_fim_exped != null))" 
										  style='font-size: 12px; color:gray' class="whitespace-no-wrap">
											{{data[indextr].hr_ini_exped | hora_min }} - {{data[indextr].hr_fim_exped | hora_min }}
										</span>
										<span v-else style="font-size: 12px; color:red" class="whitespace-no-wrap">
											Expediente não definido
										</span>										
										<vx-tooltip
											v-if="((data[indextr].hr_ini_exped != null) && (data[indextr].hr_fim_exped != null))"
											text="Horário de início e fim de expediente"
											position="top"
										>
											<feather-icon
												class="ml-1 mt-1"
												:icon="'InfoIcon'"
												:svgClasses="'w-4 h-4'"
											></feather-icon>
										</vx-tooltip>										
									</div>																		
								</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].cpf">{{data[indextr].cpf}}</vs-td>

								<vs-td :data="data[indextr].ativo">
									<vs-chip transparent :color="chipColor(data[indextr].ativo)">
										<span>{{data[indextr].ativo | sim_nao }}</span>
									</vs-chip>
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
										@click="retornarEndereco(data[indextr].geo_lat, data[indextr].geo_lng, data[indextr].dt_geopos)"
									/>
								</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].placa">{{data[indextr].placa}}</vs-td>

								<vs-td v-if="data[indextr].user_id != null" class="whitespace-no-wrap" :data="data[indextr].user_id">
									<div>{{ data[indextr].email }}</div>
									<div v-if="data[indextr].auto_logoff == 'S'">
										<span
											style="font-size: 12px; color:gray"
										>{{data[indextr].hr_ini_login | hora_min }} - {{data[indextr].hr_fim_login | hora_min }}</span>
									</div>
								</vs-td>

								<vs-td v-else class="whitespace-no-wrap" :data="data[indextr].user_id">
									<vs-chip transparent>
										<span>Sem acesso</span>
									</vs-chip>
								</vs-td>

								<vs-td :data="data[indextr].logado">
									<vs-chip transparent :color="chipColor(data[indextr].logado)">
										<span>{{data[indextr].logado | sim_nao }}</span>
									</vs-chip>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<feather-icon
										icon="EditIcon"
										svgClasses="w-5 h-5 hover:text-primary stroke-current cursor-pointer"
										@click="editRecord(tr.id)"
									/>
									<feather-icon
										icon="TrashIcon"
										svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										class="ml-2"
										@click="confirmDeleteRecord(tr.id, tr.nome)"
									/>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			motoristasAtivos: this.$store.state.motorista.motoristaFiltros
				.ativos,

			selected: [],
			itemsPerPage: 10,
			isMounted: false,

			popupActive: false,
			endereco: null,
			coord: null,
			dataLocalizacao: null
		};
	},
	created() {
		this.getMotorista();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		motoristaData() {
			return this.$store.state.motorista.motoristaData;
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
				: this.motoristaData.length;
		}
	},
	watch: {
		motoristasAtivos: function(newValue, oldValue) {
			this.refresh();
		}
	},
	methods: {
		async getMotorista() {
			await this.$store
				.dispatch(
					"motorista/indexMotorista",
					this.$store.state.motorista.motoristaFiltros
				)
				.catch(err => {
					console.error(err);
				});
		},
		editRecord(id) {
			this.$router.push("/motorista/" + id + "/edit").catch(() => {});
		},
		confirmDeleteRecord(id, nome) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este motorista "${id} - ${nome}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("motorista/destroyMotorista", parameters["id"])
				.then(() => {
					this.showDeleteSuccess();
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Motorista deletado",
				text: "O motorista selecionado foi excluído com sucesso"
			});
		},
		url(id) {
			return "/motorista/" + id;
		},
		retornarEndereco(lat, lng, data) {
			this.dataLocalizacao = data;
			this.coord = "Coord:" + lat + ", " + lng;
			this.getStreetAddressFrom(lat, lng);
			this.popupActive = !this.popupActive;
		},
		async getStreetAddressFrom(lat, long) {
			try {
				//Esta chave já esta correta na conta da "app.masertransportes"
				var { data } = await this.$http.get(
					"https://maps.googleapis.com/maps/api/geocode/json?latlng=" +
						lat +
						"," +
						long +
						"&key=" + process.env.MIX_GOOGLE_MAPS_KEY
				);
				if (data.error_message) {
					console.log(data.error_message);
				} else {
					this.endereco = data.results[0].formatted_address;
				}
			} catch (error) {
				console.log(error.message);
			}
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			const payload = {
				ativos: this.motoristasAtivos
			};
			await this.$store.commit(
				"motorista/SET_MOTORISTA_FILTROS",
				payload
			);
			await this.getMotorista();
			await this.$vs.loading.close();
		}
	}
};
</script>

<style lang="scss" scoped>
</style>

