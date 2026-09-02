export default {
	SET_DISTANCE_MATRIX(state, distanceMatrix) {
		state.distanceMatrixData = distanceMatrix
	},
	REMOVE_RECORD(state, itemId) {
		const distanceMatrixIndex = state.distanceMatrixData.findIndex((u) => u.id == itemId)
		state.distanceMatrixData.splice(distanceMatrixIndex, 1)
	},
}
