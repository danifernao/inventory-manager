<template>
    <template v-if="cargado">
        <Error :error="error" v-if="error"/>
        <div class="envoltorio" v-else>
            <MigasDePan/>
            <Cabecera tipo="unidad" :registro="unidad"/>
            <div class="operaciones">
                <Opciones :opciones="opciones" @accion="exportar"/>
            </div>
            <Lista componente="movimientos" titulo="Movimientos de la unidad" ordenPor="created_at" @api-lista="obtenerApiUrl"/>
            <Archivo :url="archivoUrl" nombre="movimientos.xlsx" v-if="archivoUrl" @descargado="descargaFinalizada"/>
        </div>
    </template>
    <IconoCargando v-else/>
</template>

<script>
    import MigasDePan from './InventarioPrincipalBaseMigasDePan.vue'
    import Cabecera from './InventarioPrincipalBaseItemCabecera.vue'
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    import Lista from './InventarioPrincipalBaseLista.vue'
    import Archivo from './InventarioPrincipalBaseArchivo.vue'
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ArchivoMixin from '../mixins/archivoMixin.js'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Producto',
        components: {
            MigasDePan,
            Cabecera,
            Opciones,
            Lista,
            Archivo,
            IconoCargando,
            Error
        },
        mixins: [
            ArchivoMixin,
            ConfigMixin,
            SwalMixin
        ],
        data() {
            return {
                unidad: null,
                cargado: false,
                error: null,
                opciones: [
                    {
                        texto: 'Exportar listado',
                        icono: 'file-export'
                    }
                ]
            }
        },
        created() {
            axios({
                method: 'get',
                url: `/api/unidades/${this.$route.params.unidadId}`,
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                }
            })
            .then(respuesta => {
                this.unidad = respuesta.data
            })
            .catch(error => {
                this.error = error.response || true
            })
            .finally(() => {
                this.cargado = true
            })
        }
    }
</script>