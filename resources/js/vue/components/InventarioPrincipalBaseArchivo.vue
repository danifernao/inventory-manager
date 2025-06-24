<template>
    <a :href="url" :download="nombre" ref="descargar"></a>
</template>

<script>
  export default {
    name: 'Archivo',
    props: {
        url: String,
        nombre: String
    },
    mounted() {
        this.$refs.descargar.click()
        this.$emit('descargado')
    }
  }
</script>

<style scoped>
    a {
        position: absolute;
        top: -100vh;
        left: -100vw;
    }
</style>