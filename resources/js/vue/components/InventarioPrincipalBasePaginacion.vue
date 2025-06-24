<template>
    <nav v-if="paginacion">
        <ul>
            <li v-for="pagina in paginacion">
                <router-link :to="ruta(pagina.url)" :title="'Página ' + pagina.label" v-if="pagina.url && !pagina.active" v-html="pagina.label"></router-link>
                <span v-else v-html="pagina.label"></span>
            </li>
        </ul>
    </nav>
</template>

<script>
    export default {
        name: 'Paginacion',
        props: {
            paginacion: Array
        },
        methods: {
            ruta(url) {
                const apiUrl = new URLSearchParams(url)
                const appUrl = new URLSearchParams(location.search)
                
                appUrl.set('pagina', apiUrl.get('page'))
                
                if (apiUrl.has('buscar')) {
                    appUrl.set('buscar', apiUrl.get('buscar'))
                }
                
                return `${this.$route.path}?${appUrl.toString()}`
            }
        }
    }
</script>

<style scoped>
    nav {
        margin: 1rem 0;
        text-align: right;
    }
    
    ul {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    li * {
        padding: 0.4rem 0.7rem;
        font-size: 0.9rem;
        text-align: center;
    }
    
    li a {
        background-color: var(--color-naranja-3);
        border-radius: 0.3rem;
        color: var(--color-blanco-1);
        transition: background-color 100ms ease-out;
    }
    
    li a:hover {
        background-color: var(--color-naranja-4);
    }
</style>