<template>
    <tbody>
        <template v-if="registros.length">
            <tr v-for="registro in registros">
                <Celda tipo="imagen" clase="imagen" icono="user" :url="rutaUsuario(registro.id)"  :imgUrl="registro.foto_perfil" imgDescripcion="Fotografía del usuario" titulo="Editar usuario"/>
                <Celda clase="nombres" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="nombres(registro)"/>
                <Celda clase="apellidos" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="apellidos(registro)"/>
                <Celda clase="numero-identificacion" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="identificacion(registro)"/>
                <Celda clase="genero" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="genero(registro.genero)"/>
                <Celda clase="correo" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="registro.correo"/>
                <Celda clase="fecha-registro" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="registro.created_at"/>
                <Celda clase="tipo-usuario" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="tipoUsuario(registro.es_administrador)"/>
                <Celda :clase="claseEstadoUsuario(registro.esta_habilitado)" :url="rutaUsuario(registro.id)" titulo="Editar usuario" :dato="estadoUsuario(registro.esta_habilitado)"/>
                <Celda clase="acciones" :opciones="opciones(registro)" @accion="ejecutar"/>
            </tr>
        </template>
        <tr v-else>
            <td colspan="100%" class="vacio">No hay registros que mostrar.</td>
        </tr>
    </tbody>
</template>

