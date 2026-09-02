<template>
    <vs-chip :color="cor_fundo">
	    <span :style="corFonte(cor_fonte)">{{ sigla }}</span>		
    </vs-chip>
</template>

<script>
export default {
	name: "chip-emp",
	props: {
        cor_fundo: "",
        cor_fonte: "",
        sigla: "",
	},
	computed: {
		corFonte() {
			return value => {
				return (
					"color:" +
					value +
					"!important; font-weight: bold !important;"
				);
			};
		},
	}
};
</script>

<style lang="scss">
</style>