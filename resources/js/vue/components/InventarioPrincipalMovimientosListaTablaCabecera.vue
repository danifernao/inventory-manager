<template>
    <thead>
        <tr>
            <Celda clase="fecha" titulo="Fecha" columna="created_at" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="fuente" titulo="De"/>
            <Celda clase="destino" titulo="Hacia"/>
        </tr>
    </thead>
</template>

<script>
    import CabeceraCelda from './InventarioPrincipalBaseListaTablaCabeceraCelda.vue'
    
    export default {
        name: 'TablaCabeceraMovimientos',
        components: {
            Celda: CabeceraCelda,
        },
        props: {
            ordenarPor: String,
            ordenarEn: String
        }
    }
</script>