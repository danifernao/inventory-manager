<template>
    <div class="lista">
        <table v-if="cargado">
            <thead>
                <tr>
                    <th>Último acceso</th>
                    <th>IP</th>
                    <th>Navegador</th>
                    <th>Plataforma</th>
                    <th>Dispositivo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="sesiones.length === 0">
                    <td colspan="6">No hay sesiones guardadas.</td>
                </tr>
                <tr v-for="sesion in sesiones" v-else>
                    <td>{{ sesion.last_used_at }}</td>
                    <td>{{ sesion.ip }}</td>
                    <td>{{ sesion.navegador }}</td>
                    <td>{{ sesion.plataforma }}</td>
                    <td>{{ sesion.dispositivo }}</td>
                    <td v-if="sesion.id === sesionActivaId">
                        <span class="color-verde">Sesion actual</span>
                    </td>
                    <td v-else>
                        <button class="boton-rojo" @click.prevent="cerrar(sesion.id)">Cerrar sesion</button>
                    </td>
                </tr>
                <tr v-if="sesiones.length > 1">
                    <td colspan="5"></td>
                    <td>
                        <button class="boton-rojo" @click.prevent="cerrarTodo">Cerrar todas</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <IconoCargando v-else/>
    </div>
</template>

<script>
    import IconoCargando from './BaseIconoCargando.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Sesiones',
        components: {
            IconoCargando
        },
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        props: {
            usuario: Object
        },
        data() {
            return {
                sesiones: [],
                cargado: false
            }
        },
        computed: {
            sesionActivaId() {
                const clave = this.config('claveApi')
                return parseInt(clave.replace(/\|.*$/, ''))
            }
        },
        created() {
            axios({
                method: 'get',
                url: `/api/usuarios/${this.usuario.id}/sesiones`,
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                }
            })
            .then(respuesta => {
                this.sesiones = respuesta.data
                this.cargado = true
            })
            .catch(error => {
                this.mostrarError(error)
            })
        },
        methods: {
            cerrar(id) {
                this.confirmacion.fire({
                    text: '¿Deseas cerrar esta sesion?'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: `/api/usuarios/${this.usuario.id}/sesiones/${id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            _.remove(this.sesiones, sesion => sesion.id === id)
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Sesión cerrada'
                            })
                        })
                        .catch(error => {
                            this.mostrarError(error)
                        })
                    }
                })
            },
            
            cerrarTodo() {
                this.confirmacion.fire({
                    text: '¿Deseas cerrar todas las sesiones?'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: `/api/usuarios/${this.usuario.id}/sesiones`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.sesiones = _.remove(this.sesiones, sesion => sesion.id === this.sesionActivaId)
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Sesiones cerradas'
                            })
                        })
                        .catch(error => {
                            this.mostrarError(error)
                        })
                    }
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
    .lista {
        text-align: center;
        overflow-x: auto;
    }
    
    tbody td {
        padding: 0.2rem;
        font-size: 0.9rem;
    }
    
    tbody button {
        padding: 0.2rem 0.3rem;
        font-size: 0.8rem;
    }
</style>