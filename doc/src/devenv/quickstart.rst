Quickstart: Set up a LibreCores development environment
=======================================================

.. note:: All documentation assumes that you're running Linux.
  Unless noted otherwise, we have tested Ubuntu 16.04 and openSUSE Tumbleweed.
  If you're running another operating system or version, please get in touch when you run into trouble.
  Note that a Linux installation inside a virtual machine (VM) is usually not sufficient as the development environment runs inside a VM itself.

Step 1: Get the code from git
-----------------------------

First, you need to get the current development code from Git.

.. code-block:: bash

  cd your_preferred_code_directory
  git clone https://github.com/librecores/librecores-web.git

.. note::
  Throughout the documentation, most file paths will be given relative to the directory where you cloned the librecores-web repository.

Step 2: Provide secrets
-----------------------
LibreCores communicates with some third-party APIs, such as GitHub or Google, for user logins and other things.
To talk to these APIs, personalized API keys ("secrets") are needed.

First, copy the default secrets file as starting point:

.. code-block:: bash

  cp ansible/secrets.dist/dev-vagrant.secrets.yml ansible/secrets/dev-vagrant.secrets.yml

Now you have two options:

- Either open the ``ansible/secrets/dev-vagrant.secrets.yml`` and follow the instructions in there to get a custom API key for the various providers, or
- Leave the file as-is and therefore disable all third-party APIs. This is sufficient if you do not want to change the user-login process.

Step 3: Set up the development environment
------------------------------------------

LibreCores uses `Vagrant <https://www.vagrantup.com/>`_ and `Ansible <https://www.ansible.com/>`_ to provide a development environment that's very similar to our production environments.
To simplify the setup of all necessary components, we provide a bootstrap script for Ubuntu and openSUSE.
Simply run

.. code-block:: bash

  ./bootstrap-dev.sh

to install all dependencies and get started.

Alternative version of step 3: Manually installing the development environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're not using openSUSE or Ubuntu, you need to manually install and configure the required dependenies.

- Install VirtualBox, if you don't already have it installed.
- Install Ansible >= 2.0
- Install the NFS server packages. If you don't want to, see the note on NFS
  below.
- Install Vagrant. It's fast and simple: http://www.vagrantup.com/downloads.html
- Install vagrant-hostmanager: ``vagrant plugin install vagrant-hostmanager``
- ``cd vagrant; vagrant up``. This might take a while.
- Take your web browser to http://librecores.devel and you should see the
  LibreCores web site.

**A Note on NFS**

NFS is used by Vagrant for sharing the development files between your host and
the VM. NFS is the most reliable and performant way, but requires a bit more
setup.

If you use NFS, ...

- Install the NFS server on the host machine. Usually the package is named nfs-kernel-server.
- Make sure that the daemon runs (``sudo sytemctl status nfs-server``)
- Disable any firewall rules preventing access from vboxnet devices to the NFS ports. On openSUSE, by default the "ext" firewall rule is applied, preventing access. The easiest way is to disable the firewall completely.

If you don't use NFS, ...

- Open the file ``vagrant/Vagrantfile`` and uncomment the corresponding lines of configuration.


Step 4: Develop!
----------------

Now that all setup is done, you can start developing.
First, point your web browser to http://librecores.devel.
This will open the development version of LibreCores running on your machine.
Whenever you make a code change, a simple reload of the page in your browser is usually sufficient to show the changes.

Happy coding!
