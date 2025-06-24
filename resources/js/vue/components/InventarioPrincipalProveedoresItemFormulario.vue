<template>
    <form ref="formulario" @submit.prevent="guardar">
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
        <div class="contenido">
            <div class="logotipo">
                <Imagen :fuente="imagenUrl" descripcion="Logotipo del proveedor" icono="truck-ramp-box" boton="Cambiar logotipo" @imagen="guardarImagen"/>
            </div>
            <div class="campos">
                <Aviso tipo="informacion" :mensaje="informacion" v-if="cargado && registroEstaCompartido"/>
                <div class="seccion">
                    <label for="nombre">Nombre:<span class="color-rojo">*</span></label>
                    <input type="text" id="nombre" name="nombre" maxlength="255" autocomplete="off" placeholder="Nombre del proveedor." required v-model="nombre"/>
                </div>
                <div class="seccion">
                    <label for="nit">Identificación tributaria:</label>
                    <input type="text" id="nit" name="nit" maxlength="255" autocomplete="off" placeholder="Identificación tributaria del proveedor." v-model="nit"/>
                </div>
                <Ubicacion :ubicacion="ubicacion" @ubicacion="establecerUbicacion"/>
                <div class="seccion">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" maxlength="500" placeholder="Descripción general de las actividades del proveedor." v-model="descripcion"></textarea>
                </div>
                <div class="seccion">
                    <label for="pagina-web">Página web:</label>
                    <input type="text" id="pagina-web" name="pagina_web" maxlength="255" placeholder="https://" autocomplete="off" v-model="paginaWeb"/>
                </div>
                <div class="seccion verificacion" v-if="cargado && !registroEstaCompartido">
                    <input type="checkbox" id="registrar-fabricante" name="guardarComoFabricante" autocomplete="off"/>
                    <label for="registrar-fabricante">¿Deseas registrar un fabricante con los mismos datos de este proveedor?</label>
                </div>
                <div class="seccion nota">
                    <p><span class="color-rojo">*</span> Campos obligatorios</p>
                </div>
                <div class="seccion botones">
                    <div v-if="registro">
                        <button class="boton-rojo atras" @click.prevent="eliminar">Eliminar</button>
                    </div>
                    <div>
                        <button class="boton-cancelar" @click.prevent="$router.push(rutaAnterior)">Cancelar</button>
                        <button type="submit" class="boton-verde">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    import Aviso from './InventarioPrincipalBaseAviso.vue'
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import Imagen from './BaseFormularioImagen.vue'
    import Ubicacion from './BaseFormularioUbicacion.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'ProveedorFormulario',
        components: {
            Aviso,
            EstadoSolicitud,
            Imagen,
            Ubicacion
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
                cargado: false,
                respuesta: null,
                nombre: '',
                nit: '',
                ubicacion: {
                    pais: '',
                    departamento: '',
                    municipio: '',
                    barrio: '',
                    direccion: ''
                },
                descripcion: '',
                paginaWeb: '',
                imagenUrl: '',
                imagenArchivo: null,
                registroEstaCompartido: false, 
                registroCompartidoRuta: null
            }
        },
        computed: {
            dirPadre() {
                return this.$route.path.split('/').slice(0, -1).join('/')
            },
            rutaAnterior() {
                return this.$router.options.history.state.back || this.dirPadre
            },
            informacion() {
                return `Los cambios realizados también se aplicarán a <a href="${this.registroCompartidoRuta}" target="_blank">este fabricante</a>, dado que son la misma empresa.`
            }
        },
        created() {
            if (this.registro) {
                this.nombre = this.registro.nombre
                this.nit = this.registro.nit
                this.ubicacion = {
                    pais: this.registro.pais ? this.registro.pais.nombre : '',
                    departamento: this.registro.departamento ? this.registro.departamento.nombre : '',
                    municipio: this.registro.municipio ? this.registro.municipio.nombre : '',
                    barrio: this.registro.barrio ? this.registro.barrio.nombre : '',
                    direccion: this.registro.direccion || ''
                }
                this.descripcion = this.registro.descripcion
                this.paginaWeb = this.registro.pagina_web
                this.imagenUrl = this.registro.logotipo_url
                
                axios({
                    method: 'get',
                    url: `/api/empresas/${this.registro.empresa_id}/fabricante`,
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.registroCompartidoRuta = this.$route.path.split('/').slice(0, 3).join('/') + '/fabricantes/' + respuesta.data.id
                    this.registroEstaCompartido = true
                })
                .catch(error => {
                    this.registroEstaCompartido = false
                })
                .finally(() => {
                    this.cargado = true
                })
            }
        },
        methods: {
            guardarImagen (url, archivo) {
                this.imagenUrl = url,
                this.imagenArchivo = archivo
            },
            
            establecerUbicacion(ubicacion) {
                _.merge(this.ubicacion, ubicacion)
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
                    datos.append('logotipo', this.imagenArchivo)
                } else {
                    if (!this.imagenUrl) {
                        datos.append('logotipo', '')
                    }
                }
                
                for (const propiedad in this.ubicacion) {
                    datos.append(propiedad, this.ubicacion[propiedad])
                }
                
                axios({
                    method: 'post',
                    url: '/api/proveedores' + (this.registro ? `/${this.registro.id}` : ''),
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: datos
                })
                .then(respuesta => {
                    if (this.registro) {
                        this.$router.push(this.rutaAnterior)
                    } else {
                        this.$router.push(`${this.dirPadre}/${respuesta.data.id}`)
                    }
                })
                .catch(error => {
                    this.estado = 'error'
                    this.respuesta = error.response ? error.response.data : { message: 'Error inesperado.' }
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
            },
            
            eliminar () {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar este proveedor? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        this.$refs.formulario.classList.add('inhabilitado')
                        
                        this.estado = 'cargando'
                        this.respuesta = { message: 'Eliminando...' }
                        
                        axios({
                            method: 'delete',
                            url: `/api/proveedores/${this.registro.id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Proveedor eliminado.'
                            })
                            this.$router.push(this.dirPadre.replace(/\/\d+$/, ''))
                        })
                        .catch(error => {
                            const estado = error.response ? error.response.status : ''
                            const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                            
                            this.notificacion.fire({
                                icon: 'error',
                                title: `Error ${estado}`,
                                text: mensaje
                            })
                            
                            this.$refs.formulario.classList.remove('inhabilitado')
                        })
                    }
                })
            }
        }
    }
</script>

<style scoped>
    .contenido {
        display: flex;
        gap: 1.5rem;
    }
    
    .logotipo {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .logotipo input {
        display: none;
    }
    
    .imagen {
        position: relative;
        border: 0.06rem solid var(--color-gris-2);
        border-radius: 0.3rem;
    }
    
    .imagen img {
        display: block;
        width: 10rem;
        height: 10rem;
        border-radius: 0.3rem;
        object-fit: cover;
    }
    
    .imagen > svg {
        padding: 1.5rem;
        width: 7rem;
        height: 7rem;
        color: var(--color-gris-3);
    }
    
    .imagen a {
        position: absolute;
        right: 0.5rem;
        top: 0.5rem;
        color: var(--color-blanco-1);
        opacity: 0.7;
        z-index: 100;
    }
    
    .imagen a:hover {
        opacity: 1;
    }
    
    .imagen a svg {
        filter: drop-shadow(0 0 0.09rem var(--color-negro-1))
    }
    
    .campos {
        flex: 1;
    }
    
    @media only screen and (max-width: 760px) {
        .contenido {
            flex-direction: column;
        }
        
        .logotipo {
            align-self: center;
        }
    }
</style>