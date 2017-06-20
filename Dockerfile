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
LABEL Description=" LibreCores Web image for development needs" Vendor="Librecores" Version="0.1-alpha-dev"

RUN apt-get update && apt-get update && apt-get install -y git curl

# Ansible
RUN apt-get install -y software-properties-common && apt-add-repository ppa:ansible/ansible && apt-get update && apt-get install -y ansible
WORKDIR /opt/lc
COPY ansible /opt/lc/ansible
# TODO XDG_RUNTIME_DIR is not set for the root user 
# Fails at TASK [web : reload systemd to pick up changes in Unit files]
RUN systemctl daemon-reload
RUN mkdir /opt/lc/docker && touch /opt/lc/docker/docker.marker && ansible-playbook -i "localhost," -c local ./ansible/dev-vagrant.yml

# LibreCores content mount point
VOLUME /var/www/lc
EXPOSE 80 3306
COPY docker/run.sh /opt/lc/docker/run.sh
CMD ["/opt/lc/docker/run.sh"]

