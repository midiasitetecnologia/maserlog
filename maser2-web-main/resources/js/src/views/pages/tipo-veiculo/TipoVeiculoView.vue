<template>
	<div id="page-tipo-veiculo-view">
		<vs-alert
			color="danger"
			title="Tipo de veículo não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de tipo de veículo com o código: {{$route.params.codigo}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'tipo-veiculo'}" class="text-inherit underline">Tipos de Veículo</router-link>
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
								<td>{{ tipoVeiculoData.codigo }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Descrição</td>
								<td>{{ tipoVeiculoData.descricao }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Classe</td>
								<td>{{ tipoVeiculoData.classe | classeLabel }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Duração Atend.</td>
								<td><span v-if="tipoVeiculoData.classe != 'C'">{{tipoVeiculoData.dur_prev_atend | hora_min}}</span></td>
							</tr>
							<tr>
								<td class="font-semibold">Tempo Pavilhão</td>
								<td><span v-if="tipoVeiculoData.classe != 'C'">{{tipoVeiculoData.tempo_desloc_pavilhao | hora_min}}</span></td>
							</tr>
						</table>

						<br />

						<div class="vx-col w-full flex">
							<vs-button
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'tipo-veiculo-edit', params: { codigo: $route.params.codigo }}"
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

			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Atualização" class="mb-base">
						<table>							
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ tipoVeiculoData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ tipoVeiculoData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
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
			tipoVeiculoData: [],
			data_not_found: false
		};
	},
	created() {
		this.$store
			.dispatch("tipoVeiculo/showTipoVeiculo", this.$route.params.codigo)
			.then(res => {
				if (res.data.tipoVeiculo.length > 0) {
					this.tipoVeiculoData = res.data.tipoVeiculo[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});
	},	
	filters: {
		classeLabel(str) {
			if (str === "R") return "Carreta";
			else if (str === "C") return "Cavalo";
			else if (str === "M") return "Monobloco";
			else return str;
		}
	},
	methods: {
		confirmDeleteRecord() {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este tipo de veículo "${this.tipoVeiculoData.codigo} - ${this.tipoVeiculoData.descricao}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { codigo: this.tipoVeiculoData.codigo }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch(
					"tipoVeiculo/destroyTipoVeiculo",
					parameters["codigo"]
				)
				.then(res => {

					if (res.data.status) {
						this.showDeleteSuccess();						
						this.$router.push("/tipo-veiculo/").catch(() => {});
					} else {
						this.showDeleteFail(res.data.erros['message'][0]);
					}
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Tipo de veículo deletado",
				text: "O tipo de veículo selecionado foi excluído com sucesso"
			});
		},		
		showDeleteFail(msg) {
			this.$vs.notify({
				color: "danger",
				title: "Ops!",
				text: msg
			});
		},	
		voltar() {
			this.$router.back();
		}
	}
};
</script>

<style lang="scss">
#page-tipo-veiculo-view {
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
