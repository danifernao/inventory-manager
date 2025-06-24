<template>
    <thead>
        <tr>
            <Celda clase="imagen"/>
            <Celda clase="nombres" titulo="Nombres" columna="nombres" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="apellidos" titulo="Apellidos" columna="apellidos" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="identificacion" titulo="Identificación" columna="identificacion" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="genero" titulo="Género" columna="genero" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="correo" titulo="Correo" columna="correo" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="fecha-registro" titulo="Fecha de registro" columna="created_at" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="tipo-usuario" titulo="Tipo de usuario" columna="es_administrador" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="estado-usuario" titulo="Estado del usuario" columna="esta_habilitado" :ordenarPor="ordenarPor" :ordenarEn="ordenarEn"/>
            <Celda clase="acciones" titulo="Acciones"/>
        </tr>
    </thead>
</template>

<script>
    import CabeceraCelda from './InventarioPrincipalBaseListaTablaCabeceraCelda.vue'
    
    export default {
        name: 'TablaCabeceraUsuarios',
        components: {
            Celda: CabeceraCelda,
        },
        props: {
            ordenarPor: String,
            ordenarEn: String
        }
    }
</script>