<template>
    <template v-if="cargado">
        <Error :error="error" v-if="error"/>
        <div class="envoltorio" v-else>
            <MigasDePan/>
            <Cabecera tipo="producto" :registro="producto"/>
            <div class="operaciones">
                <Buscador consejo="Buscar por número de serie o proveedor"/>
                <Opciones :opciones="opsOperaciones" @accion="exportar"/>
            </div>
            <Lista componente="unidades" ordenPor="numero_serie" :titulo="tituloLista" :opciones="opsLista" :recargarLista="recargarLista" @listaRecargada="recargarLista = false" @accion="ejecutar" @dado-de-baja="estaDeBaja" @api-lista="obtenerApiUrl"/>
            <Archivo :url="archivoUrl" nombre="unidades.xlsx" v-if="archivoUrl" @descargado="descargaFinalizada"/>
        </div>
    </template>
    <IconoCargando v-else/>
</template>

<script>
    import MigasDePan from './InventarioPrincipalBaseMigasDePan.vue'
    import Cabecera from './InventarioPrincipalBaseItemCabecera.vue'
    import Buscador from './InventarioPrincipalBaseBuscador.vue'
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    import Lista from './InventarioPrincipalBaseLista.vue'
    import Archivo from './InventarioPrincipalBaseArchivo.vue'
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ArchivoMixin from '../mixins/archivoMixin.js'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Producto',
        components: {
            MigasDePan,
            Cabecera,
            Buscador,
            Opciones,
            Lista,
            Archivo,
            IconoCargando,
            Error
        },
        mixins: [
            ArchivoMixin,
            ConfigMixin,
            RutaMixin,
            SwalMixin
        ],
        data() {
            return {
                dadoDeBaja: false,
                cargado: false,
                error: null,
                recargarLista: false,
                apiUrlLista: null
            }
        },
        computed: {
            opsOperaciones() {
                return [
                    {
                        url: `${this.$route.path}/unidades/registrar`,
                        texto: 'Registrar unidades',
                        icono: 'plus',
                        oculto: this.ruta('proyectos')
                    },
                    {
                        texto: 'Exportar listado',
                        icono: 'file-export'
                    },
                    {
                        texto: 'Filtrar listado',
                        icono: 'filter',
                        menu: [
                            {
                                url: `${this.$route.path}?dadoDeBaja=0`,
                                texto: 'Mostrar unidades disponibles',
                                activo: !this.dadoDeBaja
                            },
                            {
                                url: `${this.$route.path}?dadoDeBaja=1`,
                                texto: 'Mostrar unidades dadas de baja',
                                activo: this.dadoDeBaja
                            }
                        ]
                    }
                ]
            },
            
            opsLista() {
                return [
                    {
                        texto: 'Acciones',
                        menu: [
                            {
                                texto: 'Mover unidades',
                                params: {
                                    accion: 'mover'
                                }
                            },
                            {
                                texto: this.dadoDeBaja ? 'Restaurar unidades' : 'Dar de baja unidades',
                                params: {
                                    accion: 'darDeBaja'
                                }
                            },
                            {
                                texto: 'Eliminar unidades',
                                params: {
                                    accion: 'eliminar'
                                }
                            }
                        ]
                    }
                ]
            },
            
            tituloLista() {
                return `Unidades ${this.dadoDeBaja ? 'dadas de baja' : 'activas'}`
            }
        },
        created() {
            axios({
                method: 'get',
                url: `/api/productos/${this.$route.params.productoId}${this.ruta('bodegaProducto') ? '?bodega_id=' + this.$route.params.bodegaId : ''}`,
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                }
            })
            .then(respuesta => {
                this.producto = respuesta.data
            })
            .catch(error => {
                this.error = error.response || true
            })
            .finally(() => {
                this.cargado = true
            })
        },
        methods: {
            ejecutar (params) {
                switch (params.accion) {
                    case 'mover':
                        this.mover()
                        break
                    case 'darDeBaja':
                        this.darDeBaja()
                        break
                    case 'eliminar':
                        this.eliminar()
                        break
                }
            },
            
            mover() {
                const elems = document.querySelectorAll('#principal .listado tbody input:checked')
                
                if (elems.length > 0) {
                    const unidades_id = _.map(elems, 'value')
                    
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
                                        const bodegaActualId = parseInt(this.$route.params.bodegaId)
                                        const proyectoActualId = parseInt(this.$route.params.proyectoId)
                                        
                                        if (bodegaActualId && radioElem.value === 'bodega') {
                                            const indiceBodegaActual = _.findIndex(registros, { id: bodegaActualId })
                                            if (indiceBodegaActual > -1) {
                                                registros.splice(indiceBodegaActual, 1)
                                            }
                                        }
                                        
                                        if (proyectoActualId && radioElem.value === 'proyecto') {
                                            const indiceProyectoActual = _.findIndex(registros, { id: proyectoActualId })
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
                                    .catch(error => {
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
                                        url: `/api/unidades/mover`,
                                        headers: {
                                            Authorization: `Bearer ${this.config('claveApi')}`
                                        },
                                        data: {
                                            destino: radioElem.value,
                                            destino_id: seleccionElem.value,
                                            unidades_id: unidades_id.join(',')
                                        }
                                    })
                                    .then(respuesta => {
                                        this.notificacion.fire({
                                            icon: 'success',
                                            title: 'Unidades movidas.'
                                        })
                                        this.recargarLista = true
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
                } else {
                    this.notificacion.fire({
                        icon: 'warning',
                        title: 'Unidades requeridas',
                        text: 'No se ha seleccionado ninguna unidad.'
                    })
                }
            },
            
            darDeBaja() {
                const elems = document.querySelectorAll('#principal .listado tbody input:checked')
                
                if (elems.length > 0) {
                    let motivoDeBaja = null
                    const unidades_id = _.map(elems, 'value')
                    
                    const solicitud = () => axios({
                        method: 'put',
                        url: `/api/unidades/baja`,
                        headers: {
                            Authorization: `Bearer ${this.config('claveApi')}`
                        },
                        data: {
                            unidades_id: unidades_id.join(','),
                            restauracion: this.dadoDeBaja,
                            motivo_de_baja: motivoDeBaja
                        }
                    })
                    .then(respuesta => {
                        this.notificacion.fire({
                            icon: 'success',
                            title: respuesta.data.message
                        })
                        this.recargarLista = true
                    })
                    .catch(error => {
                        this.mostrarError(error)
                    })
                    
                    if (this.dadoDeBaja) {
                        this.confirmacion.fire({
                            text: '¿Desea restaurar las unidades seleccionadas?'
                        })
                        .then(respuesta => {
                            if (respuesta.isConfirmed) {
                                solicitud()
                            }
                        })
                    } else {
                        this.campoAreaTexto.fire({
                            text: '¿Deseas dar de baja a las unidades seleccionadas?',
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
                } else {
                    this.notificacion.fire({
                        icon: 'warning',
                        title: 'Unidades requeridas',
                        text: 'No se ha seleccionado ninguna unidad.'
                    })
                }
            },
            
            eliminar() {
                const elems = document.querySelectorAll('#principal .listado tbody input:checked')
                
                if (elems.length > 0) {
                    const unidades_id = _.map(elems, 'value')
                    
                    this.confirmacion.fire({
                        text: '¿Deseas eliminar las unidades seleccionadas? Esta acción no se puede revertir.'
                    })
                    .then(respuesta => {
                        if (respuesta.isConfirmed) {
                            axios({
                                method: 'delete',
                                url: '/api/unidades',
                                headers: {
                                    Authorization: `Bearer ${this.config('claveApi')}`
                                },
                                data: {
                                    unidades_id: unidades_id.join(',')
                                }
                            })
                            .then(respuesta => {
                                this.notificacion.fire({
                                    icon: 'success',
                                    title: 'Unidades eliminadas.'
                                })
                                this.recargarLista = true
                            })
                            .catch(error => {
                                this.mostrarError(error)
                            })
                        }
                    })
                } else {
                    this.notificacion.fire({
                        icon: 'warning',
                        title: 'Unidades requeridas',
                        text: 'No se ha seleccionado ninguna unidad.'
                    })
                }
            },
            
            mostrarError(error) {
                const estado = error.response ? error.response.status : ''
                const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                
                this.notificacion.fire({
                    icon: 'error',
                    title: `Error ${estado}`,
                    text: mensaje
                })
            },
            
            estaDeBaja(respuesta) {
                this.dadoDeBaja = respuesta
            }
        }
    }
</script>