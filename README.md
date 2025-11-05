# Ecotxurdi — Web de gamificación educativa

Repositorio de la web Ecotxurdi: una plataforma web PHP para misiones, recompensas y clasificación de usuarios.

## Resumen

Proyecto PHP sencillo sobre proteccion al medio ambiente siguiento la ods numero 15 y el sistema  pensado para ejecutarse en un servidor local o en la Nube (p. ej. Laragon, XAMPP) y usar MySQL. Proporciona:
- Gestión de misiones (crear, editar, borrar, realizar)
- Recompensas y reclamaciones
- Panel de administración (aprobar/denegar evidencias, gestionar usuarios)
- Internacionalización (es / en / eu)

## Requisitos

- PHP 7.4+ (o compatible) con extensiones comunes (mysqli, json)
- MySQL / MariaDB
- Servidor local (Laragon, XAMPP, MAMP, etc.)
- Navegador moderno

## Instalación rápida (Windows + Laragon o Xamp)

1. Clona o copia este repositorio a la carpeta de www de Laragon o xamp (O en la tu servidor), p. ej:

   c:\laragon\www\G4_KaixoMundua

2. Importa la base de datos. En phpMyAdmin o usando la consola MySQL, importa `EcoTxurdi.sql` (está en la raíz del proyecto).

   - phpMyAdmin: sube el archivo SQL y ejecútalo.
   - Consola (PowerShell):

     mysql -u root -p < EcoTxurdi.sql

3. Configura la conexión a la base de datos en `config.php`. Ajusta host, usuario, contraseña y nombre de BD.

4. Arranca Laragon o Xamp (o tu servidor). Accede en el navegador a:

   http://localhost/G4_KaixoMundua/

5. Regístrate o usa una cuenta de admin si está disponible para probar el panel (Puedes Econtrar Los Usuarios de Prueba en el SQL).

## Configuración importante

- `config.php`: variables de conexión a la BD y ajustes generales. Revisa y actualiza antes de ejecutar.
- `i18n/`: contienen `en.json`, `es.json`, `eu.json` para traducciones. El sistema carga las cadenas desde ahí mediante `funtion/translator.php`.

### Cambiar la ruta base de la aplicación (BASE_URL)

La aplicación usa una constante `BASE_URL` definida en `config.php` para construir rutas internas. En este repositorio `config.php` contiene por ejemplo:

```php
// Constantes del proyecto
define('BASE_URL', '/G4_KAIXOMUNDUA');
define('BASE_PATH', __DIR__);
```

Para cambiar la ruta donde se sirve la web (por ejemplo si la quieres en la raíz del servidor o en otra subcarpeta), modifica `BASE_URL` manteniendo una barra inicial y sin barra final:

- Sitio en la raíz del servidor: `define('BASE_URL', '/');`
- Sitio en subcarpeta `miapp`: `define('BASE_URL', '/miapp');`

Además, si tu `session_set_cookie_params` usa `path` (por defecto en este proyecto `path` está fijado a '/' en `config.php`), ajústalo para que las cookies sean válidas en la ruta de la aplicación. Por ejemplo, si sirves desde `/miapp` pon:

```php
session_set_cookie_params([
   'lifetime' => 0,
   'path' => '/miapp', // actualizar según BASE_URL
   'domain' => '',
   'secure' => false,
   'httponly' => true,
   'samesite' => 'Strict'
]);
```

Notas prácticas:
- Usa siempre la barra inicial en `BASE_URL` y no pongas barra final.
- Actualiza también cualquier ruta codificada en plantillas si no usa `BASE_URL`.
- Si sirves la app por HTTPS en producción, cambia las opciones de cookie `secure` a `true`.


## Estructura principal del proyecto

- `index.php` — página de inicio / punto de entrada.
- `config.php` — configuración (BD, rutas, etc.).
- `includes/` — `header.php`, `footer.php` y elementos comunes.
- `dashboard/` — área administrativa (gestión de misiones, recompensas, usuarios, aprobación de evidencias).
- `login/` — páginas de login, logout y signup.
- `web/` — vistas públicas: misiones, noticias, perfil, etc.
- `i18n/` — archivos JSON de traducción.
- `assets/` — scripts PHP auxiliares, CSS y SQL (`EcoTxurdi.sql`).
- `img/` — imágenes organizadas por tipo (misiones, recompensas, usuarios, evidencias).
- `js/` — JavaScript cliente (actualizarPuntos.js, admin.js, lang.js, etc.).

## Internacionalización (i18n)

Las traducciones se encuentran en `i18n/` como JSON. Para añadir o modificar textos:

1. Edita el JSON correspondiente (`es.json`, `en.json`, `eu.json`).
2. Si añades nuevas claves, actualiza las vistas donde se cargan las cadenas (revisa `funtion/translator.php` y `includes/header.php`).

## Base de datos

- Fichero de creación: `EcoTxurdi.sql` (importar en MySQL).
- Si cambias la estructura de la BD, recuerda mantener copias y migraciones (no incluidas).

