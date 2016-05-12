LibreCores Web Site
===================

The page is written in PHP and uses the Symfony framework.

Development
-----------
The easiest way to development is using the provided Vagrant environment.
Simply run
```
cp ansible/secrets.dist/dev-vagrant.secrets.yml ansible/secrets/dev-vagrant.secrets.yml
# You can enter OAuth tokens into the secrets files if you want to. Get
# them directly from the OAuth provider.
#edit ansible/secrets/dev-vagrant.secrets.yml
./bootstrap-dev.sh
```
to install all dependencies (see below) and get started.

### Manually installing the development environment

- Install VirtualBox, if you don't already have it installed.
- Install Ansible.
- Install the NFS server packages. If you don't want to, see the note on NFS
  below.
- Install Vagrant. It's fast and simple: http://www.vagrantup.com/downloads
- Install vagrant-hostmanager: `vagrant plugin install vagrant-hostmanager`
- Create the secrets file `ansible/secrets/dev-vagrant.secrets.yml`. A 
  template is available in `ansible/secrets.dist/dev-vagrant.secrets.yml`
- `cd vagrant; vagrant up`. This might take a while.
- Take your web browser to http://librecores.devel and you should see the
  LibreCores web site. At http://blog.librecores.devel you'll find the blog.

### A Note on NFS
NFS is used by Vagrant for sharing the development files between your host and
the VM. NFS is the most reliable and performant way, but requires a bit more
setup.

If you use NFS, ...
- Install the NFS server on the host machine. Usually the package is named
  nfs-kernel-server.
- Make sure that the daemon runs (sudo sytemctl status nfs-server)
- Disable any firewall rules preventing access from vboxnet devices to the NFS
  ports. On openSUSE, by default the "ext" firewall rule is applied, preventing
  access. The easiest way is to disable the firewall completely.

If you don't use NFS, ...
- Open the file `vagrant/Vagrantfile` and uncomment the corresponding lines of
  configuration.


### Installed Tools in the VM

- RabbitMQ management plugin:
  http://librecores.devel:15672
  Username: admin
  Password: password

