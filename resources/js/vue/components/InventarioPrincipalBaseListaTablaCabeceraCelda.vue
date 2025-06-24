<template>
    <th :class="clase">
        <template v-if="tipo === 'seleccion'">
            <input type="checkbox" ref="chequeoElem" @click="$emit('accion', $event.currentTarget)"/>
        </template>
        <template v-else>
            <a href="javascript:void(0);" :title="enlaceTitulo" v-if="columna" @click.prevent="ordenar">
                <span v-if="titulo">{{ titulo }}</span>
                <template v-if="columna && ordenarPor === columna">
                    <font-awesome-icon icon="caret-up" v-if="ordenadoEn === 'asc'"/>
                    <font-awesome-icon icon="caret-down" v-else/>
                </template>
            </a>
            <template v-else>
                {{ titulo }}
            </template>
        </template>
    </th>
</template>

<script>
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    
    export default {
        name: 'CabeceraItem',
        components: {
            Opciones
        },
        props: {
            tipo: String,
            clase: String,
            titulo: String,
            columna: String,
            ordenarPor: String,
            ordenarEn: String,
            opciones: Object
        },
        data() {
            return {
                ordenadoPor: this.ordenarPor,
                ordenadoEn: this.ordenarEn
            }
        },
        computed: {
            enlaceTitulo() {
                return `Ordenar por «${this.titulo}» en orden ${this.ordenadoPor !== this.columna || this.ordenadoEn === 'desc' ? 'ascendente' : 'descendente'}`
            }
        },
        watch: {
            '$route.query'() {
                if (this.tipo === 'seleccion') {
                    this.$refs.chequeoElem.checked = false
                }
            }
        },
        methods: {
            ordenar() {
                const urlParams = new URLSearchParams(location.search)
                
                if (this.ordenarPor !== this.columna) {
                    this.ordenadoEn = 'asc'
                } else {
                    this.ordenadoEn = this.ordenadoEn === 'asc' ? 'desc' : 'asc'
                }
                
                this.ordenadoPor = this.columna
                
                urlParams.set('ordenarPor', this.ordenadoPor)
                urlParams.set('ordenarEn', this.ordenadoEn)
                
                this.$router.push({ query: Object.fromEntries(urlParams) })
            }
        }
    }
</script>

<style scoped>
    .multiple,
    .imagen,
    .acciones {
        width: 1%;
    }
</style>