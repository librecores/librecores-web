---
title: About LibreCores
---
# About LibreCores

LibreCores is your gateway to free and open source digital designs and other components that you can use and re-use in your digital designs.
Towards this goal, LibreCores provides you

- a comprehensive and easy directory of digital design components ("IP Cores"),
- means to assess the quality of those components, and
- documentation to learn more about the use and contribution to free and open source digital designs.

Please find our feature [roadmap below](#roadmap).

## Frequently Asked Questions (FAQ)

### Who's running LibreCores?
LibreCores is a project of the [Free and Open Source Silicon (FOSSi) Foundation](https://www.fossi-foundation.org), which was created to give a voice to the digital hardware design community. LibreCores, like FOSSi Foundation, is run entirely by volunteers.

### How does LibreCores relate to OpenCores?
LibreCores advances the idea of OpenCores.org to give the community a place to share projects, ideas, and knowledge in the area of free and open source digital hardware design.

As heavy users of OpenCores, we (the FOSSi Foundation) tried everything in our power to evolve the concept together with the owners of OpenCores before we started LibreCores. Unfortunately, we were not able to achieve this goal at this time and decided to give it a real fresh start under the LibreCores brand. OpenCores has since then found a new home and we are happy that we have open, fruitful interaction with the new owners at [OliScience](https://oliscience.nl/).

### <a class="anchor" name="roadmap"></a> What are the future plans for LibreCores?

We continuously add features to LibreCores to make the site more valuable to our users, and to try out new things.
We strongly believe in "release early, release often" to enable everybody to get involved and shape the future of LibreCores.
We want LibreCores to be a place where the community can truly feel at home!

- **Improved discoverability.** Finding a project or an IP core which exactly fits your needs is not easy. We're experimenting with multiple ways to make it easy to find the best IP core for the job. One specific thing we're planning to add is a categorization system for projects.

- **Even more project quality metrics.** Sometimes it's hard to decide if a project is usable for a given project. To make the decision process easier, we are thinking about both user-generated quality metrics (such as comments or reviews, likes, etc.) as well as machine-generated metrics (e.g. activity metrics or build and test status from continuous integration). Some parts of this vision have already been implemented, others are still in the making.
- **Workflow integration.** Finding an IP core on LibreCores is just the first step of using it as part of an own project. Currently, making use of a core  involves in many cases copying the source code into the custom project. This process is tedious and makes updating a core to the latest upstream version hard.

  With LibreCores we want to explore different options to make this workflow more streamlined. One example is the integration of the LibreCores project repository with the [fusesoc](https://github.com/olofk/fusesoc) package manager.
- **LibreCores CI.** ContinuousIntegration of projects is a de-facto standard approach to improving project quality and contributor experience.
There are many services providing free CI hosting for open-source software projects,
but they lack some features required for hardware projects (EDA tools, running tests on hardware, etc.).

  In LibreCores we want to provide a CI instance for projects being hosted on LibreCores.
  More information is available on the [LibreCores CI page](./librecores-ci).
- **Documentation of best practices.** At LibreCores, we love digital hardware design and want more people to get involved. Unfortunately today, getting started with digital hardware design involves climbing a steep learning curve before the one reaches productivity -- and arguably that's where all the fun starts!

  We believe a good documentation of best practices, covering both non-code issues (such as "how to organize a repository", "what license options do I have"), as well as coding related advice (such as "how to code a FSM in Verilog") is essential to get started quickly and is therefore of great benefit to the community.

### How can I contribute to LibreCores?
All code and all development on LibreCores is fully open and we welcome any input you might have regarding the site.
If you want to contribute to LibreCores, we have documented some ways to get started in the document [Contributing to LibreCores](https://librecores-web.readthedocs.io/en/latest/contributing.html).
Don't be scared if you're not a programmer: There are many tasks which do not require any programming experience. Just have a look and talk to us if you didn't find a suitable task just yet.

Code, bug tracker and project planning is done in the [librecores-web repository](https://github.com/librecores/librecores-web) on GitHub.

Development discussion happens on the [dev@lists.librecores.org](mailto:dev@lists.librecores.org) mailing list ([subscribe here](https://lists.librecores.org/listinfo/dev)).

Many developers also hang out on the [LibreCores gitter channel](https://gitter.im/librecores/Lobby) or IRC on the [#librecores channel on Freenode](https://webchat.freenode.net?channels=%23librecores&uio=d4).
