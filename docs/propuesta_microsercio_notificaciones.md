# Propuesta de Microservicio: Envío de Notificaciones

## 1. ¿Cuál es su única tarea?
Este microservicio básicamente funciona como un pequeño programa independiente que se encargará de una sola cosa, la cual es enviar un correo electrónico al cliente con el recibo y los detalles de su vehículo cada vez que se guarde una nueva renta en el sistema.

## 2. ¿Por qué es mejor separarlo de la API principal?
Separar el envío de correos en su propio microservicio brinda dos grandes ventajas técnicas:

* **La API responde más rápido:** Enviar un correo electrónico toma algunos segundos porque depende de servidores externos (como Gmail o Outlook). Si se separa, la API principal guarda la renta y le responde inmediatamente al usuario con un mensaje de éxito (201 Created), sin hacerlo esperar a que el correo termine de enviarse.

* **Evita que el sistema se caiga:** Si por alguna razón el servidor de correos falla o se queda sin internet, la aplicación principal de rentas no se bloquea y funciona con normalidad. La gente podrá seguir rentando vehículos sin problema, y el microservicio guardará los correos pendientes para enviarlos cuando recupere la conexión.

## 3. Contrato de Comunicación (API)
Para que la API principal (hecha en Laravel) le pase los datos a este nuevo microservicio, se enviará una petición con la siguiente estructura:

**Ruta a consumir:** POST /api/v1/notificaciones/enviar-recibo

### Datos enviados (Cuerpo de la Solicitud)

    {
      "cliente": {
        "nombre": "Juan Pérez",
        "email": "juan.perez@email.com"
      },
      "renta": {
        "id": 8475,
        "fecha_inicio": "2026-09-22",
        "fecha_fin": "2026-09-27",
        "total": 250.00
      },
      "vehiculo": {
        "marca": "Toyota",
        "modelo": "Yaris",
        "matricula": "TST-999"
      }
    }

### Respuestas que devolverá el microservicio

* **202 Accepted:** Eso lo que indica es que se recibieron los datos correctamente y el correo ya está en la fila para ser enviado.
* **422 Unprocessable Entity:** Significa que Laravel no le envió todos los datos (por ejemplo, que no se agregó el correo del cliente).
* **503 Service Unavailable:** Lo que quiere decir es que el servidor de correos está fallando en ese momento.