<template>
    <div class="cabecera">
        <div class="izquierda" v-if="registro.logotipo_url || registro.imagen_url">
            <img :src="registro.logotipo_url" alt="Logotipo del fabricante" v-if="registro.logotipo_url"/>
            <img :src="registro.imagen_url" alt="Imagen del producto" v-else/>
        </div>
        <div class="derecha">
            <div class="identificacion">
                <div>
                    <h2 class="nombre" v-if="registro.nombre || registro.marca || registro.numero_serie">
                        <template v-if="registro.nombre">
                            {{ registro.nombre }}
                        </template>
                        <template v-else-if="registro.marca">
                            {{ registro.marca.fabricante.empresa.nombre }} {{ registro.marca.nombre }} {{ registro.modelo }}
                        </template>
                        <template v-else>
                            {{ registro.numero_serie }}
                        </template>
                    </h2>
                    <p class="nit" v-if="registro.nit">{{ registro.nit }}</p>
                    <p class="codigo" v-if="registro.codigo">{{ registro.codigo }}</p>
                    <p class="fabricante" v-if="registro.fabricante && !$route.params.fabricanteId">
                        Fabricado por
                        <router-link :to="rutaFabricante(registro.fabricante.id)" title="Ver detalles del fabricante">
                            {{ registro.fabricante.empresa.nombre }}
                        </router-link>
                    </p>
                    <p class="proveedor" v-if="registro.proveedor && !$route.params.proveedorId">
                        Proveído por
                        <router-link :to="rutaProveedor(registro.proveedor.id)" title="Ver detalles del proveedor">
                            {{ registro.proveedor.empresa.nombre }}
                        </router-link>
                    </p>
                </div>
                <router-link :to="$route.path + '/editar'" class="editar" :title="'Editar ' + tipo">
                    <font-awesome-icon icon="pen"/>
                </router-link>
            </div>
            <div class="detalles" v-if="registro.pais || registro.direccion || registro.pagina_web">
                <p class="ubicacion" v-if="registro.pais || registro.direccion">
                    <font-awesome-icon icon="location-dot"/>
                    <span>
                        <template v-if="registro.pais">{{ registro.pais.nombre }}<template v-if="registro.departamento">, {{ registro.departamento.nombre }}<template v-if="registro.municipio">, {{ registro.municipio.nombre }}<template v-if="registro.barrio">, {{ registro.barrio.nombre }}</template></template></template></template><template v-if="registro.direccion"><template v-if="registro.pais">, </template>{{ registro.direccion }}</template>.
                    </span>
                </p>
                <p class="pagina" v-if="registro.pagina_web">
                    <font-awesome-icon icon="link"/>
                    <span><a :href="registro.pagina_web" title="Página web del fabricante" target="_blank">{{ registro.pagina_web }}</a></span>
                </p>
            </div>
            <p class="descripcion" v-if="registro.descripcion">{{ registro.descripcion }}</p>
            <div class="pie" v-if="['bodega', 'producto', 'unidad', 'proyecto'].includes(tipo)">
                <template v-if="tipo === 'bodega'">
                    <p>
                        <span>Unidades en bodega:</span>
                        <span>{{ registro.unidades_count }}</span>
                    </p>
                    <p v-if="registro.cantidad_max">
                        <span>Capacidad máxima:</span>
                        <span>{{ registro.cantidad_max }}</span>
                    </p>
                </template>
                <template v-if="tipo === 'producto'">
                    <p>
                        <span>Vida útil:</span>
                        <span>{{ registro.anos_vida_util }} años</span>
                    </p>
                    <template v-if="ruta('bodegaProducto')">
                        <p>
                            <span>Unidades en bodega:</span>
                            <span>{{ registro.unidades_count }}</span>
                        </p>
                        <p>
                            <span>Unidades requeridas:</span>
                            <span>{{ registro.bodegas[0].pivot.unidades_minimas }}</span>
                        </p>
                    </template>
                </template>
                <template v-if="tipo === 'unidad'">
                    <p v-if="registro.fecha_de_baja">
                        <span>Fecha de baja:</span>
                        <span>{{ registro.fecha_de_baja }}</span>
                    </p>
                    <p v-if="registro.fecha_adquisicion">
                        <span>Fecha de adquisición:</span>
                        <span>{{ registro.fecha_adquisicion }}</span>
                    </p>
                    <p v-if="registro.valor_adquisicion">
                        <span>Valor de adquisición:</span>
                        <span>{{ registro.valor_adquisicion }}</span>
                    </p>
                    <p v-if="parseFloat(registro.producto.anos_vida_util) > 0">
                        <span>Valor residual:</span>
                        <span>{{ calcularDepreciacion(registro) }}</span>
                    </p>
                </template>
                <template v-if="tipo === 'proyecto'">
                    <p>
                        <span>Fecha de apertura:</span>
                        <span>{{ registro.fecha_apertura || 'No registrado' }}</span>
                    </p>
                    <p>
                        <span>Fecha de clausura:</span>
                        <span>{{ registro.fecha_clausura || 'No registrado' }}</span>
                    </p>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import RutaMixin from '../mixins/rutaMixin.js'
    
    export default {
        name: 'Cabecera',
        props: {
            tipo: String,
            registro: Object
        },
        mixins: [
            RutaMixin
        ],
        computed: {
            dirPadre() {
                return this.$route.path.split('/').slice(0, 3).join('/')
            }
        },
        methods: {
            rutaProveedor(id) {
                return `${this.dirPadre}/proveedores/${id}`
            },
            rutaFabricante(id) {
                return `${this.dirPadre}/fabricantes/${id}`
            },
            calcularDepreciacion(registro) {
                let diasEnUso = null
                
                if (registro.bodega_id) {
                    diasEnUso = registro.dias_en_uso
                } else {
                    const [d, m, y] = registro.fecha_ingreso_proyecto.split(/\D/)
                    const diasAcumulados = (new Date() - new Date(y, m-1, d)) / (1000 * 60 * 60 * 24)
                    diasEnUso = registro.dias_en_uso + diasAcumulados
                }
                
                const valorDevaluado = (registro.valor_adquisicion/registro.producto.anos_vida_util)*(diasEnUso/365)
                const valorResidual = (registro.valor_adquisicion - valorDevaluado).toFixed(2)
                
                return valorResidual
            }
        }
    }
