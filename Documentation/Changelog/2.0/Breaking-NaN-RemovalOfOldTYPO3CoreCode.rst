.. include:: ../../Includes.txt

========================================
Breaking: Removal of old TYPO3 core code
========================================

See :issue:``

Description
===========

The extensions is aimed to work with the current TYPO3 LTS versions. To declutter the code basis and keep it clean, code related to older TYPO3 versions was removed.
That includes every code fragment that was removed in the latest TYPO3 LTS version as well as any switch for older versions (e.g. usage of VersionNumberUtility class).


Impact
======

Any installations using TYPO3 versions older than TYPO3 8 LTS will probably break.


Affected Installations
======================

Any installations using TYPO3 versions older than TYPO3 8 LTS.


Migration
=========

Update to a current TYPO3 LTS version.

.. index:: Backend, PHP-API, NotScanned
