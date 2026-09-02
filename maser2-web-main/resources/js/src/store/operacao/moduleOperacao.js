
import state from './moduleOperacaoState.js'
import mutations from './moduleOperacaoMutations.js'
import actions from './moduleOperacaoActions.js'
import getters from './moduleOperacaoGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

