#!/bin/bash -x

function doxy
{
   echo 'Setting up the script...'=
   set -e

   mkdir -vp build
   cd build

   git clone -b gh-pages https://git@$GH_REPO_REF
   cd $GH_REPO_NAME

   git config --global push.default simple
   git config user.name "Travis CI"
   git config user.email "travis@travis-ci.org"
   rm -rf *
   echo "" > .nojekyll

   mkdir -vp doc
   echo 'Generating Doxygen code documentation...'

   doxygen $DOXYFILE 2>&1 | tee doxygen.log

   if [ -d "doc/html" ] && [ -f "doc/html/index.html" ]; then

       mv doc/* .
       rm -rvf ./doc

cat <<EOF > README.md
# php-binance-api
PHP Binance API is an asynchronous PHP library for the Binance API designed to be easy to use.
https://github.com/binance-exchange/php-binance-api

## Documentation
You are looking at the doxygen documentation branch, this is auto generated

You can donwload a copy of the documentation using the following url:
[download](https://github.com/jaggedsoft/php-binance-api/archive/gh-pages.zip)
EOF

       cd latex
       make SHELL='sh -x'
       cd ..

       echo 'Uploading documentation to the gh-pages branch...'
       git add --all
       git commit -m "Deploy code docs to GitHub Pages Travis build: ${TRAVIS_BUILD_NUMBER}" -m "Commit: ${TRAVIS_COMMIT}"
       git push --force "https://${GH_REPO_TOKEN}@${GH_REPO_REF}" > /dev/null 2>&1

   else
       echo '' >&2
       echo 'Warning: No documentation (html) files have been found!' >&2
       echo 'Warning: Not going to push the documentation to GitHub!' >&2
       exit 1
   fi
}

doxy
