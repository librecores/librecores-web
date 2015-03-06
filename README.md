LibreCores Web Site
===================

The page is written in PHP and uses the Symfony framework.

Development
-----------
The easiest way to development is using the provided Vagrant environment.

- Install VirtualBox, if you don't already have it installed.
- Install Ansible.
- Install Vagrant. It's fast and simple: http://www.vagrantup.com/downloads
- `cd vagrant; vagrant up`. This might take a while.
- Take your web browser to http://192.168.33.10 and you should see the LibreCores web site.

On Ubuntu 14.04, this all boils down to

```
$> sudo apt-get install virtualbox ansible
$> wget https://dl.bintray.com/mitchellh/vagrant/vagrant_1.7.2_x86_64.deb
$> sudo dpkg -i vagrant_1.7.2_x86_64.deb
$> cd vagrant
$> vagrant up
# get a cup of coffee
$> firefox http://192.168.33.10
```

