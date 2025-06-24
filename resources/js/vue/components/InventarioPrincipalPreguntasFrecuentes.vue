<template>
    <div class="envoltorio">
        <form @submit.prevent>
            <h2>Preguntas frecuentes</h2>
            <input type="text" id="buscar" placeholder="Buscar..." v-model="consulta"/>
            <button type="submit">Buscar</button>
        </form>
        <dl v-if="resultado.length">
            <template v-for="(item, indice) in resultado">
                <template v-if="!item.oculto || esAdministrador">
                    <dt :class="{ visible: visiblilidad[indice] }">
                        <a href="javascript:void(0);" :title="(visiblilidad[indice] ? 'Ocultar' : 'Ver') + ' respuesta'" @click.prevent="alternar(indice)">{{ item.pregunta }}</a>
                    </dt>
                    <dd :class="{ visible: visiblilidad[indice] }" v-show="visiblilidad[indice]">{{ item.respuesta }}</dd>
                </template>
            </template>
        </dl>
        <p v-else>No se encontraron resultados.</p>
    </div>
</template>

<script>
    import ConfigMixin from '../mixins/configMixin.js'
    
    export default {
        name: 'FAQ',
        mixins: [
            ConfigMixin
        ],
        data() {
            return {
                visiblilidad: [],
                consulta: '',
                resultado: [],
                faq: [
                    {
                        pregunta: '¿Qué son las unidades?',
                        respuesta: 'Son las partes individuales que conforman un conjunto, es el artículo en sí, el elemento físico que se tiene a disposición en el inventario.',
                    },
                    {
                        pregunta: '¿Qué es un producto?',
                        respuesta: 'Es un conjunto de unidades que comparten características comunes. En un producto se detalla el modelo, la marca, etc. de un grupo de unidades, mientras que las unidades especifican el número de «existencias» que se tienen de dicho producto.'
                    },
                    {
                        pregunta: '¿Qué es una bodega?',
                        respuesta: 'Es el espacio físico en donde se almacenan las unidades.'
                    },
                    {
                        pregunta: '¿Qué es un fabricante?',
                        respuesta: 'Es la entidad que manufactura el producto.'
                    },
                    {
                        pregunta: '¿Qué es un proveedor?',
                        respuesta: 'Es la entidad que abastece de unidades a la bodega, sin que esto implique que deba manufacturarlas.'
                    },
                    {
                        pregunta: '¿Qué es un proyecto?',
                        respuesta: 'Es un emprendimiento en el que participa la empresa y al que se destinan las unidades en bodega.'
                    },
                    {
                        pregunta: '¿Por qué el producto / proveedor / fabricante no aparece en el listado?',
                        respuesta: 'Por defecto, solo se listan los productos / proveedores / fabricantes con unidades en la bodega. Para ver los registros que no tienen unidades, haga clic en el botón «Filtrar listado» y luego en «Sin unidades en esta bodega».'
                    },
                    {
                        pregunta: '¿Qué diferencia hay entre dar de baja y eliminar una unidad?',
                        respuesta: 'En la primera se mantiene el registro en la base de datos, mientras que en la segunda se retira definitivamente de ella.'
                    },
                    {
                        pregunta: '¿Cómo hago para ver las unidades dadas de baja?',
                        respuesta: 'A través de la opción «Filtrar listado».'
                    },
                    {
                        pregunta: '¿Por qué no puedo eliminar un producto?',
                        respuesta: 'El registro de una unidad depende del registro de un producto, ya que este último es quien la describe. Por lo anterior, no es posible eliminar un producto con unidades asociadas a él, así hayan sido dadas de baja.'
                    },
                    {
                        pregunta: '¿Por qué no puedo eliminar una bodega?',
                        respuesta: 'El registro de una unidad puede depender del registro de una bodega, ya que este último es el que le indica al sistema dónde está ubicado la unidad. Por lo anterior, no es posible eliminar una bodega con unidades asociadas a ella, así hayan sido dadas de baja.'
                    },
                    {
                        pregunta: '¿Por qué no pudo eliminar un proyecto?',
                        respuesta: 'El registro de una unidad puede depender del registro de un proyecto, ya que este último es el que le indica al sistema dónde está ubicado la unidad. Por lo anterior, no es posible eliminar un proyecto con unidades asociadas a él, así hayan sido dados de baja.'
                    },
                    {
                        pregunta: '¿Cómo se calcula la devaluación de una unidad?',
                        respuesta: 'Esta se calcula teniendo en cuenta la vida útil del producto (T), el valor de adquisición de la unidad (V) y los días en uso en proyectos (D), aplicando la siguiente fórmula: R = V-((V/T)*(D/365)). Si alguno de los datos requeridos no ha sido proporcionando, no se realiza ninguna operación.'
                    },
                    {
                        pregunta: '¿Cómo uso el lector de barras para registrar los números de serie?',
                        respuesta: 'Una vez conectado y configurado el lector de barras en el equipo de cómputo, presiona el botón «Con lector» del formulario y empieza a leer los códigos de barras de los bienes que tienes a la mano. Por cada código añadido al formulario, la aplicación clona otro campo de texto y queda a la espera de una próxima lectura.'
                    },
                    {
                        pregunta: '¿Por qué la aplicación no se conecta con el lector de barras?',
                        respuesta: 'Porque la aplicación no se conecta con ningún dispositivo, solo escucha los eventos que estos puedan emitir. Por lo general, los lectores de barras transmiten la información leída al equipo de cómputo a través de los eventos de teclado, característica de la que se vale la aplicación para captar los números de serie. Verifica que el lector cuente con esta funcionalidad.'
                    },
                    {
                        pregunta: '¿Qué son las notificaciones?',
                        respuesta: 'Son alertas de productos con unidades insuficientes.'
                    },
                    {
                        pregunta: '¿Por qué no me llegan las notificaciones de un determinado producto?',
                        respuesta: 'Lo más probable es que no se haya especificado las unidades mínimas requeridas para el producto, tenga presente que este valor solo se aplica para la bodega que está actualmente visitando.'
                    },
                    {
                        pregunta: '¿Por qué el campo «Unidades mínimas» no me aparece en el formulario de edición del producto?',
                        respuesta: 'Porque el campo solo está habilitado para las bodegas, no para los proyectos.'
                    },
                    {
                        pregunta: '¿Cómo hago para detener las notificaciones de un producto?',
                        respuesta: 'Deje en «0» el campo «Unidades mínimas» del producto.'
                    },
                    {
                        pregunta: '¿Por qué no puedo cambiar mi información personal?',
                        respuesta: 'Porque se requiere la integridad de la misma, ya que es empleada por los administradores para identificar a los usuarios. Si desea realizar algún cambio, consulte a un administrador.'
                    },
                    {
                        pregunta: '¿Cuál es la diferencia entre administrador y operador?',
                        respuesta: 'Las cuentas administrativas pueden registrar y gestionar otras cuentas de usuario, las operativas no.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Por qué no puedo cambiar los permisos de mi cuenta?',
                        respuesta: 'La autogestón de permisos no es posible, debes solicitar el cambio a otro administrador.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Por qué mi cuenta no aparece en la lista de usuarios?',
                        respuesta: 'Porque es la sesión activa, esta se administra a través de la opción «Cuenta» del panel lateral.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Qué significa que un usuario esté habilitado o inhabilitado?',
                        respuesta: 'Que puede usar o no la aplicación, respectivamente.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Qué significa que un usuario esté inhabilitado por el sistema?',
                        respuesta: 'Es una acción realizada por el sistema cuando el usuario intenta varias veces acceder a él con unas credenciales erradas. El usuario vuelve a estar habilitado después de 24 horas.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Por qué no puedo gestionar la cuenta de cierto administrador?',
                        respuesta: 'Lo más probable es que sea una cuenta de respaldo, estas no pueden ser administradas por terceros.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Qué son las cuentas de respaldo?',
                        respuesta: 'Son simples cuentas administrativas, pero que no pueden ser gestionadas por otros administradores. Fueron pensadas para recuperar el acceso al sistema en caso de que las demás cuentas administrativas se hayan visto comprometidas.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Cómo creo otra cuenta de respaldo?',
                        respuesta: 'No se pueden crear nuevas cuentas de respaldo.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Cómo elimino una cuenta de respaldo?',
                        respuesta: 'No se pueden eliminar.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Por qué no aparecen las cuentas de respaldo en la lista de usuarios?',
                        respuesta: 'Porque estas cuentas solo pueden ser gestionadas por el propietario de la misma.',
                        oculto: true
                    },
                    {
                        pregunta: '¿Cómo hago para recuperar una contraseña?',
                        respuesta: 'No hay manera de recuperarla, puesto que está cifrada. Lo que sí puedes hacer es iniciar el proceso de restablecimiento.'
                    },
                    {
                        pregunta: '¿Cómo hago para establecer o cambiar la contraseña de otro usuario?',
                        respuesta: 'Solo el usuario puede establecer o cambiar su contraseña. Lo que sí puedes hacer es iniciar el proceso de restablecimiento de contraseña en nombre del usuario.',
                        oculto: true
                    },
                    {
                        pregunta: '¿En qué consiste el proceso de restablecimiento de la contraseña?',
                        respuesta: 'Cuando se solicita el proceso de restablecimiento, ya sea por iniciativa de un administrador ( «Configuración > usuarios > [...] > Datos de acceso».) o por medio del enlace presente en el formulario de inicio de sesión, el sistema enviará un mensaje al correo electrónico registrado en la cuenta, proporcionando un enlace para establecer una nueva contraseña.'
                    },
                    {
                        pregunta: '¿Por qué la aplicación no funciona en determinado navegador web?',
                        respuesta: 'La aplicación fue desarrollada con base en las últimas versiones de Mozilla Firefox y Google Chrome, por lo que no se garantiza su apropiado funcionamiento fuera de estos entornos.'
                    }
                ]
            }
        },
        computed: {
            esAdministrador() {
                return this.$store.state.usuario.es_administrador
            }
        },
        watch: {
            consulta(cadena) {
                this.visiblilidad = []
                this.resultado = this.faq.filter(item => item.pregunta.includes(cadena));
            }
        },
        created() {
            this.resultado = this.faq
        },
        methods: {
            alternar(indice) {
                this.visiblilidad[indice] = this.visiblilidad[indice] ? false : true
            }
        }
    }
</script>

<style scoped>
    form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin: 0 auto;
        width: 100%;
        align-items: center;
        justify-content: center;
        background-color: transparent !important;
        border: 0 !important;
    }
        
    input {
        padding-left: 0;
        padding-right: 0;
        width: 100% !important;
        max-width: 35rem;
        background-color: transparent;
        border: 0;
        border-bottom-width: 0.06rem;
        border-bottom-style: solid;
        border-bottom-color: var(--color-gris-2);
        font-size: 1rem;
        text-align: center;
    }
    
    input:focus {
        outline: none;
    }
    
    button {
        display: none;
    }
    
    h2 {
        font-size: 2rem !important;
    }
    
    a {
        color: var(--color-negro-1);
    }
    
    a:hover,
    dt.visible a {
        font-weight: bold;
    }
    
    dt:not(.visible) {
        border-bottom: 0.06rem solid var(--color-gris-2);
    }
    
    dt, dd {
        padding: 0.5rem 0;
    }
    
    dd.visible {
        padding-left: 0.5rem;
        border-left-width: 0.3rem;
        border-left-style: solid;
        border-left-color: var(--color-gris-3);;
    }
        
    dd {
        margin: 0 0 1rem;
    }
    
    p {
        text-align: center;
    }
</style>