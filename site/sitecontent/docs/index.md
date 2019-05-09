---
title: Documentation
---
# LibreCores Documentation

<img src="/static/img/freepik/clipboard-sketch.png" align="right" hspace="25" vspace="10" />

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

<img src="/static/img/freepik/upload-to-internet-cloud-sketch.png" align="right" hspace="25" vspace="10" />

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

* User can fork your core and can create pull requests (PR) with
  their changes. You can discuss those requests and make collaborative
  changes. Finally you can merge it into your master codebase with one
  click.

* Create *issues* to track open tasks or bugs

* You can even create a static website that is published on a short
  URL

<img src="/static/img/freepik/rocket-sketch.png" align="right" hspace="25" vspace="10" />

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

<img src="/static/img/freepik/halloween-october-31-calendar-page-sketch.png" align="right" hspace="25" vspace="10" />

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

Another important aspect is to add a license to your code. The license
defines how people can use your code and what obligations they have.

<img src="/static/img/freepik/certificate-of-education-hand-drawn.png" align="right" hspace="25" vspace="10" />

The first question is how your code can be used. That answer is
actually simple: By downloading it. There are no further restrictions,
thats the idea of open source. So, despite we can understand the idea
behind adding a non-commercial clause or a non-military clause, we
highly discourage you to add any such clauses to your licenses. They
are against the definition of open source (see
[Open Source Initiative, crit. 6](opensource.org/osd): "No
Discrimination Against Fields of Endeavor - The license must not
restrict anyone from making use of the program in a specific field of
endeavor. For example, it may not restrict the program from being used
in a business, or from being used for genetic research.").

There are more options when it comes to obligations and open source
licenses vary in the *permissiveness* they give the user to deploy and
release the work derived from your code. The permissiveness of the
license is a matter for the licensor (the author, copyright owner) and
often depends on their position regarding reuse of the design. The
[more permissive](https://en.wikipedia.org/wiki/Permissive_free_software_licence)
the license, the less requirements on future users and developers of
the IP. Less permissive licenses have more restrictions, which
generally ensure the derivative works remain as free and open as the
original work. These less permissive licenses, also known as
[copyleft](https://en.wikipedia.org/wiki/Copyleft) licenses for how
they enforce the work’s openness, vary greatly from
[weak to strong copyleft](https://en.wikipedia.org/wiki/Copyleft#Strong_and_weak_copyleft)
and primarily indicate how a derivative work may be licensed - whether
it must be entirely released under an equivalently open license
(strong copyleft,
[reciprocal licenses](https://en.wikipedia.org/wiki/Viral_license)) or
not.

Almost all strong copyleft licenses have been written to apply to
software, and the mechanisms they specify (binary linking, for
example) that qualify a derivative work to be released under the same
terms and conditions - guaranteeing the IP remain open and free to be
reused - are problematic attempting to apply them a HDL design.

Once you come to a decision what kind of license you want to use,
there are a few licenses that are FOSSi-compatbile in our opinion and
that you should consider using:

 * *Permissive* licenses have two popular candidates in the software
   world: [MIT license](https://opensource.org/licenses/MIT) and
   [BSD license](https://opensource.org/licenses/BSD-3-Clause). They
   are simple and compatible with FOSSi. If you plan to add a patent
   clause, the
   [Apache license](https://opensource.org/licenses/Apache-2.0) is the
   choice in the software world and there is an adoption for hardware
   as the [Solderpad license](http://solderpad.org/licenses/).

 * The most popular *weak copyleft* license in the software world is
   the [LGPL](https://opensource.org/licenses/lgpl-license). The
   problem with the usage of it is the question what "linkage" means
   in the context of FOSSi. There is one adoption for hardware as the
   [OHDL](http://juliusbaxter.net/ohdl/) that defines linkage on file
   level.

 * For *strong copyleft* licenses the
   [GPL](http://opensource.org/licenses/gpl-license) is very popular
   in the software world. While the 2nd version of it caused many
   discussions around applying it to hardware, GPLv3 actually is
   written in a hardware-friendly language. Nevertheless, there is an
   ongoing discussion around the implications of it for hardware
   implementations.
