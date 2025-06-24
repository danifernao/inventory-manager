<template>
    <div class="envoltorio">
        <div class="imagen">
            <img :src="url" :class="clase || 'cubrir'" :alt="descripcion" v-if="url"/>
            <font-awesome-icon :icon="icono" v-else/>
            <a href="#" title="Eliminar imagen" v-if="url" @click.prevent="eliminar">
                <font-awesome-icon icon="xmark"/>
            </a>
        </div>
        <div>
            <input type="file" accept=".jpg, .jpeg, .png" :disabled="inhabilitado" @change="procesar" ref="campo"/>
            <button class="boton-gris" :disabled="inhabilitado" @click.prevent="buscar">{{ textoBoton }}</button>
        </div>
    </div>
</template>

<script>
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'Imagen',
        mixins: [
            SwalMixin
        ],
        props: {
            fuente: String,
            descripcion: String,
            clase: String,
            icono: String,
            boton: String,
            inhabilitado: Boolean
        },
        data() {
            return {
                url: this.fuente || '',
                textoBoton: this.boton || 'Cambiar imagen'
            }
        },
        watch: {
            fuente(url) {
                this.url = url
            }
        },
        methods: {
            buscar() {
                if (!this.inhabilitado) {
                    this.$refs.campo.click()
                }
            },
            
            validar(nombre) {
                const formatos = ['jpg', 'jpeg', '.png'];
                return (new RegExp('(' + formatos.join('|').replace(/\./g, '\\.') + ')$')).test(nombre);
            },
            
            procesar() {
                const archivo = this.$refs.campo.files[0]
                const extensionEsValida = this.validar(archivo.name)
                
                if (extensionEsValida) {
                    this.url = URL.createObjectURL(archivo)
                    this.$emit('imagen', this.url, archivo)
                } else {
                    archivo.value = null
                    this.notificacion.fire({
                        icon: 'error',
                        title: 'Formato no válido',
                        text: 'Solo se permiten imágenes con extensiones JPG o PNG.'
                    })
                }
            },
            
            eliminar() {
                this.$refs.campo.value = null
                this.url = ''
                this.$emit('imagen', null, null)
            }
        }
    }
</script>

<style scoped>
    .envoltorio {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .imagen {
        position: relative;
        border: 0.06rem solid var(--color-gris-2);
        border-radius: 0.3rem;
    }
    
    .imagen img {
        display: block;
        width: 10rem;
        height: 10rem;
        border-radius: 0.3rem;
    }
    
    .imagen img.cubrir {
        object-fit: cover;
    }
    
    .imagen img.contener {
        object-fit: contain;
    }
    
    .imagen img.opaco {
        background-color: var(--color-blanco-4);
    }
    
    .imagen img.naranja {
        background-color: var(--color-naranja-3);
    }
    
    .imagen > svg {
        padding: 1.5rem;
        width: 7rem;
        height: 7rem;
        color: var(--color-gris-3);
    }
    
    .imagen a {
        position: absolute;
        right: 0.5rem;
        top: 0.5rem;
        color: var(--color-blanco-1);
        opacity: 0.7;
        z-index: 100;
    }
    
    .imagen a:hover {
        opacity: 1;
    }
    
    .imagen a svg {
        filter: drop-shadow(0 0 0.09rem var(--color-negro-1))
    }
    
    input {
        display: none;
    }
</style>