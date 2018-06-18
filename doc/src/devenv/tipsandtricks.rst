Tips and Tricks
===============

When developing, some commands and tools have proven helpful.
This page lists some (mostly unsorted) tips and tricks that make you more productive.

.. note::
  We use ``vm$>`` for commands to be executed inside the Vagrant-based development VM, and ``host$>`` for commands to be executed on the host PC as your normal user.

Connect to the VM
------------------
.. code-block:: bash

  # execute this inside your top-level code directory
  host$> cd vagrant
  host$> vagrant ssh

Rebuild ORM (Doctrine) Entity
-----------------------------

.. code-block:: bash

  vm$> cd /var/www/lc/site

  # for only one class
  vm$> ./bin/console doctrine:generate:entities LibrecoresProjectRepoBundle:Project

  # for all classes
  vm$> ./bin/console doctrine:generate:entities LibrecoresProjectRepoBundle

  # finally, update the MySQL DB
  vm$> ./bin/console doctrine:schema:update --force

Access the MySQL database
-------------------------
.. note::

  In the Vagrant development environment you can connect to the database with user "root" and password "password".

To access the database through a web frontend, phpMyAdmin is your friend. You find it at http://pma.librecores.devel.

If you prefer to access the MySQL database on the command line, you need to SSH into the VM.

.. code-block:: bash

   # use the mysql client to perform queries
   vm$> mysql -uroot -ppassword librecores

   # use mysqldump to get a dump of the whole database (or parts of it)
   mysqldump -uroot -ppassword librecores


(Yes, the password is "password".)


Asynchronous Processing with RabbitMQ
-------------------------------------

Access the RabbitMQ management plugin
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
http://librecores.devel:15672

- Username: admin
- Password: password

Run the consumer
~~~~~~~~~~~~~~~~

.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./bin/console rabbitmq:consumer -m 1 update_project_info

Test the producer: update one project
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

  vm$> cd /var/www/lc/site
  # update the project information of project 1 (needs the consumer!)
  vm$> echo 1 | ./bin/console rabbitmq:stdin-producer update_project_info

Empty the queue
~~~~~~~~~~~~~~~

.. code-block:: bash

  vm$> sudo rabbitmqctl purge_queue update-project-info


Clean the Symfony caches
------------------------
.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./bin/console cache:clear

Remote PHP debugging
--------------------

The development environment has Xdebug remote debugging enabled using the common default settings:
``xdebug.remote_port`` is set to port 9000 and `xdebug.remote_connect_back` is set to ``1``.
Please refer to your IDEs manual for further information how to make use of this functionality.

Check the coding style of PHP code
----------------------------------

.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./vendor/bin/phpcs --runtime-set ignore_warnings_on_exit true -s \
    && echo You can commit: No errors found!

Use Algolia
-----------
LibreCores makes use of `Algolia <https://www.algolia.com/>`_ to provide the search functionality.
Some settings of Algolia can be managed through its web UI, but most data and configuration is pushed from the LibreCores server.
In a development environment using Algolia is optional (if it is not used, no search functionality is available).
If Algolia should be used, first register an account at their web page (the basic account is free and sufficient for development).

Then the configuration needs to be inserted into the LibreCores web app (all data is available from the Algolia web UI).
Specify the application id (``site_algolia_app_id``), the admin API key (``site_algolia_api_key``) and the search API key (``site_algolia_search_api_key``) in the corresponding configuration file in ``ansible/secrets`` (use ``dev-vagrant.secrets.yml`` for the development settings in Vagrant).

Then push the configuration to Algolia using the ``search:settings:push`` command (see below).

Afterwards push the data to the search indices using ``search:clear`` followed by ``search:import``.

Clear indices
~~~~~~~~~~~~~

The data stored with Algolia can be removed using the following commands.

.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./bin/console search:clear

Push data to Algolia (indexing)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To send data to Algolia to index it the data needs to be "imported".
This can be done using the following commands.

.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./bin/console search:import

Backup settings
~~~~~~~~~~~~~~~
.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./bin/console search:settings:backup

Push settings to Algolia
~~~~~~~~~~~~~~~~~~~~~~~~
.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./bin/console search:settings:push
