export default {
    data() {
        return {
            apiUrlLista: null,
            archivoUrl: null
        }
    },
    methods: {
        obtenerApiUrl(url) {
            this.apiUrlLista = url
        },
        
        exportar() {
            this.notificacion.fire({
                icon: 'info',
                title: 'Generando archivo',
                text: 'Por favor, espere un momento.',
                timer: false,
                timerProgressBar: false
            })
            axios({
                method: 'get',
                url: `${this.apiUrlLista}&exportar=1`,
                headers: {
                    Authorization: `Bearer ${this.config('claveApi')}`
                },
                responseType: 'blob'
            })
            .then(respuesta => {
                this.archivoUrl = URL.createObjectURL(new Blob([respuesta.data]))
                
                this.notificacion.fire({
                    icon: 'success',
                    title: `Descargando...`
                })
            })
            .catch(error => {
                const estado = error.response ? error.response.status : ''
                const mensaje = error.response ? error.response.data.message : 'Error inesperado.'
                
                this.notificacion.fire({
                    icon: 'error',
                    title: `Error ${estado}`,
                    text: mensaje
                })
            })
        },
        
        descargaFinalizada() {
            this.archivoUrl = null
        }
    }
}