
import state from './moduleWisdomState.js'
import mutations from './moduleWisdomMutations.js'
import actions from './moduleWisdomActions.js'
import getters from './moduleWisdomGetters.js'

export default {
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

