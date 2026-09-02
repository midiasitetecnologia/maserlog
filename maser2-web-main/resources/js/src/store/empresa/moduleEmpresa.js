
import state from './moduleEmpresaState.js'
import mutations from './moduleEmpresaMutations.js'
import actions from './moduleEmpresaActions.js'
import getters from './moduleEmpresaGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

