<template>
	<div id="page-cliente-view">
		<vs-alert
			color="danger"
			title="Cliente não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de cliente com o id: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'cliente'}" class="text-inherit underline">Clientes</router-link>
			</span>
		</vs-alert>

		<br v-show="data_not_found" />

		<div v-if="!data_not_found">
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Identificação" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Código</td>
								<td>{{ clienteData.codigo }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Empresa</td>
								<td>{{ clienteData.empresa }} - {{ clienteData.nome_empresa }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Nome</td>
								<td>{{ clienteData.nome }}</td>
							</tr>
							<tr v-if="clienteData.fantasia != null">
								<td class="font-semibold">Fantasia</td>
								<td>{{ clienteData.fantasia }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Tipo Pessoa</td>
								<td>{{ clienteData.tipo_pessoa | tipoPessoaLabel }}</td>
							</tr>
							<tr v-if="clienteData.tipo_pessoa == 'J'">
								<td class="font-semibold">CNPJ</td>
								<td>{{ clienteData.cpf_cnpj }}</td>
							</tr>
							<tr v-if="clienteData.tipo_pessoa != 'J'">
								<td class="font-semibold">CPF</td>
								<td>{{ clienteData.cpf_cnpj }}</td>
							</tr>
						</table>

						<div class="flex mb-1">
							<vs-switch class="mr-2" color="primary" v-model="solicitarColetas" />
							<label>Ativar solicitação de coletas pela Plataforma Maser</label>
						</div>

						<br />
						<div class="vx-col w-full flex">
							<vs-button type="border" color="danger" @click="voltar()">Voltar</vs-button>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full">
					<vx-card title="Endereço" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Endereço</td>
								<td>{{ clienteData.endereco }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Bairro</td>
								<td>{{ clienteData.bairro }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Cidade</td>
								<td>{{ clienteData.cidade }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Estado</td>
								<td>{{ clienteData.uf }}</td>
							</tr>
							<tr>
								<td class="font-semibold">CEP</td>
								<td>{{ clienteData.cep }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Localização</td>
								<td
									v-if="((clienteData.geo_lat != 0) & 
										    (clienteData.geo_lat != null) & 
											(clienteData.geo_lng != 0) & 
											(clienteData.geo_lng != null))"
								>
									lat:{{ clienteData.geo_lat }} lng:{{ clienteData.geo_lng }}
									<feather-icon
										icon="MapPinIcon"
										svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										class="ml-2"
										@click="map(clienteData.nome, clienteData.geo_lat, clienteData.geo_lng)"
									/>
								</td>
								<td v-else>Sem Coordenadas</td>
							</tr>
						</table>

						<div class="flex mb-1">
							<vs-switch class="mr-2" color="primary" v-model="localDistribuicao" />
							<label>Local de distribuição</label>
						</div>

						<br />

						<div class="vx-col w-full flex">
							<vs-button type="border" color="primary" @click="popAlterarCoord=true">Alterar Coordenadas</vs-button>
						</div>
					</vx-card>
				</div>
			</div>
			<div class="vx-row">
				<div class="vx-col lg:w-1/2 w-full">
					<vx-card title="Coleta" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Manhã</td>
								<td>
									{{ clienteData.hr_ini_coleta_man | hora_min }}
									<spam
										v-if="clienteData.hr_fim_coleta_man != null"
									>às {{ clienteData.hr_fim_coleta_man | hora_min }}</spam>
								</td>
							</tr>
							<tr>
								<td class="font-semibold">Tarde</td>
								<td>
									{{ clienteData.hr_ini_coleta_tar | hora_min }}
									<spam
										v-if="clienteData.hr_fim_coleta_tar != null"
									>às {{ clienteData.hr_fim_coleta_tar | hora_min }}</spam>
								</td>
							</tr>
						</table>
					</vx-card>
				</div>

				<div class="vx-col lg:w-1/2 w-full">
					<vx-card title="Entrega" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Manhã</td>
								<td>
									{{ clienteData.hr_ini_entrega_man | hora_min }}
									<spam
										v-if="clienteData.hr_fim_entrega_man != null"
									>às {{ clienteData.hr_fim_entrega_man | hora_min }}</spam>
								</td>
							</tr>
							<tr>
								<td class="font-semibold">Tarde</td>
								<td>
									{{ clienteData.hr_ini_entrega_tar | hora_min }}
									<spam
										v-if="clienteData.hr_fim_entrega_tar != null"
									>às {{ clienteData.hr_fim_entrega_tar | hora_min }}</spam>
								</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Atualização" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Cadastro alterado</td>
								<td>{{ clienteData.dt_alt_cad | moment("DD/MM/YYYY")}} {{ clienteData.hr_alt_cad }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ clienteData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ clienteData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>
		</div>

		<vs-popup title="Alterar Coordenadas" :active.sync="popAlterarCoord">
			<vs-input class="inputx mb-3" placeholder="Latitude" v-model="lat" v-mask="['-##.########']" />
			<vs-input class="inputx mb-3" placeholder="Longitude" v-model="lng" v-mask="['-##.########']" />

			<div class="vx-col w-full flex mb-3">
				<div :class="{'flex items-center': true, 'text-center': true,}">
					<vs-button
						v-if="validarLatLng"
						class="mr-3"
						type="border"
						:color="enderecoCoord != null ? 'success' : 'primary'"
						@click="getStreetAddressFrom(lat, lng)"
					>Validar Coordenadas</vs-button>
					<router-link
						class="ml-2"
						:to="{ name: 'cliente-mapa', params: { nome: encodeURIComponent(clienteData.nome), lat: lat, lng: lng }}"
						target="_blank"
					>
						<feather-icon
							v-if="validarLatLng"
							icon="MapPinIcon"
							svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
						/>
					</router-link>
				</div>
			</div>

			<span class="mt-3" v-if="enderecoCoord != null && enderecoCoord != ''">{{enderecoCoord}}</span>

			<vs-divider />

			<div class="vx-row">
				<div class="vx-col w-full">
					<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="alterarCoordenadas">Salvar</vs-button>
					<vs-button type="border" color="danger" @click="fecharPopAlterarCoord">Cancelar</vs-button>
				</div>
			</div>
		</vs-popup>
	</div>
</template>

<script>
export default {
	data() {
		return {
			clienteData: [],
			data_not_found: false,

			lat: null,
			lng: null,
			enderecoCoord: null,
			popAlterarCoord: false,

			solicitarColetas: false,
			localDistribuicao: false,
		};
	},
	created() {
		this.$store
			.dispatch("cliente/showCliente", this.$route.params.id)
			.then((res) => {
				if (res.data.cliente.length > 0) {
					this.clienteData = res.data.cliente[0];
					this.solicitarColetas =
						this.clienteData.solicitar_coletas == "S"
							? true
							: false;
					this.localDistribuicao =
						this.clienteData.local_distrib == "S" ? true : false;
				} else {
					this.data_not_found = true;
				}
			})
			.catch((err) => {
				console.error(err);
			});
	},
	watch: {
		solicitarColetas: function (newValue, oldValue) {
			let flag = newValue == true ? "S" : "N";
			if (flag != this.clienteData.solicitar_coletas) {
				this.clienteData.solicitar_coletas = flag;
				this.marcarSolicitarColetas();
			}
		},
		localDistribuicao: function (newValue, oldValue) {
			let flag = newValue == true ? "S" : "N";
			if (flag != this.clienteData.local_distrib) {
				this.clienteData.local_distrib = flag;
				this.marcarLocalDistribuicao();
			}
		},
	},
	computed: {
		validarLatLng() {
			return (
				this.lat != null &&
				this.lat != "" &&
				this.lng != null &&
				this.lng != ""
			);
		},
		validateForm() {
			return (
				this.lat != null &&
				this.lat != "" &&
				this.lng != null &&
				this.lng != "" &&
				this.enderecoCoord != null
			);
		},
	},
	filters: {
		tipoPessoaLabel(str) {
			if (str === "J") return "Jurídica";
			else if (str === "F") return "Física";
			else return str;
		},
	},
	methods: {
		voltar() {
			this.$router.back();
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
		},
		async alterarCoordenadas() {
			try {
				await this.$http.put(
					`api/cliente/${this.$route.params.id}`,
					{
						id: this.$route.params.id,
						geo_lat: this.lat,
						geo_lng: this.lng,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				);

				await this.fecharPopAlterarCoord();

				try {
					await this.$router.push("/cliente/");
				} catch (error) {
					console.log("error", error);
				}
			} catch (error) {
				console.log("error", error);
			}
		},
		async getStreetAddressFrom(lat, long) {
			try {
				this.enderecoCoord = null;
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
					this.enderecoCoord = data.results[0].formatted_address;
				}
			} catch (error) {
				console.log(error.message);
			}
		},
		async fecharPopAlterarCoord() {
			this.popAlterarCoord = !this.popAlterarCoord;
		},
		async marcarSolicitarColetas() {
			try {
				await this.$http.put(
					`api/cliente/${this.$route.params.id}`,
					{
						id: this.$route.params.id,
						solicitar_coletas:
							this.solicitarColetas == true ? "S" : "N",
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				);
			} catch (error) {
				console.log("error", error);
			}
		},
		async marcarLocalDistribuicao() {
			try {
				await this.$http.put(
					`api/cliente/${this.$route.params.id}`,
					{
						id: this.$route.params.id,
						local_distrib:
							this.localDistribuicao == true ? "S" : "N",
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				);
			} catch (error) {
				console.log("error", error);
			}
		},
	},
};
</script>

<style lang="scss">
#page-cliente-view {
	table {
		td {
			vertical-align: top;
			min-width: 140px;
			padding-bottom: 0.8rem;
			word-break: break-all;
		}
	}
}
</style>
