<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Información del documento ("metadata"). -->
        <meta charset="utf-8" />
        <meta name="robots" content="noindex"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}"/>
        <meta property="og:type" content="website"/>
        <meta property="og:url" content="{{ url()->current() }}"/>
        <meta property='og:title' content="{{ $aplicacion->titulo }}"/>
        @if ($aplicacion->img_icono_url)
            <meta property="og:image" content="{{ $aplicacion->img_icono_url }}"/>
        @endif
        @if ($aplicacion->descripcion)
            <meta property="og:description" content="{{ $aplicacion->descripcion }}"/>
            <meta name="description" content="{{ $aplicacion->descripcion }}"/>
        @endif
        <meta name="theme-color" content="#444444"/>
        @if ($aplicacion->img_favicon_url)
            <link rel="icon" href="{{ $aplicacion->img_favicon_url }}"/>
        @endif
        
        <title>{{ $aplicacion->titulo }}</title>
        
        <!-- Carga la hoja de estilo CSS de la aplicación. -->
        <link href="{{ mix('css/app.css') }}" rel="stylesheet"/>
    </head>
    <body>
        <!-- Envoltorio de la aplicación, en donde Vue.js cargará la interfaz de usuario. -->
        <div id="app"></div>
        
        <!-- Carga los archivos JavaScript necesarios para la aplicación. -->
        <script src="{{ mix('js/app.js') }}"></script>
        
        <!-- Mensaje que se le muestra al usuario en el caso de que navegue con JavaScript desactivado. -->
        <noscript>
            <strong>Esta página requiere el empleo de JavaScript para su visualización. Para habilitarlo en su navegador web, <a href='https://www.enable-javascript.com/es/' target='_blank'>siga estas instrucciones</a>.</strong>
        </noscript>
    </body>
</html>