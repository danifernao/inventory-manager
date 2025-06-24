<template>
    <div id="notificaciones" :class="{ vacio: !notificaciones.length }">
        <Error :error="error" v-if="error"/>
        <template v-else>
            <template v-if="cargado">
                <template v-if="notificaciones.length">
                    <ul>
                        <li v-for="notificacion in notificaciones" :class="{ nuevo: !notificacion.leido }">
                            <router-link :to="`/bodegas/${notificacion.bodega_id}/productos/${notificacion.producto_id}`" @click="notificacion.leido = 1">
                                <span>No hay suficientes unidades del producto <b>{{notificacion.producto_marca}} {{notificacion.producto_modelo}}</b> en <b>{{notificacion.bodega_nombre}}</b>.</span>
                                <span>{{notificacion.created_at}}</span>
                            </router-link>
                            <a href="javascript:void(0);" title="Eliminar notificación" @click.prevent.stop="eliminar(notificacion.id)">
                                <font-awesome-icon icon="xmark"/>
                            </a>
                        </li>
                    </ul>
                    <a href="javascript:void(0);" @click.prevent.stop="eliminarTodo">Eliminar notificaciones</a>
                </template>
                <font-awesome-icon icon="bell-slash" v-else/>
            </template>
            <IconoCargando v-else/>
        </template>
    </div>
</template>

<script>
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Notificaciones',
        components: {
            IconoCargando,
            Error
        },
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        data() {
            return {
                notificaciones: [],
                temporizador: null,
                cargado: false,
                error: false
            }
        },
        created() {
            this.listar()
        },
        beforeUnmount() {
            clearTimeout(this.temporizador)
        },
        methods: {
            listar() {
                axios({
                    method: 'get',
                    url: '/api/notificaciones',
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.notificaciones = respuesta.data
                    this.temporizador = setTimeout(() => {
                        this.marcarLeidas()
                    }, 3000)
                })
                .catch(error => {
                    this.error = error.response || true
                })
                .finally(() => {
                    this.cargado = true
                })
            },
            
            marcarLeidas() {
                axios({
                    method: 'put',
                    url: '/api/notificaciones/marcar',
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .catch(error => {
                    this.mostrarError(error)
                })
            },
            
            eliminar(id) {
                axios({
                    method: 'delete',
                    url: `/api/notificaciones/${id}`,
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.notificaciones = respuesta.data
                })
                .catch(error => {
                    this.mostrarError(error)
                })
            },
            
            eliminarTodo() {
                axios({
                    method: 'delete',
                    url: '/api/notificaciones',
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.notificaciones = []
                })
                .catch(error => {
                    this.mostrarError(error)
                })
            },
            
            mostrarError(error) {
                const estado = error.response ? error.response.status : ''
                const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                
                this.notificacion.fire({
                    icon: 'error',
                    title: `Error ${estado}`,
                    text: mensaje
                })
            }
        }
    }
</script>

<style scoped>
    #notificaciones {
        position: absolute;
        top: 3rem;
        right: 0;
        width: 20rem;
        height: 20rem;
        background-color: var(--color-blanco-1);
        border: 0.06rem solid var(--color-gris-1);
        border-radius: 0.3rem;
        overflow-y: auto;
        z-index: 2000;
    }
    
    #notificaciones.vacio {
        align-items: center;
        justify-content: center;
        color: var(--color-gris-1);
        font-size: 5rem;
    }
    
    #notificaciones > a {
        padding: 0.5rem;
        border-top: 0.06rem solid var(--color-gris-1);
        text-align: center;
        color: var(--color-negro-1);
        font-size: 0.9rem;
    }
    
    #notificaciones, ul {
        display: flex;
        flex-direction: column;
    }
    
    ul {
        flex: 1;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    li {
        display: flex;
        border-bottom: 0.06rem solid var(--color-gris-1);
    }
    
    li > a {
        display: block;
        padding: 0.8rem;
        font-size: 0.9rem;
        color: var(--color-negro-1);
    }
    
    li > a:hover,
    li.nuevo > a,
    #notificaciones > a:hover {
        background-color: var(--color-blanco-3);
    }
    
    li > a:first-of-type {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    
    li > a:first-of-type > span:last-of-type {
        font-size: 0.7rem;
        color: var(--color-gris-4);
    }
    
    li > a:last-of-type {
        display: flex;
        align-items: center;
    }
</style>