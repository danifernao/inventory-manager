<template>
    <div :class="tipo">
        <font-awesome-icon icon="circle-info" v-if="tipo === 'informacion'"/>
        <font-awesome-icon icon="circle-exclamation" v-if="tipo === 'importante'"/>
        <p v-html="mensaje"></p>
    </div>
</template>

<script>
  export default {
    name: 'Aviso',
    props: {
      tipo: String,
      mensaje: String
    }
  }
</script>

<style scoped>
    div {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.3rem;
        margin-bottom: 1rem;
        padding: 0.5rem 0.8rem;
        border-left-width: 0.3rem;
        border-left-style: solid;
        border-top-right-radius: 0.3rem;
        border-bottom-right-radius: 0.3rem;
    }
    
    svg {
        font-size: 2rem;
    }
    
    p {
        flex: 1;
        margin: 0;
        font-size: 0.8rem;
    }
    
    .informacion {
        background-color: var(--color-azul-1);
        border-left-color: var(--color-azul-2);
    }
    
    .informacion svg {
        color: var(--color-azul-2);
    }
    
    .informacion :deep(a) {
        color: var(--color-azul-6);
    }
    
    .importante {
        background-color: var(--color-naranja-1);
        border-left-color:  var(--color-naranja-3);
    }
    
    .importante svg {
        color: var(--color-naranja-3);
    }
</style>