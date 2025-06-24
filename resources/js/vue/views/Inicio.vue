<template></template>

<script>
    export default {
        name: 'Inicio',
        created() {
            if (this.$store.state.usuario) {
                this.$router.push('/bodegas')
            } else {
               this.$router.push('/iniciar-sesion')
            }
        }
    }
</script>