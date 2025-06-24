<template>
    <thead>
        <tr>
            <Celda clase="nombre" titulo="Nombre" columna="nombre" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="codigo" titulo="Código" columna="codigo" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="ubicacion" titulo="Ubicación" columna="ubicacion" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="fecha-apertura" titulo="Fecha de apertura" columna="fecha_apertura" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="fecha-clausura" titulo="Fecha de clausura" columna="fecha_clausura" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="nro-unidades" titulo="Nro. unidades" columna="unidades_count" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="estado" titulo="Estado" columna="fecha_clausura" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="acciones" titulo="Acciones"/>
        </tr>
    </thead>
</template>

<script>
    import CabeceraCelda from './InventarioPrincipalBaseListaTablaCabeceraCelda.vue'
    
    export default {
        name: 'TablaCabeceraProyectos',
        components: {
            Celda: CabeceraCelda,
        },
        props: {
            ordenarPor: String,
            ordenarEn: String
        }
    }
</script>