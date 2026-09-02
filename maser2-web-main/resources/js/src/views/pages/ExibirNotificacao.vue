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
							<img
								v-if="$route.params.tipo_mensagem == 'mensagem_sucesso'"
								src="@assets/images/pages/login.png"
								alt="login"
								class="mx-auto"
							/>
							<img v-else src="@assets/images/pages/500.png" alt="graphic-500" class="mx-auto" />
						</div>

						<div class="vx-col sm:w-full md:w-full lg:w-1/2 d-theme-dark-bg">
							<div class="px-8 pt-8 login-tabs-container">
								<div class="vx-card__title mb-4">
									<h4 class="mb-4">{{$route.params.titulo}}</h4>
									<p>{{$route.params.texto_mensagem}}</p>
								</div>

								<br />
								<br />
								<br />

								<div class="flex flex-wrap justify-between mb-3">
									<vs-button type="border" to="/login" class="px-4 w-full md:w-auto">Efetuar Login</vs-button>
									<vs-button
										to="/"
										class="float-right px-4 w-full md:w-auto mt-3 mb-8 md:mt-0 md:mb-0"
									>Voltar à página inicial</vs-button>
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
		return {};
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
