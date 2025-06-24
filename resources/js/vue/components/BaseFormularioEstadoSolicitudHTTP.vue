<template>
    <div :class="estado" ref="estado">
        <ul v-if="'errors' in respuesta">
            <template v-for="error in respuesta.errors">
                <template v-for="mensaje in error">
                    <li>{{ mensaje }}</li>
                </template>
            </template>
        </ul>
        <p v-else-if="'message' in respuesta">
            {{ respuesta.message }}
        </p>
    </div>
</template>

<script>
    export default {
        name: 'EstadoSolicitud',
        props: {
            estado: String,
            respuesta: Object
        },
        mounted() {
            this.desplazar()
        },
        updated() {
            this.desplazar()
        },
        methods: {
            desplazar() {
                this.$refs.estado.scrollIntoView({ behavior: 'smooth' })
            }
        }
    }
</script>

<style scoped>
    div {
        margin-bottom: 1rem;
        padding: 0.5rem 0.8rem;
        border-left-width: 0.3rem;
        border-left-style: solid;
        border-top-right-radius: 0.3rem;
        border-bottom-right-radius: 0.3rem;
        font-size: 0.9rem;
    }
    
    ul {
        padding-left: 1rem;
        list-style: square;
    }
    
    ul, p {
        margin: 0;
    }
    
    li:first-letter {
        text-transform: uppercase;
    }
    
    .cargando {
        background-color: var(--color-azul-1);
        border-left-color: var(--color-azul-2);
        font-style: italic;
    }
    
    .exito {
        background-color: var(--color-verde-1);
        border-color: var(--color-verde-4);
        color: var(--color-verde-4);
    }
    
    .error {
        background-color: var(--color-rojo-1);
        border-left-color: var(--color-rojo-5);
        color: var(--color-rojo-5);
    }
</style>