## Desplegar con Docker
Estas instrucciones crean un contenedor PHP/Apache y un contenedor MySQL usando `docker-compose`. Ajusta las contraseñas y versiones según tus políticas de seguridad.

Ejemplo de `Dockerfile` (colócalo en la raíz del proyecto):

```dockerfile
FROM php:8.1-apache

RUN apt-get update && apt-get install -y libzip-dev zip unzip git && \
      docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

COPY . /var/www/html/
WORKDIR /var/www/html

# Ajustar permisos si es necesario
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
```

Ejemplo de `docker-compose.yml` (colócalo en la raíz):

```yaml
version: '3.8'
services:
   web:
      build: .
      ports:
         - "8080:80"
      volumes:
         - ./:/var/www/html
      depends_on:
         - db
   db:
      image: mysql:5.7
      environment:
         MYSQL_ROOT_PASSWORD: rootpassword
         MYSQL_DATABASE: ecotxurdi
         MYSQL_USER: user
         MYSQL_PASSWORD: userpassword
      volumes:
         - db_data:/var/lib/mysql

volumes:
   db_data:
```

Cambios recomendados en `config.php` para Docker:

- Host de la BD: `'$host = 'db';` (el servicio `db` del `docker-compose`).
- Credenciales: usa las que hayas definido en `docker-compose.yml`.
- Ruta base: si sirves la app en la raíz del contenedor (como en este ejemplo), usa `define('BASE_URL', '/');`.

Ejemplo mínimo de cambios en `config.php`:

```php
$host = 'db';
$db   = 'ecotxurdi';
$user = 'user';
$pass = 'userpassword';

define('BASE_URL', '/');
define('BASE_PATH', __DIR__);
```

Construir y levantar con Docker Compose (desde PowerShell):

```powershell
# Construir y arrancar
docker-compose up -d --build

# Ver logs
docker-compose logs -f web
```

Importar la base de datos dentro del contenedor MySQL (PowerShell):

```powershell
# Asumiendo que el servicio se llama 'db' en docker-compose y la contraseña de root es 'rootpassword'
Get-Content .\EcoTxurdi.sql | docker exec -i (docker-compose ps -q db) /usr/bin/mysql -u root -prootpassword ecotxurdi
```

Notas de seguridad y producción:

- No uses contraseñas en claro en producción; usa secretos/variables de entorno seguras.
- Configura HTTPS (proxy inverso o certificado en Apache) y activa `secure => true` en cookies.
- Si usas un dominio y subcarpeta, actualiza `BASE_URL` y `session_set_cookie_params['path']` acorde.

## Buenas prácticas de desarrollo

- Haz cambios en una rama nueva y abre un Pull Request si trabajas en equipo.
- Mantén credenciales fuera del control de versiones. `config.php` actualmente contiene la configuración; si vas a publicar el repo, usa variables de entorno o un archivo de configuración no versionado.
- Sanitiza y valida entradas en formularios.

## Probar localmente

1. Arranca Laragon.
2. Asegúrate de haber importado la base de datos y actualizado `config.php`.
3. Accede a la URL local. Prueba registro, creación/realización de misiones, y flujo de administración.

## Contribuir

1. Crea una rama con tu cambio: `feature/descripción`.
2. Haz commits claros y atómicos.
3. Abre un Pull Request describiendo el cambio y cómo probarlo.

## Licencia

Licencia MIT

Copyright (c) 2025 Ecotxurdi

Por la presente se concede permiso, de manera gratuita, a cualquier persona que obtenga una copia de este software y de los archivos de documentación asociados (el "Software"), para tratar el Software sin restricción, incluyendo sin limitación los derechos a usar, copiar, modificar, fusionar, publicar, distribuir, sublicenciar y/o vender copias del Software, y permitir a las personas a las que se les proporcione el Software hacer lo mismo, sujeto a las siguientes condiciones:

El aviso de copyright anterior y este aviso de permiso se incluirán en todas las copias o partes sustanciales del Software.

EL SOFTWARE SE ENTREGA "TAL CUAL", SIN GARANTÍA DE NINGÚN TIPO, EXPRESA O IMPLÍCITA, INCLUYENDO PERO NO LIMITADO A GARANTÍAS DE COMERCIABILIDAD, IDONEIDAD PARA UN PROPÓSITO PARTICULAR Y NO INFRACCIÓN. EN NINGÚN CASO LOS AUTORES O TITULARES DEL COPYRIGHT SERÁN RESPONSABLES DE NINGUNA RECLAMACIÓN, DAÑO U OTRA RESPONSABILIDAD, YA SEA EN UNA ACCIÓN CONTRACTUAL, AGRAVIO O DE OTRO MODO, DERIVADA DE, O EN CONEXIÓN CON EL SOFTWARE O EL USO U OTRO TIPO DE ACCIONES EN EL SOFTWARE.

## Contacto

Para dudas o soporte, puedes escribirnos

---

# Ecotxurdi
