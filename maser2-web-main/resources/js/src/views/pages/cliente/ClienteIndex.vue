<template>
	<div>
		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="clienteData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="clienteData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
								<vs-switch class="mr-1" color="primary" v-model="comUsuarios" />
								<label class="mr-3">Com usuários</label>
								<vs-switch class="mr-1" color="primary" v-model="semGeoLocalizacao" />&nbsp;
								<label class="mr-1">Sem geolocalizacão</label>
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ clienteData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : clienteData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="sigla">Empresa</vs-th>
							<vs-th sort-key="codigo">Código</vs-th>
							<vs-th sort-key="nome">Nome</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="cpf_cnpj">CNPJ / CPF</vs-th>
							<vs-th sort-key="fone">Telefone</vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td :data="data[indextr].sigla">
									<chip-emp
										:cor_fundo="data[indextr].cor_fundo"
										:cor_fonte="data[indextr].cor_fonte"
										:sigla="data[indextr].sigla"
									/>
								</vs-td>

								<vs-td :data="data[indextr].codigo">{{data[indextr].codigo}}</vs-td>
								<vs-td :data="data[indextr].nome">
									<router-link
										:to="url(tr.id)"
										target="_blank"
										class="text-inherit hover:underline"
									>{{data[indextr].nome}}</router-link>
								</vs-td>
								<vs-td class="whitespace-no-wrap" :data="data[indextr].cpf_cnpj">{{data[indextr].cpf_cnpj}}</vs-td>
								<vs-td class="whitespace-no-wrap" :data="data[indextr].fone">{{data[indextr].fone}}</vs-td>
								<vs-td>
									<router-link
										v-if="((data[indextr].geo_lat != 0) & 
										       (data[indextr].geo_lat != null) & 
											   (data[indextr].geo_lng != 0) & 
											   (data[indextr].geo_lng != null))"
										class="ml-2"
										:to="{ name: 'cliente-mapa', params: { nome: encodeURIComponent(tr.nome), lat: tr.geo_lat, lng: tr.geo_lng }}"
										target="_blank"
									>
										<feather-icon
											icon="MapPinIcon"
											svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										/>
									</router-link>
								</vs-td>

								<vs-td align="center">
									<vx-tooltip v-if="data[indextr].nro_users > 0" text="Usuários" position="top">
										<vs-button
											color="primary"
											type="flat"
											icon-pack="feather"
											icon="icon-user"
											@click="exibirUsuarios(data[indextr].id, data[indextr].nome)"
										>
											<span>{{data[indextr].nro_users}}</span>
										</vs-button>
									</vx-tooltip>
								</vs-td>

								<vs-td align="center">
									<vx-tooltip
										v-if="data[indextr].nro_solic_arq > 0"
										text="Solicitações arquivadas"
										position="top"
									>
										<router-link :to="{ name: 'coleta', query: { cliente_id: tr.id, nome: encodeURIComponent(tr.nome) }}" target="_blank">
											<vs-button color="primary" type="flat" icon-pack="feather" icon="icon-clipboard">
												<span>{{data[indextr].nro_solic_arq}}</span>
											</vs-button>
										</router-link>
									</vx-tooltip>
								</vs-td>

								<vs-td align="center">
									<div class="flex flex-items-center text-center">										
										<vx-tooltip
											class="mr-2 mt-2"
											v-if="data[indextr].nro_solic_pend > 0"
											text="Solicitações pendentes"
											position="top"
										>
											<feather-icon icon="ClipboardIcon" svgClasses="h-4 w-4" />	
											
										</vx-tooltip>																				
										<span class="mt-2" v-if="data[indextr].nro_solic_pend > 0">{{data[indextr].nro_solic_pend}}</span>
									</div>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<vs-popup
			class="holamundo width60"
			:title="'Usuários: '+this.tituloPop"
			:active.sync="popupActive"
		>
			<vs-table stripe :data="usersClienteData">
				<template slot="thead">
					<vs-th sort-key="sigla">Empresa</vs-th>
					<vs-th sort-key="email">E-mail</vs-th>
					<vs-th sort-key="name">Nome</vs-th>
					<vs-th sort-key="active">Ativo</vs-th>
				</template>

				<template slot-scope="{data}">
					<vs-tr :key="indextr" v-for="(tr, indextr) in data">
						<vs-td :data="data[indextr].sigla">
							<chip-emp
								:cor_fundo="data[indextr].cor_fundo"
								:cor_fonte="data[indextr].cor_fonte"
								:sigla="data[indextr].sigla"
							/>
						</vs-td>
						<vs-td>{{ data[indextr].email }}</vs-td>
						<vs-td>{{ data[indextr].name }}</vs-td>
						<vs-td :data="data[indextr].active">
							<vs-chip transparent :color="chipColor(data[indextr].active)">
								<span>{{ data[indextr].active | active_status }}</span>
							</vs-chip>
						</vs-td>
					</vs-tr>
				</template>
			</vs-table>
		</vs-popup>
	</div>
</template>

<script>
import ChipEmp from "@/components/rgsoft/ChipEmp.vue";
export default {
	components: {
		ChipEmp
	},
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false,

			comUsuarios: false,
			semGeoLocalizacao: false,
			usersClienteData: [],
			tituloPop: null,
			popupActive: false
		};
	},
	created() {
		this.getCliente();
	},
	mounted() {
		this.isMounted = true;
	},
	watch: {
		comUsuarios: function(newValue, oldValue) {
			this.refresh();
		},
		semGeoLocalizacao: function(newValue, oldValue) {
			this.refresh();
		}
	},
	computed: {
		clienteData() {
			return this.$store.state.cliente.clienteData;
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
				: this.clienteData.length;
		},
		chipColor() {
			return value => {
				if (value === "S") return "success";
				else if (value === "N") return "danger";
				else if (value === "B") return "warning";
				else "primary";
			};
		}
	},
	methods: {
		async getCliente() {
			const payload = {
				comUsuarios: this.comUsuarios,
				semGeoLocalizacao: this.semGeoLocalizacao
			};

			await this.$store
				.dispatch("cliente/indexCliente", payload)
				.catch(err => {
					console.error(err);
				});
		},
		url(id) {
			return "/cliente/" + id;
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.getCliente();
			await this.$vs.loading.close();
		},
		async exibirUsuarios(id, nome) {
			await this.getUsersCliente(id);
			this.usersClienteData = this.$store.state.cliente.usersClienteData;
			this.tituloPop = nome;
			this.popupActive = !this.popupActive;
		},
		async getUsersCliente(id) {
			await this.$store
				.dispatch("cliente/getUsersCliente", id)
				.catch(err => {
					console.error(err);
				});
		}
	}
};
</script>

<style lang="scss">
.con-vs-popup.width60 .vs-popup {
	width: 60%;
}
</style>
