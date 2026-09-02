import axios from "@/axios.js"

export default {
	editSysCfg({ commit }) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/sys-cfg/1/edit`, { //Aqui sempre será apenas um registro, o parametro é somente necessário para trazer o resource Edit das rotas do Laravel.
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				}
			})
				.then((response) => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	}
}
