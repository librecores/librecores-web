# Contributing to the LibreCores Site

## The code
- All code lives the
  [librecores-web](https://github.com/librecores/librecores-web)
  repository on GitHub.
- In addition to the code, the repository also contains all necessary deployment
  scripts to create a Vagrant-based development environment, and to deploy the
  site to Amazon Web Services.
- We use three main branches:
  - The `master` branch is the current development code.
  - The `staging` branch is the code that's deployed to our staging site,
    http://stage.librecores.org
  - The `production` branch is the code that's deployed to our production site,
    http://www.librecores.org


## Get a development environment

### Classic Vagrant flow

Read the [README](README.md) in this repository for more information how to
get started.

### Docker Flow

:exclamation: Docker development flow is under...  development.
It may misbehave in some cases, and the performance is far from the desired level.
Use with caution.
The single-container flow is also not designed for the production use.

#### Building locally

```shell
docker build -t librecores/librecores-web-dev .
```

#### Running the container

The sample below runs LibreCores Web in a single container.

```shell
docker run --rm -p 8080:80 -p 15672:15672 -it -v ${YOUR_LIBRECORES_WEB_REPO}:/var/www/lc librecores/librecores-web-dev
```

## How to contribute
- Please usually open an issue on GitHub, or assign an existing one to yourself
  before starting development. This helps to track who's working on what.
- If you have questions on how to solve a problem, please ask on the development
  mailing list or on IRC (see below).
- If you're done with your changes, please open a pull request against the
  `master` branch.
- By submitting a pull request, you agree to license your changes under the MIT
  license (unless stated otherwise).
- After review, we'll merge your changes and push it out to staging and later to
  production. This is currently a manual process.

## Questions?
- Mailing list: [dev@lists.librecores.org](mailto:dev@lists.librecores.org)
- IRC: #librecores on freenode
