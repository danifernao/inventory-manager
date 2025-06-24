<template>
    <tbody>
        <template v-if="registros.length">
            <tr v-for="registro in registros">
                <Celda tipo="seleccion" clase="seleccion-unidades" :dato="registro.id"/>
                <Celda clase="nro-serie" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="registro.numero_serie"/>
                <template v-if="!$route.params.proveedorId">
                    <Celda clase="proveedor" :url="rutaUnidad(registro.id)" titulo="Ver detalles de la unidad" :dato="registro.proveedor_nombre" v-if="registro.proveedor_id"/>
                    <Celda clase="proveedor" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="null" v-else/>
                </template>
                <Celda clase="fecha-adquisicion" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="registro.fecha_adquisicion"/>
                <Celda clase="valor-adquisicion" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="registro.valor_adquisicion + ' COP'"/>
                <Celda clase="fecha-instalacion" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="registro.fecha_ingreso_proyecto" v-if="estaEnProyecto"/>
                <Celda clase="valor-residual" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="registro.valor_residual + ' COP'" v-if="estaEnProyecto"/>
                <Celda :clase="claseColor(registro.depreciacion)" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="registro.depreciacion + ' %'" v-if="estaEnProyecto"/>
                <Celda clase="fecha-baja" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :dato="registro.fecha_de_baja" v-if="registro.dado_de_baja"/>
                <Celda clase="motivo-baja" :url="rutaUnidad(registro.id)" titulo="Ver movimientos de la unidad" :tip="registro.motivo_de_baja" v-if="registro.dado_de_baja"/>
                <Celda clase="acciones" :opciones="opciones(registro)" @accion="ejecutar"/>
            </tr>
        </template>
        <tr v-else>
            <td colspan="100%" class="vacio">No hay registros que mostrar.</td>
        </tr>
    </tbody>
</template>

