<template>
    <template v-if="cargado">
        <Error :error="error" v-if="error"/>
        <div class="envoltorio" v-else>
            <h2>Editar {{ nombre.singular }}</h2>
            <component :is="componente" :registro="registro"/>
        </div>
    </template>
    <IconoCargando v-else/>
</template>

<script>
    import BodegaFormulario from './InventarioPrincipalBodegasItemFormulario.vue'
    import ProductoFormulario from './InventarioPrincipalProductosItemFormulario.vue'
    import UnidadFormulario from './InventarioPrincipalUnidadesItemFormulario.vue'
    import ProveedorFormulario from './InventarioPrincipalProveedoresItemFormulario.vue'
    import FabricanteFormulario from './InventarioPrincipalFabricantesItemFormulario.vue'
    import ProyectoFormulario from './InventarioPrincipalProyectosItemFormulario.vue'
    import UsuarioFormulario from './InventarioPrincipalUsuariosItemFormulario.vue'
    import IconoCargando from './BaseIconoCargando.vue'
    import Error from './BaseMensajeError.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    
    export default {
        name: 'ItemEdicion',
        components: {
            BodegaFormulario,
            ProductoFormulario,
            UnidadFormulario,
            ProveedorFormulario,
            FabricanteFormulario,
            ProyectoFormulario,
            UsuarioFormulario,
            IconoCargando,
            Error
        },
        mixins: [
            ConfigMixin
        ],
        data() {
            return {
                cargado: false,
                error: null
            }
        },
        computed: {
            nombre() {
                const ruta = this.$route.path
                const patron = /(\w+)(?:\/\d+\/(?:editar|registrar))$/gi
                const nombreDir = _.flatten((ruta.match(patron) || []).map(el => el.replace(patron, '$1')));
                const numGramal = { singular: '', plural: '' }
                
                if (nombreDir.length) {
                    numGramal.singular = nombreDir[0].slice(0, -1).replace(/re$/gi, 'r').replace(/de$/gi, 'd')
                    numGramal.plural = nombreDir[0]
                }
                
                return numGramal
            },
            id() {
                const claveParam = `${this.nombre.singular}Id`
                return this.$route.params[claveParam]
            },
            componente() {
                return `${_.capitalize(this.nombre.singular)}Formulario`
            },
            rutaApi() {
                return this.nombre.plural
            }
        },
        watch: {
            id(id) {
                if(id) {
                    this.cargado = false
                    this.verRegistro()
                }
            }
        },
        created() {
            this.verRegistro()
        },
        methods: {
            verRegistro () {
                axios({
                    method: 'get',
                    url: `/api/${this.rutaApi}/${this.id}`,
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.registro = respuesta.data
                })
                .catch(error => {
                    this.error = error.response || true
                })
                .finally (() => {
                    this.cargado = true
                })
            }
        }
    }
</script>