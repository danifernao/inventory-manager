<template>
    <aside :class="{ visible: visible, flotante: esVersionMovil }">
        <div class="principal">
            <MenuConfiguracion v-if="ruta('configuracion')"/>
            <template v-else>
                <MenuBodegas @menu-bodegas-cargado="cargarMenus"/>
                <MenuInventario :bodegaId="bodegaId" v-if="menuBodegasCargado && bodegaId"/>
            </template>
        </div>
        <footer v-if="$store.state.acercaDe">
            <p class="version" v-if="$store.state.acercaDe.version">{{ $store.state.acercaDe.version }}</p>
            <p class="autor" v-if="$store.state.acercaDe.autor">{{ $store.state.acercaDe.autor }}</p>
            <p class="licencia" v-if="$store.state.acercaDe.licencia">{{ $store.state.acercaDe.licencia }}</p>
        </footer>
    </aside>
</template>

<script>
    import MenuBodegas from '../components/InventarioLateralMenuBodegas.vue'
    import MenuInventario from '../components/InventarioLateralMenuInventario.vue'
    import MenuConfiguracion from '../components/InventarioLateralMenuConfiguracion.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import RutaMixin from '../mixins/rutaMixin.js'
    import MovilMixin from '../mixins/movilMixin.js'
    
    export default {
        name: 'Lateral',
        components: {
            MenuBodegas,
            MenuInventario,
            MenuConfiguracion
        },
        mixins: [
            ConfigMixin,
            RutaMixin,
            MovilMixin
        ],
        props: {
            visible: Boolean
        },
        data() {
            return {
                menuBodegasCargado: false
            }
        },
        computed: {
            bodegaId() {
                return this.config('bodegaDefectoId')
            }
        },
        methods: {
            cargarMenus() {
                this.menuBodegasCargado = true
                this.$emit('lateralCargado')
            }
        }
    }
</script>

<style scoped>
    aside {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 15rem;
        background-color: var(--color-purpura-7);
        color: var(--color-blanco-1);
        transition: 300ms width ease-out;
        overflow-x: hidden;
        flex-shrink: 0;
        white-space: nowrap;
    }
    
    aside.flotante {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 500;
    }
    
    aside:not(.visible) {
        width: 0;
    }
    
    .principal {
        flex: 1;
    }
    
    :deep(.menu) {
        display: flex;
        flex-direction: column;
    }
    
    :deep(.menu) h3 {
        margin: 0;
        background-color: var(--color-purpura-9);
        color: var(--color-purpura-3);
        font-size: 0.9rem;
    }
    
    :deep(.menu) h3,
    :deep(.menu) a {
        padding: 0.8rem 1rem;
    }
    
    :deep(.menu) ul {
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    :deep(.menu) a {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        border-left: 0.2rem solid transparent;
        color: var(--color-purpura-2);
        font-size: 0.9rem;
    }
    
    :deep(.menu) a:hover,
    :deep(.menu) a.activo {
        background-color: var(--color-purpura-8);
        border-left-color: var(--color-naranja-3) !important;
        color: var(--color-blanco-1);
    }
    
    :deep(.menu) svg {
        flex-shrink: 0;
        width: 1.5rem;
        text-align: center;
    }
    
    footer {
        padding: 0.5rem;
    }
    
    footer p {
        margin: 0;
        font-size: 0.7rem;
        color: var(--color-purpura-2);
        text-align: center;
    }
    
    @media only screen and (max-width: 760px) {
        :deep(.menu) a:not(.activo):hover {
            background-color: transparent;
            border-left-color: transparent !important;
            color: var(--color-purpura-2);
        }
    }
</style>