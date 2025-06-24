<template>
    <div id="icono">
        <div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'IconoCargando'
    }
</script>

<style scoped>
    #icono {
        margin-top: auto;
        margin-bottom: auto;
        width: 3.1rem;
        height: 3.1rem;
        display: inline-block;
        background: none;
        overflow: hidden;
    }
    
    #icono > div {
        width: 100%;
        height: 100%;
        position: relative;
        transform: translateZ(0) scale(0.5);
        backface-visibility: hidden;
        transform-origin: 0 0;
    }
    
    #icono > div > div {
        position: absolute;
        top: 1.9rem;
        left: 0.6rem;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        box-sizing: content-box;
    }
    
    #icono > div > div:nth-child(1) {
        background: rgb(67, 41, 116);
        animation: animacion-2 1s linear infinite;
        animation-delay: -0.5s;
    }
    
    #icono > div > div:nth-child(2) {
        background: rgba(255, 122, 24);
        animation: animacion-2 1s linear infinite;
        animation-delay: 0s;
    }
    
    #icono > div > div:nth-child(3) {
        background: rgb(67, 41, 116);
        animation: animacion-1 1s linear infinite;
        animation-delay: -0.5s;
    }
    
    @keyframes animacion-1 {
        0% {
            opacity: 1;
            transform: translate(0, 0);
        }
        
        49.99% {
            opacity: 1;
            transform: translate(2.5rem, 0);
        }
        
        50% {
            opacity: 0;
            transform: translate(2.5rem, 0);
        }
        
        100% {
            opacity: 0;
            transform: translate(0, 0);
        }
    }
    
    @keyframes animacion-2 {
        0% {
            transform: translate(0, 0);
        }
        
        50% {
            transform: translate(2.5rem, 0);
        }
        
        100% {
            transform: translate(0, 0);
        }
    }
</style>