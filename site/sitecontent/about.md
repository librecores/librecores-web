# About LibreCores

LibreCores is your gateway to free and open source digital designs and other components that you can use and re-use in your digital designs.
Towards this goal, LibreCores provides you

- a comprehensive and easy directory of digital design components ("IP Cores"),
- means to assess the quality of those components, and
- documentation to learn more about the use and contribution to free and open source digital designs.


## Frequently Asked Questions (FAQ)

### Who's running LibreCores?
LibreCores is a project of the [Free and Open Source Silicon (FOSSi) Foundation](http://www.fossi-foundation.org), which was created to give a voice to the digital hardware design community. LibreCores, like FOSSi Foundation, is run entirely by volunteers.

### How does LibreCores relate to OpenCores?
LibreCores advances the idea of OpenCores.org to give the community a place to share projects, ideas, and knowledge in the area of free and open source digital hardware design.

As heavy users of OpenCores, we (the FOSSi Foundation) tried everything in our power to evolve the concept together with the owners of OpenCores. Unfortunately, to this day, we were not yet able to achieve this goal.
Therefore, we decided to give it a real fresh start under the LibreCores brand.

### What are the future plans for LibreCores?
At the moment, LibreCores is in a preview state.
We've laid down much of the groundwork for the site, and are now able to iterate quickly on new ideas and features.
We do this to enable everybody to get involved and shape the future of LibreCores with the goal of making it a place where the community can truly feel at home.

Looking ahead in addition to many smaller improvements we want to focus on four major areas.

- **Project quality metrics.** Sometimes it's hard to decide if a project is usable for a given project. To make the decision process easier, we are thinking about both user-generated quality metrics (such as comments or reviews, likes, etc.) as well as machine-generated metrics (e.g. activity metrics or build and test status from continuous integration).
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
If you want to contribute to LibreCores, we have documented some ways to get started in the document [Contributing to LibreCores](http://librecores-web.readthedocs.io/en/latest/contributing.html).
Don't be scared if you're not a programmer: There are many tasks which do not require any programming experience. Just have a look and talk to us if you didn't find a suitable task just yet.

Code, bug tracker and project planning is done in the [librecores-web repository](https://github.com/librecores/librecores-web) on GitHub.

Development discussion happens on the [dev@lists.librecores.org](mailto:dev@lists.librecores.org) mailing list ([subscribe here](https://lists.librecores.org/listinfo/dev)).

Many developers also hang out on IRC on the [#librecores channel on Freenode](http://webchat.freenode.net?channels=%23librecores&uio=d4).
