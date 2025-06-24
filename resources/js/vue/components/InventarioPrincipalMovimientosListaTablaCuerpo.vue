<template>
    <tbody>
        <template v-if="registros.length">
            <tr v-for="registro in registros">
                <Celda clase="fecha" :dato="registro.fecha"/>
                <Celda clase="fuente" :url="rutaItem(registro, 0)" :dato="registro.fuente.nombre"/>
                <Celda clase="destino" :url="rutaItem(registro, 1)" :dato="registro.destino.nombre"/>
            </tr>
        </template>
        <tr v-else>
            <td colspan="100%" class="vacio">No hay registros que mostrar.</td>
        </tr>
    </tbody>
</template>

<script>
    import CuerpoCelda from './InventarioPrincipalBaseListaTablaCuerpoCelda.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    
    export default {
        name: 'TablaCuerpoMovimientos',
        components: {
            Celda: CuerpoCelda,
        },
        props: {
            registros: Object
        },
        methods: {
            rutaItem(registro, indice) {
                let url = ''
                const tipos = registro.tipo.split('_')
                
                if (typeof(registro.destino.nombre) !== 'undefined') {
                    const clave = indice ? 'destino' : 'fuente'
                    url = `/${tipos[indice].toLowerCase()}s/${registro[clave].id}`
                } else {
                    url = this.$route.path
                }
                
                return url
            }
        }
    }
</script>