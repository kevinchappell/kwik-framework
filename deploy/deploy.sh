#!/bin/sh

# this script take one argument which is the plugin slug in the WordPress repositiory
PLUGIN_SLUG=$1;
SVN_REPO_PATH="/tmp/${PLUGIN_SLUG}"; #path to a temp SVN repo. No trailing slash (be cautious about incorrect paths, note that we rm the contents later)
SVN_REPO_URL="http://plugins.svn.wordpress.org/${PLUGIN_SLUG}/trunk/"; #Remote SVN repo with no trailing slash
SVN_IGNORE_FILES=".svnignore";
MESSAGE=$(git log -1 HEAD --pretty=format:%s);
CURRENT_DIR=${PWD%/*};


echo "Preparing to push ${PLUGIN_SLUG} to ${SVN_REPO_URL}";

echo 'Cleaning the destination path';
rm -Rf ${SVN_REPO_PATH};

echo "Creating local copy of SVN repo at ${SVN_REPO_PATH}";
svn checkout ${SVN_REPO_URL} ${SVN_REPO_PATH};

echo 'Prepping the SVN repo to receive the git';
rm -Rf ${SVN_REPO_PATH}/*

echo 'Exporting the HEAD of master from git to SVN';
git checkout-index -a -f --prefix=${SVN_REPO_PATH}/

echo 'Exporting git submodules to SVN';
git submodule foreach 'git checkout-index -a -f --prefix=${SVN_REPO_PATH}/\$path/'

echo 'Copying and reformatting README.md to readme.txt';
cat README.md | sed 's/^\#* //' > ${SVN_REPO_PATH}/readme.txt;

echo 'Removing any svn:executable properties for security';
find ${SVN_REPO_PATH} -type f -not -iwholename *svn* -exec svn propdel svn:executable {} \; | grep 'deleted from';

echo 'Setting svn:ignore properties';
svn propset svn:ignore -F $SVN_IGNORE_FILES $SVN_REPO_PATH;

svn proplist -v ${SVN_REPO_PATH};

echo 'Marking deleted files for removal from the SVN repo';
svn st ${SVN_REPO_PATH} | grep '^\!' | sed 's/\!\s*//g' | xargs svn rm

echo 'Marking new files for addition to the SVN repo';
svn st ${SVN_REPO_PATH} | grep '^\?' | sed 's/\?\s*//g' | xargs svn add

echo 'Now forcibly removing the files that are supposed to be ignored in the svn repo';

while read file; do
  svn rm --force ${SVN_REPO_PATH}/$file;
done <"${SVN_REPO_PATH}/deploy/${SVN_IGNORE_FILES}";


echo "
##################################################
Committing the changes to the WordPress repository
##################################################
";
cd ${SVN_REPO_PATH};
svn commit -m "${MESSAGE}";
cd "${CURRENT_DIR}";


echo "
#############################
Automatic processes complete!
#############################
";
