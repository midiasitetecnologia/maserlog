
import state from './moduleSysCfgState.js'
import mutations from './moduleSysCfgMutations.js'
import actions from './moduleSysCfgActions.js'
import getters from './moduleSysCfgGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

