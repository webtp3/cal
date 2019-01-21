.. include:: ../../Includes.txt

============================================
Breaking: Removal of WEC map extension Stuff
============================================

See :issue:``

Description
===========

The extension wec_map got its last update early 2017 and is not compatible with any current TYPO3 LTS version. To clean up the code base, every usage and mention of the extension (and corresponding follow ups) were removed.
Even if the functions are considered useful it makes no sense to keep outdated code in the ode base of cal. A better way would be to check either


Impact
======

Functions that rely on wec_map are removed and will not work any more. That includes the display of any map and the "find events nearby" function.


Affected Installations
======================

Any installations using functions of wec_map.


Migration
=========

Nothing to migrate, the functions are gone and needed to redone if needed.

.. index:: Backend, PHP-API, NotScanned
