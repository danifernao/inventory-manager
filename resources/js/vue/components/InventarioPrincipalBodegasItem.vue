<template>
    <template v-if="cargado">
        <Error :error="error" v-if="error"/>
        <div class="envoltorio" v-else>
            <Cabecera tipo="bodega" :registro="bodega"/>
            <div class="operaciones">
                <Buscador consejo="Buscar..."/>
                <Opciones :opciones="opciones" @accion="exportar"/>
            </div>
            <Lista componente="productos" ordenPor="marca" :titulo="titulo" @en-bodega="estaEnBodega" @api-lista="obtenerApiUrl"/>
            <Archivo :url="archivoUrl" nombre="productos.xlsx" v-if="archivoUrl" @descargado="descargaFinalizada"/>
        </div>
    </template>
    <IconoCargando v-else/>
</template>

<script>
    import Cabecera from './InventarioPrincipalBaseItemCabecera.vue'
    import Buscador from './InventarioPrincipalBaseBuscador.vue'
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    import Lista from './InventarioPrincipalBaseLista.vue'
    import Archivo from './InventarioPrincipalBaseArchivo.vue'
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ArchivoMixin from '../mixins/archivoMixin.js'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Bodega',
        components: {
            Cabecera,
            Buscador,
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
                bodega: null,
                enBodega: 1,
                cargado: false,
                error: null
            }
        },
        computed: {
            titulo() {
                if (this.enBodega) {
                    return 'Productos con unidades en esta bodega'
                } else {
                    return 'Productos sin unidades en esta bodega'
                }
            },
            opciones() {
                return [
                    {
                        url: `${this.$route.path}/productos/registrar`,
                        texto: 'Registrar producto',
                        icono: 'plus'
                    },
                    {
                        texto: 'Exportar listado',
                        icono: 'file-export'
                    },
                    {
                        texto: 'Filtrar listado',
                        icono: 'filter',
                        menu: [
                            {
                                url: `${this.$route.path}?enBodega=1`,
                                texto: 'Con unidades en esta bodega',
                                activo: this.enBodega
                            },
                            {
                                url: `${this.$route.path}?enBodega=0`,
                                texto: 'Sin unidades en esta bodega',
                                activo: !this.enBodega
                            }
                        ]
                    }
                ]
            }
        },
        beforeRouteUpdate(destino, fuente) {
            if (destino.params.bodegaId !== fuente.params.bodegaId) {
                this.cargado = false
                this.verBodega(destino.params.bodegaId)
            }
        },
        created () {
            this.verBodega(this.$route.params.bodegaId)
        },
        methods: {
            verBodega (id) {
                axios({
                    method: 'get',
                    url: `/api/bodegas/${id}`,
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.bodega = respuesta.data
                    this.error = null
                    this.cargado = true
                })
                .catch(error => {
                    this.error = error.response || true
                    if (error.response && this.error.status === 404) {
                        this.$store.commit('actualizarBodega')
                    }
                    this.cargado = true
                })
            },
            
            estaEnBodega(respuesta) {
                this.enBodega = respuesta
            }
        }
    }
</script>