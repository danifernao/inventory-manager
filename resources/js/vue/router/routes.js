const routes = [
    {
        name: 'inicio',
        path: '/',
        component: () => import('../views/Inicio.vue')
    },
    {
        name: 'inicioSesion',
        path: '/iniciar-sesion',
        component: () => import('../views/Autenticacion.vue'),
        children: [
            {
                path: '',
                component: () => import('../components/AutenticacionInicioSesion.vue')
                
            }
        ]
    },
    {
        name: 'restablecimiento',
        path: '/restablecer-contrasena/:token?',
        component: () => import('../views/Autenticacion.vue'),
        children: [
            {
                path: '',
                component: () => import('../components/AutenticacionRestablecimiento.vue')
                
            }
        ]
    },
    {
        name: 'bodegas',
        path: '/bodegas',
        component: () => import('../views/Inventario.vue'),
        children: [
            {
                name: 'bodegaRegistro',
                path: 'registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'bodega',
                path: ':bodegaId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalBodegasItem.vue')
            },
            {
                path: ':bodegaId([1-9][0-9]*)/productos',
                redirect: { name: 'bodega' }
            },
            {
                name: 'bodegaEdicion',
                path: ':bodegaId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'bodegaProductoRegistro',
                path: ':bodegaId([1-9][0-9]*)/productos/registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'bodegaProducto',
                path: ':bodegaId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProductosItem.vue')
            },
            {
                path: ':bodegaId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades',
                redirect: { name: 'bodegaProducto' }
            },
            {
                name: 'bodegaProductoEdicion',
                path: ':bodegaId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'bodegaProductoUnidadRegistro',
                path: ':bodegaId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'bodegaProductoUnidad',
                path: ':bodegaId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalUnidadesItem.vue')
            },
            {
                name: 'bodegaProductoUnidadEdicion',
                path: ':bodegaId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            }
        ]
    },
    {
        name: 'proveedores',
        path: '/bodegas/:bodegaId([1-9][0-9]*)/proveedores',
        component: () => import('../views/Inventario.vue'),
        children: [
            {
                name: 'proveedoresLista',
                path: '',
                component: () => import('../components/InventarioPrincipalProveedoresLista.vue')
            },
            {
                name: 'proveedorRegistro',
                path: 'registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'proveedor',
                path: ':proveedorId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProveedoresItem.vue')
            },
            {
                path: ':proveedorId([1-9][0-9]*)/productos',
                redirect: { name: 'proveedor' }
            },
            {
                name: 'proveedorEdicion',
                path: ':proveedorId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proveedorProducto',
                path: ':proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProductosItem.vue')
            },
            {
                name: 'proveedorProductoEdicion',
                path: ':proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proveedorProductoUnidadRegistro',
                path: ':proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'proveedorProductoUnidad',
                path: ':proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalUnidadesItem.vue')
            },
            {
                name: 'proveedorProductoUnidadEdicion',
                path: ':proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            }
        ]
    },
    {
        name: 'fabricantes',
        path: '/bodegas/:bodegaId([1-9][0-9]*)/fabricantes',
        component: () => import('../views/Inventario.vue'),
        children: [
            {
                path: '',
                name: 'fabricantesLista',
                component: () => import('../components/InventarioPrincipalFabricantesLista.vue')
            },
            {
                name: 'fabricanteRegistro',
                path: 'registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'fabricante',
                path: ':fabricanteId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalFabricantesItem.vue')
            },
            {
                path: ':fabricanteId([1-9][0-9]*)/productos',
                redirect: { name: 'fabricante' }
            },
            {
                name: 'fabricanteEdicion',
                path: ':fabricanteId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'fabricanteProductoRegistrar',
                path: ':fabricanteId([1-9][0-9]*)/productos/registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'fabricanteProducto',
                path: ':fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProductosItem.vue')
            },
            {
                name: 'fabricanteProductoEdicion',
                path: ':fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'fabricanteProductoUnidadRegistro',
                path: ':fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'fabricanteProductoUnidad',
                path: ':fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalUnidadesItem.vue')
            },
            {
                name: 'fabricanteProductoUnidadEdicion',
                path: ':fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            }
            
        ]
    },
    {
        name: 'proyectos',
        path: '/proyectos',
        component: () => import('../views/Inventario.vue'),
        children: [
            {
                name: 'proyectosLista',
                path: '',
                component: () => import('../components/InventarioPrincipalProyectosLista.vue')
            },
            {
                name: 'proyectoRegistro',
                path: 'registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'proyecto',
                path: ':proyectoId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProyectosItem.vue')
            },
            {
                path: ':proyectoId([1-9][0-9]*)/productos',
                redirect: { name: 'proyecto' }
            },
            {
                name: 'proyectoEdicion',
                path: ':proyectoId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoProducto',
                path: ':proyectoId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProductosItem.vue')
            },
            {
                name: 'proyectosProductoEdicion',
                path: ':proyectoId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoProductoUnidad',
                path: ':proyectoId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalUnidadesItem.vue')
            },            
            {
                name: 'proyectosProductoUnidadEdicion',
                path: ':proyectoId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoProveedores',
                path: ':proyectoId([1-9][0-9]*)/proveedores',
                component: () => import('../components/InventarioPrincipalProveedoresLista.vue')
            },
            {
                name: 'proyectoProveedor',
                path: ':proyectoId([1-9][0-9]*)/proveedores/:proveedorId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProveedoresItem.vue')
            },
            {
                name: 'proyectoProveedorEdicion',
                path: ':proyectoId([1-9][0-9]*)/proveedores/:proveedorId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoProveedorProducto',
                path: ':proyectoId([1-9][0-9]*)/proveedores/:proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProductosItem.vue')
            },
            {
                name: 'proyectoProveedorProductoEdicion',
                path: ':proyectoId([1-9][0-9]*)/proveedores/:proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoProvedorProductoUnidad',
                path: ':proyectoId([1-9][0-9]*)/proveedores/:proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalUnidadesItem.vue')
            },            
            {
                name: 'proyectoProveedorProductoUnidadEdicion',
                path: ':proyectoId([1-9][0-9]*)/proveedores/:proveedorId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoFabricantes',
                path: ':proyectoId([1-9][0-9]*)/fabricantes',
                component: () => import('../components/InventarioPrincipalFabricantesLista.vue')
            },
            {
                name: 'proyectoFabricante',
                path: ':proyectoId([1-9][0-9]*)/fabricantes/:fabricanteId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalFabricantesItem.vue')
            },
            {
                name: 'proyectoFabricanteEdicion',
                path: ':proyectoId([1-9][0-9]*)/fabricantes/:fabricanteId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoFabricanteProducto',
                path: ':proyectoId([1-9][0-9]*)/fabricantes/:fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalProductosItem.vue')
            },
            {
                name: 'proyectoFabricanteProductoEdicion',
                path: ':proyectoId([1-9][0-9]*)/fabricantes/:fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                name: 'proyectoFabricanteProductoUnidad',
                path: ':proyectoId([1-9][0-9]*)/fabricantes/:fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)',
                component: () => import('../components/InventarioPrincipalUnidadesItem.vue')
            },            
            {
                name: 'proyectoFabricanteProductoUnidadEdicion',
                path: ':proyectoId([1-9][0-9]*)/fabricantes/:fabricanteId([1-9][0-9]*)/productos/:productoId([1-9][0-9]*)/unidades/:unidadId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            }
        ]
    },
    {
        name: 'configuracion',
        path: '/configuracion',
        component: () => import('../views/Inventario.vue'),
        children: [
            {
                name: 'usuariosLista',
                path: 'usuarios',
                component: () => import('../components/InventarioPrincipalUsuariosLista.vue')
            },
            {
                name: 'usuarioRegistro',
                path: 'usuarios/registrar',
                component: () => import('../components/InventarioPrincipalBaseItemRegistro.vue')
            },
            {
                name: 'usuarioEdicion',
                path: 'usuarios/:usuarioId([1-9][0-9]*)/editar',
                component: () => import('../components/InventarioPrincipalBaseItemEdicion.vue')
            },
            {
                path: 'aplicacion',
                redirect: { name: 'aplicacionEdicion' }
            },
            {
                name: 'aplicacionEdicion',
                path: 'aplicacion/editar',
                component: () => import('../components/InventarioPrincipalAplicacionFormulario.vue')
            }
        ]
    },
    {
        name: 'ayuda',
        path: '/ayuda',
        component: () => import('../views/Inventario.vue'),
        children: [
            {
                path: '',
                redirect: { name: 'ayudaPreguntasFrecuentes' }
            },
            {
                name: 'ayudaPreguntasFrecuentes',
                path: 'preguntas-frecuentes',
                component: () => import('../components/InventarioPrincipalPreguntasFrecuentes.vue')
            }
        ]
    },
    {
        name: 'error',
        path: '/:catchAll(.*)',
        component: () => import('../views/404.vue')
    }
]

export default routes