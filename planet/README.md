Planet LibreCores
=================

This directory contains the code for Planet Librecores, a feed aggregator
based on [Planet Venus](http://intertwingly.net/code/venus/). Venus lives
inside the "generator" folder.

We use Venus only to generate an aggregated Atom feed (together with a FOAF
and OPML subscription list). These feeds are then stored in the web folder
of the main Symfony web app. The feed is displayed on the LibreCores site
through PHP code. This allows us to use the usual Symfony theming and integrate
seamlessly into the page.

cron calls the generate.sh script in regular intervals.

The configuration (most importantly, which blogs to aggregate) is contained
in the config.ini file.

We use Planet Venus from git, revision 9de2109 from 18 Feb 2011.
