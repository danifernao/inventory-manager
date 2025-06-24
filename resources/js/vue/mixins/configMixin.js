export default {
    computed: {
        configuracion() {
            return this.$store.state.config
        }
    },
    methods: {
        config(clave) {
            return this.configuracion[clave] || null
        },
        
        alternarConfig(clave) {
            let objeto = null;
            
            if (_.has(this.configuracion, clave)) {
                objeto = _.omit(this.configuracion, clave)
            } else {
                objeto = _.set(this.configuracion, clave, true)
            }
            
            this.$store.commit('guardarConfiguracion', objeto)
        },
        
        verificarConfig(clave, valor = true) {
            if (_.find(this.configuracion, { clave: valor }) === 'undefined') {
                return false
            }
            return  true
        },
        
        guardarConfig(clave, valor = true) {
            const objeto = _.set(this.configuracion, clave, valor)
            this.$store.commit('guardarConfiguracion', objeto)
        },
        
        eliminarConfig(clave) {
            const objeto = _.omit(this.configuracion, clave)
            this.$store.commit('guardarConfiguracion', objeto)
        }
    }
}