<template>
    <div class="envoltorio">
        <h2>Registrar {{ nombre.singular === 'unidad' ? nombre.plural : nombre.singular }}</h2>
        <component :is="componente"/>
    </div>
</template>

<script>
    import BodegaFormulario from './InventarioPrincipalBodegasItemFormulario.vue'
    import ProductoFormulario from './InventarioPrincipalProductosItemFormulario.vue'
    import UnidadFormulario from './InventarioPrincipalUnidadesItemFormulario.vue'
    import ProveedorFormulario from './InventarioPrincipalProveedoresItemFormulario.vue'
    import FabricanteFormulario from './InventarioPrincipalFabricantesItemFormulario.vue'
    import ProyectoFormulario from './InventarioPrincipalProyectosItemFormulario.vue'
    import UsuarioFormulario from './InventarioPrincipalUsuariosItemFormulario.vue'
    
    export default {
        name: 'FabricanteRegistro',
        components: {
            BodegaFormulario,
            ProductoFormulario,
            UnidadFormulario,
            ProveedorFormulario,
            FabricanteFormulario,
            ProyectoFormulario,
            UsuarioFormulario
        },
        computed: {
            nombre() {
                const ruta = this.$route.path
                const patron = /(\w+)(?:\/(?:editar|registrar))$/gi
                const nombreDir = _.flatten(ruta.match(patron).map(el => el.replace(patron, '$1')));
                const numGramal = { singular: '', plural: ''}
                
                numGramal.singular = nombreDir[0].slice(0, -1).replace(/re$/gi, 'r').replace(/de$/gi, 'd')
                numGramal.plural = nombreDir[0]
                
                return numGramal
            },
            componente() {
                return `${_.capitalize(this.nombre.singular)}Formulario`
            }
        }
    }
</script>