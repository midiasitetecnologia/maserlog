import Vue from 'vue'

Vue.filter('toLowerCase', function (value) {
	if (!value) return ''
	value = value.toString()
	return value.toLowerCase()
})

Vue.filter('toUpperCase', function (value) {
	if (!value) return ''
	value = value.toString()
	return value.toUpperCase()
})

Vue.filter('capitalize', function (value) {
	if (!value) return ''
	value = value.toString()
	let arr = value.split(" ")
	let capitalized_array = []
	arr.forEach((word) => {
		let capitalized = word.charAt(0).toUpperCase() + word.slice(1)
		capitalized_array.push(capitalized)
	})
	return capitalized_array.join(" ");
})

Vue.filter('title', function (value, replacer = "_") {
	if (!value) return ''
	value = value.toString()

	let arr = value.split(replacer)
	let capitalized_array = []
	arr.forEach((word) => {
		let capitalized = word.charAt(0).toUpperCase() + word.slice(1)
		capitalized_array.push(capitalized)
	})
	return capitalized_array.join(" ");
})

Vue.filter('truncate', function (value, limit) {
	return value.substring(0, limit)
})

Vue.filter('tailing', function (value, tail) {
	return value + tail;
})

Vue.filter('time', function (value, is24HrFormat = false) {
	if (value) {
		const date = new Date(Date.parse(value));
		let hours = date.getHours();
		const min = (date.getMinutes() < 10 ? '0' : '') + date.getMinutes()
		if (!is24HrFormat) {
			const time = hours > 12 ? 'AM' : 'PM';
			hours = hours % 12 || 12;
			return hours + ':' + min + ' ' + time
		}
		return hours + ':' + min
	}
})

Vue.filter('date', function (value, fullDate = false) {
	value = String(value)
	const date = value.slice(8, 10).trim();
	const month = value.slice(4, 7).trim();
	const year = value.slice(11, 15);

	if (!fullDate) return date + ' ' + month;
	else return date + ' ' + month + ' ' + year;
})

Vue.filter('month', function (val, showYear = true) {
	val = String(val)

	const regx = /\w+\s(\w+)\s\d+\s(\d+)./;
	if (!showYear) {
		return regx.exec(val)[1];
	} else {
		return regx.exec(val)[1] + ' ' + regx.exec(val)[2];
	}

})

Vue.filter('csv', function (value) {
	return value.join(', ')
})

Vue.filter('filter_tags', function (value) {
	return value.replace(/<\/?[^>]+(>|$)/g, "")
})

//RGSOFT

Vue.filter('first_name', function (value) {
	if (!value) return ''
	value = value.toString()
	let first_name = value.split(" ")[0]
	return first_name;
})

Vue.filter('to_kg', function (num) {
	return parseFloat(num.replace(",", ".")) > 0 ? parseFloat(num.replace(",", ".")) + 'kg' : num
})

Vue.filter('format_number', function (value) {
    if (value === null || value === undefined || isNaN(value)) {
        return '0';
    }

    return new Intl.NumberFormat('pt-BR').format(value);
});

Vue.filter('sim_nao', function (value) {
	if (value === "S") return "Sim"
	if (value === "N") return "Não"
	return value
})

Vue.filter('active_status', function (value) {
	if (value === "S") return "Sim"
	if (value === "N") return "Não"
	if (value === "B") return "Bloqueado"
	return value
})

Vue.filter('hora_min', function (value) {

	if (value == null) {
		return value
	}

	let hora_min = value.split(":");
	return hora_min[0] + ':' + hora_min[1]

})

//Filtros para Coleta

