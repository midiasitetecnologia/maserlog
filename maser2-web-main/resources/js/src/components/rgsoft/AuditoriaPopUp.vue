<template>
	<vs-popup class="holamundo width60 minh550 fontew400" :title="titulo" :active.sync="auditoria">
		<vs-tabs>
			<vs-tab label="Log">
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-table
							ref="tableLog"
							:noDataText="coletaLogData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
							v-model="selectedLog"
							max-items="100"
							stripe
							:data="coletaLogData"
						>
							<template slot="thead">
								<vs-th sort-key="created_at">Data</vs-th>
								<vs-th sort-key="descricao">Descrição</vs-th>
								<vs-th sort-key="email">Usuário</vs-th>
							</template>

							<template slot-scope="{data}">
								<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
									<vs-td
										class="whitespace-no-wrap"
									>{{data[indextr].created_at | moment("DD/MM/YYYY HH:mm:ss")}}</vs-td>
									<vs-td>
										<div>{{data[indextr].descricao}}</div>
										<div v-if="data[indextr].texto != null">
											<span style="font-size: 12px; color:gray">{{data[indextr].texto}}</span>
										</div>
									</vs-td>
									<vs-td class="whitespace-no-wrap">
										<span v-if="data[indextr].email != null">
											{{data[indextr].email}} ({{data[indextr].name | first_name }})
										</span>
									</vs-td>
								</vs-tr>
							</template>
						</vs-table>
					</div>
				</div>
			</vs-tab>

			<vs-tab label="Instruções">
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-list v-if="coletaInstrData.cont_coletas > 0">
							<vs-list-header title="Coleta"></vs-list-header>
							<template v-for="item in coletaInstrData.instrucoes">
								<vs-list-item
									v-if="item.evento == 'C01'"
									:key="item.id"
									class="list-item"
									:title="item.texto"
									:subtitle="dataInstrucao(item.created_at)"
								>
									<div class="flex flex-items-center justify-end">
										<span class="mr-1">Lida</span>
										<feather-icon
											:icon="item.lida == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="item.lida == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-warning'"
										/>
									</div>
									<div v-if="item.lida == 'S'">
										<span style="font-size: .85rem">{{dataInstrucao(item.updated_at)}}</span>
									</div>
								</vs-list-item>
							</template>
						</vs-list>

						<vs-list v-if="coletaInstrData.cont_entregas > 0">
							<vs-list-header title="Entrega"></vs-list-header>
							<template v-for="item in coletaInstrData.instrucoes">
								<vs-list-item
									v-if="item.evento == 'E01'"
									:key="item.id"
									class="list-item"
									:title="item.texto"
									:subtitle="dataInstrucao(item.created_at)"
								>
									<div class="flex flex-items-center justify-end">
										<span class="mr-1">Lida</span>
										<feather-icon
											:icon="item.lida == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="item.lida == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-warning'"
										/>
									</div>
									<div v-if="item.lida == 'S'">
										<span style="font-size: .85rem">{{dataInstrucao(item.updated_at)}}</span>
									</div>
								</vs-list-item>
							</template>
						</vs-list>
					</div>
				</div>
			</vs-tab>

			<vs-tab label="Posição">
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-table
							ref="table"
							:noDataText="coletaPosData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
							v-model="selected"
							max-items="100"
							stripe
							:data="coletaPosData"
						>
							<template slot="thead">
								<vs-th sort-key="status">Status</vs-th>
								<vs-th sort-key="placa">Placa</vs-th>
								<vs-th sort-key="nome">Motorista</vs-th>
								<vs-th sort-key="geo_lat">Latitude</vs-th>
								<vs-th sort-key="geo_lng">Longitude</vs-th>
								<vs-th sort-key="created_at">Data</vs-th>
							</template>

							<template slot-scope="{data}">
								<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
									<vs-td class="whitespace-no-wrap" :data="data[indextr].status">
										<div class="flex items-center text-center">
											<vs-avatar
												size="small"
												:color="corStatus(data[indextr].status)"
												:text="inicialStatus(data[indextr].status)"
											/>
											<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
										</div>
									</vs-td>

									<vs-td class="whitespace-no-wrap" :data="data[indextr].placa">{{data[indextr].placa}}</vs-td>
									<vs-td
										class="whitespace-no-wrap"
										:data="data[indextr].nome"
									>{{data[indextr].nome | truncate(12)}}</vs-td>
									<vs-td class="whitespace-no-wrap" :data="data[indextr].geo_lat">{{data[indextr].geo_lat}}</vs-td>
									<vs-td class="whitespace-no-wrap" :data="data[indextr].geo_lng">{{data[indextr].geo_lng}}</vs-td>
									<vs-td
										class="whitespace-no-wrap"
										:data="data[indextr].created_at"
									>{{data[indextr].created_at | moment("DD/MM/YYYY HH:mm:ss")}}</vs-td>
								</vs-tr>
							</template>
						</vs-table>
					</div>
				</div>
			</vs-tab>
		</vs-tabs>
	</vs-popup>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";

export default {
	name: "auditoria-pop-up",
	mixins: [procsMixins],
	props: {
		titulo: {
			type: String,
			default: "Auditoria"
		}
	},
	data() {
		return {
			selectedLog: [],
			selected: [],
			selectedInstr: []
		};
	},
	computed: {
		auditoria: {
			get: function() {
				return this.$store.state.exibirAuditoria;
			},
			set: function() {
				this.$store.commit("EXIBIR_AUDITORIA");
			}
		},
		coletaLogData() {
			return this.$store.state.coleta.coletaLogData;
		},
		coletaInstrData() {
			return this.$store.state.coleta.coletaInstrData;
		},
		coletaPosData() {
			return this.$store.state.coleta.coletaPosData;
		}
	},
	methods: {
		dataInstrucao(data) {
			var moment = require("moment");
			var retorno = moment(data).format("DD/MM/YYYY HH:mm:ss");
			return retorno;
		}
	}
};
</script>

<style lang="scss">
.con-vs-popup.width60 .vs-popup {
	width: 60%;
	/* height: 100%; */
}
.con-vs-popup.minh550 .vs-popup {
	min-height: 550px;
}
.con-vs-popup.fontew400 .vs-list--title {
	font-weight: 400;
}
</style>