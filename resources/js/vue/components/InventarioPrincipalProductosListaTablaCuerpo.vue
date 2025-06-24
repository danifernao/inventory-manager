<template>
    <tbody>
        <template v-if="registros.length">
            <tr v-for="registro in registros">
                <Celda tipo="imagen" clase="imagen" icono="box" :url="rutaProducto(registro.id)" :imgUrl="registro.imagen_url" imgDescripcion="Logotipo del producto" titulo="Ver detalles del producto"/>
                <template v-if="!$route.params.fabricanteId">
                    <Celda clase="fabricante" :url="rutaProducto(registro.id)" titulo="Ver detalles del producto" :dato="registro.fabricante"/>
                </template>
                <Celda clase="marca" :url="rutaProducto(registro.id)" titulo="Ver detalles del producto" :dato="registro.marca"/>
                <Celda clase="modelo" :url="rutaProducto(registro.id)" titulo="Ver detalles del producto" :dato="registro.modelo"/>
                <Celda :clase="nroUnidadesClase(registro)" :url="rutaProducto(registro.id)" titulo="Ver detalles del producto" :dato="registro.unidades_count"/>
                <Celda clase="acciones" :opciones="opciones(registro)" @accion="ejecutar"/>
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
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'TablaCuerpoProductos',
        components: {
            Celda: CuerpoCelda,
        },
        mixins: [
            ConfigMixin,
            RutaMixin,
            SwalMixin
        ],
        props: {
            registros: Object
        },
        computed: {
            dirPadre() {
                return this.$route.path.split('/').slice(0, 3).join('/')
            }
        },
        methods: {
            rutaProducto(id) {
                return `${this.$route.path}/productos/${id}`
            },
            
            nroUnidadesClase(producto) {
                let clase = 'nro-unidades'
                if (typeof(producto.unidades_minimas) !== 'undefined' && producto.unidades_count < producto.unidades_minimas) {
                    clase += ' rojo'
                }
                return clase
            },
            
            opciones(producto) {
                return [
                    {
                        texto: 'Acciones',
                        menu: [
                            {
                                url: this.rutaProducto(producto.id),
                                texto: 'Ver producto'
                            },
                            {
                                url: `${this.dirPadre}/fabricantes/${producto.fabricante_id}`,
                                texto: 'Ver fabricante',
                                oculto: this.$route.params.fabricanteId
                            },
                            {
                                url: this.rutaProducto(producto.id) + '/editar',
                                texto: 'Editar producto'
                            },
                            {
                                url: this.rutaProducto(producto.id) + '/unidades/registrar',
                                texto: 'Añadir unidades',
                                oculto: !this.ruta('bodegas')
                            },
                            {
                                texto: 'Eliminar producto',
                                params: {
                                    accion: 'eliminar',
                                    id: producto.id
                                }
                            }
                        ]
                    }
                ]
            },
            
            ejecutar(params) {
                switch (params.accion) {
                    case 'eliminar':
                        this.eliminar(params.id)
                        break
                }
            },
            
            eliminar(id) {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar este producto? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: `/api/productos/${id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then (respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Producto eliminado.'
                            })
                            this.$emit('recargarLista')
                        })
                        .catch (error => {
                            const estado = error.response ? error.response.status : ''
                            const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                            
                            this.notificacion.fire({
                                icon: 'error',
                                title: `Error ${estado}`,
                                text: mensaje
                            })
                        })
                    }
                })
            }
        }
    }
</script>

<style scoped>
    :deep(.nro-unidades.rojo) a {
        color: var(--color-rojo-4);
    }
</style>