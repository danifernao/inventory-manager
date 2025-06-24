<template>
    <thead>
        <tr>
            <Celda clase="imagen"/>
            <Celda clase="fabricante" titulo="Fabricante" columna="fabricante" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="!$route.params.fabricanteId"/>
            <Celda clase="marca" titulo="Marca" columna="marca" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="modelo" titulo="Modelo" columna="modelo" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="nro-unidades" titulo="Nro. unidades" columna="unidades_count" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="$route.query.lista !== 'completa'"/>
            <Celda clase="acciones" titulo="Acciones"/>
        </tr>
    </thead>
</template>

<script>
    import CabeceraCelda from './InventarioPrincipalBaseListaTablaCabeceraCelda.vue'
    
    export default {
        name: 'TablaCabeceraProductos',
        components: {
            Celda: CabeceraCelda,
        },
        props: {
            ordenarPor: String,
            ordenarEn: String
        },
        computed: {
            rutaEsProyecto() {
                return this.$route.params.proyectoId || this.$route.params.seccion === 'proyectos'
            }
        }
    }
</script>