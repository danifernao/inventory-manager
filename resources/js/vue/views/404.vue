<template>
    <div>
        <h1>404</h1>
        <p>Página no econtrada</p>
        <p><router-link to="/">Inicio</router-link></p>
    </div>
</template>

<script>
    export default {
        name: 'NoEncontrado'
    }
</script>

<style scoped>
    div {
        padding: 1rem;
        text-align: center;
    }
    
    h1 {
        margin: 0;
        font-size: 5rem;
    }
    
    p:first-of-type {
        margin: 0;
    }
</style>