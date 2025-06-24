<template>
    <template v-if="cargado">
        <Error :error="error" v-if="error"/>
        <div class="envoltorio" v-else>
            <MigasDePan/>
            <Cabecera tipo="proveedor" :registro="proveedor"/>
            <div class="operaciones">
                <Buscador consejo="Buscar..."/>
                <Opciones :opciones="opciones"/>
            </div>
            <Lista componente="productos" ordenPor="marca" :titulo="tituloLista" @dado-de-baja="estaDeBaja"/>
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
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    
    export default {
        name: 'Proveedor',
        components: {
            MigasDePan,
            Cabecera,
            Buscador,
            Opciones,
            Lista,
            IconoCargando,
            Error
        },
        mixins: [
            ConfigMixin,
            RutaMixin
        ],
        data() {
            return {
                proveedor: null,
                dadoDeBaja: false,
                cargado: false,
                error: null
            }
        },
        computed: {
            opciones() {
                return [
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
                url: `/api/proveedores/${this.$route.params.proveedorId}`,
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                }
            })
            .then(respuesta => {
                this.proveedor = respuesta.data
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