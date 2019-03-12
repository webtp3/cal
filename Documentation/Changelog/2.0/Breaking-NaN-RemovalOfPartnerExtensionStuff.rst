.. include:: ../../Includes.txt

============================================
Breaking: Removal of partner extension Stuff
============================================

See :issue:``

Description
===========

The extension tx_partner  got its last update mid 2009 and is not compatible with any current TYPO3 LTS version. To clean up the code base, every usage and mention of the extension (and corresponding follow ups) were removed.


Impact
======

Functions that rely on tx_partner are removed and will not work any more.


Affected Installations
======================

Any installations using functions of tx_partner.


Migration
=========

Nothing to migrate, the functiona are gone and needed to redone if needed.

.. index:: Backend, PHP-API, NotScanned
