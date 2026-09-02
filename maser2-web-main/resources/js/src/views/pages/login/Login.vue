<template>
	<div
		class="h-screen flex w-full bg-img vx-row no-gutter items-center justify-center"
		id="page-login"
	>
		<div class="vx-col sm:w-1/2 md:w-1/2 lg:w-3/4 xl:w-3/5 sm:m-0 m-4">
			<vx-card>
				<div slot="no-body" class="full-page-bg-color">
					<div class="vx-row no-gutter justify-center items-center">
						<div class="vx-col hidden lg:block lg:w-1/2">
							<img src="@assets/images/pages/login.png" alt="login" class="mx-auto" />
						</div>

						<div class="vx-col sm:w-full md:w-full lg:w-1/2 d-theme-dark-bg">
							<div class="px-8 pt-8 login-tabs-container">
								<div class="vx-card__title mb-6">
									<h4 class="mb-4">Login</h4>
									<p>Bem-vindo à plataforma Maser Log.</p>
								</div>

								<!-- Aceita o "Enter" em qualquer um dos Inputs -->
								<div v-on:keyup.enter="login">
									<vs-input
										v-validate="'required|email'"
										data-vv-validate-on="blur"
										name="email"
										icon-no-border
										icon="icon icon-user"
										icon-pack="feather"
										label-placeholder="Email"
										v-model="email"
										class="w-full"
									/>
									<span class="text-danger text-sm">{{ errors.first('email') }}</span>

									<vs-input
										data-vv-validate-on="blur"
										v-validate="'required'"
										data-vv-as="senha"
										type="password"
										name="password"
										icon-no-border
										icon="icon icon-lock"
										icon-pack="feather"
										label-placeholder="Senha"
										v-model="password"
										class="w-full mt-8"
									/>
									<span class="text-danger text-sm">{{ errors.first('password') }}</span>

									<div class="flex flex-wrap justify-between my-5">
										<!-- Como estes componentes não estão fazendo nada, vamos deixar comentando por enquanto... -->
										<!-- <vs-checkbox v-model="checkbox_remember_me" class="mb-3">Mantenha-me conectado</vs-checkbox>
										<router-link to="/pages/forgot-password">Esqueceu sua senha?</router-link>-->
									</div>
									<div class="flex flex-wrap justify-between mb-3">
										<vs-button @click="login">Login</vs-button>
									</div>
								</div>
							</div>
						</div>
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
			email: "",
			password: "",
			reg: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/,

			checkbox_remember_me: false
		};
	},
	methods: {
		validarEmail() {
			if (this.email == null || this.email == "") {
				return false;
			} else if (!this.reg.test(this.email)) {
				return false;
			} else {
				return true;
			}
		},
		async login() {
			if (this.validarEmail()) {
				this.$vs.loading();

				const payload = {
					checkbox_remember_me: this.checkbox_remember_me,
					userDetails: {
						email: this.email,
						password: this.password
					}
				};

				try {
					await this.$store.dispatch("auth/login", payload);

					this.$vs.loading.close();
					await this.$acl.change(
						this.$store.state.AppActiveUser.userRole
					);

					try {
						await this.$router.push(
							this.$router.currentRoute.query.to || "/"
						);
					} catch (error) {						
					}
				} catch (error) {
					console.log("error", error);

					this.$vs.loading.close();
					this.$vs.notify({
						title: "Falha na autenticação",
						text: error.message,
						iconPack: "feather",
						icon: "icon-alert-circle",
						color: "danger"
					});
				}
			}
		}
	}
};
</script>

<style lang="scss">
.login-tabs-container {
	min-height: 505px;

	.con-tab {
		padding-bottom: 14px;
	}

	.con-slot-tabs {
		margin-top: 1rem;
	}
}
</style>
