// axios
import axios from 'axios'
import router from '@/router'

const baseURL = "/"

const axiosInstance = axios.create({
	baseURL: baseURL,
})

// Sempre que uma resposta for de status "401". Redirecionamos para o Login.
// Significa que o usuário não tem autorização.
axiosInstance.interceptors.response.use(function (response) {
	return response;
}, function (error) {
	if (401 === error.response.status) {

		router.push({ path: '/login' }).catch(e => { })
		localStorage.removeItem("userInfo");
		localStorage.removeItem("apiToken");

	} else {
		return Promise.reject(error);
	}
});

export default axiosInstance
