dnl $Id$
dnl config.m4 for extension pdo_neo4j

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

dnl If your extension references something external, use with:

dnl PHP_ARG_WITH(pdo_neo4j, for pdo_neo4j support,
dnl Make sure that the comment is aligned:
dnl [  --with-pdo_neo4j             Include pdo_neo4j support])

dnl Otherwise use enable:

dnl PHP_ARG_ENABLE(pdo_neo4j, whether to enable pdo_neo4j support,
dnl Make sure that the comment is aligned:
dnl [  --enable-pdo_neo4j           Enable pdo_neo4j support])

if test "$PHP_PDO_NEO4J" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-pdo_neo4j -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/pdo_neo4j.h"  # you most likely want to change this
  dnl if test -r $PHP_PDO_NEO4J/$SEARCH_FOR; then # path given as parameter
  dnl   PDO_NEO4J_DIR=$PHP_PDO_NEO4J
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for pdo_neo4j files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       PDO_NEO4J_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$PDO_NEO4J_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the pdo_neo4j distribution])
  dnl fi

  dnl # --with-pdo_neo4j -> add include path
  dnl PHP_ADD_INCLUDE($PDO_NEO4J_DIR/include)

  dnl # --with-pdo_neo4j -> check for lib and symbol presence
  dnl LIBNAME=pdo_neo4j # you may want to change this
  dnl LIBSYMBOL=pdo_neo4j # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $PDO_NEO4J_DIR/lib, PDO_NEO4J_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_PDO_NEO4JLIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong pdo_neo4j lib version or lib not found])
  dnl ],[
  dnl   -L$PDO_NEO4J_DIR/lib -lm
  dnl ])
  dnl
  dnl PHP_SUBST(PDO_NEO4J_SHARED_LIBADD)

  PHP_NEW_EXTENSION(pdo_neo4j, pdo_neo4j.c, $ext_shared)
fi