<script>
    import CuerpoCelda from './InventarioPrincipalBaseListaTablaCuerpoCelda.vue'
    import ConfigMixin from '../mixins/configMixin.js'
    import SwalMixin from '../mixins/swalMixin.js'
    
    export default {
        name: 'TablaCuerpoUsuarios',
        components: {
            Celda: CuerpoCelda,
        },
        mixins: [
            ConfigMixin,
            SwalMixin
        ],
        props: {
            registros: Object
        },
        methods: {
            rutaUsuario(id) {
                return this.$route.path + '/' + id + '/editar'
            },
            
            nombres(usuario) {
                return usuario.primer_nombre + (usuario.segundo_nombre ? ` ${usuario.segundo_nombre}` : '')
            },
            
            apellidos(usuario) {
                let apellidos = usuario.primer_apellido ? usuario.primer_apellido + (usuario.segundo_apellido ? ` ${usuario.segundo_apellido}` : '') : ''
                return apellidos || null
            },
            
            identificacion(usuario) {
                if (usuario.tipo_identificacion && usuario.numero_identificacion) {
                    return usuario.tipo_identificacion.toUpperCase() + '-' + usuario.numero_identificacion
                } else {
                    return null
                }
            },
            
            genero(genero) {
                let cadena = null
                switch (genero) {
                    case 'h':
                        cadena = 'Hombre'
                        break
                    case 'm':
                        cadena = 'Mujer'
                        break
                    case 'n':
                        cadena = 'No binario'
                        break
                }
                return cadena
            },
            
            tipoUsuario(esAdministrador) {
                return esAdministrador ? 'Administrador' : 'Operador'
            },
            
            claseEstadoUsuario(esta_habilitado) {
                return 'estado-usuario ' + (esta_habilitado ? 'verde' : 'rojo')
            },
            
            estadoUsuario (esta_habilitado) {
                return esta_habilitado ? 'Habilitado' : 'Inhabilitado'
            },
            
            opciones(usuario) {
                return [
                    {
                        texto: 'Acciones',
                        menu: [
                            {
                                url: `${this.$route.path}/${usuario.id}/editar`,
                                texto: 'Editar usuario'
                            },
                            {
                                texto: 'Nombrar administrador',
                                params: {
                                    id: usuario.id,
                                    accion: 'administrador'
                                },
                                oculto: usuario.es_administrador
                            },
                            {
                                texto: 'Nombrar operador',
                                params: {
                                    id: usuario.id,
                                    accion: 'operador'
                                },
                                oculto: !usuario.es_administrador
                            },
                            {
                                texto: 'Habilitar usuario',
                                params: {
                                    id: usuario.id,
                                    accion: 'habilitar'
                                },
                                oculto: usuario.esta_habilitado
                            },
                            {
                                texto: 'Inhabilitar usuario',
                                params: {
                                    id: usuario.id,
                                    accion: 'inhabilitar'
                                },
                                oculto: !usuario.esta_habilitado
                            },
                            {
                                texto: 'Cerrar sesiones',
                                params: {
                                    id: usuario.id,
                                    accion: 'sesiones'
                                }
                            },
                            {
                                texto: 'Eliminar usuario',
                                params: {
                                    id: usuario.id,
                                    accion: 'eliminar'                                
                                },
                                oculto: usuario.es_administrador
                            }
                        ]
                    }
                ]
            },
            
            ejecutar(params) {
                switch (params.accion) {
                    case 'administrador':
                        this.otorgar('administrador', params.id, '¿Deseas otorgarle permisos administrativos a este usuario?')
                        break
                    case 'operador':
                        this.otorgar('operador', params.id, '¿Deseas retirarle los permisos administrativos a este usuario?')
                        break
                    case 'habilitar':
                        this.otorgar('habilitar', params.id, '¿Deseas habilitar este usuario?')
                        break
                    case 'inhabilitar':
                        this.otorgar('inhabilitar', params.id, '¿Deseas inhabilitar este usuario?')
                        break
                    case 'sesiones':
                        this.cerrarSesiones(params.id)
                        break
                    case 'eliminar':
                        this.eliminar(params.id)
                        break
                }
            },
            
            otorgar(accion, id, mensaje) {
                this.confirmacion.fire({
                    text: mensaje
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'put',
                            url: `/api/usuarios/${id}/autorizar`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            },
                            data: {
                                accion: accion
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Cambios guardados.'
                            })
                            this.$emit('recargarLista')
                        })
                        .catch(error => {
                            this.mostrarError(error)
                        })
                    }
                })
            },
            
            cerrarSesiones(id) {
                this.confirmacion.fire({
                    text: '¿Deseas cerrar todas las sesiones de este usuario?'
                })
                .then(respuesta => {
                    if (respuesta.isConfirmed) {
                        axios({
                            method: 'delete',
                            url: `/api/usuarios/${id}/sesiones`,
                            headers: {
                                Authorization: `Bearer ${this.config('claveApi')}`
                            }
                        })
                        .then(respuesta => {
                            this.notificacion.fire({
                                icon: 'success',
                                title: 'Sesiones cerradas.'
                            })
                        })
                        .catch(error => {
                            this.mostrarError(error)
                        })
                    }
                })
            },
            
            eliminar(id) {
                this.campoContrasena.fire({
                    confirmButtonText: 'Eliminar',
                    inputLabel: 'Ingresa tu contraseña:',
                    inputAttributes: {
                        maxlength: 255
                    },
                    inputValidator: async (contrasena) => {
                        if (!contrasena) {
                          return 'Ingresa la contraseña pra proceder con la eliminación.'
                        } else {
                            return await axios({
                                method: 'delete',
                                url: `/api/usuarios/${id}`,
                                headers: {
                                    Authorization: `Bearer ${this.config('claveApi')}`
                                },
                                data: {
                                    contrasena: contrasena
                                }
                            })
                            .then (respuesta => {
                                this.$emit('recargarLista')
                            })
                            .catch (error => {
                                this.$swal.getInput().value = ''
                                return error.response ? error.response.data.message : 'Error inesperado.'
                            })
                        }
                    }
                })
            },
            
            mostrarError(error) {
                const estado = error.response ? error.response.status : ''
                const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                
                this.notificacion.fire({
                    icon: 'error',
                    title: `Error ${estado}`,
                    text: mensaje
                })
            }
        }
    }
</script>

<style scoped>
    :deep(.estado-usuario.verde) a {
        color: var(--color-verde-2);
    }
    
    :deep(.estado-usuario.rojo) a {
        color: var(--color-rojo-3);
    }
</style>