Vue.filter('coleta_status', function (value) {

	if (value == 'C0') {
		return 'Coleta - Solicitada'
	} else if (value == 'C1') {
		return 'Coleta - Autorizada'
	} else if (value == 'C2') {
		return 'Coleta - Deslocamento'
	} else if (value == 'C3') {
		return 'Coleta - Chegada'
	} else if (value == 'C4') {
		return 'Coleta - Iniciada'
	} else if (value == 'CN') {
		return 'Coleta - Não realizada'
	} else if (value == 'CR') {
		return 'Coleta - Realizada'
	} else if (value == 'E0') {
		return 'Entrega - Carga definida'
	} else if (value == 'E1') {
		return 'Entrega - Autorizada'
	} else if (value == 'E2') {
		return 'Entrega - Deslocamento'
	} else if (value == 'E3') {
		return 'Entrega - Chegada'
	} else if (value == 'E4') {
		return 'Entrega - Iniciada'
	} else if (value == 'EN') {
		return 'Entrega - Não realizada'
	} else if (value == 'EP') {
		return 'Entrega - Parcial'
	} else if (value == 'ER') {
		return 'Entrega - Realizada'
	} else {
		return value
	}

})

Vue.filter('coleta_status_res', function (value) {

	//Status da Coleta e Entrega de forma resumida.
	if (value == 'C0') {
		return 'Solicitada'
	} else if (value == 'C1' || value == 'E1') {
		return 'Autorizada'
	} else if (value == 'C2' || value == 'E2') {
		return 'Deslocamento'
	} else if (value == 'C3' || value == 'E3') {
		return 'Chegada'
	} else if (value == 'C4' || value == 'E4') {
		return 'Iniciada'
	} else if (value == 'CN' || value == 'EN') {
		return 'Não realizada'
	} else if (value == 'CR' || value == 'ER') {
		return 'Realizada'
	} else if (value == 'E0') {
		return 'Carga definida'
	} else if (value == 'EP') {
		return 'Parcial'
	} else {
		return value
	}

})

//Descrição do sistema de carga - Coletas...
Vue.filter('descr_sis_carga', function (value) {
	if (value === "N") return "Nenhum"
	else if (value === "E") return "Empilhadeira"
	else if (value === "P") return "Ponte Rolante"
	else if (value === "M") return "Manual"
	else return value
})

//Descrição do tipo de frete - Coletas...
Vue.filter('descr_tipo_frete', function (value) {
	if (value === "N") return "Normal"
	else if (value === "R") return "Retorno Embalagem / Beneficiamento"
	else return value
})

//Nível de consumo dos veículos
Vue.filter('nivel_cons_veiculo', function (value) {
	if (value == '0') {
		return 'Não definido'
	} else if (value == '1') {
		return 'Nível 1 (menor consumo)'
	} else if (value == '2') {
		return 'Nível 2'
	} else if (value == '3') {
		return 'Nível 3'
	} else if (value == '4') {
		return 'Nível 4'
	} else if (value == '5') {
		return 'Nível 5'
	} else if (value == '6') {
		return 'Nível 6 (maior consumo)'
	}
})

//Motivos de não entrega
Vue.filter('mot_nao_entrega', function (value) {

	if (value == '01') {
		return 'Entrega cancelada'
	} else if (value == '11') {
		return 'Empresa fechada'
	} else if (value == '12') {
		return 'Fora do dia ou horário'
	} else if (value == '50') {
		return 'Informado em cada nota'
	} else if (value == '51') {
		return 'Mercadoria não conforme'
	} else if (value == '52') {
		return 'Recusa de nota fiscal'
	} else if (value == '99') {
		return 'Outro'
	} else {
		return value
	}
})

//Motivos de não entrega nota fiscal
Vue.filter('mot_nao_entrega_nf', function (value) {

	if (value == '51') {
		return 'Mercadoria não conforme'
	} else if (value == '52') {
		return 'Recusa de nota fiscal'
	} else {
		return value
	}
})

Vue.filter('coleta_origem_reg', function (value) {

	if (value == 'A1') {
		return 'Gerado pelas coletas fixas'
	} else if (value == 'A2') {
		return 'Aplicativo do motorista'
	} else if (value == 'A3') {
		return 'Criado pelo cliente no portal'
	} else if (value == 'A4') {
		return 'Criado pelos usuários da Maser na plataforma'
	} else if (value == 'SD') {
		return 'Importado do sistema Domper'
	} else {
		return value
	}

})