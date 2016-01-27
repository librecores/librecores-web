# LibreCores Documentation

If you are a developer of a LibreCore you are probably a good
developer of HDL designs. You are planning this very cool small
functional block that makes life so much easier for other programmers?
Or you have build an entire processor core for the last years? That is
very cool and this is a good starting point to get people involved in
your core. On this page we assembled some quick start for you into how
to publish your code, what license to choose and how to build a
community around your block.

## Publish your code

You most probably know that sending around zip files with your code to
whoever you think might be interested or putting it on a website is
not the favored way of publishing anymore.

Instead, source code is managed in code revision systems, like
subversion or git. Such repositories are widely used in software
projects and also open source digital designs benefit a lot from such
systems. While subversion is still be used a lot in closed projects,
we highly encourage using git for your source code management. It
easily allows to have different local branches where you can play
around with features and optimizations, it makes merging work of you
or other contributors very easy, and it has great support by online
platforms, just to name a few benefits.

### Where to host your code?

While LibreCores.org will be the community hub to publish your work or
find cores, we will not be a hosting site. There are free services
that can do this way better! Most known is
[Github](http://www.github.com), but also
[BitBucket](http://www.bitbucket.org) has quite a use base. It is good
to search around the platforms to learn about the benefits of the
platforms. Just to give you an idea about what you do there:

* Create multiple repositories in one namespace (your username or a
  group you form)

* Maintain a repository for your core, even privately (free with
  bitbucket)

* Create a Wiki with usage instructions and documentation of your core

* User can fork your core and can create pull requeusts (PR) with
  their changes. You can discuss those requests and make collaborative
  changes. Finally you can merge it into your master codebase with one
  click.

* Create *issues* to track open tasks or bugs

* You can even create a static website that is published on a short
  URL

You may now think: "So why should I ever come back to LibreCores.org?"
Fair question as we are still in the process of building the core of
our platform. But just to give you an idea what LibreCores.org is
about then, those are the features we are planning:

* Make your core known with our database, add basic information and
  tag it for supported interfaces or implemented features

* Connect multiple cores into systems and cross-link cores

* Endorse cores: Thumbs up for a core that you like, or a short report
  of how stable the core actually is

* Test statistics and resource utilization

### When to publish my code?

A common question is when to publish a code. This is a question not
original to digital design, but of course care must be taken if you
think people may actually pick it up and build a chip from it. Of
course they should carefully test it, but you should highlight the
state of your core honestly.

In our opinion it is good to start with publishing your code early. It
gives the community the chance to learn about your code and contribute
at early stages. That helps you and helps the community in being able
to follow your development.

Of course there are situation where you may want to put out the code
after it is fully tested and documented. That is why source code
hosting websites like Github have *releases* for. You should develop
your IP core and whenever you reached a milestone, make this a release
of your core that is supposed to be stable and tested, while you can
go on in your repository with cool new features.

We highly encourage to make your code public as soon as you are sure
you want to follow this project and work on a LibreCore. This helps
you involving the community from early on.

## Licensing

TODO: put in short license text and link to [](licenses.html)

## Community

TODO, probably remove



