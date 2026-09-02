import axios from "@/axios.js"

export default {
	SET_BEARER(state, apiToken) {
		axios.defaults.headers.common['Authorization'] = 'Bearer ' + apiToken
	}
}