<template>
    <div class="envoltorio">
        <div class="operaciones">
            <Buscador consejo="Buscar por nombre, identificación o correo"/>
            <Opciones :opciones="opciones" @accion="exportar"/>
        </div>
        <Lista componente="usuarios" ordenPor="nombres" titulo="Lista de usuarios" @api-lista="obtenerApiUrl"/>
        <Archivo :url="archivoUrl" nombre="usuarios.xlsx" v-if="archivoUrl" @descargado="descargaFinalizada"/>
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
        name: 'Usuarios',
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
        data() {
            return {
                opciones: [
                    {
                        url: `${this.$route.path}/registrar`,
                        texto: 'Registrar usuario',
                        icono: 'plus'
                    },
                    {
                        texto: 'Exportar listado',
                        icono: 'file-export'
                    }
                ]
            }
        },
        created() {
            if (!this.$store.state.usuario.es_administrador) {
                this.$router.push('/404')
            }
        }
    }
</script>