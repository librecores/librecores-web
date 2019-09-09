Notification System
===================

The notification system sends out notifications to the users.

To interact with the notification system, you need to be inside the VM.
You also need to configure the email credentials before sending emails via Email notification
consumer.

Run the notification consumer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. code-block:: bash

  vm$> ./bin/console rabbitmq:multiple-consumer notification

Create a notification using Symfony command
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. code-block:: bash

  vm$> ./bin/console librecores:send-notification "$subject" "$message" "$type" "$recipient"

Swiftmailer configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The swiftmailer configuration resides in `ansible/secrets/dev-vagrant.secrets.yml`. The
configuration must be carefully set up to allow emails to be sent without throwing
any exceptions that may break the crawler.
Refer to `Symfony documentation <https://symfony.com/doc/current/email.html>`_ for
more details on configuring swiftmailer parameters.

An example is given below:

.. code-block:: bash

  site_smtp_transport: "smtp"
  site_smtp_host: "smtp.gmail.com"
  site_smtp_port: 465
  site_smtp_encryption: 'ssl'
  site_smtp_user: "YOUR_GMAIL_ADDRESS"
  site_smtp_password: "YOUR_GMAIL_PASSWORD"


Notification mailer configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Currently, the notification-mailer configuration resides in `ansible/vars/dev-vagrant.yml`.
You can change it according to your preferences.

.. code-block:: bash

  notification_from_address: "NOTIFICATION_SENDER_ADDRESS"
  notification_from_name: "NOTIFICATION_SENDER_NAME"

