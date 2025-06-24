<template>
    <form ref="formulario" @submit.prevent="guardar">
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
        <Aviso tipo="informacion" :mensaje="informacion" v-if="!registro"/>
        <fieldset id="general">
            <legend>Información personal</legend>
            <div>
                <div class="campos-texto">
                    <div class="campos">
                        <div class="seccion">
                            <label for="primer-nombre">Primer nombre:<span class="color-rojo">*</span></label>
                            <input type="text" id="primer-nombre" name="primer_nombre" maxlength="255" autocomplete="off" placeholder="Primer nombre del usuario." required :disabled="inhabilitado" v-model="primerNombre"/>
                        </div>
                        <div class="seccion">
                            <label for="segundo-nombre">Segundo nombre:</label>
                            <input type="text" id="segundo-nombre" name="segundo_nombre" maxlength="255" autocomplete="off" placeholder="Segundo nombre del usuario." :disabled="inhabilitado" v-model="segundoNombre"/>
                        </div>
                    </div>
                    <div class="campos">
                        <div class="seccion">
                            <label for="primer-apellido">Primer apellido:</label>
                            <input type="text" id="primer-apellido" name="primer_apellido" maxlength="255" autocomplete="off" placeholder="Primer apellido del usuario." :disabled="inhabilitado" v-model="primerApellido"/>
                        </div>
                        <div class="seccion">
                            <label for="segundo-apellido">Segundo apellido:</label>
                            <input type="text" id="segundo-apellido" name="segundo_apellido" maxlength="255" autocomplete="off" placeholder="Segundo apellido del usuario." :disabled="inhabilitado" v-model="segundoApellido"/>
                        </div>
                    </div>
                    <div class="campos">
                        <div class="seccion">
                            <label for="nro-identificacion">Identificación:</label>
                            <div class="campos identificacion">
                                <select id="tipo-identificacion" name="tipo_identificacion" :disabled="inhabilitado" v-model="tipoIdentificacion">
                                    <option value="cc" title="Cédula de ciudadanía">CC</option>
                                    <option value="ce" title="Cédula de extranjería">CE</option>
                                    <option value="ti" title="Tarjeta de identidad">TI</option>
                                    <option value="rc" title="Registro civil">RC</option>
                                    <option value="di" title="Documento de identidad">Otro</option>
                                </select>
                                <input type="text" id="nro-identificacion" name="numero_identificacion" maxlength="255" autocomplete="off" placeholder="Número de identificación." :disabled="inhabilitado" v-model="nroIdentificacion"/>
                            </div>
                        </div>
                        <div class="seccion">
                            <label for="genero">Género:</label>
                            <select id="genero" name="genero" :disabled="inhabilitado" v-model="genero">
                                <option value="h">Hombre</option>
                                <option value="m">Mujer</option>
                                <option value="n">No binario</option>
                                <option value="u">No definido</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="foto">
                    <Imagen :fuente="imagenUrl" descripcion="Fotografía del usuario" icono="user" :inhabilitado="inhabilitado" @imagen="guardarImagen"/>
                </div>
            </div>
        </fieldset>
        <fieldset id="acceso">
            <legend>Información de acceso</legend>
            <div class="seccion">
                <label for="correo">Correo electrónico <span class="color-rojo">*</span>:</label>
                <input type="email" id="correo" name="correo" maxlength="255" autocomplete="off" placeholder="correo@ejemplo.com" required :disabled="inhabilitado" v-model="correo"/>
            </div>
            <template v-if="registro">
                <div class="campos contrasena" v-if="registro.id === $store.state.usuario.id">
                    <div class="seccion">
                        <label for="contrasena">Contraseña <span class="color-rojo">*</span>:</label>
                        <input type="password" id="contrasena" name="contrasena" maxlength="72" placeholder="Contraseña nueva." v-model="contrasena"/>
                    </div>
                    <div class="seccion">
                        <label for="contrasena-confirmacion">Confirmación de la contraseña <span class="color-rojo">*</span>:</label>
                        <input type="password" id="contrasena-confirmacion" name="contrasena_confirmation" placeholder="Confirmación de la contraseña nueva." v-model="contraConfirmacion"/>
                    </div>
                </div>
                <div class="seccion restablecer" v-else>
                    <label>Contraseña:</label>
                    <div>
                        <Aviso tipo="importante" :mensaje="importante"/>
                        <button class="boton-aceptar" @click.prevent="restablecer">Restablecer contraseña</button>
                    </div>
                </div>
            </template>

        </fieldset>
        <fieldset id="permisos" v-if="$store.state.usuario.es_administrador">
            <legend>Permisos</legend>
            <div class="campos permisos">
                <div class="seccion">
                    <label for="tipo-usuario">Tipo de usuario:</label>
                    <select id="tipo-usuario" name="es_administrador" :disabled="registro && registro.id === $store.state.usuario.id" v-model="esAdministrador">
                        <option value="0">Operador</option>
                        <option value="1">Administrador</option>
                    </select>
                </div>
                <div class="seccion">
                    <label for="estado-usuario">Estado del usuario:</label>
                    <select id="estado-usuario" name="esta_habilitado" :disabled="registro && registro.id === $store.state.usuario.id" v-model="estaHabilitado" @change="sisMsjVisibilidad = false">
                        <option value="1">Habilitado</option>
                        <option value="0">Inhabilitado</option>
                    </select>
                    <p class="sistema color-naranja" v-if="sisMsjVisibilidad && registro.fecha_inhabilitacion">Acción realizada por el sistema.</p>
                </div>
            </div>
        </fieldset>
        <fieldset id="sesiones" v-if="registro">
            <legend>Sesiones</legend>
            <Sesiones :usuario="registro"/>
        </fieldset>
        <fieldset id="autenticacion">
            <legend>Confirmar cambios</legend>
            <p>Ingresa tu contraseña actual para guardar los cambios.</p>
            <div class="seccion confirmacion">
                <label for="autenticacion">Contraseña <span class="color-rojo">*</span>:</label>
                <input type="password" id="autenticacion" name="contrasena_autenticacion" maxlength="255" placeholder="Contraseña actual." required v-model="contraAutenticacion"/>
            </div>
        </fieldset>
        <div class="seccion nota">
            <p><span class="color-rojo">*</span> Campos obligatorios</p>
        </div>
        <div class="seccion botones">
            <div v-if="registro && !registro.es_respaldo && !registro.es_administrador && $store.state.usuario.es_administrador">
                <button class="boton-rojo atras" @click.prevent="eliminar">Eliminar</button>
            </div>
            <div>
                <button class="boton-cancelar" v-if="!registro || registro.id !== $store.state.usuario.id" @click.prevent="$router.push(rutaAnterior)">Cancelar</button>
                <button type="submit" class="boton-verde">Guardar</button>
            </div>
        </div>
    </form>
