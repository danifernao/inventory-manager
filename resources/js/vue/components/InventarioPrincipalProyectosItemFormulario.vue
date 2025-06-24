<template>
    <form ref="formulario" @submit.prevent="guardar">
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
        <div class="seccion">
            <label for="nombre">Nombre:<span class="color-rojo">*</span></label>
            <input type="text" id="nombre" name="nombre" maxlength="255" autocomplete="off" placeholder="Nombre del proyecto" required v-model="nombre"/>
        </div>
        <div class="seccion">
            <label for="codigo">Codigo de referencia:</label>
            <input type="text" id="ubicacion" name="codigo" maxlength="255" autocomplete="off" placeholder="Identificación del proyecto." v-model="codigo"/>
        </div>
        <Ubicacion :ubicacion="ubicacion" @ubicacion="establecerUbicacion"/>
        <div class="seccion">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" maxlength="500" autocomplete="off" placeholder="Descripción general del proyecto" v-model="descripcion"></textarea>
        </div>
        <div class="seccion fechas">
            <div>
                <label for="apertura">Fecha de apertura:</label>
                <input type="date" id="apertura" name="fecha_apertura" v-model="apertura"/>
            </div>
            <div>
                <label for="clausura">Fecha de clausura:</label>
                <input type="date" id="clausura" name="fecha_clausura" v-model="clausura"/>
            </div>
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
    </form>
</template>

<script>
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import Ubicacion from './BaseFormularioUbicacion.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'ProyectoFormulario',
        components: {
            EstadoSolicitud,
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
                respuesta: null,
                nombre: '',
                codigo: '',
                ubicacion: {
                    pais: '',
                    departamento: '',
                    municipio: '',
                    barrio: '',
                    direccion: ''
                },
                descripcion: '',
                apertura: '',
                clausura: ''
            }
        },
        computed: {
            dirPadre() {
                return this.$route.path.split('/').slice(0, -1).join('/')
            },
            rutaAnterior() {
                return this.$router.options.history.state.back || this.dirPadre
            }
        },
        created() {
            if (this.registro) {
                this.nombre = this.registro.nombre
                this.codigo = this.registro.codigo
                this.ubicacion = {
                    pais: this.registro.pais ? this.registro.pais.nombre : '',
                    departamento: this.registro.departamento ? this.registro.departamento.nombre : '',
                    municipio: this.registro.municipio ? this.registro.municipio.nombre : '',
                    barrio: this.registro.barrio ? this.registro.barrio.nombre : '',
                    direccion: this.registro.direccion
                }
                this.descripcion = this.registro.descripcion
                this.apertura = this.obtenerFecha(this.registro.fecha_apertura)
                this.clausura = this.obtenerFecha(this.registro.fecha_clausura)
            }
        },
        methods: {
            establecerUbicacion(ubicacion) {
                _.merge(this.ubicacion, ubicacion)
            },
        
            obtenerFecha(cadena) {
                if (cadena) {
                    return cadena.split('/').reverse().join('-')
                }
                return null
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
                
                for (const propiedad in this.ubicacion) {
                    datos.append(propiedad, this.ubicacion[propiedad])
                }
                
                axios({
                    method: 'post',
                    url: '/api/proyectos' + (this.registro ? `/${this.registro.id}` : ''),
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: datos
                })
                .then(respuesta => {
                    this.$router.push(this.dirPadre + (this.registro ? '' : `/${respuesta.data.id}`))
                })
                .catch(error => {
                    this.estado = 'error'
                    this.respuesta = error.response ? error.response.data : { message: 'Error inesperado.' }
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
            },
            
            eliminar() {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar este proyecto? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        this.$refs.formulario.classList.add('inhabilitado')
                        
                        this.estado = 'cargando'
                        this.respuesta = { message: 'Eliminando...' }
                        
                        axios({
                            method: 'delete',
                            url: `/api/proyectos/${this.registro.id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Proyecto eliminado.'
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
    .fechas {
        flex-direction: row !important;
        gap: 1rem;
    }
    
    .fechas label {
        margin: 0;
    }
    
    .fechas > div {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    @media only screen and (max-width: 760px) {
        .fechas {
            flex-direction: column !important;
        }
    }
</style>