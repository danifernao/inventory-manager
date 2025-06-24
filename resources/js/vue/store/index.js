import { createStore } from 'vuex'

const store = createStore({
    state () {
        return {
            aplicacion: null,
            usuario: null,
            config: {},
            bodegaDesactualizada: false,
            acercaDe: null
        }
    },
    mutations: {
        guardarAppDatos (state, datos) {
            state.aplicacion = datos
        },
        
        guardarUsuario (state, datos) {
            state.usuario = datos
        },
        
        eliminarUsuario (state) {
            state.usuario = null
        },
        
        actualizarBodega (state, dato = true) {
            state.bodegaDesactualizada = dato
        },
        
        guardarConfiguracion (state, datos) {
            state.config = datos
            localStorage.setItem('config', JSON.stringify(datos))
        },
        
        eliminarConfiguracion (state) {
            state.config = {}
            localStorage.removeItem('config')
        },
        
        guardarAcercaDe (state, datos) {
            state.acercaDe = datos
        }
    }
})

export default store