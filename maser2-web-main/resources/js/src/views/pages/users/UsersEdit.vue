<template>
	<div class="page-users-edit">
		<vs-alert
			color="danger"
			title="Usuário não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro do usuário com o ID: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'users'}" class="text-inherit underline">Usuários</router-link>
			</span>
		</vs-alert>

		<vs-alert
			color="danger"
			:active.sync="tem_erros"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<ul>
				<li v-for="(value, i) in erros_form" :key="i">{{ value[0] }}</li>
			</ul>
		</vs-alert>

		<br v-show="tem_erros" />

		<!-- VERTICAL LAYOUT -->
		<div class="vx-col w-full mb-base">
			<vx-card v-if="!data_not_found">
				<div class="vx-row mb-6" v-show="$acl.check('admin')">
					<div class="vx-col w-full">
						<label class="vs-input--label">Tipo de Usuário</label>
						<v-select
							v-model="userType_local"
							:options="userTypeOptions"
							:clearable="false"
							v-validate="'required'"
							name="user_type"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
						<span
							class="text-danger text-sm"
							v-show="errors.has('user_type')"
						>{{ errors.first('user_type') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6" v-show="$acl.check('admin')" v-if="usersData.user_type =='C'">
					<div class="vx-col w-full">
						<label class="vs-input--label">Cliente</label>
						<v-select class="w-full" label="nome" :options="clienteData" clearable v-model="clienteCombo">
							<template v-slot:option="option">{{ option.nome_empresa }} - {{ option.nome }}</template>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">{{ this.usersData.user_type =='M' ? 'ID Login' : 'Email' }}</label>
						<vx-input-group>
							<vs-input
								:disabled="usersData.user_type =='M'"
								autocomplete="off"
								data-vv-validate-on="blur"
								v-validate="'required|max:100'"
								name="email"
								class="w-full"
								v-model="usersData.email"
							/>
							<template slot="append" v-if="usersData.user_type =='M'">
								<div class="append-text btn-addon">
									<vs-button color="primary" @click="gerarIdLogin">Gerar</vs-button>
								</div>
							</template>
						</vx-input-group>
						<span class="text-danger text-sm" v-show="errors.has('email')">{{ errors.first('email') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:100'"
							data-vv-as="nome"
							name="name"
							class="w-full"
							label="Nome"
							v-model="usersData.name"
						/>
						<span class="text-danger text-sm" v-show="errors.has('name')">{{ errors.first('name') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">Ativo</label>
						<v-select
							v-model="active_local"
							:options="activeOptions"
							:clearable="false"
							v-validate="'required'"
							name="active"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
						<span class="text-danger text-sm" v-show="errors.has('active')">{{ errors.first('active') }}</span>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card v-if="!data_not_found">
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="updateRecord()">Salvar</vs-button>
						<vs-button type="border" color="danger" @click="voltar()">Cancelar</vs-button>
					</div>
				</div>
			</vx-card>
		</div>
	</div>
</template>

<script>
import vSelect from "vue-select";

export default {
	components: {
		vSelect
	},
	data() {
		return {
			usersData: [],
			data_not_found: false,
			tem_erros: false,
			erros_form: [],

			userTypeOptions: [
				{ label: "Cliente", value: "C" },
				{ label: "Motorista", value: "M" },
				{ label: "Usuário", value: "U" }
			],

			clienteData: [],

			activeOptions: [
				{ label: "Sim", value: "S" },
				{ label: "Não", value: "N" },
				{ label: "Bloqueado", value: "B" }
			]
		};
	},
	created() {
		this.$store
			.dispatch("users/editUsers", this.$route.params.id)
			.then(res => {
				if (res.data.users.length > 0) {
					this.usersData = res.data.users[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});

		this.getCliente();
	},
	computed: {
		userType_local: {
			get() {
				return {
					label: this.userTypeLabel(this.usersData.user_type),
					value: this.usersData.user_type
				};
			},
			set(obj) {
				this.usersData.user_type = obj.value;
			}
		},
		clienteCombo: {
			get() {
				if (this.usersData.cliente_id == null) {
					return "Selecione o cliente em que esta conta será vinculada";
				} else {
					return {
						nome:
							this.usersData.nome_empresa +
							" - " +
							this.usersData.nome_cliente
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.usersData.cliente_id = obj.id;
					this.usersData.nome_cliente = obj.nome;
					this.usersData.nome_empresa = obj.nome_empresa;
				} else {
					this.usersData.cliente_id = null;
					this.usersData.nome_cliente = null;
					this.usersData.nome_empresa = null;
				}
			}
		},
		active_local: {
			get() {
				return {
					label: this.activeLabel(this.usersData.active),
					value: this.usersData.active
				};
			},
			set(obj) {
				this.usersData.active = obj.value;
			}
		},
		validateForm() {
			return (
				!this.errors.any() &&
				this.usersData.name != "" &&
				this.usersData.email != "" &&
				this.usersData.user_type != "" &&
				this.usersData.active != ""
			);
		}
	},
	methods: {
		getCliente() {
			this.$http
				.post(
					`api/getDadosCliente`,
					{
						empresa: 0 //Vai trazer os clientes de todas as empresas.
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(response => {
					if (response.data.status) {
						this.clienteData = response.data.cliente;
					}
				})
				.catch();
		},
		userTypeLabel(str) {
			if (str === "C") return "Cliente";
			else if (str === "M") return "Motorista";
			else if (str === "U") return "Usuário";
			else return str;
		},
		activeLabel(str) {
			if (str === "S") return "Sim";
			else if (str === "N") return "Não";
			else if (str === "B") return "Bloqueado";
			else return str;
		},
		updateRecord() {
			this.$http
				.put(
					`api/users/${this.$route.params.id}`,
					{
						id: this.usersData.id,
						name: this.usersData.name,
						email: this.usersData.email,
						user_type: this.usersData.user_type,
						active: this.usersData.active,
						cliente_id: this.usersData.cliente_id
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(response => {
					if (response.data.status) {
						this.$router.push("/users/").catch(() => {});
					}
					if (response.data.status === false) {
						this.erros_form = response.data.erros;
						this.tem_erros = true;
						console.log(response.data.erros);
					}
				})
				.catch(e => {
					this.erros_form = e.response.data.errors;
					this.tem_erros = true;
					console.log(e.response.data.errors);
				});
		},
		voltar() {
			this.$router.back();
		},
		gerarIdLogin() {
			let gerar = false;

			if (this.usersData.email != null && this.usersData.email != "") {
				let r = confirm(
					"Você realmente deseja alterar a identificação de login?"
				);

				gerar = r;

				if (gerar == false) {
					event.preventDefault();
				}
			} else {
				gerar = true;
			}

			if (gerar) {
				this.$http
					.get(`api/gerarIdLogin`, {
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					})
					.then(response => {
						this.usersData.email = response.data.idLogin;
					})
					.catch(error => {});
			}
		}
	}
};
</script>

<style lang="scss">
</style>
