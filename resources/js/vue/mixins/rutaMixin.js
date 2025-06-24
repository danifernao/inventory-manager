export default {
    methods: {
        ruta(nombre) {
            return _.find(this.$route.matched, { 'name': nombre }) ? true : false
        }
    }
}