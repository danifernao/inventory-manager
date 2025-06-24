<template>
    <form :class="{ edicion: registro}" ref="formulario" @submit.prevent>
        <EstadoSolicitud v-if="estado" :estado="estado" :respuesta="respuesta" />
        <Aviso tipo="importante" :mensaje="dadoDeBajaMensaje" v-if="registro && registro.dado_de_baja"/>
        <fieldset>
            <legend>General</legend>
            <div class="seccion" v-if="producto">
                <label for="producto">Producto:<span class="color-rojo">*</span></label>
                <input type="text" id="producto" maxlength="255" disabled readonly :value="`${producto.marca.fabricante.empresa.nombre || ''} ${producto.marca.nombre || ''} ${producto.modelo || ''}`"/>
            </div>
            <div class="seccion" v-if="registro">
                <label for="nro-serie">Número de serie:<span class="color-rojo">*</span></label>
                <input type="text" id="nro-serie" name="numero_serie" maxlength="255" required v-model="nroSerie"/>
            </div>
            <div class="seccion">
                <label for="proveedor">Proveedor:</label>
                <select id="proveedor" name="proveedor_id" v-model="proveedorId">
                    <option disabled value="">Seleccionar proveedor...</option>
                    <option :value="proveedor.id" v-for="proveedor in proveedores">{{ proveedor.nombre }}</option>
                </select>
                <div class="registrar">
                    <font-awesome-icon icon="plus"/>
                    <a :href="$route.path" @click.prevent="registrarProveedor">Registrar nuevo proveedor</a>
                </div>
            </div>
            <div class="seccion fecha-adquisicion">
                <label for="fecha-adquisicion">Fecha de adquisición:</label>
                <input type="date" id="fecha-adquisicion" class="form-control" name="fecha_adquisicion" v-model="fechaAdquisicion"/>
            </div>
            <div class="seccion valor-adquisicion">
                <label for="valor-adquisicion">Precio de adquisición:</label>
                <div>
                    <input type="number" id="valor-adquisicion" name="valor_adquisicion" min="0" v-model="valorAdquisicion"/>
                    COP
                </div>
            </div>
            <template v-if="registro && !registro.proyecto_id && registro.dias_en_uso > 0">
                <Aviso tipo="informacion" mensaje="Esta unidad ya ha sido empleada en proyectos, por lo que se lleva el acumulado de su uso para calcular su depreciación."/>
                <div class="seccion dias-uso">
                    <label for="valor-adquisicion">Días en uso:</label>
                    <div>
                        <input type="number" id="dias-uso" name="dias_en_uso" min="0" v-model="diasEnUso"/>
                        días.
                    </div>
                </div>
            </template>
        </fieldset>
        <fieldset v-if="!registro">
            <legend>Unidades<span class="color-rojo">*</span></legend>
            <Aviso tipo="importante" :mensaje="importante"/>
            <div class="seccion nro-series" v-for="(serie, indice) in nroSeries">
                <label :for="`nro-serie-${indice + 1}`">Número de serie:</label>
                <input type="text" :id="`nro-serie-${indice + 1}`" maxlength="255" required :placeholder="`Serial #${indice + 1}`" :ref="`txtSerial${indice + 1}`" v-model="nroSeries[indice]"/>
                <button type="button" class="boton-gris" title="Ingresar con lector de barras" :ref="`btnSerial${indice + 1}`" @click.prevent="leerCodigo(indice, true)">
                    <font-awesome-icon icon="barcode"/>
                </button>
                <button type="button" class="boton-rojo" title="Eliminar este campo" @click.prevent="eliminarCampo(indice)">
                    <font-awesome-icon icon="trash"/>
                </button>
            </div>
            <div class="nuevo-registro">
                <p>Agregar nuevo número de serie con:</p>
                <div>
                    <button type="button" class="boton-aceptar" title="Agregar nuevo campo" @click.prevent="agregarCampo(false, true)">
                        <font-awesome-icon icon="keyboard"/> Teclado
                    </button>
                    <button type="button" class="boton-aceptar" title="Agregar nuevo campo" ref="nuevoConLector" @click.prevent="agregarCampo(true, true)">
                        <font-awesome-icon icon="barcode"/> Lector
                    </button>
                </div>
            </div>
            <input type="text" class="foco" ref="foco"/>
        </fieldset>
        <div class="seccion nota">
            <p><span class="color-rojo">*</span> Campos obligatorios</p>
        </div>
        <div class="seccion botones">
            <div v-if="registro">
                <button type="button" class="boton-rojo atras" @click.prevent="eliminar">Eliminar</button>
            </div>
            <div>
                <button type="button" class="boton-cancelar" @click.prevent="$router.push(rutaAnterior)">Cancelar</button>
                <button type="button" class="boton-aceptar" v-if="registro && registro.dado_de_baja" @click.prevent="guardar(true)">Restaurar</button>
                <button type="button" class="boton-verde" @click.prevent="guardar()">Guardar</button>
            </div>
        </div>
    </form>
