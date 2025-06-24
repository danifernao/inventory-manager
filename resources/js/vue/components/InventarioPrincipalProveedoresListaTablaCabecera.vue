<template>
    <thead>
        <tr>
            <Celda clase="imagen"/>
            <Celda clase="nombre" titulo="Nombre" columna="nombre" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="nit" titulo="NIT" columna="nit" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="ubicacion" titulo="Ubicación" columna="ubicacion" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="nro-unidades" titulo="Nro. unidades" columna="unidades_count" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn" v-if="$route.query.lista !== 'completa'"/>
            <Celda clase="acciones" titulo="Acciones"/>
        </tr>
    </thead>
</template>

<script>
    import CabeceraCelda from './InventarioPrincipalBaseListaTablaCabeceraCelda.vue'
    
    export default {
        name: 'TablaCabeceraProveedores',
        components: {
            Celda: CabeceraCelda,
        },
        props: {
            ordenarPor: String,
            ordenarEn: String
        }
    }
</script>