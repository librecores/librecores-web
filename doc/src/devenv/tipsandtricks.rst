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
  vm$> ./app/console doctrine:generate:entities LibrecoresProjectRepoBundle:Project

  # for all classes
  vm$> ./app/console doctrine:generate:entities LibrecoresProjectRepoBundle

  # finally, update the MySQL DB
  vm$> ./app/console doctrine:schema:update --force


Asynchronous Processing with RabbitMQ
-----------------------~~~~~~~~~~~~~~

Access the RabbitMQ management plugin
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
http://librecores.devel:15672

- Username: admin
- Password: password

Run the consumer
~~~~~~~~~~~~~~~~

.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./app/console rabbitmq:consumer -m 1 update_project_info

Test the producer: update one project
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

  vm$> cd /var/www/lc/site
  # update the project information of project 1 (needs the consumer!)
  vm$> echo 1 | ./app/console rabbitmq:stdin-producer update_project_info


Clean the Symfony caches
------------------------
.. code-block:: bash

  vm$> cd /var/www/lc/site
  vm$> ./app/console cache:clear


Access the MySQL database
-------------------------
.. code-block:: bash

  vm$> mysql -uroot librecores -ppassword