</template>

<script>
    import Aviso from './InventarioPrincipalBaseAviso.vue'
    import EstadoSolicitud from './BaseFormularioEstadoSolicitudHTTP.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'ProyectoFormulario',
        components: {
            Aviso,
            EstadoSolicitud
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
                bodegaId: this.$route.params.bodegaId,
                producto: null,
                proveedorId: this.$route.params.proveedorId || '',
                proveedores: [],
                nroSerie: null,
                nroSeries: [],
                fechaAdquisicion: new Date().toISOString().slice(0, 10),
                valorAdquisicion: 0,
                diasEnUso: 0,
                lector: {
                    auto: false,
                    indice: null,
                    leyendo: false,
                    lectura: '',
                    temporizador: null
                },
                conTeclado: false,
                importante: 'La información ingresada en el apartado <i>General</i> se aplicará para cada una de las unidades registradas a continuación. Si ya existen unidades en la base de datos con el número de serie ingresado, estos se ignorarán.'
            }
        },
        computed: {
            dirPadre() {
                return this.$route.path.split('/').slice(0, -1).join('/')
            },
            
            rutaAnterior() {
                return this.$router.options.history.state.back || this.dirPadre
            },
            
            dadoDeBajaMensaje() {
                let mensaje = 'Esta unidad ha sido dada de baja'
                mensaje += this.registro.motivo_de_baja ? ` por motivo de <b>«${this.registro.motivo_de_baja}»</b>. ` : '. '
                mensaje += 'Si desea restaurarla, presione el botón <i>Restaurar</i>. Tenga presente que los cambios realizados se guardarán en el proceso.'
                return mensaje
            },
            
            leyendo() {
                return this.lector.leyendo
            },
            
            tamanoNroSeries() {
                return this.nroSeries.length
            }
        },
        watch: {
            tamanoNroSeries(actual, anterior) {
                if (actual > anterior) {
                    const observarDOM = () => {
                        setTimeout(() => {
                            const campo = this.$refs[`txtSerial${actual}`]
                            if (campo) {
                                if (this.lector.auto) {
                                    this.leerCodigo(actual - 1)
                                } else {
                                    campo[0].focus()
                                }
                            } else {
                                observarDOM()
                            }
                        }, 300)
                    }
                    
                    observarDOM()
                }
            },
            
            leyendo(estaLeyendo) {
                if(!estaLeyendo) {
                    this.nroSeries[this.lector.indiceDestino] = this.lector.lectura
                    if (this.lector.auto) {
                        if (this.lector.lectura.length > 0) {
                            this.agregarCampo(true)
                        } else {
                            this.lector.leyendo = true
                        }
                    } else {
                        this.notificacion.close()
                        this.$refs.formulario.classList.remove('inhabilitado')
                    }
                }
            }
        },
        created() {
            if (this.registro) {
                this.bodegaId = this.registro.bodegaId
                this.producto = this.registro.producto
                this.proveedorId = this.registro.proveedor_id || ''
                this.nroSerie = this.registro.numero_serie
                this.fechaAdquisicion = this.obtenerFecha(this.registro.fecha_adquisicion)
                this.valorAdquisicion = this.registro.valor_adquisicion
                this.Adquisicion = this.registro.valor_adquisicion
                this.diasEnUso = this.registro.dias_en_uso
            } else {
                axios({
                    method: 'get',
                    url: `/api/productos/${this.$route.params.productoId}`,
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.producto = respuesta.data
                })
                .catch(error => {
                    this.mostrarError(error)
                })
            }
        
            axios({
                method: 'get',
                url: '/api/proveedores?lista=simple',
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                }
            })
            .then(respuesta => {
                this.proveedores = respuesta.data
            })
            .catch(error => {
                this.mostrarError(error)
            })

        },
        mounted() {
            document.addEventListener('keydown', evento => {
                if (this.lector.leyendo) {
                    if (evento.key === 'Escape') {
                        this.lector.leyendo = false
                        this.lector.auto = false
                    } else {
                        if (evento.key === 'Enter' || evento.key === 'Tab') {
                            this.lector.leyendo = false
                        } else {
                            this.lector.lectura += evento.key
                        }
                    }
                    
                    clearTimeout(this.lector.temporizador)
                    
                    this.lector.temporizador = setTimeout(() => {
                        this.lector.leyendo = false
                    }, 300)
                } else {
                    if (evento.key === 'Enter') {
                        this.agregarCampo()
                    }
                }
            })
        },
        methods: {
            obtenerFecha(cadena) {
                if (cadena) {
                    return cadena.split('/').reverse().join('-')
                }
                return null
            },
            
            agregarCampo(conLector = false, mostrarMsj = false) {
                if(mostrarMsj) {
                    if (conLector) {
                        this.mostrarMsjLeyendo()
                    } else {
                        this.mostrarMsjAtajo()
                    }
                }
                
                if(conLector) {
                    this.lector.auto = true
                }
                
                this.nroSeries.push(null)
                
                if (this.$refs.formulario) {
                    this.$refs.formulario.scrollIntoView({ behavior: 'smooth', block: 'end' })
                }
            },
            
            eliminarCampo(indice) {
                this.nroSeries.splice(indice, 1)
            },
            
            leerCodigo(indice, mostrarMsj = false) {
                if(mostrarMsj) {
                    this.mostrarMsjLeyendo()
                }
                
                this.$refs.formulario.classList.add('inhabilitado')
                this.$refs.foco.focus()
                
                this.lector.lectura = ''
                this.lector.leyendo = true
                this.lector.indiceDestino = indice
            },
            
            mostrarMsjLeyendo() {
                this.notificacion.fire({
                    icon: 'info',
                    title: 'Leyendo...',
                    text: 'Presione ESC para detener.',
                    timer: false,
                    timerProgressBar: false
                })
            },
            
            mostrarMsjAtajo() {
                this.notificacion.fire({
                    icon: 'info',
                    title: 'Campo agregado',
                    text: 'Presione Enter para agregar nuevo campo.'
                })
            },
            
            registrarProveedor() {
                this.campoTexto.fire({
                    confirmButtonText: 'Registrar',
                    inputLabel: 'Nombre del proveedor:',
                    inputAttributes: {
                        maxlength: 255
                    },
                    inputValidator: async (nombre) => {
                        if (!nombre) {
                          return 'Ingresa el nombre del proveedor.'
                        } else {
                            return await axios({
                                method: 'post',
                                url: '/api/proveedores',
                                headers: {
                                    Authorization: `Bearer ${this.config('claveApi')}`
                                },
                                data: {
                                    nombre: nombre
                                }
                            })
                            .then(respuesta => {
                                this.proveedorId = respuesta.data.id
                                this.proveedores.unshift(respuesta.data)
                            })
                            .catch(error => {
                                return error.response ? error.response.data.message : 'Error no identificado.'
                            })
                        }
                    }
                })
            },
            
            guardar(restaurar = false) {
                this.$refs.formulario.classList.add('inhabilitado')
                
                this.estado = 'cargando'
                this.respuesta = {
                    message: this.registro ? 'Guardando cambios...' : 'Registrando...'
                }
                
                const datos = new FormData(this.$refs.formulario)
                
                datos.append('producto_id', this.producto.id)
                datos.append('bodega_id', this.bodegaId)
                datos.append('restaurar', restaurar ? 1 : 0)
                
                if (this.registro) {
                    datos.append('_method', 'PUT')
                } else {
                    datos.append('nros_serie', JSON.stringify(_.compact(this.nroSeries)))
                }
                
                axios({
                    method: 'post',
                    url: '/api/unidades' + (this.registro ? `/${this.registro.id}` : ''),
                    headers: {
                        'Content-type': 'multipart/form-data',
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: datos
                })
                .then(respuesta => {
                    this.$router.push(this.rutaAnterior)
                })
                .catch(error => {
                    this.estado = 'error'
                    this.respuesta = this.respuesta = error.response ? error.response.data : { message: 'Error inesperado.' }
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
                .finally(() => {
                    this.$refs.formulario.classList.remove('inhabilitado')
                })
            },
            
            eliminar() {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar esta unidad del proyecto? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        this.$refs.formulario.classList.add('inhabilitado')
                        
                        this.estado = 'cargando'
                        this.respuesta = { message: 'Eliminando...' }
                        
                        axios({
                            method: 'delete',
                            url: `/api/unidades/${this.registro.id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Unidad del producto eliminado.'
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
    .edicion fieldset {
        padding: 0;
        border: 0;
    }
    
    .edicion legend {
        display: none;
    }
    
    .fecha-adquisicion,
    .valor-adquisicion,
    .dias-uso,
    .nro-series {
        align-items: center;
        flex-direction: row !important;
        gap: 0.5rem;
    }
    
    .fecha-adquisicion label,
    .valor-adquisicion label,
    .dias-uso label,
    .nro-series label {
        margin: 0;
    }
    
    .valor-adquisicion input,
    .dias-uso input {
        text-align: right;
    }
    
    .dias-uso input {
        width: 8rem;
    }
    
    .nro-series button {
        padding: 0.5rem 0.7rem;
    }
    
    .nuevo-registro {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .nuevo-registro p {
        margin: 0;
    }
    
    .nuevo-registro button {
        margin-right: 0.5rem;
    }
    
    .foco {
        position: absolute;
        top: -100%;
        left: -100%;
        height: 0 !important;
        width: 0 !important;
    }
    
    @media only screen and (max-width: 760px) {
        .fecha-adquisicion,
        .valor-adquisicion,
        .nro-serie {
            flex-direction: column !important;
            align-items: start;
        }
    }
</style>