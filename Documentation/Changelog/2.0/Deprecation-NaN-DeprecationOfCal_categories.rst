.. include:: ../../Includes.txt

==========================================
Deprecation: Deprecation of cal_categories
==========================================

See :issue:``

Description
===========

Former versions of cal provided their own version of categories. With the introduction of the sys_categories of TYPO3, these categories became superfluous. With the removal of extJS from the TYPO3 core the tree functionality provided by the extension ceased to work. So it finally the time has come to deprecate the cal_categories.


Impact
======

Using older extensions in combination with the current version of cal might cause unexpected side effects. If you maintain any extension, that extends or works with the tables tx_cal_category or tx_cal_event_category_mm you need to update your extension.
The tables tx_cal_category and tx_cal_event_category_mm will be kept during the existence of cal 2.x.y to avoid any data loss. But in later versions, the tables will be removed.


Affected Installations
======================

Any installation that relies on a extension extending the tables tx_cal_category or tx_cal_event_category_mm. Do a full scan of you ext folder for this expressions.


Migration
=========

A upgrade wizard is provided and is callable thru the install tool. This upgrade wizard will migrate all active and hidden (but no deleted) categories from cal to the table sys_category will also take care of all existing relations within the old table tx_cal_event_category_mm and migrate them to sys_category_record_mm.

.. index:: Backend, Database, NotScanned
