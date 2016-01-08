#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Install dependencies required to run this script
function install_deps
{
  echo Installing dependencies. You may be prompted for a password by sudo.
  case $(lsb_release -is) in
  Ubuntu)
    sudo apt-get install -y ansible python-boto
    ;;
  *SUSE*)
    sudo zypper install -y ansible python-boto
    ;;
  *)
    echo Unknown distribution. Please extend this script! >&2
    exit 1
    ;;
  esac
}

function usage
{
  cat << EOF
Usage: $0 ACTION

Executes the deployment action ACTION on Amazon Web Services (AWS).

Available ACTIONs:
  initial-setup
    Run the initial setup of the AWS account to create all necessary
    instances and firewall settings.

  staging
    Deploy current code to stage.librecores.org

  staging-initdata
    Initialize stage.librecores.org with the data fixtures from Git, overwriting
    all existing data.
EOF
}

# Check if all required AWS credentials are set in the environment
function ensure_aws_creds
{
  test -f aws-secrets.include && . aws-secrets.include
  if [ -z "$AWS_ACCESS_KEY_ID" ] || [ -z "$AWS_SECRET_ACCESS_KEY" ]; then
    echo Error: No AWS secrets were found in the environment. >&2
    echo >&2
    echo Fix option 1: >&2
    echo cp aws-secrets.include.dist aws-secrets.include >&2
    echo and update the file with your credentials. >&2
    echo >&2
    echo Fix option 2:>&2
    echo Manually set the environment variables AWS_ACCESS_KEY_ID and >&2
    echo AWS_SECRET_ACCESS_KEY. >&2
    echo >&2
    echo Then run this script again. >&2
    exit 1
  fi
}

# check (and possibly install) dependencies
ansible_missing=$(which ansible >/dev/null 2>&1; echo $?)
boto_missing=$(python -c "import boto" >/dev/null 2>&1; echo $?)

if [ $ansible_missing -eq 1 ] || [ $boto_missing -eq 1 ]; then
  install_deps
fi

action=$1
case $action in
  initial-setup)
    ensure_aws_creds
    ansible-playbook -i $SCRIPT_DIR/ansible/ec2.py ansible/aws-setup.yml
    ;;
  staging)
    ansible-playbook -i $SCRIPT_DIR/aws-static-inventory ansible/staging-aws.yml
    ;;
  staging-initdata)
    ansible-playbook -i $SCRIPT_DIR/aws-static-inventory ansible/staging-aws-initdata.yml
    ;;
  "")
    echo ERROR: No action given. >&2
    echo
    usage
    exit 1
    ;;
  *)
    echo "ERROR: Unknown action '$action'". >&2
    echo
    usage
    exit 1
esac
