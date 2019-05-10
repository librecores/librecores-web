---
title: LibreCores CI
---
# LibreCores CI

[LibreCores CI](https://ci.librecores.org/) is a service, 
which provides Continuous Integration of projects being hosted on LibreCores.
The objective of the service is to improve the contributor experience and to increase trust to projects by providing automated testing and health metrics of the projects.
Currently the service is **under development**.

<img src="/img/ci-overview.svg" class="img-responsive" style="float: right; margin: 20px"/>

With LibreCores CI you will be able to...

* Build your projects using [FuseSoC](https://github.com/olofk/fusesoc) and open-source EDAs
* Run automated simulation tests in the cloud
* Run tests on FPGAs and custom peripherals using "bring your own" agents. Users or sponsors can also provide their FPGA agents for other individual projects or even all projects tagged for that FPGA.
* Define custom build flows using [Jenkins Pipeline](https://jenkins.io/doc/book/pipeline/) 
* Setup pull request builders and test reporters in order to improve the contributor experience

The service is powered by [Jenkins](https://jenkins.io/index.html).
More information about the project is available in [GitHub](https://github.com/librecores/librecores-ci/).

## Current status

The LibreCores CI instance is **under development** now.
The short-term plan is to provide a generic framework for automation of the FuseSoC-based projects.

Status overview from [ORCONF2016](http://orconf.org/):

<iframe id="51cda97e786f411abab92287754e486d" data-ratio="1.33333333333333" src="https://speakerd.s3.amazonaws.com/presentations/51cda97e786f411abab92287754e486d/ORCONF_LibrecoresCI.pdf" width="100%" height="500" style="border:0; padding:0; margin:0; background:transparent;" frameborder="0" allowtransparency="true" allowfullscreen="allowfullscreen" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>

In the case of any questions, please use bugtracker in [GitHub](https://github.com/librecores/librecores-ci/).

## Become a beta-tester

If you are interested to try LibreCores CI, please contact us via the 
[dev@lists.librecores.org](https://lists.librecores.org/listinfo/dev) mailing list.
