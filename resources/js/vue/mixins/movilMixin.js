export default {
    data() {
        return {
            esVersionMovil: this.verificarAnchoPantalla()
        }
    },
    methods: {
        verificarAnchoPantalla () {
            return window.matchMedia('only screen and (max-width: 1000px)').matches
        }
    },
    mounted() {
        window.addEventListener('resize', () => {
            this.esVersionMovil = this.verificarAnchoPantalla()
            document.body.classList.toggle('movil', this.esVersionMovil)
        })
    }
}