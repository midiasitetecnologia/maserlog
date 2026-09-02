<template>
	<span>
		<span
			v-if="coleta_fixa == 'C'"
			class="font-semibold ml-2"
			style="color: white; background-color: #28C76F; border-radius: 2px; font-size: 12px; top: -1px; position: relative;"
		>&nbsp;C&nbsp;</span>
		<span
			v-if="coleta_fixa == 'D'"
			class="font-semibold ml-2"
			style="color: white; background-color: #174083; border-radius: 2px; font-size: 11px; top: -2px; position: relative;"
		>&nbsp;D&nbsp;</span>
		<span
			v-if="coleta_fixa == 'M'"
			class="font-semibold ml-2"
			style="color: white; background-color: #FF9F43; border-radius: 2px; font-size: 11px; top: -2px; position: relative;"
		>&nbsp;M&nbsp;</span>
	</span>
</template>

<script>
export default {
	name: "col-fixa",
	props: {
		coleta_fixa: null
	}
};
</script>

<style lang="scss">
</style>