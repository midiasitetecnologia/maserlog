<template>
	<div id="page-empresa-view">
		<vs-alert
			color="danger"
			title="Empresa não encontrada"
			:active.sync="empresa_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de empresa com o código: {{$route.params.codigo}} não encontrado.</span>
			<span>
				<span>Verifique todas as</span>
				<router-link :to="{name:'empresa'}" class="text-inherit underline">Empresas</router-link>
			</span>
		</vs-alert>

		<br v-show="empresa_not_found" />

		<div v-if="!empresa_not_found">
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Código</td>
								<td>{{ empresaData.codigo }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Nome</td>
								<td>{{ empresaData.nome }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Sigla</td>
								<td>{{ empresaData.sigla }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Cor Fonte</td>
								<td>{{ empresaData.cor_fonte }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Cor Fundo</td>
								<td>{{ empresaData.cor_fundo }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ empresaData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ empresaData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>							
						</table>

						<br />

						<div class="vx-col w-full flex">
							<vs-button
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'empresa-edit', params: { codigo: $route.params.codigo }}"
							>Editar</vs-button>
							<vs-button
								type="border"
								color="danger"
								icon-pack="feather"
								icon="icon-trash"
								class="mr-4"
								@click="confirmDeleteRecord"
							>Excluir</vs-button>
							<vs-button type="border" color="danger" @click="voltar()">Voltar</vs-button>
						</div>
					</vx-card>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			empresaData: [],
			empresa_not_found: false
		};
	},
	created() {
		this.$store
			.dispatch("empresa/showEmpresa", this.$route.params.codigo)
			.then(res => {
				if (res.data.empresa.length > 0) {
					this.empresaData = res.data.empresa[0];
				} else {
					this.empresa_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});
	},
	methods: {
		confirmDeleteRecord() {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir esta empresa "${this.empresaData.codigo} - ${this.empresaData.nome}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { codigo: this.empresaData.codigo }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch(
					"empresa/destroyEmpresa",
					parameters["codigo"]
				)
				.then(() => {
					this.showDeleteSuccess();
					this.$router.push("/empresa/").catch(() => {});
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Empresa deletada",
				text: "A empresa selecionada foi excluída com sucesso"
			});
		},
		voltar() {
			this.$router.back();
		}
	}
};
</script>

<style lang="scss">
#page-empresa-view {
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
