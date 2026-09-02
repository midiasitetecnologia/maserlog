<template>
	<div class="page-wisdom-create">
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

		<div class="vx-col w-full mb-base">
			<vx-card>
				<div class="vx-row">
					<div class="vx-col w-full">
						<label class="vs-input--label">Texto</label>
						<vs-textarea
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required'"
							data-vv-as="texto"
							name="texto"
							class="w-full"
							rows="6"
							v-model="wisdomData[0].texto"
						/>
						<span class="text-danger text-sm" v-show="errors.has('texto')">{{ errors.first('texto') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:100'"
							data-vv-as="fonte"
							name="fonte"
							class="w-full"
							label="Fonte"
							v-model="wisdomData[0].fonte"
						/>
						<span class="text-danger text-sm" v-show="errors.has('fonte')">{{ errors.first('fonte') }}</span>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card>
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
			wisdomData: [
				{
					texto: "",
					fonte: ""
				}
			],

			tem_erros: false,
			erros_form: []
		};
	},
	computed: {
		validateForm() {
			return (
				!this.errors.any() &&
				this.wisdomData[0].texto != "" &&
				this.wisdomData[0].fonte != ""
			);
		}
	},
	methods: {
		addNewRecord() {
			this.$http
				.post(
					`api/wisdom`,
					{
						texto: this.wisdomData[0].texto,
						fonte: this.wisdomData[0].fonte
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
						this.$router.push("/sabedoria/").catch(() => {});
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
