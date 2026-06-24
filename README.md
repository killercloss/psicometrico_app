# Sistema de Evaluación Psicométrica v2

Prototipo local en HTML/PHP/MySQL para aplicar una escala Likert de 60 reactivos, 5 dimensiones y temporizador por sección.

## Instalación local con XAMPP o Laragon

1. Copia la carpeta `psicometrico_v2` dentro de `htdocs` o `www`.
2. Abre phpMyAdmin.
3. Importa `database.sql`.
4. Revisa `includes/config.php` si tu usuario/contraseña MySQL no es `root` sin contraseña.
5. Abre: `http://localhost/psicometrico_v2/`

## Accesos de prueba

Aspirante:
- Folio CENEVAL: `1234`
- Código de acceso: `ABCD-1234`

Admin:
- URL: `http://localhost/psicometrico_v2/admin/login.php`
- Usuario: `admin`
- Contraseña: `admin123`

## Funciones incluidas

- Alta, edición y eliminación de aspirantes.
- Alta, edición y eliminación de dimensiones.
- Alta, edición y eliminación de preguntas.
- 60 reactivos cargados en la base de datos.
- Puntuación directa e inversa.
- Tiempo límite por dimensión, configurable desde la tabla `dimensiones`.
- Pantalla inicial con espera automática de 2 minutos y botón para comenzar inmediatamente.
- Bloqueo básico para impedir regresar desde el navegador.
- Si se agota el tiempo, las preguntas no enviadas de la dimensión se registran con 0 puntos.
- El tiempo sigue contando aunque el aspirante cierre o recargue la página porque se calcula contra `inicio_at` en la base de datos.
- Al finalizar, si el aspirante intenta iniciar sesión nuevamente, se incrementa `intentos_post_finalizacion`.
- Dashboard administrativo.
- Reporte individual por aspirante.
- Reportes filtrables por folio o maestría.
- Exportación CSV.

## Aviso importante

Este es un prototipo para pruebas locales. Para uso institucional real, conviene agregar:

- Contraseñas de admin con hash.
- HTTPS.
- Aviso de privacidad y consentimiento informado.
- Respaldos automáticos.
- Protección CSRF.
- Validación psicométrica formal del instrumento.
- Revisión jurídica y académica antes de usar resultados como criterio de admisión.


## Cambio v3

- Se agregó catálogo editable de maestrías y doctorados en `admin/programas.php`.
- Al dar de alta o editar aspirantes, el programa ahora se selecciona desde un combobox.
- La tabla `programas` se crea y se precarga desde `database.sql`.
