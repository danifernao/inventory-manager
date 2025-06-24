<template>
    <template v-if="cargado">
        <Error :error="error" v-if="error"/>
        <div class="envoltorio" v-else>
            <MigasDePan/>
            <Cabecera tipo="proyecto" :registro="proyecto"/>
            <div class="operaciones">
                <Buscador consejo="Buscar..."/>
                <Opciones :opciones="opciones" @accion="exportar"/>
            </div>
            <Lista componente="productos" ordenPor="marca" :titulo="tituloLista" @dado-de-baja="estaDeBaja" @api-lista="obtenerApiUrl"/>
            <Archivo :url="archivoUrl" nombre="productos.xlsx" v-if="archivoUrl" @descargado="descargaFinalizada"/>
        </div>
    </template>
    <IconoCargando v-else/>
</template>

<script>
    import MigasDePan from './InventarioPrincipalBaseMigasDePan.vue'
    import Cabecera from './InventarioPrincipalBaseItemCabecera.vue'
    import Buscador from './InventarioPrincipalBaseBuscador.vue'
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    import Lista from './InventarioPrincipalBaseLista.vue'
    import Archivo from './InventarioPrincipalBaseArchivo.vue'
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ArchivoMixin from '../mixins/archivoMixin.js'
    import ConfigMixin from '../mixins/configMixin.js'
    
    export default {
        name: 'Proyecto',
        components: {
            MigasDePan,
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
            ConfigMixin
        ],
        data() {
            return {
                proyecto: null,
                dadoDeBaja: false,
                cargado: false,
                error: null
            }
        },
        computed: {
            opciones() {
                return [
                    {
                        texto: 'Exportar listado',
                        icono: 'file-export'
                    },
                    {
                        texto: 'Filtrar listado',
                        icono: 'filter',
                        menu: [
                            {
                                url: `${this.$route.path}?dadoDeBaja=0`,
                                texto: 'Con unidades activas',
                                activo: !this.dadoDeBaja
                            },
                            {
                                url: `${this.$route.path}?dadoDeBaja=1`,
                                texto: 'Con unidades dadas de baja',
                                activo: this.dadoDeBaja
                            }
                        ]
                    }
                ]
            },
            tituloLista() {
                return `Productos con unidades ${this.dadoDeBaja ? 'dadas de baja' : 'activas'}`
            }
        },
        created() {
            axios({
                method: 'get',
                url: `/api/proyectos/${this.$route.params.proyectoId}`,
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                }
            })
            .then(respuesta => {
                this.proyecto = respuesta.data
            })
            .catch(error => {
                this.error = error.response || true
            })
            .finally(() => {
                this.cargado = true
            })
        },
        methods: {
            estaDeBaja(respuesta) {
                this.dadoDeBaja = respuesta
            }
        }
    }
</script>