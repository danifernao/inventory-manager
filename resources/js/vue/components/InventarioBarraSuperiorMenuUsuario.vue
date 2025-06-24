<template>
    <ul id="opciones" v-if="visible">
        <li>
            <router-link :to="'/configuracion/usuarios/' + $store.state.usuario.id + '/editar'" title="Editar perfil">
                <img :src="$store.state.usuario.foto_perfil_url" alt="Fotografía de perfil" v-if="$store.state.usuario.foto_perfil_url"/>
                <font-awesome-icon icon="user" v-else/>
                <span>{{ $store.state.usuario.primer_nombre }} {{ $store.state.usuario.primer_apellido }}</span>
                <span>{{ $store.state.usuario.correo }}</span>
                <span v-if="$store.state.usuario.es_administrador">Administrador</span>
                <span v-else>Operador</span>
            </router-link>
        </li>
        <li>
            <router-link :to="'/configuracion/usuarios/' + $store.state.usuario.id + '/editar'" :class="{ activo: ruta('configuracion') }">
                <font-awesome-icon icon="gear"/>
                <span>Configuración</span>
            </router-link>
        </li>
        <li>
            <router-link to="/ayuda/preguntas-frecuentes" :class="{ activo: ruta('ayuda') }">
                <font-awesome-icon icon="circle-info"/>
                <span>Preguntas frecuentes</span>
            </router-link>
        </li>
        <li>
            <a href="javascript:void(0);" @click.prevent="cerrarSesion">
                <font-awesome-icon icon="right-from-bracket"/>
                <span>Cerrar sesión</span>
            </a>
        </li>
    </ul>
</template>

<script>
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'OpcionesUsuario',
        mixins: [
            ConfigMixin,
            RutaMixin,
            SwalMixin
        ],
        props: {
            visible: Boolean
        },
        methods: {
            cerrarSesion() {
                this.confirmacion.fire({
                    text: '¿Deseas cerrar cerrar la sesión?'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: '/api/usuarios/sesion',
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.$store.commit('eliminarUsuario')
                            this.eliminarConfig('claveApi')
                            this.$router.push('/iniciar-sesion')
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
    #opciones {
        position: absolute;
        top: 3rem;
        right: 0;
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
        width: 16rem;
        background-color: var(--color-blanco-1);
        color: var(--color-negro-1);
        list-style: none;
        z-index: 2000;
        white-space: nowrap;
    }
    
    #opciones li,
    #opciones li:first-of-type a {
        display: flex;
        flex-direction: column;
    }
    
    #opciones li:first-of-type {
        padding: 0.8rem 4rem;
        background-color: var(--color-naranja-3);
        border-top-right-radius: 0.3rem;
        border-top-left-radius: 0.3rem;
        text-align: center;
    }
    
    #opciones li:first-of-type a {
        align-items: center;
        color: var(--color-blanco-1);
    }
    
    #opciones li:first-of-type svg {
        padding: 1rem;
        width: 3rem;
        height: 3rem;
        color: var(--color-naranja-3);
    }
    
    #opciones li:first-of-type svg,
    #opciones li:first-of-type img {
        margin-bottom: 0.3rem;
        background-color: var(--color-blanco-1);
        border-radius: 50%;
    }
    
    #opciones li:first-of-type img {
        width: 4rem;
        height: 4rem;
        color: var(--color-blanco-1);
        object-fit: cover;
    }
    
    #opciones li:first-of-type span:first-of-type {
        font-size: 0.9rem;
    }
    
    #opciones li:first-of-type span:not(:first-of-type) {
        margin-top: 0.3rem;
        font-size: 0.8rem;
    }
    
    #opciones li:not(:first-of-type) {
        border: 0.06rem solid var(--color-gris-1);
        border-top: 0;
    }
    
    #opciones li:not(:first-of-type) a {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        padding: 0.8rem 4rem;
        font-size: 0.9rem;
        color: var(--color-negro-1);
        transition: background-color 100ms ease-out;
    }
    
    #opciones li:not(:first-of-type) a:hover,
    #opciones li:not(:first-of-type) a.activo {
        background-color: var(--color-blanco-3);
    }
    
    #opciones li:last-of-type {
        border-bottom-right-radius: 0.3rem;
        border-bottom-left-radius: 0.3rem;
    }
    
    @media only screen and (max-width: 760px) {
        #opciones li:not(:first-of-type) a:not(.activo):hover {
            background-color: transparent;
        }
    }
</style>