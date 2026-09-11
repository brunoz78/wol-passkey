#!/usr/bin/env bash
# Engine comes from community-scripts/core; this repo only ships the scripts.
# A local core checkout wins (COMMUNITY_SCRIPTS_CORE_DIR, else a sibling ../core),
# so a fork or branch of core can be tested without editing this file.
_cs_boot="${COMMUNITY_SCRIPTS_CORE_DIR:-$(dirname "${BASH_SOURCE[0]}")/../../core}/core/build.func"
source "$_cs_boot" 2>/dev/null || source <(curl -fsSL "${COMMUNITY_SCRIPTS_CORE_URL:-https://raw.githubusercontent.com/community-scripts/core/main}/core/build.func")
# Copyright (c) 2021-2026 community-scripts ORG
# Author: brunoz78
# License: MIT | https://github.com/community-scripts/ProxmoxVED/raw/main/LICENSE
# Source: https://github.com/brunoz78/wol-passkey

APP="WoL-Passkey"
var_tags="${var_tags:-network;wake-on-lan}"
var_cpu="${var_cpu:-1}"
var_ram="${var_ram:-512}"
var_disk="${var_disk:-4}"
var_os="${var_os:-debian}"
var_version="${var_version:-13}"
#var_arm64="${var_arm64:-no}" # unset = ask the user; set yes/no only when verified
var_unprivileged="${var_unprivileged:-1}"

header_info "$APP"
variables
color
catch_errors

function update_script() {
  header_info
  check_container_storage
  check_container_resources

  if [[ ! -d /opt/wol-passkey ]]; then
    msg_error "No ${APP} Installation Found!"
    exit
  fi

  if check_for_gh_release "wol-passkey" "brunoz78/wol-passkey"; then
    msg_info "Stopping Services"
    systemctl stop nginx
    # Timer only exists on containers installed after it was introduced.
    systemctl stop wol-passkey-check.timer 2>/dev/null || true
    msg_ok "Stopped Services"

    create_backup /opt/wol-passkey/config.php \
      /opt/wol-passkey/auth/data.php \
      /opt/wol-passkey/auth/devices-data.php \
      /opt/wol-passkey/auth/log-data.php \
      /opt/wol-passkey/auth/status-data.php

    CLEAN_INSTALL=1 fetch_and_deploy_gh_release "wol-passkey" "brunoz78/wol-passkey" "prebuild" "latest" "/opt/wol-passkey" "wol-passkey-*.zip"

    restore_backup

    msg_info "Restoring Permissions"
    chown -R www-data:www-data /opt/wol-passkey
    chmod 640 /opt/wol-passkey/config.php
    msg_ok "Restored Permissions"

    if [[ ! -f /etc/systemd/system/wol-passkey-check.timer ]]; then
      msg_info "Creating Background Check"
      cat <<EOF >/etc/systemd/system/wol-passkey-check.service
[Unit]
Description=WoL Passkey device status check

[Service]
Type=oneshot
User=www-data
ExecStart=/usr/bin/php /opt/wol-passkey/cron.php
EOF
      cat <<EOF >/etc/systemd/system/wol-passkey-check.timer
[Unit]
Description=Run WoL Passkey device status check every minute

[Timer]
OnCalendar=minutely
AccuracySec=5s

[Install]
WantedBy=timers.target
EOF
      systemctl daemon-reload
      msg_ok "Created Background Check"
    fi

    msg_info "Starting Services"
    systemctl restart php8.4-fpm
    systemctl start nginx
    systemctl enable -q --now wol-passkey-check.timer
    msg_ok "Started Services"
    msg_ok "Updated successfully!"
  fi
  exit
}

start
build_container
description

msg_ok "Completed Successfully!\n"
echo -e "${CREATING}${GN}${APP} setup has been successfully initialized!${CL}"
echo -e "${INFO}${YW}Access it using the following URL:${CL}"
echo -e "${GATEWAY}${BGN}http://${IP}${CL}"
echo -e "${INFO}${YW}Setup key - open /setup.php once to set the login password:${CL}"
echo -e "${GATEWAY}${BGN}$(pct exec "$CTID" -- sed -n 's/^.setup_key = "\(.*\)";/\1/p' /opt/wol-passkey/config.php)${CL}"
