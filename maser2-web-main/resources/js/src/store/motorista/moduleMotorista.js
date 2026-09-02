
import state from './moduleMotoristaState.js'
import mutations from './moduleMotoristaMutations.js'
import actions from './moduleMotoristaActions.js'
import getters from './moduleMotoristaGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

