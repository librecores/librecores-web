#/bin/sh

TOPDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd $TOPDIR
python generator/planet.py config.ini

