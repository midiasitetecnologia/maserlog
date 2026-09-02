<template>
  <div>
    <vx-card>
      <div class="vx-row">
        <div class="vx-col md:w-1/4 sm:w-1/4 w-full">
          <label class="text-sm opacity-75">Evento</label>
          <v-select
            :options="eventoOptions"
            :clearable="false"
            v-model="filtros[0].eventoSelected"
          >
            <div slot="no-options">Opção não disponível</div>
          </v-select>
        </div>
        <div class="vx-col md:w-1/5 sm:w-1/5 w-full">
          <label class="text-sm opacity-75">Início</label>
          <flat-pickr
            :config="configdateTimePickerDate"
            v-model="filtros[0].filtro_ini"
            class="w-full vs-inputx vs-input--input normal hasValue"
            style="border: 1px solid rgba(0, 0, 0, 0.2)"
          />
        </div>
        <div class="vx-col md:w-1/5 sm:w-1/5 w-full">
          <label class="text-sm opacity-75">Fim</label>
          <flat-pickr
            :config="configdateTimePickerDate"
            v-model="filtros[0].filtro_fim"
            class="w-full vs-inputx vs-input--input normal hasValue"
            style="border: 1px solid rgba(0, 0, 0, 0.2)"
          />
        </div>
        <div class="vx-col md:w-1/6 sm:w-1/6 w-full">
          <label class="text-sm opacity-75">Status</label>
          <v-select
            :options="statusOptions"
            :clearable="false"
            v-model="filtros[0].statusSelected"
          >
            <div slot="no-options">Opção não disponível</div>
          </v-select>
        </div>
        <div class="vx-col md:w-2/12 sm:w-full w-full">
					<label class="text-sm opacity-0">Pesquisar</label>
					<vs-button
						color="primary"
						type="border"
						icon-pack="feather"
						icon="icon-search"
						@click="refresh"
					></vs-button>
				</div>
      </div>
    </vx-card>

    <br />

    <!-- Cabeçalho -->
    <div class="vx-row">
      <div class="vx-col w-full">
        <vx-card>
          <vs-table
            ref="table"
            :noDataText="
              logProData.length > 0
                ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.'
                : 'Não há registros para mostrar.'
            "
            v-model="selected"
            :max-items="itemsPerPage"
            pagination
            search
            stripe
            :data="logProData"
            @selected="getLogProDetalhe"
          >
            <div
              slot="header"
              class="flex flex-wrap-reverse items-center flex-grow justify-between"
            >
              <div class="flex mb-1">
                <!-- div para alinhar o dropdown a direita. Se tiver algum filtro simples de uma linha, pode ser colocado aqui.
								Ex: Filtro de ativos dos motoristas-->
              </div>

              <div class="flex flex-wrap-reverse items-center">
                <feather-icon
                  @click="refresh"
                  icon="RotateCwIcon"
                  svgClasses="h-4 w-4"
                  class="cursor-pointer mr-4"
                />
                <vs-dropdown
                  vs-trigger-click
                  class="cursor-pointer mr-4 items-per-page-handler"
                >
                  <div
                    class="p-2 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium whitespace-no-wrap"
                  >
                    <span class
                      >{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} -
                      {{
                        logProData.length - currentPage * itemsPerPage > 0
                          ? currentPage * itemsPerPage
                          : logProData.length
                      }}
                      de {{ queriedItems }}</span
                    >
                    <feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
                  </div>

                  <vs-dropdown-menu>
                    <vs-dropdown-item @click="itemsPerPage = 5">
                      <span>5</span>
                    </vs-dropdown-item>
                    <vs-dropdown-item @click="itemsPerPage = 10">
                      <span>10</span>
                    </vs-dropdown-item>
                    <vs-dropdown-item @click="itemsPerPage = 15">
                      <span>15</span>
                    </vs-dropdown-item>
                  </vs-dropdown-menu>
                </vs-dropdown>
              </div>
            </div>
            <template slot="thead">
              <vs-th sort-key="id">Proc. ID</vs-th>
              <vs-th sort-key="created_at">Data</vs-th>
              <vs-th
                sort-key="evento"
                v-if="filtros[0].eventoSelected.value == 'todos'"
                >Evento</vs-th
              >
              <vs-th sort-key="msg">Mensagem</vs-th>
              <vs-th sort-key="status">Status</vs-th>
            </template>

            <template slot-scope="{ data }">
              <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                <vs-td :data="data[indextr].proc_id">{{
                  data[indextr].proc_id
                }}</vs-td>
                <vs-td
                  class="whitespace-no-wrap"
                  :data="data[indextr].created_at"
                  >{{
                    data[indextr].created_at | moment("DD/MM/YYYY HH:mm:ss")
                  }}</vs-td
                >
                <vs-td
                  :data="data[indextr].evento"
                  v-if="filtros[0].eventoSelected.value == 'todos'"
                  >{{ data[indextr].evento }}</vs-td
                >
                <vs-td :data="data[indextr].msg">
                  <div>{{ data[indextr].msg }}</div>
                  <div v-if="data[indextr].err != null">
                    <hr />
                    <div>{{ data[indextr].err }}</div>
                  </div>
                </vs-td>

                <vs-td :data="data[indextr].status">
                  <vs-chip transparent :color="chipColor(data[indextr].status)">
                    <span>{{ data[indextr].status | statusLabel }}</span>
                  </vs-chip>
                </vs-td>
              </vs-tr>
            </template>
          </vs-table>
        </vx-card>
      </div>
    </div>

    <br />

    <!-- Detalhes -->
    <div class="vx-row" v-if="selected['id'] > 0">
      <div class="vx-col w-full">
        <vx-card>
          <vs-table
            :noDataText="
              logProDetalheData.length > 0
                ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.'
                : 'Não há registros para mostrar.'
            "
            v-model="selectedDetail"
            max-items="10"
            pagination
            search
            stripe
            :data="logProDetalheData"
          >
            <div
              slot="header"
              class="flex flex-wrap-reverse items-center flex-grow justify-between"
            >
              <div class="flex mb-1">Detalhes</div>

              <div class="flex flex-wrap-reverse items-center">
                <feather-icon
                  @click="refresh('detalhes')"
                  icon="RotateCwIcon"
                  svgClasses="h-4 w-4"
                  class="cursor-pointer mr-4"
                />
              </div>
            </div>
            <template slot="thead">
              <vs-th sort-key="id">ID</vs-th>
              <vs-th sort-key="created_at">Data</vs-th>
              <vs-th sort-key="msg">Mensagem</vs-th>
              <vs-th sort-key="status">Status</vs-th>
            </template>

            <template slot-scope="{ data }">
              <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                <vs-td :data="data[indextr].id">{{ data[indextr].id }}</vs-td>
                <vs-td
                  class="whitespace-no-wrap"
                  :data="data[indextr].created_at"
                  >{{
                    data[indextr].created_at | moment("DD/MM/YYYY HH:mm:ss")
                  }}</vs-td
                >
                <vs-td :data="data[indextr].msg">
                  <div>{{ data[indextr].msg }}</div>
                  <div v-if="data[indextr].err != null">
                    <hr />
                    <div>{{ data[indextr].err }}</div>
                  </div>
                </vs-td>

                <vs-td :data="data[indextr].status">
                  <vs-chip transparent :color="chipColor(data[indextr].status)">
                    <span>{{ data[indextr].status | statusLabel }}</span>
                  </vs-chip>
                </vs-td>
              </vs-tr>
            </template>
          </vs-table>
        </vx-card>
      </div>
    </div>
  </div>
