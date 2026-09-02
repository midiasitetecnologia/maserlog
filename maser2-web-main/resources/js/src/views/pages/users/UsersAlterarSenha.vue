<template>
	<div class="page-users-alterar-senha">
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
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">{{ this.usersData.user_type =='M' ? 'ID Login' : 'Email' }}</label>
						<vs-input
							disabled="true"
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:100'"
							name="email"
							class="w-full"
							v-model="usersData.email"
						/>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							disabled="true"
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:100'"
							data-vv-as="nome"
							name="name"
							class="w-full"
							label="Nome"
							v-model="usersData.name"
						/>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|min:6|max:100'"
							data-vv-as="senha"
							name="password"
							type="password"
							class="w-full"
							label="Senha"
							v-model="usersData.password"
							ref="password"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('password')"
						>{{ errors.first('password') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|confirmed:password|min:6|max:100'"
							data-vv-as="confirmação da senha"
							name="confirm_password"
							type="password"
							class="w-full"
							label="Confirmação da Senha"
							v-model="usersData.password_confirmation"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('confirm_password')"
						>{{ errors.first('confirm_password') }}</span>
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
export default {
	data() {
		return {
			usersData: [],
			data_not_found: false,
			tem_erros: false,
			erros_form: []
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
	},
	computed: {
		validateForm() {
			return (
				!this.errors.any() &&
				this.usersData.password != null &&
				this.usersData.password_confirmation != null
			);
		}
	},
	methods: {
		updateRecord() {
			this.$http
				.put(
					`api/users/${this.$route.params.id}`,
					{
						id: this.usersData.id,
						password: this.usersData.password,
						password_confirmation: this.usersData
							.password_confirmation
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
		}
	}
};
</script>

<style lang="scss">
</style>
