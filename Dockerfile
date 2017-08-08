# The MIT License
#
#  Copyright (c) 2017, Oleg Nenashev
#
#  Permission is hereby granted, free of charge, to any person obtaining a copy
#  of this software and associated documentation files (the "Software"), to deal
#  in the Software without restriction, including without limitation the rights
#  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
#  copies of the Software, and to permit persons to whom the Software is
#  furnished to do so, subject to the following conditions:
#
#  The above copyright notice and this permission notice shall be included in
#  all copies or substantial portions of the Software.
#
#  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
#  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
#  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
#  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
#  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
#  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
#  THE SOFTWARE.

FROM ubuntu:16.04
MAINTAINER Oleg Nenashev <o.v.nenashev@gmail.com>
LABEL Description="LibreCores Web image for development needs" Vendor="Librecores" Version="0.1-alpha-dev"

RUN apt-get update && apt-get install -y software-properties-common && apt-add-repository ppa:ansible/ansible && apt-get update && apt-get install -y git curl ansible

# Web User
RUN useradd -ms /bin/bash webuser

# Embedded Web Contents
# They are not supposed to be actually used when you 
# COPY site /var/www/lc/site
# COPY planet /var/www/lc/planet

# Ansible
WORKDIR /opt/lc
COPY ansible /opt/lc/ansible
COPY sql/dev_fixtures_blog.sql /var/www/lc/sql/dev_fixtures_blog.sql
# TODO XDG_RUNTIME_DIR is not set for the root user 
# Fails at TASK [web : reload systemd to pick up changes in Unit files]
#RUN systemctl daemon-reload
RUN mkdir /opt/lc/docker && touch /opt/lc/docker/docker.marker && ansible-playbook -i "localhost," --extra-vars "ansible_ssh_user=webuser" -c local ./ansible/dev-vagrant.yml

# LibreCores content mount point
VOLUME /var/www/lc
EXPOSE 80 3306
COPY docker/run.sh /opt/lc/docker/run.sh
RUN chmod 755 /opt/lc/docker/run.sh
RUN cp /opt/lc/site/app/config/parameters.yml /opt/lc/site/app/config/parameters.yml.dist
RUN chmod 777 /opt/lc/site/app/config/parameters.yml && chmod 777 /opt/lc/site/app/config/parameters.yml.dist


# Run
USER root
RUN mysqld -user=root -password=password &

USER webuser
CMD ["/opt/lc/docker/run.sh"]

