/*	=========================================================================================
  	Mixins são uma forma flexível de distribuir funcionalidade reutilizável em diversos componentes Vue. 
    Um objeto mixin pode conter quaisquer opções de componente. Quando um componente utiliza um mixin, 
    todas as opções deste serão misturadas (em inglês, mixed in) com as opções do próprio componente.
==========================================================================================	*/

const labelsMixins = {
	methods: {
		/* Estes valores são apenas o label que aparece no retorno de cada componente,
		   verifique se as opções e os valores estão corretos em cada situação/component.vue

		   Obs: Utilizado apenas como "methods", se a chamada esperada deve ser um "filters" 
		   então o arquivo correto é o "filters.js" ou o próprio component.vue
		*/
		tipoColetaLabel(str) {			
			if (str === "D") return "Diária (solicitação normal; veículo pode atender vários clientes)";
			else if (str === "C") return "Contrato (por expediente; veículo dedicado a um único cliente)";
			else if (str === "M") return "Multi-destinos (coleta na mesma origem para vários destinos; veículo pode atender vários clientes)";
			else return str;
		},
		sisCargaLabel(str) {
			if (str === "N") return "Nenhum";
			else if (str === "E") return "Empilhadeira";
			else if (str === "P") return "Ponte Rolante";
			else if (str === "M") return "Manual";
			else return str;
		},
		tipoFreteLabel(str) {
			if (str === "N") return "Normal";
			else if (str === "R") return "Retorno Embalagem / Beneficiamento";
			else return str;
		},
		reentregaLabel(str) {
			if (str === "R") return "Reentrega";
			else if (str === "D") return "Devolução";
			else return str;
		},
		motNaoEntregaNfLabel(str) {
			if (str === "51") return "Mercadoria não conforme";
			else if (str === "52") return "Recusa de nota fiscal";
			else return str;
		},
		serviceLabel(str) {
			if (str === "google_cloud") return "Google Cloud";
			else if (str === "mapbox") return "Mapbox";
			else return str;
		},
		priorityLabel(str) {
			if (str === "1") return "Nível 1 (Alta prioridade)";
			else if (str === "2") return "Nível 2";
			else if (str === "3") return "Nível 3";
			else if (str === "4") return "Nível 4";
			else if (str === "5") return "Nível 5";
			else if (str === "6") return "Nível 6 (Baixa prioridade)";
			else return str;
		},
	}
}

export default labelsMixins