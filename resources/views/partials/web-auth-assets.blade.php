
<!-- Seguridad de la sesión web -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- JavaScript compartido para cerrar sesión -->
@vite('resources/js/ui/web-logout.js')
