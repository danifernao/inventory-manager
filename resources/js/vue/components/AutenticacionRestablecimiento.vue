<template>
    <h2>
        <template v-if="paso === 'restablecer-contrasena'">Restablecer contraseña</template>
        <template v-if="paso === 'mensaje-enviado'">Mensaje enviado</template>
        <template v-if="paso === 'cambiar-contrasena'">Cambiar contraseña</template>
        <template v-if="paso === 'contrasena-cambiada'">Contraseña cambiada</template>
    </h2>
    
    <template v-if="paso === 'mensaje-enviado' || paso === 'contrasena-cambiada'">
        <p v-if="paso === 'mensaje-enviado'">Te hemos enviado un mensaje a tu correo electrónico con los pasos a seguir.</p>
        <p v-else>¡Enhorabuena! Tu contraseña ha sido cambiada. Por favor, inicia sesión con tus nuevas credenciales.</p>
        <p><router-link to="/iniciar-sesion">Iniciar sesión</router-link></p>
    </template>
    
    <form v-if="paso === 'restablecer-contrasena' || paso === 'cambiar-contrasena'" ref="formulario" @submit.prevent="restablecer">
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
        <template v-if="paso === 'restablecer-contrasena'">
            <div class="seccion">
                <input type="email" placeholder="Correo electrónico" required v-model="correo"/>
                <font-awesome-icon icon="envelope"/>
            </div>
            <div class="seccion recaptcha" v-if="recaptchaSiteKey">
                <vue-recaptcha :sitekey="recaptchaSiteKey" ref="recaptcha" @verify="verificarReCaptcha"></vue-recaptcha>
            </div>
        </template>
        <template v-else>
            <div class="seccion">
                <input type="password" placeholder="Nueva contraseña" maxlength="72" required v-model="contrasena"/>
                <font-awesome-icon icon="lock"/>
            </div>
            <div class="seccion">
                <input type="password" placeholder="Confirmar nueva contraseña" required v-model="confirmacion"/>
                <font-awesome-icon icon="lock"/>
            </div>
        </template>
        <div class="seccion boton">
            <button type="submit" class="boton-verde">{{ textoBoton }}</button>
        </div>
        <div class="seccion pie">
            <router-link to="/iniciar-sesion">¿Deseas iniciar sesión?</router-link>
        </div>
    </form>
</template>

<script>
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import { VueRecaptcha } from 'vue-recaptcha';
    
    export default {
        name: 'RestablecimientoContrasena',
        components: {
          EstadoSolicitud,
          VueRecaptcha
        },
        data() {
            return {
                paso: 'restablecer-contrasena',
                estado: null,
                respuesta: null,
                correo: '',
                contrasena: '',
                confirmacion: '',
                recaptcha: null,
                recaptchaSiteKey: process.env.MIX_RECAPTCHA_SITE_KEY
            }
        },
        computed: {
            textoBoton () {
                return (this.paso === 'restablecer-contrasena') ? 'Restablecer' : 'Cambiar contraseña'
            }
        },
        created () {
            this.paso = this.$route.params.token ? 'cambiar-contrasena' : 'restablecer-contrasena'
        },
        methods: {
            verificarReCaptcha(respuesta) {
                this.recaptcha = respuesta
            },
            restablecer () {
                this.$refs.formulario.classList.add('inhabilitado')
                
                this.estado = 'cargando'
                this.respuesta = { message: 'Validando datos...' }
                
                if (this.paso === 'restablecer-contrasena') {
                    axios.post('/api/restablecer', {
                        correo: this.correo,
                        recaptcha: this.recaptcha
                    })
                    .then(respuesta => {
                        this.paso = 'mensaje-enviado'
                    })
                    .catch(error => {
                        this.estado = 'error'
                        this.respuesta = error.response ? error.response.data : { message: 'Error no identificado.' }
                        if (this.$refs.recaptcha) {
                          this.$refs.recaptcha.reset()
                        }
                        this.$refs.formulario.classList.remove('inhabilitado')
                    })
                } else {
                    axios.put(`/api/restablecer/${this.$route.params.token}`, {
                        email: this.$route.query.email,
                        contrasena: this.contrasena,
                        contrasena_confirmation: this.confirmacion
                    })
                    .then(respuesta => {
                        this.paso = 'contrasena-cambiada'
                    })
                    .catch(error => {
                        this.estado = 'error'
                        this.respuesta = error.response ? error.response.data : { message: 'Error no identificado.' }
                        this.$refs.formulario.classList.remove('inhabilitado')
                    })
                }
            }
        }
    }
</script>

<style scoped>
    p {
        text-align: center;
    }
    
    .recaptcha {
        display: flex;
        justify-content: center;
    }
</style>