<script>
    import CuerpoCelda from './InventarioPrincipalBaseListaTablaCuerpoCelda.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'TablaCuerpoUnidades',
        components: {
            Celda: CuerpoCelda,
        },
        mixins: [
            ConfigMixin,
            RutaMixin,
            SwalMixin
        ],
        props: {
            registros: Object
        },
        computed: {
            dirPadre() {
                return this.$route.path.split('/').slice(0, 3).join('/')
            },
            estaEnProyecto() {
                return this.ruta('proyectos')
            }
        },
        methods: {
            claseColor(valor) {
                let clase = 'depreciacion'
                if (valor <= 30) {
                    clase += '  verde'
                } else {
                    if (valor <=50) {
                        clase += ' naranja'
                    } else {
                        clase += ' rojo'
                    }
                }
                return clase
            },
            
            rutaUnidad (id) {
                return `${this.$route.path}/unidades/${id}`
            },
        
            opciones (unidad) {
                return [
                    {
                        texto: 'Acciones',
                        menu: [
                            {
                                url: `${this.dirPadre}/proveedores/${unidad.proveedor_id}`,
                                texto: 'Ver proveedor',
                                oculto: this.$route.params.proveedorId
                            },
                            {
                                url: this.rutaUnidad(unidad.id),
                                texto: 'Ver movimientos'
                            },
                            {
                                url: `${this.rutaUnidad(unidad.id)}/editar`,
                                texto: 'Editar unidad'
                            },
                            {
                                texto: 'Mover unidad',
                                params: {
                                    accion: 'mover',
                                    unidad: unidad
                                },
                                oculto: unidad.dado_de_baja
                            },
                            {
                                texto: unidad.dado_de_baja ? 'Restaurar unidad' : 'Dar de baja',
                                params: {
                                    accion: 'darDeBaja',
                                    unidad: unidad
                                }
                            },
                            {
                                texto: 'Eliminar unidad',
                                params: {
                                    accion: 'eliminar',
                                    id: unidad.id
                                }
                            }
                        ]
                    }
                ]
            },
            
            ejecutar(params) {
                switch (params.accion) {
                    case 'mover':
                        this.mover(params.unidad)
                        break
                    case 'darDeBaja':
                        this.darDeBaja(params.unidad)
                        break
                    case 'eliminar':
                        this.eliminar(params.id)
                        break
                }
            },
            
            mover (unidad) {
                this.html.fire({
                    confirmButtonText: 'Mover',
                    html: `
                        <form name="mover">
                            <fieldset>
                                <legend>Mover a...</legend>
                                <div class="radios">
                                    <div>
                                        <label for="bodega">Bodega</label>
                                        <input type="radio" id="bodega" name="destino" value="bodega"/>
                                    </div>
                                    <div>
                                        <label for="proyecto">Proyecto</label>
                                        <input type="radio" id="proyecto" name="destino" value="proyecto"/>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset class="registros">
                                <legend></legend>
                                <p class="cargando">Cargando registros...</p>
                                <p class="mensaje-sin-registros">No hay registros que mostrar.</p>
                                <select name="destino_id"></select>
                            </fieldset>
                        </form>
                    `,
                    willOpen: () => {
                        const form = this.html.getHtmlContainer().querySelector('form')
                        
                        form.querySelectorAll('input').forEach(radioElem => {
                            radioElem.addEventListener('change', () => {
                                form.classList.add('visible')
                                form.classList.remove('cargado', 'sin-registros')
                                form.querySelector('.registros legend').textContent = `Elegir ${radioElem.value}`
                                
                                this.html.resetValidationMessage()
                                
                                axios({
                                    method: 'get',
                                    url: `/api/${radioElem.value}s?lista=simple`,
                                    headers: {
                                        Authorization: `Bearer ${this.config('claveApi')}`
                                    }
                                })
                                .then(respuesta => {
                                    const registros = respuesta.data
                                    
                                    if (radioElem.value === 'bodega') {
                                        const indiceBodegaActual = _.findIndex(registros, { id: unidad.bodega_id })
                                        if (indiceBodegaActual > -1) {
                                            registros.splice(indiceBodegaActual, 1)
                                        }
                                    } else {
                                        const indiceProyectoActual = _.findIndex(registros, { id: unidad.proyecto_id })
                                        if (indiceProyectoActual > -1) {
                                            registros.splice(indiceProyectoActual, 1)
                                        }
                                    }
                                    
                                    if (registros.length > 0) {
                                        const seleccionElem = form.querySelector('select')
                                        const opcionElem = document.createElement('option')
                                        
                                        opcionElem.setAttribute('value', '')
                                        opcionElem.textContent = `Seleccionar ${radioElem.value}...`
                                        
                                        seleccionElem.innerHTML = ''
                                        seleccionElem.appendChild(opcionElem)
                                        
                                        registros.forEach(registro => {
                                            const opcionElem = document.createElement('option')
                                            opcionElem.setAttribute('value', registro.id)
                                            opcionElem.textContent = registro.nombre
                                            seleccionElem.appendChild(opcionElem)
                                        })
                                    } else {
                                        form.classList.add('sin-registros')
                                    }
                                    
                                    form.classList.add('cargado')
                                })
                                .catch (error => {
                                    this.html.showValidationMessage(error.response ? error.response.data.message : 'Error inesperado.')
                                })
                            })
                        })
                    },
                    preConfirm: async () => {
                        const form = this.html.getHtmlContainer().querySelector('form')
                        const radioElem = form.querySelector('input:checked')
                        
                        this.html.resetValidationMessage()
                        
                        if (!radioElem) {
                          this.html.showValidationMessage('Elige una opción.')
                        } else {
                            const seleccionElem = form.querySelector('select')
                            if (!seleccionElem.value) {
                                this.html.showValidationMessage('Selecciona un destino.')
                            } else {
                                form.classList.add('inhabilitado')
                                const respuesta = await axios({
                                    method: 'put',
                                    url: `/api/unidades/${unidad.id}/mover`,
                                    headers: {
                                        Authorization: `Bearer ${this.config('claveApi')}`
                                    },
                                    data: {
                                        destino: radioElem.value,
                                        destino_id: seleccionElem.value
                                    }
                                })
                                .then(respuesta => {
                                    this.notificacion.fire({
                                        icon: 'success',
                                        title: 'Unidad movida.'
                                    })
                                    this.$emit('recargarLista')
                                })
                                .catch(error => {
                                    this.html.showValidationMessage(error.response ? error.response.data.message : 'Error inesperado.')
                                    form.classList.remove('inhabilitado')
                                })
                                return respuesta
                            }
                        }
                        
                        return false
                    }
                })
            },
            
            darDeBaja(unidad) {
                let motivoDeBaja = null
                const solicitud = () => axios({
                    method: 'put',
                    url: `/api/unidades/${unidad.id}/baja`,
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    },
                    data: {
                        motivo_de_baja: motivoDeBaja
                    }
                })
                .then(respuesta => {
                    this.notificacion.fire({
                        icon: 'success',
                        title: respuesta.data.message
                    })
                    this.$emit('recargarLista')
                })
                .catch(error => {
                    this.mostrarError(error)
                })
                
                if (unidad.dado_de_baja) {
                    this.confirmacion.fire({
                        text: 'Esta unidad ha sido dada de baja, ¿deseas restaurarla?'
                    })
                    .then(respuesta => {
                        if (respuesta.isConfirmed) {
                            solicitud()
                        }
                    })
                } else {
                    this.campoAreaTexto.fire({
                        text: '¿Deseas dar de baja esta unidad?',
                        confirmButtonText: 'Dar de baja',
                        inputAttributes: {
                            maxlength: 500,
                            placeholder: 'Razón de baja (campo opcional)...'
                        },
                        inputValidator: async (motivo) => {
                            motivoDeBaja = motivo
                            solicitud()
                        }
                    })
                }
            },
            
            eliminar(id) {
                this.confirmacion.fire({
                    text: '¿Deseas eliminar esta unidad del producto? Esta acción no se puede revertir.'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: `/api/unidades/${id}`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Unidad del producto eliminada.'
                            })
                            this.$emit('recargarLista')
                        })
                        .catch(error => {
                            this.mostrarError(error)
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
    :deep(.depreciacion.verde) a {
        color: var(--color-verde-2);
    }
    
    :deep(.depreciacion.naranja) a {
        color: var(--color-naranja-5);
    }
    
    :deep(.depreciacion.rojo) a {
        color: var(--color-rojo-4);
    }
</style>