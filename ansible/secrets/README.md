# Deployment and Site Secrets
This directory contains all secrets (passwords, API Keys, etc.) needed for 
provisioning and deployment.

The secret files are distributed independently, please ask one of the site 
maintainers if you need to get access to those.

You can find a template of how the secrets files should look in ../secrets.dist

In particular, the following file types are present:
- All .secrets.yml are encrypted using Ansible Vault and require a password.
  An exception is usually the dev-vagrant.secrets.yml file, which is not
  encrypted and not shared.
- All *-sshkeys.secrets.yml files contain SSH key material for the corresponding
  environment.

## Editing Ansible Vault files
ansible-vault edit FILE.secrets.yml

For more information, see http://docs.ansible.com/ansible/playbooks_vault.html

## Creating SSH Keys
To regenerate the SSH keys, run

ssh-keygen -b 2048 -f staging_key -C stage.librecores.org
and write the outputs of staging_key{.pub} into the vault file.

