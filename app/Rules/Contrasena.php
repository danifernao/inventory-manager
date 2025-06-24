<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class Contrasena implements Rule
{
    public function __construct()
    {
        $this->longitud = true;
        $this->mayuscula = true;
        $this->numerico = true;
        $this->caracterEspecial = true;
    }

    /**
     * Verifica que si la contraseña NO cumple con los siguientes criterios:
     * - Debe tener al menos ocho caracteres.
     * - Debe tener al menos una mayúscula.
     * - Debe tener al menos una minúscula.
     * - Debe tener al menos un caracter especial.
     */
    public function passes($atributo, $valor)
    {
        $this->longitud = (Str::length($valor) >= 8);
        $this->mayuscula = (Str::lower($valor) !== $valor);
        $this->numerico = ((bool) preg_match('/[0-9]/', $valor));
        $this->caracterEspecial = ((bool) preg_match('/[^A-Za-z0-9]/', $valor));

        return ($this->longitud && $this->mayuscula && $this->numerico && $this->caracterEspecial);
    }
    
    public function message()
    {
        $mensaje = array();
        
        switch (true) {
            case ! $this->longitud:
                array_push($mensaje, 'La contraseña debe tener al menos 8 caracteres.');
            case ! $this->mayuscula:
                array_push($mensaje, 'La contraseña debe contener al menos una mayúscula.');
            case ! $this->numerico:
                array_push($mensaje, 'La contraseña  debe contener al menos un número.');
            case ! $this->caracterEspecial:
                array_push($mensaje, 'La contraseña  debe contener al menos un caracter especial.');
        }
        
        return $mensaje;
    }
}
