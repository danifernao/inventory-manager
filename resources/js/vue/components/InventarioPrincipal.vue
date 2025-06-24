<template>
    <div id="principal">
        <router-view v-if="lateralCargado || configuracionVisible || expandido"></router-view>
        <IconoCargando v-else/>
    </div>
</template>

<script>
    import IconoCargando from './BaseIconoCargando.vue'
    
    export default {
        name: 'Principal',
        components: {
            IconoCargando
        },
        props: {
            lateralCargado: Boolean,
            proyectosVisible: Boolean,
            configuracionVisible: Boolean,
            expandido: Boolean
        }
    }
</script>

<style scoped>
    #principal {
        align-items: center;
        min-width: 0;
        min-height: min-content;
        overflow: auto;
    }
    
    #principal,
    :deep(.envoltorio) {
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    
    :deep(.envoltorio) {
        padding: 1rem;
        width: 100%;
    }
    
    :deep(h2) {
        margin: 0;
        font-size: 1.2rem;
    }
    
    :deep(form),
    :deep(form) .seccion {
        display: flex;
        flex-direction: column;
    }
    
    :deep(form) {
        margin-top: 1rem;
        padding: 1.3rem 1rem;
        background-color: var(--color-blanco-1);
        border: 0.06rem solid var(--color-gris-1);
        border-radius: 0.3rem;
    }
    
    :deep(form) .seccion {
        gap: 0.5rem;
    }
    
    :deep(form) .seccion:not(:last-of-type) {
        margin-bottom: 1.5rem;
    }
    
    :deep(form) input[type="text"],
    :deep(form) input[type="email"],
    :deep(form) input[type="password"],
    :deep(form) select,
    :deep(form) .seccion .registrar {
        width: 50%;
    }
    
    :deep(form) .seccion .registrar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        font-size: 0.9rem;
    }
    
    :deep(form) .verificacion {
        flex-direction: row;
        align-items: flex-start;
    }
    
    :deep(form) .verificacion label {
        flex: 1;
    }
    
    :deep(form) .nota {
        font-size: 0.9rem;
    }
    
    :deep(form) .botones {
        display: flex;
        flex-direction: row;
        gap: 0.3rem;
    }
    
    :deep(form) .botones div:first-of-type {
        flex: 1;
    }
    
    :deep(form) .botones button:not(:first-of-type) {
        margin-left: 0.5rem;
    }
    
    :deep(form) .botones div:last-of-type {
        text-align: right;
    }
    
    :deep(.inhabilitado) {
        pointer-events: none;
    }
    
    :deep(.inhabilitado) input,
    :deep(.inhabilitado) button,
    :deep(.inhabilitado) select {
        opacity: 0.5;
    }
    
    :deep(.operaciones) {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        width: 100%;
        gap: 1rem;
    }
    
    :deep(table) {
        width: 100%;
        background-color: var(--color-blanco-1);
        border: 0.06rem solid var(--color-gris-2);
        border-top-width: 0.2rem;
        border-radius: 0.3rem;
        border-spacing: 0;
    }
    
    :deep(th) {
        padding: 0.5rem;
        background-color: var(--color-blanco-3);
        border-bottom: 0.12rem solid var(--color-gris-3);
        text-align: left;
    }
    
    :deep(th),
    :deep(td) {
        white-space: nowrap;
    }
    
    :deep(th) {
        text-align: center;
    }
    
    :deep(th) a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--color-negro-1);
    }
    
    :deep(th) a:hover span {
        text-decoration: underline;
    }
    
    :deep(tbody) {
        background-color: var(--color-blanco-1);
    }
    
    :deep(tbody) tr td:first-of-type {
        border: 0.3rem solid transparent;
    }
    
    :deep(tbody) tr:hover td {
        background-color: var(--color-blanco-3) !important;
    }
    
    :deep(tbody) tr:hover td:first-of-type {
        border-left-color: var(--color-gris-3);
    }
    
    :deep(tbody) tr:nth-child(2n) {
        background-color: var(--color-blanco-2);
    }
    
    :deep(tbody) tr:not(:last-child) td {
        border-bottom: 0.06rem solid var(--color-gris-2);
    }
    
    :deep(td) {
        padding: 0.1rem 0.5rem;
    }
    
    :deep(td) a {
        color: var(--color-negro-1);
    }
    
    :deep(tr) > th:not(:last-child),
    :deep(tr) > td:not(:last-child) {
        border-right: 0.06rem solid var(--color-gris-2) !important;
        white-space: normal;
    }
    
    :deep(td.vacio) {
        padding: 1rem;
        text-align: center;
    }
    
    @media only screen and (max-width: 760px) {
        :deep(form) input[type="text"],
        :deep(form) input[type="email"],
        :deep(form) input[type="password"],
        :deep(form) select,
        :deep(form) .seccion .registrar {
            width: 100%;
        }
        
        :deep(.operaciones) {
            flex-direction: column;
            flex-wrap: wrap;
            align-items: start;
        }
        
        :deep(.operaciones) .opciones {
            gap: 0.3rem;
        }
        
        :deep(.operaciones) .opciones li a {
            border-right-width: 0.06rem !important;
            border-radius: 0.3rem !important;
        }
    }
</style>