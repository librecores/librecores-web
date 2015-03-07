LibreCores Web Site
===================

The page is written in PHP and uses the Symfony framework.

Development
-----------
The easiest way to development is using the provided Vagrant environment.

- Install VirtualBox, if you don't already have it installed.
- Install Ansible.
- Install the NFS server packages. If you don't want to, see the note on NFS
  below.
- Install Vagrant. It's fast and simple: http://www.vagrantup.com/downloads
- `cd vagrant; vagrant up`. This might take a while.
- Take your web browser to http://192.168.33.10 and you should see the
  LibreCores web site.

On Ubuntu 14.04, this all boils down to

```
$> sudo apt-get install virtualbox ansible nfs-kernel-server
$> wget https://dl.bintray.com/mitchellh/vagrant/vagrant_1.7.2_$(uname -m).deb
$> sudo dpkg -i vagrant_1.7.2_$(uname -m).deb
$> cd vagrant
$> vagrant up
# get a cup of coffee
$> firefox http://192.168.33.10
```

### A Note on NFS
NFS is used by Vagrant for sharing the development files between your host and
the VM. NFS is the most reliable and performant way, but requires a bit more
setup.

If you use NFS, ...
- Install the NFS server on the host machine. Usually the package is named
  nfs-kernel-server.
- Disable any firewall rules preventing access from vboxnet devices to the NFS
  ports. On openSUSE, by default the "ext" firewall rule is applied, preventing
  access. The easiest way is to disable the firewall completely.

If you don't use NFS, ...
- Open the file `vagrant/Vagrantfile` and uncomment the corresponding lines of
  configuration.

