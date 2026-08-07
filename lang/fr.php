<?php
/*
  Textes français. L'allemand (lang/de.php) est la langue source : si une clé
  est absente ici, le texte allemand est utilisé automatiquement.
*/
return [
    // ---------- Navigation / barre supérieure ----------
    'nav.wake'        => 'Réveiller',
    'nav.devices'     => 'Gérer les appareils',
    'nav.passkey'     => 'Gérer les clés d\'accès',
    'nav.backup'      => 'Sauvegarde',
    'nav.logout'      => 'Déconnexion',
    'nav.aria'        => 'Navigation',
    'nav.open'        => 'Ouvrir le menu',
    'nav.close'       => 'Fermer le menu',
    'nav.language'    => 'Langue',
    'nav.about'       => 'À propos (version %s)',

    // ---------- Notice de mise à jour ----------
    'update.available' => 'Mise à jour disponible : version %s',

    'theme.aria'      => 'Choisir un thème',
    'theme.light'     => 'Thème clair',
    'theme.dark'      => 'Thème sombre',
    'theme.vivid'     => 'Thème vif',
    'lang.aria'       => 'Choisir une langue',

    // ---------- Page d'accueil (réveil) ----------
    'index.sub'          => 'Choisir un appareil et le réveiller',
    'index.csrf'         => 'Requête invalide, veuillez renvoyer le formulaire.',
    'index.wake_failed'  => 'Le paquet magique n\'a pas pu être envoyé.',
    'index.wake_sent'    => 'Réveil envoyé à %s',
    'index.no_devices'   => 'Aucun appareil ajouté pour l\'instant.',
    'index.add_device'   => 'Ajouter un appareil',
    'index.your_devices' => 'Vos appareils',
    'index.wake'         => 'Réveiller',

    // ---------- Connexion ----------
    'login.title'          => 'Connexion',
    'login.sub'            => 'Veuillez vous connecter',
    'login.csrf'           => 'Requête invalide, veuillez recharger la page.',
    'login.locked'         => 'Trop de tentatives échouées. Veuillez réessayer dans %d minute(s).',
    'login.no_password'    => 'Aucun mot de passe n\'a encore été défini. Veuillez d\'abord ouvrir setup.php.',
    'login.wrong_password' => 'Mot de passe incorrect.',
    'login.with_passkey'   => 'Se connecter avec une clé d\'accès',
    'login.or_password'    => 'ou avec un mot de passe',
    'login.password'       => 'Mot de passe',
    'login.submit'         => 'Se connecter',

    // ---------- Configuration initiale ----------
    'setup.title'        => 'Configuration',
    'setup.brand'        => 'Configuration de connexion',
    'setup.sub'          => 'Définir ou réinitialiser le mot de passe',
    'setup.csrf'         => 'Requête invalide, veuillez recharger la page.',
    'setup.locked'       => 'Configuration verrouillée : veuillez d\'abord définir une valeur secrète '
                          . 'personnelle pour {key} dans le fichier config.php (voir config.sample.php) '
                          . 'et retélécharger le fichier.',
    'setup.not_writable' => 'Le dossier « auth » n\'est pas accessible en écriture pour le serveur web. '
                          . 'Le mot de passe ne peut donc pas être enregistré – veuillez donner les droits '
                          . 'd\'écriture sur le dossier « auth » à l\'utilisateur du serveur web (groupe « http »).',
    'setup.key_wrong'    => 'La clé de configuration est incorrecte.',
    'setup.pw_too_short' => 'Le mot de passe doit comporter au moins 8 caractères.',
    'setup.pw_mismatch'  => 'Les deux mots de passe ne correspondent pas.',
    'setup.saved'        => 'Mot de passe défini avec succès. Vous pouvez maintenant vous connecter.',
    'setup.save_failed'  => 'Échec de l\'enregistrement : le dossier « auth » n\'est pas accessible en '
                          . 'écriture pour le serveur web. Veuillez donner les droits d\'écriture sur le '
                          . 'dossier à l\'utilisateur du serveur web (groupe « http ») et réessayer.',
    'setup.to_login'     => 'Aller à la connexion',
    'setup.key_label'    => 'Clé de configuration',
    'setup.new_pw'       => 'Nouveau mot de passe',
    'setup.new_pw2'      => 'Répéter le nouveau mot de passe',
    'setup.save'         => 'Enregistrer le mot de passe',

    // ---------- Gestion des appareils ----------
    'devices.title'         => 'Appareils',
    'devices.brand'         => 'Gérer les appareils',
    'devices.sub'           => 'Ajouter ou supprimer des appareils cibles',
    'devices.csrf'          => 'Requête invalide, veuillez réessayer.',
    'devices.name_required' => 'Veuillez indiquer un nom d\'appareil.',
    'devices.name_too_long' => 'Le nom de l\'appareil ne doit pas dépasser 40 caractères.',
    'devices.mac_invalid'   => 'L\'adresse MAC est invalide. 12 caractères hexadécimaux sont attendus, par ex. 00:11:22:33:44:55.',
    'devices.exists'        => 'Un appareil portant ce nom existe déjà. Veuillez d\'abord le supprimer ou choisir un autre nom.',
    'devices.added'         => 'Appareil « %s » ajouté.',
    'devices.removed'       => 'Appareil « %s » supprimé.',
    'devices.not_found'     => 'Appareil introuvable.',
    'devices.save_failed'   => 'Échec de l\'enregistrement : le dossier « auth » n\'est pas accessible en écriture pour le serveur web.',
    'devices.not_writable'  => 'Le dossier « auth » n\'est pas accessible en écriture pour le serveur web '
                             . '– les modifications ne peuvent donc pas être enregistrées.',
    'devices.none'          => 'Aucun appareil ajouté pour l\'instant.',
    'devices.your_devices'  => 'Vos appareils',
    'devices.remove'        => 'Supprimer',
    'devices.confirm'       => 'Vraiment supprimer l\'appareil « %s » ?',
    'devices.new'           => 'Nouvel appareil',
    'devices.name'          => 'Nom de l\'appareil',
    'devices.name_ph'       => 'p. ex. PC du salon',
    'devices.mac'           => 'Adresse MAC',
    'devices.ip'            => 'Adresse IP',
    'devices.ip_ph'         => 'p. ex. 192.168.1.50 (facultatif)',
    'devices.ip_invalid'    => 'L\'adresse IP est invalide.',
    'devices.ip_save'       => 'Enregistrer l\'adresse IP',
    'devices.ip_saved'      => 'Adresse IP enregistrée pour « %s ».',
    'devices.add'           => 'Ajouter un appareil',

    // ---------- Gestion des clés d'accès ----------
    'passkey.title'          => 'Clé d\'accès',
    'passkey.brand'          => 'Gérer les clés d\'accès',
    'passkey.sub'            => 'Empreinte digitale / Face ID de cet appareil',
    'passkey.csrf'           => 'Requête invalide, veuillez réessayer.',
    'passkey.not_writable'   => 'Le dossier « auth » n\'est pas accessible en écriture pour le serveur web '
                               . '– les modifications ne peuvent donc pas être enregistrées.',
    'passkey.registered'     => 'Clés d\'accès enregistrées',
    'passkey.created'        => 'enregistrée le %s',
    'passkey.unnamed'        => 'Sans nom',
    'passkey.device_name'    => 'Nom de cet appareil',
    'passkey.device_name_ph' => 'p. ex. Mon smartphone',
    'passkey.register'       => 'Enregistrer une clé d\'accès',
    'passkey.default_device' => 'Appareil sans nom',
    'passkey.remove'         => 'Supprimer',
    'passkey.confirm'        => 'Vraiment supprimer la clé d\'accès « %s » ?',
    'passkey.removed'        => 'Clé d\'accès « %s » supprimée.',
    'passkey.not_found'      => 'Clé d\'accès introuvable.',
    'passkey.save_failed'    => 'Échec de l\'enregistrement : le dossier « auth » n\'est pas accessible en écriture pour le serveur web.',

    // ---------- Sauvegarde / restauration ----------
    'backup.title'          => 'Sauvegarde',
    'backup.brand'          => 'Sauvegarde',
    'backup.sub'            => 'Sauvegarder ou restaurer la configuration et les données',
    'backup.csrf'           => 'Requête invalide, veuillez réessayer.',
    'backup.unsupported'    => 'L\'extension PHP « zip » n\'est pas active sur ce serveur - la sauvegarde '
                             . 'et la restauration ne sont donc pas disponibles.',
    'backup.create_title'   => 'Créer une sauvegarde',
    'backup.create_desc'    => 'Télécharge un ZIP contenant config.php ainsi que les données (connexion, '
                             . 'clés d\'accès, liste des appareils). Le fichier contient la clé de '
                             . 'configuration et le hash du mot de passe - à conserver en lieu sûr.',
    'backup.create'         => 'Télécharger la sauvegarde',
    'backup.create_failed'  => 'La sauvegarde n\'a pas pu être créée.',
    'backup.restore_title'  => 'Restaurer une sauvegarde',
    'backup.restore_desc'   => 'Écrase config.php ainsi que les données avec le contenu du fichier ZIP '
                             . 'envoyé. Un fichier absent du ZIP reste inchangé.',
    'backup.file_label'     => 'ZIP de sauvegarde',
    'backup.restore_button' => 'Restaurer',
    'backup.restore_confirm'=> 'Vraiment restaurer ? La configuration et les données existantes seront écrasées.',
    'backup.no_file'        => 'Veuillez sélectionner un fichier ZIP.',
    'backup.invalid_zip'    => 'Ce n\'est pas un ZIP de sauvegarde valide.',
    'backup.restore_failed' => 'Échec de la restauration : le(s) fichier(s) n\'ont pas pu être écrits.',
    'backup.restore_ok'     => 'Restauration réussie. Les sessions déjà connectées (y compris celle-ci) restent actives.',

    // ---------- Messages WebAuthn du serveur ----------
    'wa.not_logged_in'    => 'Non connecté.',
    'wa.bad_request'      => 'Requête invalide.',
    'wa.no_reg_request'   => 'Aucune demande d\'enregistrement active. Veuillez recharger la page.',
    'wa.no_login_request' => 'Aucune demande de connexion active. Veuillez recharger la page.',
    'wa.no_passkey'       => 'Aucune clé d\'accès envoyée.',
    'wa.unknown_passkey'  => 'Cette clé d\'accès n\'est pas enregistrée ici.',
    'wa.save_failed'      => 'Échec de l\'enregistrement : le dossier « auth » n\'est pas accessible en écriture pour le serveur web.',

    // ---------- Messages affichés dans le navigateur (JavaScript) ----------
    'js.no_support'           => 'Ce navigateur ne prend pas en charge les clés d\'accès.',
    'js.no_secure_context'    => 'Les clés d\'accès nécessitent une connexion sécurisée (HTTPS). Cette page est servie en HTTP – seule la connexion par mot de passe est possible ici.',
    'js.confirm_biometry'     => 'Veuillez confirmer avec la biométrie de l\'appareil …',
    'js.confirm_fingerprint'  => 'Veuillez confirmer avec l\'empreinte digitale/Face ID …',
    'js.register_prepare'     => 'Erreur lors de la préparation de l\'enregistrement.',
    'js.register_failed'      => 'Échec de l\'enregistrement.',
    'js.register_ok'          => 'Clé d\'accès enregistrée avec succès !',
    'js.login_prepare'        => 'Erreur lors de la préparation de la connexion.',
    'js.login_failed'         => 'Échec de la connexion.',
    'js.login_ok'             => 'Connexion réussie, redirection …',
    'js.err_cancelled'        => 'Annulé ou délai dépassé. Veuillez réessayer.',
    'js.err_already_registered' => 'Cet appareil possède déjà une clé d\'accès enregistrée.',
    'js.err_origin'           => 'L\'adresse de cette page ne correspond pas à la clé d\'accès. Une clé d\'accès n\'est valable que pour un seul nom d\'hôte.',
    'js.unknown_error'        => 'Erreur inconnue',
    'js.device_online'        => 'En ligne',
];
