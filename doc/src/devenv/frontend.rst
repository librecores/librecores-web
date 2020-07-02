Frontend development
====================

The frontend JavaScript and CSS files are built (minified, combined, processed, etc.) by Webpack Encore.

Interaction with all frontend tools requires you to be inside the development VM.
See the Tips and Tricks section for more information how to connect to the VM.

Build frontend files
--------------------

.. code-block:: bash

  vm$> cd /var/www/lc/site

  # Build in development mode
  vm$> yarn dev

  # Build in production mode
  vm$> yarn build

  # Build in development mode and auto-refresh on changes
  vm$> yarn watch

.. note::
  To improve performance of :code:`yarn install`, perform :code:`yarn install`
  outside the VM, inside the site folder. You need NodeJS and Yarn installed on
  the host system for this to work.

.. note::
  For assets auto-reload and HMR, run :code:`yarn dev-server:local` outside the VM.
  This will allow webpack to watch changes efficiently and compile them faster.

Install and update dependencies
--------------------------------

Dependencies are declared in `site/package.json`.
This file is used by Yarn to download and install all dependencies.

.. code-block:: bash

  vm$> cd /var/www/lc/site

  # Install dependencies
  vm$> yarn install