</template>

<script>
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import Aviso from './InventarioPrincipalBaseAviso.vue'
    import Imagen from './BaseFormularioImagen.vue'
    import Sesiones from './InventarioPrincipalUsuariosItemFormularioSesiones.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'UsuarioFormulario',
        components: {
            EstadoSolicitud,
            Aviso,
            Imagen,
            Sesiones
        },
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        props: {
            registro: Object
        },
        data() {
            return {
                estado: null,
                respuesta: null,
                primerNombre: '',
                segundoNombre: '',
                primerApellido: '',
                segundoApellido: '',
                tipoIdentificacion: 'cc',
                nroIdentificacion: '',
                genero: 'u',
                correo: '',
                contrasena: '',
                contraConfirmacion: '',
                contraAutenticacion: '',
                esAdministrador: '0',
                estaHabilitado: '1',
                imagenUrl: '',
                imagenArchivo: null,
                sisMsjVisibilidad: false,
                informacion: 'Un mensaje será enviado al correo electrónico del usuario registrado para que pueda establecer su contraseña.',
                importante: 'Solo el propietario de la cuenta puede establecer su contraseña. Si el acceso a la cuenta se ha visto comprometido, puedes inhabilitarla mientras inicia el proceso de restablecimiento.'
            }
        },
        computed: {
            rutaAnterior() {
                return this.$router.options.history.state.back || '/configuracion/usuarios'
            },
            inhabilitado() {
                return !this.$store.state.usuario.es_administrador
            }
        },
        created() {
            if (this.registro) {
                this.primerNombre = this.registro.primer_nombre
                this.segundoNombre = this.registro.segundo_nombre
                this.primerApellido = this.registro.primer_apellido
                this.segundoApellido = this.registro.segundo_apellido
                this.tipoIdentificacion = this.registro.tipo_identificacion || 'cc'
                this.nroIdentificacion = this.registro.numero_identificacion
                this.genero = this.registro.genero || 'u'
                this.imagenUrl = this.registro.foto_perfil_url
                this.correo = this.registro.correo
                this.esAdministrador = this.registro.es_administrador ? '1' : '0'
                this.estaHabilitado = this.registro.esta_habilitado ? '1' : '0'
                this.sisMsjVisibilidad = this.registro.fecha_inhabilitacion || false
            } else {
                if (!this.$store.state.usuario.es_administrador) {
                    this.$router.push('/404')
                }
            }
        },
        methods: {
            guardarImagen(url, archivo) {
                this.imagenUrl = url,
                this.imagenArchivo = archivo
            },
            
            restablecer() {
                this.confirmacion.fire({
                    text: 'Se enviará un correo electrónico al usuario registrado para que pueda restablecer su contraseña, ¿desea continuar?'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'get',
                            url: `/api/usuarios/${this.registro.id}/restablecer`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then (respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Correo enviado.'
                            })
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
            },
            
            guardar() {
                this.$refs.formulario.classList.add('inhabilitado')
                
                this.estado = 'cargando'
                this.respuesta = {
                    message: this.registro ? 'Guardando cambios...' : 'Registrando...'
                }
                
                const datos = new FormData(this.$refs.formulario)
                
                if (this.registro) {
                    datos.append('_method', 'PUT')
                }
                
                if (this.imagenArchivo) {
                    datos.append('foto', this.imagenArchivo)
                } else {
                    if (!this.imagenUrl) {
                        datos.append('foto', '')
                    }
                }
                
                axios({
                    method: 'post',
                    url: '/api/usuarios' + (this.registro ? `/${this.registro.id}` : ''),
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: datos
                })
                .then (respuesta => {
                    if (this.registro) {
                        this.estado = 'exito'
                        this.respuesta = { message: 'Cambios guardados.' }
                        if (respuesta.data.id === this.$store.state.usuario.id) {
                            this.$store.commit('guardarUsuario', respuesta.data)
                        }
                    } else {
                        this.$router.push('/configuracion/usuarios')
                    }
                })
                .catch(error => {
                    this.estado = 'error'
                    this.respuesta = error.response ? error.response.data : { message: 'Error inesperado.' }
                    if (error.response && error.response.data.message === 'Unauthenticated.') {
                        this.$router.go(0)
                    }
                })
                .finally(() => {
                    this.contrasena = ''
                    this.contraConfirmacion = ''
                    this.contraAutenticacion = ''
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
            },
            
            eliminar () {
                this.campoContrasena.fire({
                    confirmButtonText: 'Eliminar',
                    inputLabel: 'Ingresa tu contraseña:',
                    inputAttributes: {
                        maxlength: 255
                    },
                    inputValidator: async (contrasena) => {
                        if (!contrasena) {
                          return 'Ingresa la contraseña pra proceder con la eliminación.'
                        } else {
                            return await axios({
                                method: 'delete',
                                url: `/api/usuarios/${this.registro.id}`,
                                headers: {
                                    Authorization: `Bearer ${this.config('claveApi')}`
                                },
                                data: {
                                    contrasena: contrasena
                                }
                            })
                            .then (respuesta => {
                                if (this.registro.id === this.$store.state.usuario.id) {
                                    this.$store.commit('eliminarUsuario')
                                    this.eliminarConfig('claveApi')
                                    this.$router.push('/iniciar-sesion')
                                } else {
                                    this.$router.push('/configuracion/usuarios')
                                }
                            })
                            .catch (error => {
                                this.$swal.getInput().value = ''
                                return error.response ? error.response.data.message : 'Error inesperado.'
                            })
                        }
                    }
                })
            }
        }
    }
</script>

<style scoped>
    fieldset {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    select {
        width: min-content !important;
    }

    #general > div {
        display: flex;
        gap: 1.5rem;
    }
    
    #general .campos-texto {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    #general  .seccion {
        margin-bottom: 0 !important;
    }
    
    .campos {
        display: flex;
        flex-direction: row;
        gap: 1rem;
    }
    
    .campos .seccion {
        flex: 1;
    }
    
    .campos .seccion input:not(#nro-identificacion) {
        width: 100%;
    }
    
    .seccion {
        margin-bottom: 0 !important;
    }
    
    .identificacion input {
        width: auto !important;
    }
    
    .restablecer :deep(.importante) {
        margin-bottom: 0.5rem;
    }
    
    #autenticacion label {
        margin-bottom: 0 !important;
    }
    
    #autenticacion p {
        margin: 0;
    }
    
    #autenticacion .confirmacion {
        flex-direction: row !important;
        align-items: center;
    }
    
    .sistema {
        margin: 0;
        font-size: 0.8rem;
        font-style: italic;
    }
    
    @media only screen and (max-width: 760px) {
        .contenido,
        #general > div,
        .campos:not(.identificacion) {
            flex-direction: column;
        }
        
        .campos-texto {
            order: 2;
        }
        
        .foto {
            align-self: center;
            order: 1;
        }
    }
</style>