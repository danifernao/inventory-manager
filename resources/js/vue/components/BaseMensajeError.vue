<template>
    <div v-if="error">
        <h2>Error {{ error.status }}</h2>
        <p>{{ error.statusText }}</p>
    </div>
    <div v-else>
        <p>No se pudo cargar la información, <a href="#" @click.prevent="recargar">inténtolo de nuevo</a> más tarde.</p>
    </div>
</template>

<script>
    export default {
        name: 'Error',
        props: {
            error: Object
        },
        methods: {
            recargar() {
                this.$router.go(0)
            }
        }
    }
</script>

<style scoped>
    div {
        margin: auto;
        padding: 1rem;
        text-align: center;
    }
</style>