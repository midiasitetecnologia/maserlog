<template>
	<div id="page-coleta-fixa-bloq-view">
		<vs-alert
			color="danger"
			title="Bloqueio de Coleta Fixa não encontrado"
			:active.sync="data_not_found"
		>
			<span>Registro de bloqueio de coleta fixa com id: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todas as</span>
				<router-link :to="{name:'coleta-fixa'}" class="text-inherit underline">Coletas Fixas</router-link>
			</span>
		</vs-alert>

		<br v-show="data_not_found" />

		<div v-if="!data_not_found">
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Bloqueio" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Data Inicial</td>
								<td>{{ coletaFixaBloqData.dt_ini | moment("DD MMM YYYY") }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Data Final</td>
								<td>{{ coletaFixaBloqData.dt_fim | moment("DD MMM YYYY") }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Observação</td>
								<td>{{ coletaFixaBloqData.observ }}</td>
							</tr>
						</table>

						<br />
						<div class="vx-col w-full flex">
							<vs-button
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'coleta-fixa-bloq-edit', params: { id: $route.params.id }}"
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

				<div class="vx-col w-full">
					<vx-card title="Contrato" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Empresa</td>
								<td>{{ coletaFixaBloqData.nome_empresa }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Período</td>
								<td>{{ coletaFixaBloqData.per_ini | moment("DD MMM YYYY") }} a {{ coletaFixaBloqData.per_fim | moment("DD MMM YYYY") }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Cliente</td>
								<td>{{ coletaFixaBloqData.nome_cliente }} #{{ coletaFixaBloqData.cod_cliente }}</td>
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
								<td class="font-semibold">Criado</td>
								<td>{{ coletaFixaBloqData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ coletaFixaBloqData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
						</table>
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
			coletaFixaBloqData: [],
			data_not_found: false
		};
	},
	created() {
		this.$store
			.dispatch(
				"coletaFixaBloq/showColetaFixaBloq",
				this.$route.params.id
			)
			.then(res => {
				if (res.data.coletaFixaBloq.length > 0) {
					this.coletaFixaBloqData = res.data.coletaFixaBloq[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});
	},
	methods: {
		voltar() {
			this.$router.back();
		},
		confirmDeleteRecord() {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este bloqueio?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: this.coletaFixaBloqData.id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch(
					"coletaFixaBloq/destroyColetaFixaBloq",
					parameters["id"]
				)
				.then(res => {
					if (res.data.status) {
						this.showDeleteSuccess();
						this.voltar();
					} else {
						this.showDeleteFail(res.data.erros["message"][0]);
					}
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Bloqueio de Coleta Fixa deletado",
				text:
					"O bloqueio da coleta fixa selecionado foi excluído com sucesso"
			});
		},
		showDeleteFail(msg) {
			this.$vs.notify({
				color: "danger",
				title: "Ops!",
				text: msg
			});
		}
	}
};
</script>

<style lang="scss">
#page-coleta-fixa-bloq-view {
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
