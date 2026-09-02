<template functional>
	<footer class="the-footer flex-wrap justify-between" :class="classes">
		<p>
			<span>COPYRIGHT &copy;</span>
			<span>{{new Date().getFullYear()}}</span>
			<a href="http://www.masertransportes.com.br/" target="_blank" rel="nofollow">Maser</a>
			<span class="hidden sm:inline-block">, Todos os direitos reservados.</span>
		</p>
		<p>
			<span class="hidden sm:inline-block">Desenvolvido por </span>			
			<a href="https://www.rgsoft.com.br/" target="_blank" rel="nofollow">RGSoft</a>
		</p>
	</footer>
</template>

<script>
export default {
	name: "the-footer",
	props: {
		classes: {
			type: String,
		},
	},
	inject: {
		components: {
			default: {},
		},
	},
};
</script>
