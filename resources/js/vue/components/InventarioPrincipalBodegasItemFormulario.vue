<template>
    <form ref="formulario" @submit.prevent="guardar">
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
        <Aviso tipo="informacion" :mensaje="informacion"/>
        <div class="seccion">
            <label for="nombre">Nombre:<span class="color-rojo">*</span></label>
            <input type="text" id="nombre" name="nombre" maxlength="255" autocomplete="off" placeholder="Nombre de la bodega." required v-model="nombre"/>
        </div>
        <Ubicacion :ubicacion="ubicacion" @ubicacion="establecerUbicacion"/>
        <div class="seccion">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" maxlength="500" placeholder="Descripción general de la bodega." v-model="descripcion"></textarea>
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
    import Aviso from './InventarioPrincipalBaseAviso.vue'
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import Ubicacion from './BaseFormularioUbicacion.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'BodegaFormulario',
        components: {
            Aviso,
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
                ubicacion: {
                    pais: '',
                    departamento: '',
                    municipio: '',
                    barrio: '',
                    direccion: ''
                },
                descripcion: '',
                informacion: 'Esta bodega no podrá eliminarse mientras existan unidades asociadas a ella.'
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
                this.ubicacion = {
                    pais: this.registro.pais ? this.registro.pais.nombre : '',
                    departamento: this.registro.departamento ? this.registro.departamento.nombre : '',
                    municipio: this.registro.municipio ? this.registro.municipio.nombre : '',
                    barrio: this.registro.barrio ? this.registro.barrio.nombre : '',
                    direccion: this.registro.direccion || ''
                }
                this.descripcion = this.registro.descripcion
            }
        },
        methods: {
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
                
                for (const propiedad in this.ubicacion) {
                    datos.append(propiedad, this.ubicacion[propiedad])
                }
                
                axios({
                    method: 'post',
                    url: '/api/bodegas' + (this.registro ? `/${this.registro.id}` : ''),
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: datos
                })
                .then(respuesta => {
                    this.$router.push(`/bodegas/${respuesta.data.id}`)
                })
                .catch(error => {
                    this.estado = 'error'
                    this.respuesta = error.response ? error.response.data : { message: 'Error no identificado.' }
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
                .finally(() => {
                    this.$store.commit('actualizarBodega')
                })
            },
            
            eliminar() {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar esta bodega? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        this.$refs.formulario.classList.add('inhabilitado')
                        
                        this.estado = 'cargando'
                        this.respuesta = { message: 'Eliminando...' }
                        
                        axios({
                            method: 'delete',
                            url: `/api/bodegas/${this.registro.id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.estado = 'cargando'
                            this.respuesta = { message: 'Redirigiendo...' }
                            
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Bodega eliminada.'
                            })
                            
                            this.$router.push('/bodegas')
                        })
                        .catch(error => {
                            this.notificacion.fire({
                                icon: 'error',
                                title: `Error ${error.response && error.response.status}`,
                                text: error.response ? error.response.data.message : 'Error no identificado.'
                            })
                            this.$refs.formulario.classList.remove('inhabilitado')
                        })
                    }
                })
            }
        }
    }
</script>

