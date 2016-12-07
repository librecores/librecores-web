#!/bin/bash
#
# Bootstrap Librecores Web project in Vagrant
#
# Installs all necessary components on the development machine, and optionally
# runs Vagrant to display the site in a browser.
#

set -e

# Version of vagrant we depend on
VAGRANT_VERSION=1.9.1


# make sure we're in the top-level dir
SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd $SCRIPTDIR

# check if we need to install vagrant
INSTALL_VAGRANT=1
if hash vagrant 2>/dev/null; then
  INSTALLED_VAGRANT_VERSION=$(vagrant -v)
  if [ ${INSTALLED_VAGRANT_VERSION##Vagrant } = "$VAGRANT_VERSION" ]; then
    INSTALL_VAGRANT=0
  else
    echo Re-installing vagrant because we need version $VAGRANT_VERSION.
  fi
fi

# check if we need to install Ansible
INSTALL_ANSIBLE=1
if hash ansible 2>/dev/null; then
  INSTALLED_ANSIBLE_VERSION=$(ansible --version | head -n1)
  INSTALLED_ANSIBLE_VERSION=${INSTALLED_ANSIBLE_VERSION##ansible }
  if [ $(echo $INSTALLED_ANSIBLE_VERSION | grep -q '^2.'; echo $?) -eq 0 ]; then
    INSTALL_ANSIBLE=0
  else
    echo "Re-installing Ansible because we need version >= 2.0."
  fi
fi

# distribution-dependent steps
DLDIR=$(mktemp -d)

echo Installing dependencies. You may be prompted for a password by sudo.
case $(lsb_release -is) in
  Ubuntu)
    sudo apt-get install virtualbox nfs-kernel-server curl

    if [ $INSTALL_VAGRANT = 1 ]; then
      curl -L https://releases.hashicorp.com/vagrant/${VAGRANT_VERSION}/vagrant_${VAGRANT_VERSION}_$(uname -m).deb > "$DLDIR/vagrant.deb"
      sudo dpkg -i "$DLDIR/vagrant.deb"
    fi

    if [ $INSTALL_ANSIBLE = 1 ]; then
      if [ $(lsb_release -cs) == "trusty" ]; then
        sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 6125E2A8C77F2818FB7BD15B93C4A3FD7BB9C367
        sudo add-apt-repository ppa:ansible/ansible
        sudo apt-get update
      fi
      sudo apt-get -y install ansible
    fi
    ;;
  *SUSE*)
    sudo zypper install -y virtualbox nfs-kernel-server curl

    if [ $INSTALL_VAGRANT = 1 ]; then
      curl -L https://releases.hashicorp.com/vagrant/${VAGRANT_VERSION}/vagrant_${VAGRANT_VERSION}_$(uname -m).rpm > "$DLDIR/vagrant.rpm"
      sudo rpm -Uhv --oldpackage "$DLDIR/vagrant.rpm"

      echo Disable the firewall in Yast or configure it otherwise to enable
      echo NFS access through the VirtualBox network device.
      echo Otherwise the booting of the VM will stop at \"Mounting NFS shared folders...\"

      # ensure the NFS server is started on boot and start now
      sudo systemctl enable nfs-server
      sudo systemctl start nfs-server
    fi

    if [ $INSTALL_ANSIBLE = 1 ]; then
      zypper lr -u | grep -q http://download.opensuse.org/repositories/systemsmanagement/openSUSE_Leap_42.1/
      if [ $? -ne 0 ]; then
        sudo zypper ar http://download.opensuse.org/repositories/systemsmanagement/openSUSE_Leap_42.1/systemsmanagement.repo
      fi
      sudo zypper install -y --from http://download.opensuse.org/repositories/systemsmanagement/openSUSE_Leap_42.1/ ansible
    fi
    ;;
  *)
    echo Unknown distribution. Please extend this script!
    exit 1
    ;;
esac

rm -rf $DLDIR 2>/dev/null

# setup vagrant
vagrant plugin install vagrant-hostmanager
echo -e "\n\n"

# make sure the development secrets are present
if [ ! -e ansible/secrets/dev-vagrant.secrets.yml ]; then
    echo "Error: Development secrets file ansible/secrets/dev-vagrant.secrets.yml not found"
    echo
    echo "Please create this file by copying the template from "
    echo "ansible/secrets.dist/dev-vagrant.secrets.yml and modify it to your needs."
    echo "Note: You do not need to enter the OAuth API tokens if you do not plan to"
    echo "test the OAuth login (e.g. GitHub or Google)."
    
    exit 1
fi

echo Installation of all prerequisites finished.
read -p "Start the development VM now? [yN] " yn

case $yn in
  [Yy]*)
    echo Running 'vagrant up' to setup. This will take some time.
    cd vagrant
    vagrant up
    ;;
  [Nn]*|*)
    echo You can now start vagrant.
    echo Then take your browser to 'http://librecores.devel' to view the site.
    exit
    ;;
esac

read -p "View librecores web site? [yN] " yn

case $yn in
  [Yy]*)
    firefox http://librecores.devel
    ;;
esac

echo Done!

