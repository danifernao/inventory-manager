<template>
    <tbody>
        <template v-if="registros.length">
            <tr v-for="registro in registros">
                <Celda tipo="imagen" clase="imagen" icono="industry" :url="$route.path + '/' + registro.id"  :imgUrl="registro.logotipo_url" imgDescripcion="Logotipo del fabricante" titulo="Ver detalles del fabricante"/>
                <Celda clase="nombre" :url="$route.path + '/' + registro.id" titulo="Ver detalles del fabricante" :dato="registro.nombre"/>
                <Celda clase="nit" :url="$route.path + '/' + registro.id" titulo="Ver detalles del fabricante" :dato="registro.nit"/>
                <Celda clase="ubicacion" :url="$route.path + '/' + registro.id" titulo="Ver detalles del fabricante" :dato="registro.ubicacion"/>
                <Celda clase="nro-unidades" :url="$route.path + '/' + registro.id" titulo="Ver detalles del fabricante" :dato="registro.unidades_count"/>
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
        name: 'TablaCuerpoFabricantes',
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
            opciones(id) {
                return [
                    {
                        texto: 'Acciones',
                        menu: [
                            {
                                url: `${this.$route.path}/${id}`,
                                texto: 'Ver fabricante'
                            },
                            {
                                url: `${this.$route.path}/${id}/editar`,
                                texto: 'Editar fabricante'
                            },
                            {
                                texto: 'Eliminar fabricante',
                                params: id
                            }
                        ]
                    }
                ]
            },
            
            eliminar(id) {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar este fabricante? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: `/api/fabricantes/${id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Fabricante eliminado.'
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