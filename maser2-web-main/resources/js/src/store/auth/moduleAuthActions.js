import axios from "@/axios.js"

export default {
	login({ commit }, payload) {

		return new Promise((resolve, reject) => {
			axios.post("/api/AutenticarUsuario", { email: payload.userDetails.email, password: payload.userDetails.password })
				.then(response => {

					if (response.data.retorno.cod_retorno === "A100") {

						// Update user details
						commit('UPDATE_USER_INFO', response.data.dados.userInfo, { root: true })

						// Set bearer token in axios
						commit("SET_BEARER", response.data.dados.api_token)

						// Set apiToken						
						localStorage.setItem("apiToken", response.data.dados.api_token)

						resolve(response)
					} else {
						reject({ message: response.data.retorno.msg_retorno })
					}

				})
				.catch(error => { reject(error) })
		})
	}
}
