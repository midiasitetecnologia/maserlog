/*	=========================================================================================
  	Mixins são uma forma flexível de distribuir funcionalidade reutilizável em diversos componentes Vue. 
    Um objeto mixin pode conter quaisquer opções de componente. Quando um componente utiliza um mixin, 
    todas as opções deste serão misturadas (em inglês, mixed in) com as opções do próprio componente.
==========================================================================================	*/

const procsMixins = {
	data() {
		return {
			popupFoto: false,
			foto: null,
			fotoTitulo: ' ', //Aqui tem um espaço, se for null/trim, vai exibir o título "popup" default do component.
		}
	},
	computed: {
		windowWidth() {
			return this.$store.state.windowWidth;
		},
		corStatus() {
			return value => {
				if (
					value == "C0" ||
					value == "C1" ||
					value == "E0" ||
					value == "E1"
				) {
					return "danger";
				} else if (
					value == "C2" ||
					value == "C3" ||
					value == "C4" ||
					value == "E2" ||
					value == "E3" ||
					value == "E4" ||
					value == "EP"
				) {
					return "warning";
				} else if (value == "CR" || value == "ER") {
					return "success";
				} else if (value == "CN" || value == "EN") {
					return "primary";
				}
			};
		},
		inicialStatus() {
			return value => {
				var inicial = value.charAt(0);
				if (inicial == 'C') {
					return 'Coleta'
				} else {
					return 'Entrega'
				}
			};
		}
	},
	methods: {
		retCorPercOcup(percentual) {
			if (percentual <= 50) {
				return "success";
			} else if (percentual <= 75) {
				return "warning";
			} else {
				return "danger";
			}
		},
		exibirFoto(src, titulo = ' ') {
			this.foto = src;
			this.fotoTitulo = titulo;
			this.popupFoto = !this.popupFoto;
		},
		vueIgualTrimNull(value) {
			let retorno = false;
			if ((value == null) || (value.trim() == '')) {
				retorno = true;
			}
			return retorno;
		},
		vueIgualZeroNull(value) {
			let retorno = false;
			if ((value == null) || (value == 0)) {
				retorno = true;
			}
			return retorno;
		},
		inicioMes() {
			var date = new Date();
			var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
			return firstDay;
		},
		montarCarga(volumes, especie, peso) {
			var carga;
			var fPeso;
			carga = null;

			if (peso != null) {
				fPeso = parseFloat(peso.replace(",", ".")) > 0 ? parseFloat(peso.replace(",", ".")) + 'kg' : ''
			} else {
				fPeso = ''
			}

			if (volumes != null) {
				carga = volumes;
			}

			if (especie != null) {
				if (carga != null) {
					carga = carga + " " + especie;
				} else {
					carga = especie;
				}
			}

			if (peso != null) {
				if (carga != null) {
					carga = carga + " " + fPeso;
				} else {
					carga = fPeso;
				}
			}

			return carga;
		},
		montarDimensao(comp, larg, alt, parent = true) {
			var dimensao;
			var fComp;
			var fLarg;
			var fAlt;
			dimensao = null;


			if (comp != null) {
				fComp = parseFloat(comp.replace(",", ".")) > 0 ? parseFloat(comp.replace(",", ".")) : 0;
			} else {
				fComp = 0
			}

			if (larg != null) {
				fLarg = parseFloat(larg.replace(",", ".")) > 0 ? parseFloat(larg.replace(",", ".")) : 0;
			} else {
				fLarg = 0
			}

			if (alt != null) {
				fAlt = parseFloat(alt.replace(",", ".")) > 0 ? parseFloat(alt.replace(",", ".")) : 0;
			} else {
				fAlt = 0
			}

			if (fComp > 0 || fLarg > 0 || fAlt > 0) {
				if (parent == true) {
					dimensao = '(' + fComp + ' x ' + fLarg + ' x ' + fAlt + ')';
				} else {
					dimensao = fComp + ' x ' + fLarg + ' x ' + fAlt;
				}
			}

			return dimensao;
		},
	}
}

export default procsMixins