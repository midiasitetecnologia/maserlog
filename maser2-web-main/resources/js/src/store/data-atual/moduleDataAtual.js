import state from './moduleDataAtualState.js'
import mutations from './moduleDataAtualMutations.js'
import actions from './moduleDataAtualActions.js'
import getters from './moduleDataAtualGetters.js'

export default {
	namespaced: true,
    state: state,
    mutations: mutations,
    actions: actions,
    getters: getters
}
