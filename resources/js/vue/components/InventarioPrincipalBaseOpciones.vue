<template>
    <ul class="opciones">
        <template v-for="opcion in opciones">
            <li class="opcion" v-if="!opcion.oculto">
                <template v-if="opcion.menu">
                    <a href="javascript:void(0);" @click.prevent="alternarMenu($event.currentTarget)">
                        <font-awesome-icon :icon="opcion.icono" v-if="opcion.icono"/>
                        <span v-if="opcion.texto">{{ opcion.texto }}</span>
                        <font-awesome-icon icon="caret-down" v-if="!opcion.icono"/>
                    </a>
                    <ul :class="{ visible: visible }">
                        <template v-for="item in opcion.menu">
                            <li v-if="!item.oculto">
                                <a href="javascript:void(0);" v-if="item.params" @click.prevent="ejecutar(item.params)">
                                    <font-awesome-icon :icon="item.icono" v-if="item.icono"/>
                                    <span>{{ item.texto }}</span>
                                </a>
                                <router-link :to="item.url" :class="{ activo: item.activo }" v-else>
                                    <font-awesome-icon :icon="item.icono" v-if="item.icono"/>
                                    <span>{{ item.texto }}</span>
                                </router-link>
                            </li>
                        </template>
                    </ul>
                </template>
                <router-link :to="opcion.url" v-else-if="opcion.url">
                    <font-awesome-icon :icon="opcion.icono" v-if="opcion.icono"/>
                    <span>{{ opcion.texto }}</span>
                </router-link>
                <a href="javascript:void(0);" v-else @click.prevent="ejecutar(opcion.params)">
                    <font-awesome-icon :icon="opcion.icono" v-if="opcion.icono"/>
                    <span>{{ opcion.texto }}</span>
                </a>
            </li>
        </template>
    </ul>
</template>

<script>
    export default {
        name: 'Opciones',
        props: {
            opciones: Array
        },
        data() {
            return {
                visible: false,
                noOcultar: false
            }
        },
        mounted() {
            const listadoElem = document.querySelector('.listado')
            
            if (listadoElem) {
                listadoElem.addEventListener('scroll', () => {
                    this.visible = false
                })
            }
            
            document.querySelector('#principal').addEventListener('scroll', () => {
                this.visible = false
            })
            
            document.body.addEventListener('click', () => {
                if (this.noOcultar) {
                    this.noOcultar = false
                } else {
                    this.visible = false
                }
            })
            
            window.addEventListener('resize', () => {
                this.visible = false
            })
        },
        methods: {
            posicionarMenu(elem) {
                const opcionElem = elem.closest('.opcion')
                const menuElem = elem.nextSibling
                const listadoElem = elem.closest('.listado')
                
                const opcionCoords = opcionElem.getBoundingClientRect()
                const menuCoords = menuElem.getBoundingClientRect()
                const padreCoords = document.querySelector('#cuerpo').getBoundingClientRect()
                
                let desbordamiento = 0
                
                if (listadoElem && listadoElem.clientWidth + listadoElem.scrollLeft < listadoElem.scrollWidth) {
                    desbordamiento = listadoElem.scrollWidth - listadoElem.clientWidth - listadoElem.scrollLeft
                }
                
                if (document.documentElement.clientHeight > opcionCoords.bottom + menuCoords.height + 10) {
                    menuElem.style.top = opcionCoords.bottom - padreCoords.top + 10 + 'px'
                } else {
                    menuElem.style.top = opcionCoords.top - menuCoords.height - padreCoords.top - 10 + 'px'
                }
                
                const posIzquierda = opcionCoords.right - menuCoords.width - desbordamiento
                
                if (posIzquierda > 0) {
                    menuElem.style.left = posIzquierda + 'px'
                } else {
                    menuElem.style.left = opcionCoords.left + 'px'
                }
            },
            
            alternarMenu(elem) {
                this.noOcultar = true
                this.visible = this.visible ? false : true
                this.posicionarMenu(elem)
            },
            
            ejecutar(params) {
                this.$emit('accion', params)
            }
        }
    }
</script>

<style scoped>
    ul {
        display: inline-flex;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    a {
        padding: 0.5rem 0.8rem;
        background-color: var(--color-blanco-2);
        color: var(--color-negro-1);
        font-size: 0.9rem;
        transition: background-color 100ms ease-out;
    }
    
    a:hover, .activo {
        background-color: var(--color-blanco-3);
    }
    
    .opciones {
        flex: 1;
        justify-content: end;
    }
    
    .opciones ul {
        flex-direction: column;
    }
    
    .opciones li {
        display: flex;
    }
    
    .opciones li a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
        border: 0.06rem solid var(--color-gris-2);
        white-space: nowrap;
    }
    
    .opciones > li:first-of-type > a {
      border-top-left-radius: 0.3rem;
      border-bottom-left-radius: 0.3rem;
    }
    
    .opciones > li:last-of-type > a {
        border-top-right-radius: 0.3rem;
        border-bottom-right-radius: 0.3rem;
    }
    
    .opciones > li:not(:last-of-type) > a {
        border-right-width: 0;
    }
    
    .opciones ul li:not(:last-of-type) a {
        border-bottom: 0;
    }
    
    .opciones ul li:first-of-type  a {
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
    }
    
    .opciones ul li:last-of-type a {
        border-bottom-left-radius: 0.3rem;
        border-bottom-right-radius: 0.3rem;
    }
        
    .opciones ul {
        position: absolute;
        z-index: 200;
    }
    
    .opciones ul:not(.visible) {
        visibility: hidden;
        top: -100vh !important;
        left: -100vw !important;
        z-index: -9999;
    }
    
    @media only screen and (max-width: 600px) {
        .opciones {
            flex-wrap: wrap;
            justify-content: start;
        }
    }
</style>