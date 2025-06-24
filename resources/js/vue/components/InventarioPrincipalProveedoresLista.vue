<template>
    <div class="envoltorio">
        <MigasDePan v-if="ruta('proyectos')"/>
        <div class="operaciones">
            <Buscador consejo="Buscar..."/>
            <Opciones :opciones="opciones" @accion="exportar"/>
        </div>
        <Lista componente="proveedores" ordenPor="nombre" :titulo="titulo" @en-bodega="estaEnBodega" @en-proyecto="estaEnProyecto" @api-lista="obtenerApiUrl"/>
        <Archivo :url="archivoUrl" nombre="proveedores.xlsx" v-if="archivoUrl" @descargado="descargaFinalizada"/>
    </div>
</template>

<script>
    import MigasDePan from './InventarioPrincipalBaseMigasDePan.vue'
    import Buscador from './InventarioPrincipalBaseBuscador.vue'
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    import Lista from './InventarioPrincipalBaseLista.vue'
    import Archivo from './InventarioPrincipalBaseArchivo.vue'
    import ArchivoMixin from '../mixins/archivoMixin.js'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Proveedores',
        components: {
            MigasDePan,
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
                enBodega: 1,
                enProyecto: 1
            }
        },
        computed: {
            titulo() {
                if (this.ruta('proyectos')) {
                    if (this.enProyecto) {
                        return 'Proveedores con unidades en este proyecto'
                    } else {
                        return 'Proveedores sin unidades en este proyecto'
                    }
                } else {
                    if (this.enBodega) {
                        return 'Proveedores con unidades en esta bodega'
                    } else {
                        return 'Proveedores sin unidades en esta bodega'
                    }
                }
            },
            opciones() {
                return [
                    {
                        url: `${this.$route.path}/registrar`,
                        texto: 'Registrar proveedor',
                        icono: 'plus',
                        oculto: this.ruta('proyectos')
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
                                activo: this.enBodega,
                                oculto: this.ruta('proyectos')
                            },
                            {
                                url: `${this.$route.path}?enBodega=0`,
                                texto: 'Sin unidades en esta bodega',
                                activo: !this.enBodega,
                                oculto: this.ruta('proyectos')
                            },
                            {
                                url: `${this.$route.path}?enProyecto=1`,
                                texto: 'Con unidades en este proyecto',
                                activo: this.enProyecto,
                                oculto: !this.ruta('proyectos')
                            },
                            {
                                url: `${this.$route.path}?enProyecto=0`,
                                texto: 'Sin unidades en este proyecto',
                                activo: !this.enProyecto,
                                oculto: !this.ruta('proyectos')
                            }
                        ]
                    }
                ]
            }
        },
        methods: {
            estaEnBodega(respuesta) {
                this.enBodega = respuesta
            },
            estaEnProyecto(respuesta) {
                this.enProyecto = respuesta
            }
        }
    }
</script>