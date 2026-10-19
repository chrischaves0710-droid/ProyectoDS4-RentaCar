
import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

const formulario = document.getElementById('form-vehiculo');

if (formulario) {
    const api = new ApiClient('/api');

    const estado = new AppState({
        listo: false,
        guardando: false,
        errores: {},
        mensaje: ''
    });

    const campos = [
        'placa',
        'marca',
        'modelo',
        'anno',
        'kilometraje',
        'precio_diario',
        'categoria_id'
    ];

    const boton = formulario.querySelector('button[type="submit"]');
    const textoBoton = boton.textContent;

    const rutaActual = window.location.pathname.replace(/\/$/, '');
    const esEdicion = rutaActual.endsWith('/editar');

    const idVehiculo =
        new URLSearchParams(window.location.search).get('id') ||
        formulario.elements.namedItem('id')?.value;

    const rutaListado = rutaActual.startsWith('/lab8/admin/')
        ? '/lab8/admin/vehiculos'
        : '/lab8/gerente/vehiculos';

    // Actualizar la vista cuando cambie el estado
    estado.subscribe((datos) => {
        boton.disabled = !datos.listo || datos.guardando;

        boton.textContent = datos.guardando
            ? 'Guardando...'
            : textoBoton;

        mostrarErrores(datos.errores);
        mostrarMensaje(datos.mensaje);
    });

    estado.notify();

    // Iniciar y cargar los datos del formulario
    async function iniciar() {

        if (esEdicion && !idVehiculo) {
            estado.setState({
                mensaje: 'No se indicó qué vehículo se desea editar.'
            });
            return;
        }

        try {
            await cargarCategorias();

            if (esEdicion) {
                const respuesta = await api.get(
                    `/vehiculos/${encodeURIComponent(idVehiculo)}`
                );

                const vehiculo = respuesta.data ?? respuesta;

                rellenarCampos({
                    placa: vehiculo.placa ?? vehiculo.matricula,
                    marca: vehiculo.marca,
                    modelo: vehiculo.modelo,
                    anno: vehiculo.anno ??
                        vehiculo.anio_fabricacion ??
                        vehiculo.anio,
                    kilometraje: vehiculo.kilometraje,
                    precio_diario: vehiculo.precio_diario,
                    categoria_id: vehiculo.categoria_id
                });
            }

            const recuperado = restaurarDatos();

            estado.setState({
                listo: true,
                mensaje: recuperado
                    ? 'Se recuperaron los datos guardados antes de la expiración de sesión.'
                    : ''
            });

        } catch (error) {
            if (error.status !== 401) {
                estado.setState({
                    mensaje: error.message ||
                        'No se pudo cargar el formulario.'
                });
            }
        }
    }

    // Obtener las categorías desde Laravel
    async function cargarCategorias() {

        const select = formulario.elements.namedItem('categoria_id');

        select.replaceChildren(
            new Option('Seleccione una categoría', '')
        );

        let pagina = 1;
        let totalPaginas = 1;

        do {
            const respuesta = await api.get(
                `/categorias?per_page=50&page=${pagina}`
            );

            const categorias = Array.isArray(respuesta.data)
                ? respuesta.data
                : [];

            for (const categoria of categorias) {
                select.add(
                    new Option(categoria.nombre, categoria.id)
                );
            }

            totalPaginas = Number(
                respuesta.meta?.last_page ??
                respuesta.meta?.total_pages ??
                1
            );

            pagina++;

        } while (pagina <= totalPaginas);

        if (select.options.length === 1) {
            throw new Error(
                'No hay categorías registradas para seleccionar.'
            );
        }
    }

    // Colocar valores dentro del formulario
    function rellenarCampos(datos) {

        for (const nombre of campos) {

            const campo = formulario.elements.namedItem(nombre);

            if (
                campo &&
                datos[nombre] !== undefined &&
                datos[nombre] !== null
            ) {
                campo.value = datos[nombre];
            }
        }
    }

    // Restaurar información guardada ante un error 401
    function restaurarDatos() {

        const respaldo = sessionStorage.getItem('form_backup_401');

        if (!respaldo) return false;

        try {
            const datos = JSON.parse(respaldo);

            if (
                !datos ||
                !campos.some(nombre => Object.hasOwn(datos, nombre))
            ) {
                return false;
            }

            rellenarCampos(datos);

            sessionStorage.removeItem('form_backup_401');

            return true;

        } catch {
            sessionStorage.removeItem('form_backup_401');
            return false;
        }
    }

    // Guardar o actualizar un vehículo
    formulario.addEventListener('submit', async (evento) => {

        evento.preventDefault();

        if (
            !estado.getState().listo ||
            estado.getState().guardando
        ) {
            return;
        }

        estado.setState({
            guardando: true,
            errores: {},
            mensaje: ''
        });

        const datos = new FormData(formulario);

        const vehiculo = Object.fromEntries(
            campos.map(nombre => [nombre, datos.get(nombre)])
        );

        try {
            if (esEdicion) {

                await api.put(
                    `/vehiculos/${encodeURIComponent(idVehiculo)}`,
                    vehiculo
                );

            } else {

                await api.post('/vehiculos', vehiculo);
            }

            window.location.assign(rutaListado);

        } catch (error) {

            if (error.status === 422) {

                estado.setState({
                    errores: error.errors || {},
                    mensaje: 'Revisa los campos marcados y vuelve a intentarlo.'
                });

            } else if (error.status !== 401) {

                estado.setState({
                    mensaje: error.message ||
                        'No se pudo guardar el vehículo.'
                });
            }

        } finally {
            estado.setState({
                guardando: false
            });
        }
    });

    // Mostrar errores debajo de cada campo
    function mostrarErrores(errores) {

        formulario.querySelectorAll('.mensaje-error').forEach(elemento => {
            elemento.textContent = '';
            elemento.style.display = 'none';
        });

        formulario.querySelectorAll('[aria-invalid="true"]').forEach(elemento => {
            elemento.removeAttribute('aria-invalid');
        });

        for (const [nombre, mensajes] of Object.entries(errores || {})) {

            if (!campos.includes(nombre)) continue;

            const campo = formulario.elements.namedItem(nombre);
            const aviso = document.getElementById(`error-${nombre}`);

            if (campo) {
                campo.setAttribute('aria-invalid', 'true');
            }

            if (aviso) {
                aviso.textContent = Array.isArray(mensajes)
                    ? mensajes[0]
                    : String(mensajes);

                aviso.style.display = 'block';
            }
        }

        formulario.querySelector('[aria-invalid="true"]')?.focus();
    }

    // Mostrar mensajes generales
    function mostrarMensaje(texto) {

        let aviso = document.getElementById('mensaje-formulario');

        if (!aviso) {
            aviso = document.createElement('div');
            aviso.id = 'mensaje-formulario';
            aviso.setAttribute('role', 'alert');
            aviso.style.marginBottom = '1rem';

            formulario.prepend(aviso);
        }

        aviso.textContent = texto;
    }

    iniciar();
}
