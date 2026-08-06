#!/usr/bin/env bash

# Copyright (c) 2021-2026 community-scripts ORG
# Author: brunoz78
# License: MIT | https://github.com/community-scripts/ProxmoxVED/raw/main/LICENSE
# Source: https://github.com/brunoz78/wol-passkey

source /dev/stdin <<<"$FUNCTIONS_FILE_PATH"
color
verb_ip6
catch_errors
setting_up_container
network_check
update_os

msg_info "Installing Dependencies"
$STD apt install -y nginx
msg_ok "Installed Dependencies"

PHP_VERSION="8.4" PHP_FPM="YES" setup_php

fetch_and_deploy_gh_release "wol-passkey" "brunoz78/wol-passkey" "prebuild" "latest" "/opt/wol-passkey" "wol-passkey-*.zip"

msg_info "Configuring WoL Passkey"
BROADCAST=$(ip -4 -o addr show scope global | awk '{for (i = 1; i <= NF; i++) if ($i == "brd") { print $(i + 1); exit }}')
cp /opt/wol-passkey/config.sample.php /opt/wol-passkey/config.php
# The leading '.' matches the PHP sigil without fighting sed over '$'.
sed -i -e "s|^.setup_key = .*|\$setup_key = \"$(openssl rand -hex 24)\";|" \
  -e "s|^.networkbroadcast = .*|\$networkbroadcast = \"${BROADCAST:-255.255.255.255}\";|" \
  /opt/wol-passkey/config.php
chown -R www-data:www-data /opt/wol-passkey
chmod 640 /opt/wol-passkey/config.php
msg_ok "Configured WoL Passkey"

msg_info "Creating Service"
cat <<EOF >/etc/nginx/sites-available/wol-passkey
server {
    listen 80;
    listen [::]:80;
    server_name _;

    root /opt/wol-passkey;
    index index.php;

    # Includes, libraries and the self-protecting runtime data files.
    # Must stay above the PHP block - nginx takes the first matching regex.
    location ~ ^/(auth|lib|lang|partials)/ {
        deny all;
    }

    location / {
        try_files \$uri \$uri/ /index.php\$is_args\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$(get_php_fpm_socket);
    }

    access_log /var/log/nginx/wol-passkey.access.log;
    error_log /var/log/nginx/wol-passkey.error.log;
}
EOF
nginx_enable_site wol-passkey
msg_ok "Created Service"

motd_ssh
customize
cleanup_lxc
