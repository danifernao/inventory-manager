<template>
    <template v-if="cargado">
        <Error v-if="error"/>
        <router-view v-else></router-view>
    </template>
    <IconoCargando v-else/>
</template>

<script>
    import IconoCargando from './components/BaseIconoCargando.vue'
    import Error from './components/BaseMensajeError.vue'
    import ConfigMixin from './mixins/configMixin.js'
    import SwalMixin from './mixins/swalMixin.js'
    
    export default {
        name: 'App',
        components: {
            IconoCargando,
            Error
        },
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        data () {
            return {
                cargado: false,
                error: false,
                acercaDe: {
                    version: 'Version 1.0.1',
                    autor: 'Desarrollado por ADSI.G3',
                    licencia: 'Bajo la licencia GNU AGPLv3'
                }
            }
        },
        watch: {
            $route() {
                this.$nextTick(() => {
                    this.actualizarTitulo()
                })
            }
        },
        created() {
            const promesas = []
            const config = localStorage.getItem('config')
            
            if (config) {
                this.$store.commit('guardarConfiguracion', JSON.parse(config))
            }
            
            if (this.config('claveApi')) {
                promesas.push(axios({
                    method: 'get',
                    url: '/api/usuarios/autenticado',
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.$store.commit('guardarUsuario', respuesta.data)
                    this.actualizarTitulo()
                })
                .catch(() => {
                    this.notificacion.fire({
                        icon: 'warning',
                        title: 'La sesión ha expirado.'
                    })
                    this.eliminarConfig('claveApi')
                }))
            }
            
            promesas.push(axios.get('/api/aplicacion')
                .then(respuesta => {
                    this.$store.commit('guardarAppDatos', respuesta.data)
                })
                .catch(() => {
                    this.error = true
                }))
                
            Promise.all(promesas).then(() => {
                this.$store.commit('guardarAcercaDe', this.acercaDe)
                this.cargado = true
            })
        },
        methods: {
            actualizarTitulo() {
                setTimeout(() => {
                    const html_titulo = document.body.querySelector('h2')
                    const bd_titulo = this.$store.state.aplicacion ? this.$store.state.aplicacion.titulo : ''
                    
                    if (html_titulo) {
                        document.title = html_titulo.textContent + (bd_titulo ? ` | ${bd_titulo}` : '')
                    } else {
                        if (bd_titulo) {
                            document.title = this.$store.state.aplicacion.titulo
                        }
                        this.actualizarTitulo()
                    }
                }, 500)
            }
        }
    }
</script>

<style scoped>
    p {
        margin: 1rem;
        text-align: center;
    }
</style>