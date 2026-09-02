const coletaMixins = {
	data() {
		return {
		}
	},
	computed: {
	},
	methods: {
		async getNotasFiscais(coleta_id) {
			await this.$store
				.dispatch("coleta/getNotasFiscais", coleta_id)
				.catch(err => {
					console.error(err);
				});
		},
		async getColetaLog(coleta_id) {
			await this.$store
				.dispatch("coleta/getColetaLog", coleta_id)
				.catch(err => {
					console.error(err);
				});
		},
		async getColetaPos(coleta_id) {
			await this.$store
				.dispatch("coleta/getColetaPos", coleta_id)
				.catch(err => {
					console.error(err);
				});
		},
		async retornarInstrucoesColeta(coleta_id) {
			await this.$store
				.dispatch("coleta/retornarInstrucoesColeta", coleta_id)
				.catch(err => {
					console.error(err);
				});
		},
	}
}

export default coletaMixins