<?php
/*
  Textos en español. El alemán (lang/de.php) es el idioma de origen: si falta
  una clave aquí, se usa automáticamente el texto en alemán.
*/
return [
    // ---------- Navegación / barra superior ----------
    'nav.wake'        => 'Despertar',
    'nav.devices'     => 'Gestionar dispositivos',
    'nav.passkey'     => 'Gestionar claves de acceso',
    'nav.backup'      => 'Copia de seguridad',
    'nav.logout'      => 'Cerrar sesión',
    'nav.aria'        => 'Navegación',
    'nav.open'        => 'Abrir menú',
    'nav.close'       => 'Cerrar menú',
    'nav.language'    => 'Idioma',
    'nav.about'       => 'Acerca de (versión %s)',

    // ---------- Aviso de actualización ----------
    'update.available' => 'Actualización disponible: versión %s',

    'theme.aria'      => 'Elegir un tema',
    'theme.light'     => 'Tema claro',
    'theme.dark'      => 'Tema oscuro',
    'theme.vivid'     => 'Tema vivo',
    'lang.aria'       => 'Elegir un idioma',

    // ---------- Página principal (despertar) ----------
    'index.sub'          => 'Elige un dispositivo y despiértalo',
    'index.csrf'         => 'Solicitud no válida, vuelve a enviar el formulario.',
    'index.wake_failed'  => 'No se pudo enviar el paquete mágico.',
    'index.wake_sent'    => 'Aviso de despertar enviado a %s',
    'index.no_devices'   => 'Todavía no se han añadido dispositivos.',
    'index.add_device'   => 'Añadir dispositivo',
    'index.your_devices' => 'Tus dispositivos',
    'index.wake'         => 'Despertar',

    // ---------- Inicio de sesión ----------
    'login.title'          => 'Iniciar sesión',
    'login.sub'            => 'Inicia sesión',
    'login.csrf'           => 'Solicitud no válida, recarga la página.',
    'login.locked'         => 'Demasiados intentos fallidos. Vuelve a intentarlo en %d minuto(s).',
    'login.no_password'    => 'Todavía no se ha establecido ninguna contraseña. Abre primero setup.php.',
    'login.wrong_password' => 'La contraseña es incorrecta.',
    'login.with_passkey'   => 'Iniciar sesión con clave de acceso',
    'login.or_password'    => 'o con contraseña',
    'login.password'       => 'Contraseña',
    'login.submit'         => 'Iniciar sesión',

    // ---------- Configuración inicial ----------
    'setup.title'        => 'Configuración',
    'setup.brand'        => 'Configuración de inicio de sesión',
    'setup.sub'          => 'Establecer o restablecer la contraseña',
    'setup.csrf'         => 'Solicitud no válida, recarga la página.',
    'setup.locked'       => 'Configuración bloqueada: primero introduce en el archivo config.php un valor '
                          . 'secreto propio para {key} (ver config.sample.php) y vuelve a subir el archivo.',
    'setup.not_writable' => 'La carpeta «auth» no tiene permiso de escritura para el servidor web. Por eso '
                          . 'la contraseña no se puede guardar; concede permiso de escritura sobre la '
                          . 'carpeta «auth» al usuario del servidor web (grupo «http»).',
    'setup.key_wrong'    => 'La clave de configuración es incorrecta.',
    'setup.pw_too_short' => 'La contraseña debe tener al menos 8 caracteres.',
    'setup.pw_mismatch'  => 'Las dos contraseñas no coinciden.',
    'setup.saved'        => 'Contraseña establecida correctamente. Ya puedes iniciar sesión.',
    'setup.save_failed'  => 'Error al guardar: la carpeta «auth» no tiene permiso de escritura para el '
                          . 'servidor web. Concede permiso de escritura sobre la carpeta al usuario del '
                          . 'servidor web (grupo «http») y vuelve a intentarlo.',
    'setup.to_login'     => 'Ir a iniciar sesión',
    'setup.key_label'    => 'Clave de configuración',
    'setup.new_pw'       => 'Nueva contraseña',
    'setup.new_pw2'      => 'Repetir nueva contraseña',
    'setup.save'         => 'Guardar contraseña',

    // ---------- Gestión de dispositivos ----------
    'devices.title'         => 'Dispositivos',
    'devices.brand'         => 'Gestionar dispositivos',
    'devices.sub'           => 'Añadir o eliminar dispositivos de destino',
    'devices.csrf'          => 'Solicitud no válida, vuelve a intentarlo.',
    'devices.name_required' => 'Indica un nombre de dispositivo.',
    'devices.name_too_long' => 'El nombre del dispositivo no puede superar los 40 caracteres.',
    'devices.mac_invalid'   => 'La dirección MAC no es válida. Se esperan 12 caracteres hexadecimales, p. ej. 00:11:22:33:44:55.',
    'devices.exists'        => 'Ya existe un dispositivo con ese nombre. Elimínalo primero o elige otro nombre.',
    'devices.added'         => 'Dispositivo «%s» añadido.',
    'devices.removed'       => 'Dispositivo «%s» eliminado.',
    'devices.not_found'     => 'Dispositivo no encontrado.',
    'devices.save_failed'   => 'Error al guardar: la carpeta «auth» no tiene permiso de escritura para el servidor web.',
    'devices.not_writable'  => 'La carpeta «auth» no tiene permiso de escritura para el servidor web: los '
                             . 'cambios no se pueden guardar.',
    'devices.none'          => 'Todavía no se han añadido dispositivos.',
    'devices.your_devices'  => 'Tus dispositivos',
    'devices.remove'        => 'Eliminar',
    'devices.confirm'       => '¿Eliminar realmente el dispositivo «%s»?',
    'devices.new'           => 'Nuevo dispositivo',
    'devices.name'          => 'Nombre del dispositivo',
    'devices.name_ph'       => 'p. ej. PC del salón',
    'devices.mac'           => 'Dirección MAC',
    'devices.ip'            => 'Dirección IP',
    'devices.ip_ph'         => 'p. ej. 192.168.1.50 (opcional)',
    'devices.ip_invalid'    => 'La dirección IP no es válida.',
    'devices.ip_save'       => 'Guardar dirección IP',
    'devices.ip_saved'      => 'Dirección IP guardada para «%s».',
    'devices.add'           => 'Añadir dispositivo',

    // ---------- Gestión de claves de acceso ----------
    'passkey.title'          => 'Clave de acceso',
    'passkey.brand'          => 'Gestionar claves de acceso',
    'passkey.sub'            => 'Huella dactilar / Face ID de este dispositivo',
    'passkey.csrf'           => 'Solicitud no válida, vuelve a intentarlo.',
    'passkey.not_writable'   => 'La carpeta «auth» no tiene permiso de escritura para el servidor web: '
                               . 'los cambios no se pueden guardar.',
    'passkey.registered'     => 'Claves de acceso registradas',
    'passkey.created'        => 'registrada el %s',
    'passkey.unnamed'        => 'Sin nombre',
    'passkey.device_name'    => 'Nombre de este dispositivo',
    'passkey.device_name_ph' => 'p. ej. Mi smartphone',
    'passkey.register'       => 'Registrar clave de acceso',
    'passkey.default_device' => 'Dispositivo sin nombre',
    'passkey.remove'         => 'Eliminar',
    'passkey.confirm'        => '¿Eliminar realmente la clave de acceso «%s»?',
    'passkey.removed'        => 'Clave de acceso «%s» eliminada.',
    'passkey.not_found'      => 'Clave de acceso no encontrada.',
    'passkey.save_failed'    => 'Error al guardar: la carpeta «auth» no tiene permiso de escritura para el servidor web.',

    // ---------- Copia de seguridad / restauración ----------
    'backup.title'          => 'Copia de seguridad',
    'backup.brand'          => 'Copia de seguridad',
    'backup.sub'            => 'Guardar o restaurar la configuración y los datos',
    'backup.csrf'           => 'Solicitud no válida, vuelve a intentarlo.',
    'backup.unsupported'    => 'La extensión PHP «zip» no está activa en este servidor, por lo que la '
                             . 'copia de seguridad y la restauración no están disponibles.',
    'backup.create_title'   => 'Crear copia de seguridad',
    'backup.create_desc'    => 'Descarga un ZIP con config.php y los datos (inicio de sesión, claves de '
                             . 'acceso, lista de dispositivos). El archivo contiene la clave de '
                             . 'configuración y el hash de la contraseña; guárdalo en un lugar seguro.',
    'backup.create'         => 'Descargar copia de seguridad',
    'backup.create_failed'  => 'No se pudo crear la copia de seguridad.',
    'backup.restore_title'  => 'Restaurar copia de seguridad',
    'backup.restore_desc'   => 'Sobrescribe config.php y los datos con el contenido del archivo ZIP '
                             . 'subido. Si falta un archivo en el ZIP, se deja tal cual.',
    'backup.file_label'     => 'ZIP de copia de seguridad',
    'backup.restore_button' => 'Restaurar',
    'backup.restore_confirm'=> '¿Restaurar de verdad? Se sobrescribirán la configuración y los datos existentes.',
    'backup.no_file'        => 'Selecciona un archivo ZIP.',
    'backup.invalid_zip'    => 'No es un ZIP de copia de seguridad válido.',
    'backup.restore_failed' => 'Error al restaurar: no se pudieron escribir uno o varios archivos.',
    'backup.restore_ok'     => 'Restauración correcta. Las sesiones ya iniciadas (incluida esta) siguen activas.',

    // ---------- Mensajes WebAuthn del servidor ----------
    'wa.not_logged_in'    => 'No has iniciado sesión.',
    'wa.bad_request'      => 'Solicitud no válida.',
    'wa.no_reg_request'   => 'No hay ninguna solicitud de registro activa. Recarga la página.',
    'wa.no_login_request' => 'No hay ninguna solicitud de inicio de sesión activa. Recarga la página.',
    'wa.no_passkey'       => 'No se ha enviado ninguna clave de acceso.',
    'wa.unknown_passkey'  => 'Esta clave de acceso no está registrada aquí.',
    'wa.save_failed'      => 'Error al guardar: la carpeta «auth» no tiene permiso de escritura para el servidor web.',

    // ---------- Mensajes mostrados en el navegador (JavaScript) ----------
    'js.no_support'           => 'Este navegador no admite claves de acceso.',
    'js.no_secure_context'    => 'Las claves de acceso requieren una conexión segura (HTTPS). Esta página se sirve por HTTP; aquí solo es posible iniciar sesión con contraseña.',
    'js.confirm_biometry'     => 'Confirma con la biometría del dispositivo …',
    'js.confirm_fingerprint'  => 'Confirma con la huella dactilar/Face ID …',
    'js.register_prepare'     => 'Error al preparar el registro.',
    'js.register_failed'      => 'Error al registrar.',
    'js.register_ok'          => '¡Clave de acceso registrada correctamente!',
    'js.login_prepare'        => 'Error al preparar el inicio de sesión.',
    'js.login_failed'         => 'Error al iniciar sesión.',
    'js.login_ok'             => 'Sesión iniciada, redirigiendo …',
    'js.err_cancelled'        => 'Cancelado o se agotó el tiempo. Vuelve a intentarlo.',
    'js.err_already_registered' => 'Este dispositivo ya tiene una clave de acceso registrada.',
    'js.err_origin'           => 'La dirección de esta página no coincide con la clave de acceso. Una clave de acceso solo es válida para un único nombre de host.',
    'js.unknown_error'        => 'Error desconocido',
    'js.device_online'        => 'En línea',
];
