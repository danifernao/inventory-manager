<template>
    <div id="barra">
        <header :class="{ visible: lateralVisible || principalExpandido, expandido: principalExpandido }">
            <router-link to="/bodegas" title="Página principal" v-if="imagenCabeceraUrl">
                <img :src="imagenCabeceraUrl" alt="Cabecera de la aplicación"/>
            </router-link>
            <h1 v-else>
                <router-link to="/bodegas" title="Página principal">
                    {{ $store.state.aplicacion.titulo }}
                </router-link>
            </h1>
        </header>
        <nav>
            <ul>
                <li>
                    <a href="javascript:void(0);" :class="{ activo: lateralVisible }" title="Menú lateral" v-if="!principalExpandido" @click.prevent.stop="alternarLateral">
                        <font-awesome-icon icon="bars"/>
                    </a>
                </li>
                <li>
                    <router-link to="/bodegas" :class="{ activo: ruta('bodegas') || ruta('fabricantes') || ruta('proveedores') }" title="Inventario">
                        <font-awesome-icon icon="warehouse"/>
                        <span>Inventario</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/proyectos" :class="{ activo: ruta('proyectos') }" title="Proyectos">
                        <font-awesome-icon icon="suitcase"/>
                        <span>Proyectos</span>
                    </router-link>
                </li>
                <li id="menu-notificaciones">
                    <a href="javascript:void(0);" title="Notificaciones" :class="{ activo: notificacionesVisible }" @click.prevent.stop="alternarNotificaciones">
                        <font-awesome-icon icon="bell"/>
                        <span class="nuevo" v-if="nuevasNotificaciones">!</span>
                    </a>
                    <Notificaciones v-if="notificacionesVisible"/>
                </li>
                <li id="menu-usuario">
                    <a href="javascript:void(0);" :class="{ activo: menuUsuarioVisible }" title="Mi cuenta" @click.prevent.stop="alternarMenuUsuario">
                        <img :src="$store.state.usuario.foto_perfil_url" alt="Fotografía de perfil" v-if="$store.state.usuario.foto_perfil_url"/>
                        <font-awesome-icon icon="user" v-else/>
                        <span>{{ $store.state.usuario.primer_nombre }}</span>
                    </a>
                    <OpcionesUsuario :visible="menuUsuarioVisible"/>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    import Notificaciones from './InventarioBarraSuperiorNotificaciones.vue'
    import OpcionesUsuario from './InventarioBarraSuperiorMenuUsuario.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    import MovilMixin from '../mixins/movilMixin.js'
    
    export default {
        name: 'BarraSuperior',
        components: {
            Notificaciones,
            OpcionesUsuario
        },
        mixins: [
            ConfigMixin,
            RutaMixin,
            SwalMixin,
            MovilMixin
        ],
        props: {
            lateralVisible: Boolean,
            principalExpandido: Boolean
        },
        data() {
            return {
                temporizador: null,
                notificacionesVisible: false,
                nuevasNotificaciones: false,
                menuUsuarioVisible: false
            }
        },
        computed: {
            imagenCabeceraUrl() {
                return this.$store.state.aplicacion.img_inventario_url
            }
        },
        created() {
            this.chequearNotificaciones()
            this.temporizador = setInterval(() => {
                this.chequearNotificaciones()
            }, 60000)
        },
        mounted () {
            document.body.addEventListener('click', () => {
                this.notificacionesVisible = false
                this.menuUsuarioVisible = false
            })
        },
        beforeUnmount() {
            clearInterval(this.temporizador)
        },
        methods: {
            alternarLateral(){
                this.$emit('alternarLateral')
                this.notificacionesVisible = false
                this.menuUsuarioVisible = false
            },
            
            alternarNotificaciones() {
                this.notificacionesVisible = this.notificacionesVisible ? false : true
                
                if (this.nuevasNotificaciones) {
                    this.nuevasNotificaciones = false
                }
                
                if (this.esVersionMovil) {
                    this.$emit('ocultarLateral')
                }
            },
            
            alternarMenuUsuario() {
                this.notificacionesVisible = false
                this.menuUsuarioVisible = this.menuUsuarioVisible ? false : true
                if (this.esVersionMovil) {
                    this.$emit('ocultarLateral')
                }
            },
            
            chequearNotificaciones() {
                axios({
                    method: 'get',
                    url: '/api/notificaciones/chequear',
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.nuevasNotificaciones = respuesta.data.total > 0 ? true : false
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
        }
    }
</script>

<style scoped>
    #barra {
        display: flex;
        align-items: stretch;
        background-color: var(--color-naranja-3);
        color: var(--color-blanco-1);
    }
    
    header {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 15rem;
        box-sizing: border-box;
        text-align: center;
        transition: 300ms width ease-out;
    }
    
    header:not(.expandido) {
        background-color: var(--color-naranja-4);
    }
    
    header:not(.visible) {
        width: 0;
    }
    
    header.expandido {
        padding-left: 1rem;
    }
    
    header,
    header h1,
    header a {
        white-space: nowrap;
        overflow: hidden;
    }
    
    header h1 {
        margin: 0;
        padding: 0 1rem;
        font-size: 1.2rem;
        text-overflow: ellipsis;
    }
    
    header a {
        flex: 1;
        color: var(--color-blanco-1);
    }
    
    header img {
        width: auto;
        height: 2.4rem;
    }
    
    nav {
        display: flex;
        flex: 1;
        padding: 0.4rem;
    }
    
    nav > ul {
        display: flex;
        flex: 1;
        align-items: stretch;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    nav > ul li {
        display: flex;
        align-items: stretch;
        padding: 0 0.15rem;
    }
    
    nav > ul li:first-of-type {
        flex: 1;
        padding-left: 0;
    }
    
    nav > ul li:last-of-type {
        padding-right: 0;
    }
    
    nav > ul li:not(:first-of-type):not(:last-of-type) {
        border-right: 0.1rem solid var(--color-naranja-4);
    }
    
    nav > ul li a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.3rem 1rem;
        transition: background-color 100ms ease-out;
    }
    
    nav > ul li a:hover,
    nav > ul li a.activo {
        background-color: var(--color-naranja-4);
        border-radius: 0.3rem;
    }
    
    nav > ul li a span {
        font-size: 0.9rem;
        color: var(--color-blanco-1);
    }
    
    nav > ul li a svg {
        color: var(--color-blanco-1);
    }
    
    #menu-notificaciones a {
        position: relative;
    }
    
    #menu-notificaciones .nuevo {
        position: absolute;
        top: 0.1rem;
        right: 0.3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 0.8rem;
        height: 0.8rem;
        background-color: var(--color-blanco-1);
        border-radius: 50%;
        color: var(--color-naranja-3);
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    #menu-notificaciones,
    #menu-usuario {
        position: relative;
    }
    
    #menu-usuario > a > img {
        width: 1.5rem;
        height: 1.5rem;
        color: var(--color-blanco-1);
        object-fit: cover;
    }
    
    #menu-usuario > a > img,
    #menu-usuario > a > .fa-user {
        background-color: var(--color-blanco-1);
        border-radius: 50%;
    }
    
    #menu-usuario > a .fa-user {
        padding: 0.3rem;
        width: 0.9rem;
        height: 0.9rem;
        color: var(--color-naranja-3);
    }
    
    @media only screen and (max-width: 760px) {
        #barra {
            flex-direction: column;
        }
        
        header {
            width: 100% !important;
            height: 2.9rem;
        }
        
        nav > ul li a:not(.activo):hover {
            background-color: transparent;
        }
        
        nav > ul > li:not(#menu-usuario) a span {
            display: none;
        }
    }
</style>