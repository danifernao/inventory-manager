<template>
    <div id="menu-bodegas">
        <template v-if="cargado && bodegas.length">
            <a :href="$route.path" title="Ver bodegas" :class="{ activo: visible }" @click.prevent.stop="alternarMenu">
                <span v-if="rutaRegistro">Nueva bodega</span>
                <span v-else>{{ bodegaDefecto.nombre }}</span>
                <font-awesome-icon icon="caret-down"/>
            </a>
            <ul v-show="visible">
                <li v-for="bodega in bodegas">
                    <router-link :to="'/bodegas/' + bodega.id" :class="{ activo: !rutaRegistro && bodega.id === bodegaDefecto.id }" :title="bodega.nombre" @click.prevent="predeterminar(bodega.id)">
                        <span>{{ bodega.nombre }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/bodegas/registrar" :class="{ activo: rutaRegistro }">Nueva bodega</router-link>
                </li>
            </ul>
        </template>
        <a href="#" :class="{ activo: cargado }" v-else @click.prevent>
            <template v-if="cargado">Registrar bodega</template>
            <template v-else-if="error">Error</template>
            <template v-else>Cargando...</template>
        </a>
    </div>
</template>

<script>
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'MenuBodegas',
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        data() {
            return {
                bodegas: [],
                cargado: false,
                error: false,
                visible: false
            }
        },
        computed: {
            bodegaDefecto() {
                const id = this.config('bodegaDefectoId')
                if (id) {
                    return _.find(this.bodegas, { id: id }) || this.bodegas[0]
                } else {
                    return this.bodegas[0]
                }
            },
            rutaRegistro() {
                return this.$route.name === 'bodegasRegistro'
            }
        },
        watch: {
            '$store.state.bodegaDesactualizada'(desactualizada) {
                if (desactualizada) {
                    this.cargarMenu()
                }
            }
        },
        created() {
            this.cargarMenu()
        },
        mounted() {
            document.body.addEventListener('click', () => {
                this.visible = false
            })
        },
        methods: {
            alternarMenu() {
                this.visible = this.visible ? false : true
            },
            
            cargarMenu() {
                axios({
                    method: 'get',
                    url: '/api/bodegas',
                    headers: {
                        Authorization: `Bearer ${this.config('claveApi')}`
                    }
                })
                .then(respuesta => {
                    this.bodegas = respuesta.data
                    
                    const bodegaRutaId = parseInt(this.$route.params.bodegaId)
                    const bodegaRutaExiste = bodegaRutaId ? _.find(this.bodegas, { id: bodegaRutaId }) : null
                    
                    this.predeterminar(bodegaRutaExiste ? bodegaRutaId : null)
                    
                    if (this.bodegas.length) {
                        if (this.$route.name === 'bodegas' || (bodegaRutaId && !bodegaRutaExiste)) {
                            this.$router.push(`/bodegas/${ this.config('bodegaDefectoId') }`)
                        }
                    } else {
                        this.$router.push('/bodegas/registrar')
                    }
                    
                    this.cargado = true
                    this.$emit('menu-bodegas-cargado', true)
                })
                .catch(error => {
                    const estado = error.response ? error.response.status : ''
                    const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                    
                    this.error = true
                    
                    this.notificacion.fire({
                        icon: 'error',
                        title: `Error ${estado}`,
                        text: mensaje
                    })
                })
                .finally(() => {
                    this.$store.commit('actualizarBodega', false)
                })
            },
            predeterminar (id = null) {
                if (id) {
                    this.guardarConfig('bodegaDefectoId', id)
                } else {
                    if (this.bodegas.length) {
                        const bodegaDefectoId = this.config('bodegaDefectoId')
                        if (bodegaDefectoId) {
                            const bodegaDefectoExiste = _.find(this.bodegas, { id: bodegaDefectoId })
                            if (!bodegaDefectoExiste) {
                                this.guardarConfig('bodegaDefectoId', this.bodegas[0].id)
                            }
                        } else {
                            this.guardarConfig('bodegaDefectoId', this.bodegas[0].id)
                        }
                    } else {
                        this.eliminarConfig('bodegaDefectoId')
                    }
                }
            }
        }
    }
</script>

<style scoped>
    #menu-bodegas {
        position: relative;
    }
    
    #menu-bodegas > a {
        transition: color, background-color 100ms ease;
    }
    
    #menu-bodegas > a:not(.activo) {
        color: var(--color-purpura-2);
    }
    
    #menu-bodegas > a.activo,
    #menu-bodegas > a:hover {
        color: var(--color-purpura-1);
        background-color: var(--color-purpura-5);
    }
    
    a {
        display: flex;
        align-items: center;
        padding: 0.8rem 1rem;
        font-size: 0.9rem;
        transition: background-color, color 100ms ease;
        white-space: nowrap;
    }
    
    span {
        flex: 1;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    
    li a {
        color: var(--color-purpura-1);
    }
    
    li a:hover,
    li a.activo {
        background-color: var(--color-purpura-6);
        color: var(--color-blanco-1);
    }
    
    ul {
        position: absolute;
        top: 100%;
        left: 0;
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
        width: 100%;
        max-height: 20rem;
        list-style: none;
        background-color: var(--color-purpura-5);
        overflow-x: hidden;
        z-index: 1000;
    }
    
    li {
        border-top: 0.06rem solid var(--color-purpura-4);
    }
    
    @media only screen and (max-width: 760px) {
        li a:not(.activo):hover {
            background-color: transparent;
            color: var(--color-purpura-1);
        }
    }
</style>