</template>

<script>
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";

export default {
  components: {
    vSelect,
    flatPickr,
  },
  data() {
    return {
      selected: [],
      selectedDetail: [],

      itemsPerPage: 5,
      isMounted: false,

      filtros: [
        {
          eventoSelected: {
            label: "Todos",
            value: "todos",
          },
          filtro_ini: new Date(),
          filtro_fim: new Date(),
          statusSelected: {
            label: "Todos",
            value: "todos",
          },
        },
      ],

      configdateTimePickerDate: {
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "Y-m-d",
        locale: Portuguese,
      },

      eventoOptions: [
        {
          label: "Atualizar Nota Frete",
          value: "atualizar_nota_frete",
        },
        {
          label: "Erros Email",
          value: "erros_email",
        },
        {
          label: "Erros Plataforma de Rastreamento",
          value: "erros_sis_track",
        },
        {
          label: "Exportar Coletas",
          value: "exportacao_coletas",
        },
        {
          label: "Gerar Coletas Fixas",
          value: "gerar_coletas_fixas",
        },
        {
          label: "Importar Clientes",
          value: "carga_inicial_clientes",
        },
        {
          label: "Importar Coletas",
          value: "importacao_coletas",
        },
        {
          label: "Importar Motoristas",
          value: "carga_inicial_motoristas",
        },
        {
          label: "Marcar Coletas Exportadas",
          value: "marca_coletas_exportadas",
        },
        {
          label: "Sincronizar Clientes",
          value: "sincronizacao_clientes",
        },
        {
          label: "Sincronizar Motoristas",
          value: "sincronizacao_motoristas",
        },
        {
          label: "Todos",
          value: "todos",
        },
      ],

      statusOptions: [
        {
          label: "Todos",
          value: "todos",
        },
        {
          label: "OK",
          value: "0",
        },
        {
          label: "Erro",
          value: "1",
        },
      ],
    };
  },
  created() {
    this.getLogPro();
  },
  mounted() {
    this.isMounted = true;
  },  
  computed: {
    logProData() {
      return this.$store.state.logPro.logProData;
    },
    logProDetalheData() {
      return this.$store.state.logPro.logProDetalheData;
    },
    chipColor() {
      return (value) => {
        if (value === "0") return "success";
        else if (value === "1") return "danger";
        else "primary";
      };
    },
    currentPage() {
      if (this.isMounted) {
        return this.$refs.table.currentx;
      }
      return 0;
    },
    queriedItems() {
      return this.$refs.table
        ? this.$refs.table.queriedResults.length
        : this.logProData.length;
    },
  },
  filters: {
    statusLabel(str) {
      if (str === "0") return "OK";
      else if (str === "1") return "Erro";
      else return str;
    },
  },
  methods: {
    async getLogPro() {
      this.selected = []; //Limpa o array selecionado, dessa forma não vamos carregar a tabela de detalhes.
      await this.$store
        .dispatch("logPro/indexLogPro", this.filtros[0])
        .catch((err) => {
          console.error(err);
        });
    },
    async getLogProDetalhe() {
      await this.$store
        .dispatch("logPro/detalheLogPro", this.selected["proc_id"])
        .catch((err) => {
          console.error(err);
        });
    },
    async refresh(str = "") {
		const { filtro_ini, filtro_fim } = this.filtros[0];

		// Verifica se ambas as datas são válidas
		if (filtro_ini && filtro_fim) {
			const diffMs = new Date(filtro_fim) - new Date(filtro_ini); // diferença em ms
			const diffDias = diffMs / (1000 * 60 * 60 * 24);

			if (diffDias > 30) {
			this.$vs.notify({
				title: "Período inválido",
				text: "O intervalo de datas não pode ultrapassar 30 dias.",
				color: "danger",
			});
			return; // interrompe a execução
			}
		}

		await this.$vs.loading({ scale: 0.5 });		
		if (str == "detalhes") {
			await this.getLogProDetalhe();
		} else {
			await this.getLogPro();
		}		
		await this.$vs.loading.close();
    },
  },
};
</script>

<style lang="scss" scoped>
</style>