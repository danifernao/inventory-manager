<template>
    <div>
        <h2>{{ titulo }}</h2>
        <form @submit.prevent="establecerTotal">
            <label for="porPagina">Registros por página:</label>
            <input type="number" id="porPagina" min="1" max="300" v-model="total" @change="establecerTotal"/>
            <Opciones :opciones="opciones" v-if="opciones" v-bind="$attrs"/>
        </form>
    </div>
    
</template>

<script>
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    
    export default {
        name: 'ListaCabecera',
        components: {
            Opciones
        },
        props: {
            titulo: String,
            totalPorPagina: {
                type: [ String, Number ]
            },
            opciones: Object
        },
        data() {
            return {
                total: null
            }
        },
        created() {
            this.total = this.totalPorPagina || 10
        },
        methods: {
            establecerTotal() {
                const urlParams = new URLSearchParams(location.search)
                urlParams.set('porPagina', this.total)
                this.$router.push({ query: Object.fromEntries(urlParams) })
            }
        }
    }
</script>

<style scoped>
    div {
        gap: 1rem;
    }
    
    div, form {
        display: flex !important;
        flex-direction: row !important;
        align-items: center;
    }
    
    h2 {
        flex: 1;
    }
    
    form {
        gap: 0.5rem;
        margin: 0 !important;
        padding: 0 !important;
        background-color: transparent !important;
        border-width: 0 !important;
    }
    
    label {
        margin: 0 !important;
        font-size: 0.9rem;
        text-align: right;
    }
    
    input {
        max-width: 5rem;
        text-align: right;
    }
</style>