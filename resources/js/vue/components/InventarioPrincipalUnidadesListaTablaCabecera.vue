<template>
    <thead>
        <tr>
            <Celda tipo="seleccion" clase="seleccion" @accion="seleccionar"/>
            <Celda clase="nro-serie" titulo="Nro. de serie" columna="numero_serie" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="proveedor" titulo="Proveedor" columna="proveedor_nombre" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="!$route.params.proveedorId"/>
            <Celda clase="fecha-adquisicion" titulo="Fecha de adquisición" columna="fecha_adquisicion" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="valor-adquisicion" titulo="Valor de adquisición" columna="valor_adquisicion" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="fecha-instalacion" titulo="Fecha de instalación" columna="fecha_ingreso_proyecto" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="estaEnProyecto"/>
            <Celda clase="valor-residual" titulo="Valor residual" columna="valor_residual" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="estaEnProyecto"/>
            <Celda clase="depreciacion" titulo="Depreciación" columna="depreciacion" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="estaEnProyecto"/>
            <Celda clase="fecha-baja" titulo="Fecha de baja" columna="fecha_de_baja" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="dadoDeBaja"/>
            <Celda clase="motivo-baja" titulo="Motivo de baja" v-if="dadoDeBaja"/>
            <Celda clase="acciones" titulo="Acciones"/>
        </tr>
    </thead>
</template>

<script>
    import CabeceraCelda from './InventarioPrincipalBaseListaTablaCabeceraCelda.vue'
    import RutaMixin from '../mixins/rutaMixin.js'
    
    export default {
        name: 'TablaCabeceraUnidades',
        components: {
            Celda: CabeceraCelda,
        },
        mixins: [
            RutaMixin
        ],
        props: {
            ordenarPor: String,
            ordenarEn: String
        },
        computed: {
            estaEnProyecto() {
                return this.ruta('proyectos')
            },
            dadoDeBaja() {
                return this.$route.query.dadoDeBaja === '1' ? true : false
            }
        },
        methods: {
            seleccionar(chequeoElem) {
                const elems = document.querySelectorAll('#principal .listado tbody input')
                elems.forEach(elem => {
                    elem.checked = chequeoElem.checked
                })
            }
        }
    }
</script>