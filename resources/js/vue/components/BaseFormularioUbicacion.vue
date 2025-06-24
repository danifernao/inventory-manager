<template>
    <div class="seccion ubicacion">
        <label for="pais">Ubicación:</label>
        <div>
            <input type="text" id="pais" name="pais" maxlength="255" placeholder="País" v-model="lugar.pais" @input="actualizar"/>
            <input type="text" id="departamento" name="departamento" placeholder="Departamento / Estado / Provincia" maxlength="255" v-model="lugar.departamento" @input="actualizar"/>
            <input type="text" id="municipio" name="municipio" maxlength="255" placeholder="Municipio" v-model="lugar.municipio" @input="actualizar"/>
            <input type="text" id="barrio" name="barrio" maxlength="255" placeholder="Barrio / Localidad" v-model="lugar.barrio" @input="actualizar"/>
            <input type="text" id="direccion" name="direccion" maxlength="255" placeholder="Dirección" v-model="lugar.direccion" @input="actualizar"/>
        </div>
    </div>
</template>

<script>
    
    export default {
        name: 'Ubicacion',
        props: {
            ubicacion: Object
        },
        data() {
            return {
                lugar: {
                    pais: '',
                    departamento: '',
                    municipio: '',
                    barrio: '',
                    direccion: ''
                }
            }
        },
        methods: {
            actualizar() {
                this.$emit('ubicacion', this.lugar)
            }
        },
        created() {
            this.lugar.pais = this.ubicacion.pais
            this.lugar.departamento = this.ubicacion.departamento
            this.lugar.municipio = this.ubicacion.municipio
            this.lugar.barrio = this.ubicacion.barrio
            this.lugar.direccion = this.ubicacion.direccion
        }
    }
</script>

<style scoped>
    .ubicacion div {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 0.3rem;
    }
    
    .ubicacion input {
        flex: 1;
        width: auto !important;
    }
</style>