# Ansible Variables
This directory contains variables used by all Ansible plays.

- All .secrets.yml are encrypted using Ansible Vault and require a password.
- All *-sshkeys.secrets.yml files contain SSH key material for the corresponding
  environment.

## Editing Ansible Vault files
ansible-vault edit FILE.secrets.yml

For more information, see http://docs.ansible.com/ansible/playbooks_vault.html

## Creating SSH Keys
To regenerate the SSH keys, run

ssh-keygen -b 2048 -f staging_key -C stage.librecores.org
and write the outputs of staging_key{.pub} into the vault file.
