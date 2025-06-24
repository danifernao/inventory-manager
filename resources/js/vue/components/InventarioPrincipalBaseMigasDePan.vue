<template>
    <nav v-if="rutas.length">
        Estás en:
        <ul>
            <li v-for="ruta in rutas">
                <router-link :to="ruta.url">
                    {{ ruta.texto }}
                </router-link>
                »
            </li>
        </ul>
    </nav>
</template>

<script>
    export default {
        name: 'MigasDePan',
        data() {
            return {
                rutas: []
            }
        },
        created() {
            const rutaPartes = _.drop(this.$route.path.split('/'))
            const indice = rutaPartes[0] === 'bodegas' ? 1 : 0
            const patronId = /^\d+$/
            
            for (let i = indice; i < rutaPartes.length - 1; i++) {
                if (!['productos', 'unidades'].includes(rutaPartes[i])) {
                    let url = '/' + rutaPartes.slice(0, i + 1).join('/')
                    let texto = null
                    
                    if (patronId.test(rutaPartes[i])) {
                        texto = _.capitalize(rutaPartes[i - 1]).slice(0, -1).replace(/re$/gi, 'r')
                    } else {
                        texto = _.capitalize(rutaPartes[i])
                    }
                    
                    this.rutas.push({
                        url: url,
                        texto: texto
                    })
                }
            }
        }
    }
</script>

<style scoped>
    nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.7rem;
        font-size: 0.9rem;
    }
    ul {
        display: inline-flex;
        gap: 0.5rem;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    
    li {
        display: flex;
        gap: 0.5rem;
    }
</style>