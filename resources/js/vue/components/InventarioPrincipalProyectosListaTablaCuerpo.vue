<template>
    <tbody>
        <template v-if="registros.length">
            <tr v-for="registro in registros">
                <Celda clase="nombre" :url="$route.path + '/' + registro.id" titulo="Ver detalles del proyecto" :dato="registro.nombre"/>
                <Celda clase="codigo" :url="$route.path + '/' + registro.id" titulo="Ver detalles del proyecto" :dato="registro.codigo"/>
                <Celda clase="ubicacion" :url="$route.path + '/' + registro.id" titulo="Ver detalles del proyecto" :dato="registro.ubicacion"/>
                <Celda clase="fecha-apertura" :url="$route.path + '/' + registro.id" titulo="Ver detalles del proyecto" :dato="registro.fecha_apertura"/>
                <Celda clase="fecha-clausura" :url="$route.path + '/' + registro.id" titulo="Ver detalles del proyecto" :dato="registro.fecha_clausura"/>
                <Celda clase="nro-unidades" :url="$route.path + '/' + registro.id" titulo="Ver detalles del proyecto" :dato="registro.unidades_count"/>
                <Celda :clase="estadoClase(registro.esta_activo)" :url="$route.path + '/' + registro.id" titulo="Ver detalles del proyecto" :dato="estado(registro.esta_activo)"/>
                <Celda clase="acciones" :opciones="opciones(registro.id)" @accion="eliminar"/>
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
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'TablaCuerpoProyectos',
        components: {
            Celda: CuerpoCelda,
        },
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        props: {
            registros: Object
        },
        methods: {
            estado(activo) {
                return activo ? 'Activo' : 'No activo'
            },
            
            estadoClase(activo) {
                return activo ? 'estado-activo' : 'estado-inactivo'
            },
            
            opciones(id) {
                return [
                    {
                        texto: 'Acciones',
                        menu: [
                            {
                                url: `${this.$route.path}/${id}`,
                                texto: 'Ver proyecto'
                            },
                            {
                                url: `${this.$route.path}/${id}/editar`,
                                texto: 'Editar proyecto'
                            },
                            {
                                texto: 'Eliminar proyecto',
                                params: id
                            }
                        ]
                    }
                ]
            },
            
            eliminar(id) {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar este proyecto? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: `/api/proyectos/${id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Proyecto eliminado.'
                            })
                            this.$emit('recargarLista')
                        })
                        .catch(error => {
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
    :deep(.estado-activo) a {
        color: var(--color-verde-2) !important;
    }
    
    :deep(.estado-inactivo) a {
        color: var(--color-rojo-3) !important;
    }
</style>