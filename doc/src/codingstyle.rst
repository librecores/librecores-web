Coding Style
============

A common coding style helps to keep our code readable and maintainable.
Towards that goal the most important rule is: write code in the same style as existing code.
Look at current code and try to mimic what you see.
If you have good reason to do things differently, do so.

Find below a discussion of the coding style we use for the different programming languages.

PHP
---

The LibreCores PHP code follows mostly the `Symfony Coding Standards <https://symfony.com/doc/current/contributing/code/standards.html>`_.
The following exceptions/changes and extensions are made:

* Try to keep lines at 80 characters length.
* Yoda-style comparisons (e.g. ``if (0 === $someVar)``) are discouraged.
* Use the ``||`` and ``&&`` operators for logical AND and OR (instead of ``and`` and ``or``).

The PHP code can be checked using the `phpcs <https://github.com/squizlabs/PHP_CodeSniffer>`_ tool.
The required configuration is part of the source repository (``site/phpcs.xml.dist``) and will be picked up by phpcs automatically.
