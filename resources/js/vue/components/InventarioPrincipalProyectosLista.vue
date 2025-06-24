<template>
    <div class="envoltorio">
        <div class="operaciones">
            <Buscador consejo="Buscar..."/>
            <Opciones :opciones="opciones" @accion="exportar"/>
        </div>
        <Lista componente="proyectos" ordenPor="nombre" titulo="Proyectos" @api-lista="obtenerApiUrl"/>
        <Archivo :url="archivoUrl" nombre="proyectos.xlsx" v-if="archivoUrl" @descargado="descargaFinalizada"/>
    </div>
</template>

<script>
    import Buscador from './InventarioPrincipalBaseBuscador.vue'
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    import Lista from './InventarioPrincipalBaseLista.vue'
    import Archivo from './InventarioPrincipalBaseArchivo.vue'
    import ArchivoMixin from '../mixins/archivoMixin.js'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Proyectos',
        components: {
            Buscador,
            Opciones,
            Lista,
            Archivo
        },
        mixins: [
            ArchivoMixin,
            ConfigMixin,
            RutaMixin,
            SwalMixin
        ],
        computed: {
            opciones() {
                return [
                    {
                        url: `${this.$route.path}/registrar`,
                        texto: 'Registrar proyecto',
                        icono: 'plus'
                    },
                    {
                        texto: 'Exportar listado',
                        icono: 'file-export'
                    }
                ]
            }
        }
    }
</script>