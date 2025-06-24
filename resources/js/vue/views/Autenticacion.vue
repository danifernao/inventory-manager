<template>
    <div id="autenticacion" v-if="!$store.state.usuario">
        <header>
            <router-link to="/iniciar-sesion" title="Iniciar sesión" v-if="imagenCabeceraUrl">
                <img :src="imagenCabeceraUrl" alt="Cabecera de la aplicación"/>
            </router-link>
            <h1 v-else>
                <router-link to="/iniciar-sesion" title="Iniciar sesión">
                    {{ $store.state.aplicacion.titulo }}
                </router-link>
            </h1>
        </header>
        <main>
            <router-view></router-view>
        </main>
    </div>
</template>

<script>
    export default {
        name: 'Autenticacion',
        computed: {
            imagenCabeceraUrl () {
                return this.$store.state.aplicacion.img_autenticacion_url
            }
        },
        created() {
            if (this.$store.state.usuario) {
                this.$router.push('/bodegas')
            }
        }
    }
</script>

<style scoped>
    #autenticacion {
        display: flex;
        flex-direction: column;
        padding: 1rem;
        width: 100%;
        max-width: 25rem;
    }
    
    header {
        margin-bottom: 0.5rem;
        text-align: center;
    }
    
    header a {
        color: var(--color-negro-1);
        text-decoration: none !important;
    }
    
    header h1 {
        margin: 0;
    }
    
    header img {
        max-width: 17rem;
        max-height: 7rem;
    }
    
    main {
        padding: 1.5rem;
        background-color: var(--color-blanco-1);
        border: 0.06rem solid var(--color-gris-1);
        border-radius: 0.4rem;
    }
    
    :deep(h2) {
        margin: 0 0 1.5rem;
        text-align: center;
    }
    
    :deep(a:hover) {
        text-decoration: underline;
    }
    
    :deep(input[type='text']),
    :deep(input[type='email']),
    :deep(input[type='password']) {
        padding: 0.5rem 2rem 0.5rem 0.8rem;
        width: 100%;
    }
    
    :deep(svg) {
        position: absolute;
        top: calc(50% - 0.5rem);
        right: 0.8rem;
        color: var(--color-gris-4);
    }
    
    :deep(.inhabilitado) {
        pointer-events: none;
    }
    
    :deep(.inhabilitado) input,
    :deep(.inhabilitado) button {
        opacity: 0.5;
    }
    
    :deep(form) {
        margin-top: 1rem;
    }
    
    :deep(.seccion) {
        position: relative;
        margin: 1rem 0;
    }
    
    :deep(.seccion):first-of-type {
        margin-top: 0;
    }
    
    :deep(.seccion).mostrar-contrasena {
        display: flex;
        align-items: center;
    }
    
    :deep(.seccion).mostrar-contrasena label {
        font-size: 0.9rem;
    }
    
    :deep(.seccion).boton {
        margin-top: 2rem;
    }
    
    :deep(.seccion).boton,
    :deep(.seccion).pie {
        text-align: center;
    }
    
    :deep(.seccion).pie {
        font-size: 0.9rem;
        text-align: center;
    }
</style>