<template>
	<div class="page-empresa-create">
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
			<vx-card>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							class="w-full"
							type="number"
							min="1"
							max="2147483647"							
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|numeric|max:10|max_value:2147483647'"
							data-vv-as="código"
							name="codigo"							
							label="Código"							
							v-model.number="empresaData[0].codigo"
						/>
						<span class="text-danger text-sm" v-show="errors.has('codigo')">{{ errors.first('codigo') }}</span>
					</div>
				</div>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							class="w-full"
							data-vv-validate-on="blur"
							v-validate="'required|max:60'"
							data-vv-as="nome"
							maxlength="60"
							name="nome"							
							label="Nome"							
							v-model="empresaData[0].nome"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('nome')"
						>{{ errors.first('nome') }}</span>
					</div>
				</div>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input							
							class="w-full"
							data-vv-validate-on="blur"
							v-validate="'required|max:3'"
							data-vv-as="sigla"
							maxlength="3"
							name="sigla"							
							label="Sigla"							
							v-model="empresaData[0].sigla"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('sigla')"
						>{{ errors.first('sigla') }}</span>
					</div>
				</div>				

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							class="w-full"
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:7'"
							data-vv-as="cor fonte"
							maxlength="7"
							name="cor_fonte"							
							label="Cor Fonte"							
							v-model="empresaData[0].cor_fonte"
						/>
						<input type="color" v-model="empresaData[0].cor_fonte" name="" value="#rrggbb">
						<span
							class="text-danger text-sm"
							v-show="errors.has('cor_fonte')"
						>{{ errors.first('cor_fonte') }}</span>
					</div>
				</div>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							class="w-full"
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:7'"
							data-vv-as="cor fundo"
							maxlength="7"
							name="cor_fundo"							
							label="Cor Fundo"							
							v-model="empresaData[0].cor_fundo"
						/>
						<input type="color" v-model="empresaData[0].cor_fundo" name="" value="#rrggbb">
						<span
							class="text-danger text-sm"
							v-show="errors.has('cor_fundo')"
						>{{ errors.first('cor_fundo') }}</span>
					</div>
				</div>

				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="addNewRecord()">Salvar</vs-button>
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
			empresaData: [
				{
					codigo: "",
					nome: "",
					sigla: "",
					cor_fonte: "",
					cor_fundo: ""
				}
			],			

			tem_erros: false,
			erros_form: [],			
		};
	},
	computed: {
		validateForm() {
			return (
				!this.errors.any() &&
				this.empresaData[0].codigo != "" &&
				this.empresaData[0].nome != "" &&
				this.empresaData[0].sigla != ""
			);
		},
	},
	methods: {
		addNewRecord() {
			this.$http
				.post(
					`api/empresa`,
					{
						codigo: this.empresaData[0].codigo,
						nome: this.empresaData[0].nome,
						sigla: this.empresaData[0].sigla,
						cor_fonte: this.empresaData[0].cor_fonte,
						cor_fundo: this.empresaData[0].cor_fundo
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
						this.$router.push("/empresa/").catch(() => {});
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
					console.log(e);
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
