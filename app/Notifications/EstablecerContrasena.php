<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EstablecerContrasena extends Notification
{
    use Queueable;
    
    public $correo;
    public $clave;
    
    public function __construct($correo, $clave)
    {
        $this->correo = urlencode($correo);
        $this->clave = $clave;
    }
    
    public function via($notifiable)
    {
        return ['mail'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Usuario registrado')
                    ->greeting('Establecer contraseña')
                    ->line('Tu cuenta ha sido creada, pero para que puedas acceder a ella, es necesario que establezcas una contraseña. Para ello, presiona el siguiente botón:')
                    ->action('Establecer contraseña', url('/restablecer-contrasena/' . $this->clave . '?email=' . $this->correo));
    }
}
