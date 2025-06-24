<template>
    <form ref="formulario" @submit.prevent="guardar">
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
        <div class="contenido">
            <div class="imagen-producto">
                <Imagen :fuente="imagenUrl" descripcion="Fotografía del producto" icono="box" @imagen="guardarImagen"/>
            </div>
            <div class="campos">
                <Aviso tipo="informacion" :mensaje="informacion"/>
                <div class="seccion">
                    <label for="fabricante">Fabricante:<span class="color-rojo">*</span></label>
                    <select id="fabricante" name="fabricante_id" v-model="fabricanteId">
                        <option value="">Seleccionar fabricante...</option>
                        <option :value="fabricante.id" v-for="fabricante in fabricantes">{{ fabricante.nombre }}</option>
                    </select>
                    <div class="registrar">
                        <font-awesome-icon icon="plus"/>
                        <a :href="$route.path" @click.prevent="registrarFabricante">Registrar nuevo fabricante</a>
                    </div>
                </div>
                <div class="seccion">
                    <label for="marca">Marca:<span class="color-rojo">*</span></label>
                    <input type="text" id="marca" name="marca" maxlength="50" placeholder="Marca del producto." required v-model="marca"/>
                </div>
                <div class="seccion">
                    <label for="modelo">Modelo:<span class="color-rojo">*</span></label>
                    <input type="text" id="modelo" name="modelo" maxlength="255" autocomplete="off" placeholder="Modelo del producto." required v-model="modelo"/>
                </div>
                <div class="seccion">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" maxlength="500" placeholder="Descripción general del producto." v-model="descripcion"></textarea>
                </div>
                <div class="seccion vida-util">
                    <label for="vida-util">Vida útil:</label>
                    <div>
                        <input type="number" id="vida-util" name="anos_vida_util" min="0" v-model="vidaUtil" /> años.
                    </div>
                </div>
                <div class="seccion vida-util" v-if="ruta('bodegas')">
                    <label for="min-unidades">Unidades mínimas requeridas:</label>
                    <div>
                        <input type="number" id="min-unidades" name="unidades_minimas" min="0" v-model="minUnidades" /> unidades.
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
            </div>
        </div>
    </form>
</template>

<script>
    import Aviso from './InventarioPrincipalBaseAviso.vue'
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import Imagen from './BaseFormularioImagen.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'ProductoFormulario',
        components: {
            Aviso,
            EstadoSolicitud,
            Imagen
        },
        mixins: [
            ConfigMixin,
            RutaMixin,
            SwalMixin
        ],
        props: {
            registro: Object
        },
        data() {
            return {
                estado: null,
                respuesta: null,
                bodegaId: this.$route.params.bodegaId || null,
                fabricanteId: this.$route.params.fabricanteId || '',
                fabricantes: [],
                marca: '',
                modelo: '',
                descripcion: '',
                vidaUtil: '0',
                minUnidades: '0',
                imagenUrl: '',
                imagenArchivo: null,
                informacion: `Este producto también ${this.registro ? 'está' : 'estará'} disponible en las demás bodegas y proyectos, por lo que no podrá eliminarse si en alguno de estos hay unidades asociadas a él.`
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
                this.marca = this.registro.marca.nombre
                this.modelo = this.registro.modelo
                this.descripcion = this.registro.descripcion
                this.vidaUtil = this.registro.anos_vida_util
                this.imagenUrl = this.registro.imagen_url
                this.fabricanteId = this.registro.marca.fabricante_id || ''
                
                if (this.registro.bodegas && this.$route.params.bodegaId) {
                    const bodega = _.find(this.registro.bodegas, { id: parseInt(this.$route.params.bodegaId) })
                    this.minUnidades = bodega.pivot.unidades_minimas
                }
            }
            
            axios({
                method: 'get',
                url: '/api/fabricantes?lista=simple',
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                }
            })
            .then(respuesta => {
                this.fabricantes = _.sortBy(respuesta.data, fabricante => fabricante.nombre.toLowerCase())
            })
            .catch(error => {
                this.mostrarError(error)
            })
        },
        methods: {
            guardarImagen(url, archivo) {
                this.imagenUrl = url,
                this.imagenArchivo = archivo
            },
            
            registrarFabricante() {
                this.campoTexto.fire({
                    confirmButtonText: 'Registrar',
                    inputLabel: 'Nombre del fabricante:',
                    inputAttributes: {
                        maxlength: 255
                    },
                    inputValidator: async (nombre) => {
                        if (!nombre) {
                          return 'Ingresa el nombre del fabricante.'
                        } else {
                            return await axios({
                                method: 'post',
                                url: '/api/fabricantes',
                                headers: {
                                    Authorization: `Bearer ${this.config('claveApi')}`
                                },
                                data: {
                                    nombre: nombre
                                }
                            })
                            .then (respuesta => {
                                this.fabricanteId = respuesta.data.id
                                this.fabricantes.push(respuesta.data)
                                this.fabricantes = _.sortBy(this.fabricantes, fabricante => fabricante.nombre.toLowerCase())
                            })
                            .catch (error => {
                                return error.response ? error.response.data.message : 'Error inesperado.'
                            })
                        }
                    }
                })
            },
            
            guardar () {
                this.$refs.formulario.classList.add('inhabilitado')
                
                this.estado = 'cargando'
                this.respuesta = {
                    message: this.registro ? 'Guardando cambios...' : 'Registrando...'
                }
                
                const datos = new FormData(this.$refs.formulario)
                
                if (this.registro) {
                    datos.append('_method', 'PUT')
                }
                
                if (this.bodegaId) {
                    datos.append('bodega_id', this.bodegaId)
                }
                
                if (this.imagenArchivo) {
                    datos.append('imagen', this.imagenArchivo)
                } else {
                    if (!this.imagenUrl) {
                        datos.append('imagen', '')
                    }
                }
                
                axios({
                    method: 'post',
                    url: '/api/productos' + (this.registro ? `/${this.registro.id}` : ''),
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: datos
                })
                .then (respuesta => {
                    if (this.registro) {
                        this.$router.push(this.rutaAnterior)
                    } else {
                        this.$router.push(`${this.dirPadre}/${respuesta.data.id}`)
                    }
                })
                .catch (error => {
                    this.estado = 'error'
                    this.respuesta = error.response ? error.response.data : { message: 'Error inesperado.' }
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
            },
            
            eliminar () {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar este producto? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        this.$refs.formulario.classList.add('inhabilitado')
                        
                        this.estado = 'cargando'
                        this.respuesta = { message: 'Eliminando...' }
                        
                        axios({
                            method: 'delete',
                            url: `/api/productos/${this.registro.id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Producto eliminado.'
                            })
                            this.$router.push(this.dirPadre.replace(/\/\d+$/, ''))
                        })
                        .catch(error => {
                            this.mostrarError(error)
                            this.$refs.formulario.classList.remove('inhabilitado')
                        })
                    }
                })
            },
            
            mostrarError(error) {
                const estado = error.response ? error.response.status : ''
                const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                
                this.notificacion.fire({
                    icon: 'error',
                    title: `Error ${estado}`,
                    text: mensaje
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
    
    .imagen-producto {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .imagen-producto input {
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
    
    .vida-util,
    .min-unidades {
        align-items: center;
        flex-direction: row !important;
    }
    
    .vida-util label,
    .min-unidades label {
        margin: 0;
    }
    
    .vida-util input,
    .min-unidades input {
        width: 7rem;
        text-align: right;
    }
    
    @media only screen and (max-width: 760px) {
        .contenido {
            flex-direction: column;
        }
        
        .imagen-producto {
            align-self: center;
        }
    }
</style>