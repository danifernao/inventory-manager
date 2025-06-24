<template>
    <div id="inventario" v-if="$store.state.usuario">
        <BarraSuperior :lateralVisible="lateralVisible" :principalExpandido="principalExpandido" @alternar-lateral="alternarLateral" @ocultar-lateral="ocultarLateral"/>
        <div id="cuerpo">
            <Lateral :visible="lateralVisible" v-if="!principalExpandido" @lateral-cargado="cargarPrincipal"/>
            <Principal :lateralCargado="lateralCargado" :expandido="principalExpandido" :configuracionVisible="configuracionVisible"/>
        </div>
    </div>
</template>

<script>
    import BarraSuperior from '../components/InventarioBarraSuperior.vue'
    import Lateral from '../components/InventarioLateral.vue'
    import Principal from '../components/InventarioPrincipal.vue'
    import RutaMixin from '../mixins/rutaMixin.js'
    import MovilMixin from '../mixins/movilMixin.js'
    
    export default {
        name: 'Inventario',
        components: {
            BarraSuperior,
            Lateral,
            Principal
        },
        mixins: [
            RutaMixin,
            MovilMixin
        ],
        data() {
            return {
                lateralVisible: true,
                lateralCargado: false,
                historial: []
            }
        },
        computed: {
            principalExpandido() {
                return this.ruta('proyectos') || this.ruta('ayuda')
            },
            configuracionVisible() {
                return this.ruta('configuracion')
            }
        },
        beforeRouteUpdate(destino, fuente) {
            if (destino.name === 'bodegas') {
                this.$store.commit('actualizarBodega')
            }
        },
        created() {
            this.ocultarLateral()
            if (!this.$store.state.usuario) {
                this.$router.push('/iniciar-sesion')
            }
        },
        mounted() {
            document.body.addEventListener('click', () => {
                this.ocultarLateral ()
            })
        },
        methods: {
            alternarLateral() {
                this.lateralVisible = this.lateralVisible ? false : true
            },
            ocultarLateral() {
                if (this.esVersionMovil) {
                    this.lateralVisible = false
                }
            },
            cargarPrincipal() {
                this.lateralCargado = true
            }
        }
    }
</script>

<style scoped>
    #inventario,
    #cuerpo {
        display: flex;
        flex: 1;
        overflow: hidden;
    }
    
    #inventario {
        flex-direction: column;
        width: 100%;
    }
    
    #cuerpo {
        position: relative;
    }
</style>