</script>

<style scoped>
    .cabecera {
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .cabecera,
    .cabecera > div {
        display: flex;
    }
    
    .derecha {
        flex-direction: column;
        flex: 1;
        gap: 0.5rem;
        font-size: 0.9rem;
    }
    
    p {
        margin: 0;
    }
    
    img {
        width: 6rem;
        height: 6rem;
        border: 0.06rem solid var(--color-gris-2);
        border-radius: 0.3rem;
        object-fit: cover;
    }
    
    .identificacion {
        display: flex;
        align-items: flex-start;
    }
    
    .identificacion > div {
        flex: 1;
    }
    
    .nombre {
        font-size: 1.4rem;
    }
    
    .editar {
        font-size: 0.9rem;
        color: var(--color-negro-1);
        opacity: 0.7;
        transition: opacity 300ms ease;
    }
    
    .editar:hover {
        opacity: 1;
    }
    
    .detalles {
        display: flex;
        flex-direction: column;
    }
    
    .detalles > p {
        display: flex;
        align-items: center;
    }
    
    .detalles svg {
        flex-basis: 1.5rem;
    }
    
    .descripcion {
        margin-left: 0.6rem;
        padding-left: 0.5rem;
        border-left: 0.4rem solid var(--color-gris-2);
    }
    
    .pie,
    .pie p {
        display: flex;
    }
    
    .pie {
        gap: 1rem;
    }
    
    .pie p {
        gap: 0.5rem;
    }
    
    .pie p:first-of-type,
    .pie p span:first-of-type {
        flex: 1;
    }
    
    .pie p span {
        text-align: right;
    }
    
    .pie p span:first-of-type {
        font-weight: bold;
    }
    
    @media only screen and (max-width: 760px) {
        a:hover {
            opacity: 0.7;
        }
    }
</style>