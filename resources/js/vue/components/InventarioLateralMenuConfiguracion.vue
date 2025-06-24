<template>
    <div class="menu">
        <h3>CONFIGURACIÓN</h3>
        <nav>
            <ul>
                <li>
                    <router-link :to="'/configuracion/usuarios/' + $store.state.usuario.id + '/editar'" :class="{ activo: parseInt($route.params.usuarioId) === $store.state.usuario.id }">
                        <font-awesome-icon icon="user-gear"/>
                        <span>Cuenta</span>
                    </router-link>
                </li>
                <li v-if="$store.state.usuario.es_administrador">
                    <router-link to="/configuracion/usuarios" :class="{ activo: ruta('usuariosLista') || ruta('usuarioRegistro') || (ruta('usuarioEdicion') && parseInt($route.params.usuarioId) !== $store.state.usuario.id) }">
                        <font-awesome-icon icon="users-gear"/>
                        <span>Usuarios</span>
                    </router-link>
                </li>
                <li v-if="$store.state.usuario.es_administrador">
                    <router-link to="/configuracion/aplicacion/editar" :class="{ activo: ruta('aplicacionEdicion') }">
                        <font-awesome-icon icon="window-maximize"/>
                        <span>Aplicación</span>
                    </router-link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    import RutaMixin from '../mixins/rutaMixin.js'
    
    export default {
        name: 'MenuConfiguracion',
        mixins: [
            RutaMixin
        ]
    }
</script>