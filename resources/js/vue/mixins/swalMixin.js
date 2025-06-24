export default {
    data() {
        return {
            notificacion: this.$swal.mixin({
                toast: true,
                position: 'top',
                heightAuto: false,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                showCloseButton: true,
                customClass: {
                    popup: 'notificacion'
                },
                showClass: {
                    popup: 'animate__animated animate__slideInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__slideOutUp'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', this.$swal.stopTimer)
                    toast.addEventListener('mouseleave', this.$swal.resumeTimer)
                }
            }),
            
            alerta: this.$swal.mixin({
                position: 'center',
                heightAuto: false,
                showConfirmButton: true,
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                buttonsStyling: false,
                reverseButtons: true,
                customClass: {
                    popup: 'alerta',
                    confirmButton: 'boton-aceptar'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                }
            }),
            
            confirmacion: this.$swal.mixin({
                position: 'center',
                heightAuto: false,
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Sí',
                cancelButtonText: 'No',
                buttonsStyling: false,
                reverseButtons: true,
                customClass: {
                    popup: 'confirmacion',
                    confirmButton: 'boton-aceptar',
                    cancelButton: 'boton-cancelar'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                }
            }),
            
            campoTexto: this.$swal.mixin({
                input: 'text',
                position: 'center',
                heightAuto: false,
                showConfirmButton: true,
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                reverseButtons: true,
                customClass: {
                    popup: 'formulario',
                    confirmButton: 'boton-verde',
                    cancelButton: 'boton-cancelar'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                }
            }),
            
            campoContrasena: this.$swal.mixin({
                input: 'password',
                position: 'center',
                heightAuto: false,
                showConfirmButton: true,
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                reverseButtons: true,
                customClass: {
                    popup: 'formulario',
                    confirmButton: 'boton-rojo',
                    cancelButton: 'boton-cancelar'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                }
            }),
            
            campoAreaTexto: this.$swal.mixin({
                input: 'textarea',
                position: 'center',
                heightAuto: false,
                showConfirmButton: true,
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                reverseButtons: true,
                customClass: {
                    popup: 'formulario',
                    confirmButton: 'boton-verde',
                    cancelButton: 'boton-cancelar'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                }
            }),
            
            html: this.$swal.mixin({
                position: 'center',
                heightAuto: false,
                showConfirmButton: true,
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                reverseButtons: true,
                customClass: {
                    popup: 'html',
                    confirmButton: 'boton-verde',
                    cancelButton: 'boton-cancelar'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                }
            })
        }
    }
}