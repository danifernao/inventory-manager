<template>
    <div class="menu">
        <h3>INVENTARIO</h3>
        <nav>
            <ul>
                <li>
                    <router-link :to="'/bodegas/' + bodegaId" :class="{ activo: ruta('bodegas') }" @click.prevent>
                        <font-awesome-icon icon="box"/>
                        <span>Productos</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="'/bodegas/' + bodegaId + '/proveedores'" :class="{ activo: ruta('proveedores') }" @click.prevent>
                        <font-awesome-icon icon="truck-ramp-box"/>
                        <span>Proveedores</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="'/bodegas/' + bodegaId + '/fabricantes'" :class="{ activo: ruta('fabricantes') }" @click.prevent>
                        <font-awesome-icon icon="industry"/>
                        <span>Fabricantes</span>
                    </router-link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    import RutaMixin from '../mixins/rutaMixin.js'
    
    export default {
        name: 'MenuInventario',
        mixins: [
            RutaMixin
        ],
        props: {
            bodegaId: Number
        }
    }
</script>