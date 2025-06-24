<template>
    <h2>Iniciar sesión</h2>
    <form ref="formulario" @submit.prevent="autenticar">
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta"/>
        <div class="seccion">
            <input type="email" placeholder="Correo electrónico" required v-model="correo"/>
            <font-awesome-icon icon="envelope"/>
        </div>
        <div id="contrasena" class="seccion">
            <input type="password" placeholder="Contraseña" required ref="contrasena" v-model="contrasena"/>
            <font-awesome-icon icon="lock"/>
            <font-awesome-icon icon="unlock"/>
        </div>
        <div class="seccion mostrar-contrasena">
            <input type="checkbox" id="mostrar-contrasena" v-model="visibilidadContrasena" @change="mostrarContrasena">
            <label for="mostrar-contrasena" class="font-9">Mostrar contraseña</label>
        </div>
        <div class="seccion recaptcha" v-if="recaptchaSiteKey">
            <vue-recaptcha :sitekey="recaptchaSiteKey" ref="recaptcha" @verify="verificarReCaptcha"></vue-recaptcha>
        </div>
        <div class="seccion boton">
            <button type="submit" class="boton-verde">Iniciar sesión</button>
        </div>
        <div class="seccion pie">
            <router-link to="/restablecer-contrasena">¿Olvidaste tu contraseña?</router-link>
        </div>
    </form>
</template>

<script>
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import { VueRecaptcha } from 'vue-recaptcha';
    
    export default {
        name: 'InicioSesion',
        components: {
            EstadoSolicitud,
            VueRecaptcha
        },
        mixins: [
            ConfigMixin
        ],
        data() {
            return {
                estado: null,
                respuesta: null,
                correo: '',
                contrasena: '',
                visibilidadContrasena: false,
                recaptcha: null,
                recaptchaSiteKey: process.env.MIX_RECAPTCHA_SITE_KEY
            }
        },
        methods: {
            mostrarContrasena() {
                this.$refs.contrasena.setAttribute('type', this.visibilidadContrasena ? 'text' : 'password')
                this.$refs.contrasena.parentNode.classList.toggle('visible', this.visibilidadContrasena)
            },
            
            verificarReCaptcha(respuesta) {
                this.recaptcha = respuesta
            },
            
            autenticar () {
                this.$refs.formulario.classList.add('inhabilitado')
                
                this.estado = 'cargando'
                this.respuesta = { message: 'Validando datos...' }
                
                axios.post('/api/usuarios/autenticar', {
                    correo: this.correo,
                    contrasena: this.contrasena,
                    recaptcha: this.recaptcha
                })
                .then(respuesta => {
                    this.$store.commit('guardarUsuario', respuesta.data.user)
                    this.guardarConfig('claveApi', respuesta.data.token)
                    this.$router.push('/bodegas')
                })
                .catch(error => {
                    this.estado = 'error'
                    this.respuesta = error.response ? error.response.data : { message: 'Error no identificado.' }
                    this.contrasena = ''
                    if (this.$refs.recaptcha) {
                      this.$refs.recaptcha.reset()
                    }
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
            }
        }
    }
</script>

<style scoped>
    #contrasena.visible svg:first-of-type,
    #contrasena:not(.visible) svg:last-of-type {
        visibility: hidden;
    }
    
    .recaptcha {
        display: flex;
        justify-content: center;
    }
</style>