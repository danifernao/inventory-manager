<template>
    <form @submit.prevent="consultar">
        <input type="text" :placeholder="consejo" v-model="buscar"/>
        <button type="submit" class="boton-naranja">
            <font-awesome-icon icon="magnifying-glass"/>
        </button>
    </form>
</template>

<script>
    export default {
        name: 'Buscador',
        props: {
            consejo: String
        },
        data() {
            return {
                buscar: null
            }
        },
        created() {
            if (this.$route.query.buscar) {
                this.consulta = this.$route.query.buscar
                this.consultar()
            }
        },
        methods: {
            consultar() {
                const urlParams = new URLSearchParams(location.search)
                
                if (this.buscar) {
                    urlParams.set('buscar', this.buscar)
                } else {
                    urlParams.delete('buscar')
                }
                
                urlParams.set('pagina', 1)
                
                this.$router.push({ query: Object.fromEntries(urlParams) })
            }
        }
    }
</script>

<style scoped>
    form {
        display: flex !important;
        flex-direction: row !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
        min-width: 10rem;
        max-width: 20rem;
        background-color: transparent !important;
        border-width: 0 !important;
    }
    
    input {
        flex: 1;
        border-right-width: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    
    button {
        padding: 0.5rem 0.8rem;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>