<template>
    <ListaCabecera :titulo="titulo" :totalPorPagina="porPagina" :opciones="opciones" v-bind="$attrs"/>
    <template v-if="cargado">
        <Error :error="error" v-if="error"/>
        <div class="listado">
            <table>
                <component :is="cabecera" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
                <component :is="cuerpo" :registros="registros" @recargar-lista="recargar"/>
            </table>
        </div>
        <Paginacion :paginacion="paginacion"/>
    </template>
    <div class="cargando" v-else>
        <IconoCargando/>
    </div>
</template>

<script>
    import TablaCabeceraProductos from './InventarioPrincipalProductosListaTablaCabecera.vue'
    import TablaCabeceraUnidades from './InventarioPrincipalUnidadesListaTablaCabecera.vue'
    import TablaCabeceraProveedores from './InventarioPrincipalProveedoresListaTablaCabecera.vue'
    import TablaCabeceraFabricantes from './InventarioPrincipalFabricantesListaTablaCabecera.vue'
    import TablaCabeceraProyectos from './InventarioPrincipalProyectosListaTablaCabecera.vue'
    import TablaCabeceraMovimientos from './InventarioPrincipalMovimientosListaTablaCabecera.vue'
    import TablaCabeceraUsuarios from './InventarioPrincipalUsuariosListaTablaCabecera.vue'
    import TablaCuerpoProductos from './InventarioPrincipalProductosListaTablaCuerpo.vue'
    import TablaCuerpoUnidades from './InventarioPrincipalUnidadesListaTablaCuerpo.vue'
    import TablaCuerpoProveedores from './InventarioPrincipalProveedoresListaTablaCuerpo.vue'
    import TablaCuerpoFabricantes from './InventarioPrincipalFabricantesListaTablaCuerpo.vue'
    import TablaCuerpoProyectos from './InventarioPrincipalProyectosListaTablaCuerpo.vue'
    import TablaCuerpoMovimientos from './InventarioPrincipalMovimientosListaTablaCuerpo.vue'
    import TablaCuerpoUsuarios from './InventarioPrincipalUsuariosListaTablaCuerpo.vue'
    import ListaCabecera from './InventarioPrincipalBaseListaCabecera.vue'
    import Paginacion from './InventarioPrincipalBasePaginacion.vue'
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Lista',
        inheritAttrs: false,
        components: {
            ListaCabecera,
            TablaCabeceraProductos,
            TablaCabeceraUnidades,
            TablaCabeceraProveedores,
            TablaCabeceraFabricantes,
            TablaCabeceraProyectos,
            TablaCabeceraMovimientos,
            TablaCabeceraUsuarios,
            TablaCuerpoProductos,
            TablaCuerpoUnidades,
            TablaCuerpoProveedores,
            TablaCuerpoFabricantes,
            TablaCuerpoProyectos,
            TablaCuerpoMovimientos,
            TablaCuerpoUsuarios,
            Paginacion,
            IconoCargando,
            Error
        },
        props: {
            titulo: String,
            componente: String,
            ordenPor: String,
            opciones: Object,
            recargarLista: Boolean
        },
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        data () {
            return {
                cabecera: `TablaCabecera${_.capitalize(this.componente)}`,
                cuerpo: `TablaCuerpo${_.capitalize(this.componente)}`,
                enBodega: 1,
                enProyecto: 1,
                ordenarPor: this.ordenPor,
                ordenarEn: 'asc',
                numPagina: 1,
                porPagina: 10,
                dadoDeBaja: 0,
                registros: {},
                paginacion: [],
                cargado: false,
                error: null
            }
        },
        watch: {
            enBodega() {
                this.$emit('enBodega', this.enBodega)
            },
            
            enProyecto() {
                this.$emit('enProyecto', this.enProyecto)
            },
            
            dadoDeBaja() {
                this.$emit('dadoDeBaja', this.dadoDeBaja)
            },
            
            recargarLista(recargar) {
                if (recargar) {
                    this.listar()
                    this.$emit('listaRecargada')
                }
            },
            
            '$route'(rutaNueva, rutaVieja) {
                if (rutaNueva.path === rutaVieja.path && rutaNueva.query !== rutaVieja.query) {
                    this.listar()
                }
            }
        },
        created() {
            this.listar()
        },
        methods: {
            listar() {
                const apiUrl = `/api/${this.componente.toLowerCase()}`
                const apiParams = new URLSearchParams()
                const config = this.config(`${this.$route.name}Ops`)
                
                this.buscar = this.$route.query.buscar || null
                this.numPagina = this.$route.query.pagina || 1
                
                if (this.$route.query.ordenarPor) {
                    this.ordenarPor = this.$route.query.ordenarPor
                } else {
                    if (config && config.ordenarPor) {
                        this.ordenarPor = config.ordenarPor
                    }
                }
                
                if (this.$route.query.ordenarEn) {
                    this.ordenarEn = this.$route.query.ordenarEn
                } else {
                    if (config && config.ordenarEn) {
                        this.ordenarEn = config.ordenarEn
                    }
                }
                
                if (this.$route.query.porPagina) {
                    this.porPagina = this.$route.query.porPagina
                } else {
                    if (config && config.porPagina) {
                        this.porPagina = config.porPagina
                    }
                }
                
                if (this.$route.query.enBodega) {
                    this.enBodega = parseInt(this.$route.query.enBodega)
                } else {
                    if (config && typeof config.enBodega !== 'undefined') {
                        this.enBodega = parseInt(config.enBodega)
                    }
                }
                
                if (this.$route.query.enProyecto) {
                    this.enProyecto = parseInt(this.$route.query.enProyecto)
                } else {
                    if (config && typeof config.enProyecto !== 'undefined') {
                        this.enProyecto = parseInt(config.enProyecto)
                    }
                }
                
                if (this.$route.query.dadoDeBaja) {
                    this.dadoDeBaja = parseInt(this.$route.query.dadoDeBaja)
                } else {
                    if (config && typeof config.dadoDeBaja !== 'undefined') {
                        this.dadoDeBaja = parseInt(config.dadoDeBaja)
                    }
                }
                
                if (this.$route.params.bodegaId) {
                    apiParams.append('bodega_id', this.$route.params.bodegaId)
                }
                
                if (this.$route.params.productoId) {
                    apiParams.append('producto_id', this.$route.params.productoId)
                }
                
                if (this.$route.params.unidadId) {
                    apiParams.append('unidad_id', this.$route.params.unidadId)
                }
                
                if (this.$route.params.proveedorId) {
                    apiParams.append('proveedor_id', this.$route.params.proveedorId)
                }
                
                if (this.$route.params.fabricanteId) {
                    apiParams.append('fabricante_id', this.$route.params.fabricanteId)
                }
                
                if (this.$route.params.proyectoId) {
                    apiParams.append('proyecto_id', this.$route.params.proyectoId)
                }
                
                apiParams.append('ordenar_por', this.ordenarPor)
                apiParams.append('ordenar_en', this.ordenarEn)
                apiParams.append('por_pagina', this.porPagina)
                apiParams.append('page', this.numPagina)
                apiParams.append('en_bodega', this.enBodega)
                apiParams.append('en_proyecto', this.enProyecto)
                apiParams.append('dado_de_baja', this.dadoDeBaja)
                
                if (this.buscar) {
                    apiParams.append('buscar', this.buscar)
                }
                
                axios({
                    method: 'get',
                    url: apiUrl + '?' + apiParams.toString(),
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    const config = {}
                    
                    this.registros = respuesta.data.data
                    this.paginacion = respuesta.data.links
                    
                    config['enBodega'] = this.enBodega
                    config['enProyecto'] = this.enProyecto
                    config['dadoDeBaja'] = this.dadoDeBaja
                    config['ordenarPor'] = this.ordenarPor
                    config['ordenarEn'] = this.ordenarEn
                    config['porPagina'] = this.porPagina
                    
                    this.guardarConfig(`${this.$route.name}Ops`, config)
                    
                    document.body.scrollIntoView()
                    
                    const elems = document.querySelectorAll('#principal .listado input[type="checkbox"]')
                    
                    elems.forEach(elem => {
                        elem.checked = false
                    })
                    
                    this.$emit('apiLista', `${apiUrl}?${apiParams.toString()}`)
                })
                .catch(error => {
                    if (error.response && error.response.status === 404 && !this.$route.params.proyectoId) {
                        this.$store.commit('actualizarBodega')
                    }
                    this.notificacion.fire({
                        icon: 'error',
                        title: `Error ${error.response && error.response.status}`,
                        text: error.response ? error.response.data.message : 'Error no identificado.'
                    })
                })
                .finally(() => {
                    this.cargado = true
                })
            },
            
            recargar() {
                this.listar()
            }
        }
    }
</script>

<style scoped>
    .cargando {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
    }
    
    .listado {
        margin: 1rem 0;
        overflow-x: auto;
    }
</style>