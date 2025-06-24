<template>
    <div class="envoltorio">
        <h2>Editar datos de la aplicación</h2>
        <form ref="formulario" @submit.prevent="guardar">
            <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
            <fieldset id="general">
                <legend>General</legend>
                <div class="seccion">
                    <label for="titulo">Título:<span class="color-rojo">*</span></label>
                    <input type="text" id="titulo" name="titulo" maxlength="255" autocomplete="off" placeholder="Título de la aplicación." required v-model="titulo"/>
                    <p class="info">Se empleará como título en los datos META de la aplicación y en la cabecera de la misma.</p>
                </div>
                <div class="seccion">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" maxlength="500" placeholder="Descripción de la aplicación." v-model="descripcion"></textarea>
                    <p class="info">Se empleará como descripción en los datos META de la aplicación.</p>
                </div>
            </fieldset>
            <fieldset id="imagenes">
                <legend>Imágenes</legend>
                <div class="seccion icono">
                    <Imagen :fuente="iconoUrl" clase="contener" descripcion="Icono" icono="file-image" boton="Cambiar icono" @imagen="guardarIcono"/>
                    <p class="info">Se empleará como icono en los datos META de la aplicación.</p>
                </div>
                <div class="seccion favicon">
                    <Imagen :fuente="faviconUrl" clase="contener" descripcion="Favicón" icono="file-image" boton="Cambiar favicón" @imagen="guardarFavicon"/>
                    <p class="info">Se empleará como icono en la pestaña del navegador.</p>
                </div>
                <div class="seccion img-autenticacion">
                    <Imagen :fuente="imgAutenticacionUrl" clase="contener opaco" descripcion="Imagen de cabecera del formulario de autenticación" icono="file-image" boton="Cambiar imagen" @imagen="guardarImgAutenticacion"/>
                    <p class="info">Se empleará como cabecera del formulario de inicio de sesión.</p>
                </div>
                <div class="seccion img-inventario">
                    <Imagen :fuente="imgInventarioUrl" clase="contener naranja" descripcion="Imagen de cabecera del inventario" icono="file-image" @imagen="guardarImgInventario"/>
                    <p class="info">Se empleará como cabecera de la aplicación, una vez el usuario haya sido autenticado.</p>
                </div>
            </fieldset>
            <fieldset id="eliminar">
                <legend>Eliminación de inventarios</legend>
                <div class="seccion">
                    <p>La siguiente acción eliminará de manera indiscriminada todas las bodegas, proyectos, fabricantes, proveedores y unidades. Los usuarios y la información general de la aplicación se mantendrá. Esta acción no se puede revertir.</p>
                    <div>
                        <button class="boton-rojo" @click.prevent="eliminar">Eliminar inventarios</button>
                    </div>
                </div>
            </fieldset>
            <div class="seccion nota">
                <p><span class="color-rojo">*</span> Campos obligatorios</p>
            </div>
            <div class="seccion botones">
                <div>
                    <button type="submit" class="boton-verde">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import Imagen from './BaseFormularioImagen.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'BodegaFormulario',
        components: {
            EstadoSolicitud,
            Imagen
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
                titulo: '',
                descripcion: '',
                iconoUrl: '',
                faviconUrl: '',
                imgAutenticacionUrl: '',
                imgInventarioUrl: '',
                iconoArchivo: null,
                faviconArchivo: null,
                imgAutenticacionArchivo: null,
                imgInventarioArchivo: null
            }
        },
        computed: {
            aplicacion() {
                return this.$store.state.aplicacion
            }
        },
        created() {
            if (this.$store.state.usuario.es_administrador) {
                this.obtenerRegistro()
            } else {
                this.$router.push('/404')
            }
        },
        methods: {
            obtenerRegistro() {
                this.titulo = this.aplicacion.titulo
                this.descripcion = this.aplicacion.descripcion
                this.iconoUrl = this.aplicacion.img_icono_url
                this.faviconUrl = this.aplicacion.img_favicon_url
                this.imgAutenticacionUrl = this.aplicacion.img_autenticacion_url
                this.imgInventarioUrl = this.aplicacion.img_inventario_url
            },
        
            guardarIcono(url, archivo) {
                this.iconoUrl = url,
                this.iconoArchivo = archivo
            },
            
            guardarFavicon(url, archivo) {
                this.faviconUrl = url,
                this.faviconArchivo = archivo
            },
            
            guardarImgAutenticacion(url, archivo) {
                this.imgAutenticacionUrl = url,
                this.imgAutenticacionArchivo = archivo
            },
            
            guardarImgInventario(url, archivo) {
                this.imgInventarioUrl = url,
                this.imgInventarioArchivo = archivo
            },
        
            guardar() {
                this.$refs.formulario.classList.add('inhabilitado')
                
                this.estado = 'cargando'
                this.respuesta = { message: 'Guardando cambios...' }
                
                const datos = new FormData(this.$refs.formulario)
                
                datos.append('_method', 'PUT')
                
                if (this.iconoArchivo) {
                    datos.append('icono', this.iconoArchivo)
                } else {
                    if (!this.iconoUrl) {
                        datos.append('icono', '')
                    }
                }
                
                if (this.faviconArchivo) {
                    datos.append('favicon', this.faviconArchivo)
                } else {
                    if (!this.faviconUrl) {
                        datos.append('favicon', '')
                    }
                }
                
                if (this.imgAutenticacionArchivo) {
                    datos.append('img_autenticacion', this.imgAutenticacionArchivo)
                } else {
                    if (!this.imgAutenticacionUrl) {
                        datos.append('img_autenticacion', '')
                    }
                }
                
                if (this.imgInventarioArchivo) {
                    datos.append('img_inventario', this.imgInventarioArchivo)
                } else {
                    if (!this.imgInventarioUrl) {
                        datos.append('img_inventario', '')
                    }
                }
                
                axios({
                    method: 'post',
                    url: '/api/aplicacion',
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: datos
                })
                .then(respuesta => {
                    this.estado = 'exito'
                    this.respuesta = { message: 'Cambios guardados.' }
                    this.$store.commit('guardarAppDatos', respuesta.data)
                    this.obtenerRegistro()
                })
                .catch(error => {
                    this.estado = 'error'
                    this.respuesta = error.response ? error.response.data : { message: 'Error no identificado.' }
                })
                .finally(() => {
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
                          return 'Ingresa la contraseña para proceder con la eliminación.'
                        } else {
                            return await axios({
                                method: 'delete',
                                url: `/api/aplicacion`,
                                headers: {
                                    Authorization: `Bearer ${this.config('claveApi')}`
                                },
                                data: {
                                    contrasena: contrasena
                                }
                            })
                            .then(respuesta => {
                                return false
                            })
                            .catch(error => {
                                this.$swal.getInput().value = ''
                                return error.response ? error.response.data.message : 'Error no identificado.'
                            })
                        }
                    }
                })
                .then(resultado => {
                    if (resultado.isConfirmed) {
                        this.notificacion.fire({
                            icon: 'success',
                            title: 'Inventarios eliminados.'
                        })
                    }
                })
            }
        }
    }
</script>

<style scoped>
    .info {
        margin: 0;
        font-size: 0.8rem;
    }
    
    #imagenes {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    
    #imagenes .seccion {
        max-width: 10rem;
        text-align: center;
    }
    
    #imagenes .info {
        align-self: start;
    }
    
    #imagenes :deep(.envoltorio) {
        flex: inherit !important;
    }
    
    #eliminar legend {
        color: var(--color-rojo-4);
    }
    
    #eliminar p {
        margin: 0;
    }
    
    @media only screen and (max-width: 760px) {
        #imagenes {
            justify-content: center;
        }
    }
</style>