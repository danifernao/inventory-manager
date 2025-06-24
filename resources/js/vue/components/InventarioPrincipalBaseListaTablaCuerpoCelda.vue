<template>
    <td :class="clase">
        <input type="checkbox" :value="dato" @click="marcar" v-if="tipo === 'seleccion'"/>
        <router-link :to="url" :title="titulo" v-else-if="tipo === 'imagen'">
            <img :src="imgUrl" :alt="imgDescripcion" v-if="imgUrl"/>
            <font-awesome-icon :icon="icono || 'image'" v-else/>
        </router-link>
        <template v-else-if="opciones">
            <Opciones :opciones="opciones" v-bind="$attrs"/>
        </template>
        <a href="javascript:void(0);" title="Leer motivo de baja" v-else-if="tip" @click.prevent="mostrar(tip)">
            <font-awesome-icon icon="comment" v-if="tip"/>
        </a>
        <router-link :to="url" :title="titulo" v-else-if="url">
            <template  v-if="typeof(dato) !== 'undefined' && dato !== null">{{ dato }}</template>
            <span class="vacio" v-else>—</span>
        </router-link>
        <template v-else>
            <template v-if="typeof(dato) !== 'undefined' && dato !== null">{{ dato }}</template>
            <span class="vacio" v-else>—</span>
        </template>
    </td>
</template>

<script>
    import Opciones from './InventarioPrincipalBaseOpciones.vue'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'CuerpoItem',
        components: {
            Opciones
        },
        mixins: [
            SwalMixin
        ],
        props: {
            tipo: String,
            clase: String,
            icono: String,
            url: String,
            titulo: String,
            tip: String,
            dato: [Number, String],
            imgUrl: String,
            imgDescripcion: String,
            opciones: Object
        },
        methods: {
            mostrar(mensaje) {
                this.alerta.fire({
                    text: mensaje
                })
            },
            
            marcar () {
                const elemMarcarTodo = document.querySelector('#principal .listado thead input')
                const elems = document.querySelectorAll('#principal .listado tbody input')
                const elemsMarcados = document.querySelectorAll('#principal .listado tbody input:checked')
                
                if (elemMarcarTodo.checked) {
                    if (elemsMarcados.length !== elems.length) {
                        elemMarcarTodo.checked = false
                    }
                } else {
                    if (elemsMarcados.length === elems.length) {
                        elemMarcarTodo.checked = true
                    }
                }
            }
        }
    }
</script>

<style scoped>
    .imagen {
        width: 3rem;
    }
    
    .imagen img {
        object-fit: cover;
    }
    
    .imagen img,
    .imagen svg {
        width: 2.5rem;
        height: 2.5rem;
    }
    
    .imagen img,
    .imagen svg {
        border-radius: 0.3rem;
    }
    
    .imagen svg {
        color: var(--color-gris-2);
    }
    
    .imagen,
    .estado-activo,
    .estado-inactivo,
    .tipo-usuario,
    .estado-usuario,
    .motivo-baja,
    .acciones {
        text-align: center;
    }
    
    .fecha-adquisicion,
    .valor-adquisicion,
    .fecha-instalacion,
    .valor-residual,
    .depreciacion,
    .fecha-apertura,
    .fecha-clausura,
    .fecha-baja,
    .nro-unidades,
    .fecha-registro {
        text-align: right;
    }
    
    .vacio {
        display: block;
        font-style: italic;
        text-align: center;
    }
</style>