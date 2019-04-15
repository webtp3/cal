# TYPO3 Extension ``cal``
[![Latest Stable Version](https://poser.pugx.org/web-tp3/cal/v/stable)](https://packagist.org/packages/web-tp3/cal)
[![Daily Downloads](https://poser.pugx.org/web-tp3/cal/d/daily)](https://packagist.org/packages/web-tp3/cal)
[![Total Downloads](https://poser.pugx.org/web-tp3/cal/downloads)](https://packagist.org/packages/web-tp3/cal)
[![License](https://poser.pugx.org/web-tp3/cal/license)](https://packagist.org/packages/web-tp3/cal)
[![Build Status](https://travis-ci.org/webtp3/cal.svg?branch=master)](https://travis-ci.org/webtp3/cal)

## Installation

### Using Composer

The recommended way to install the extension is by using (Composer)[1]. In your Composer based TYPO3 project root, just do `composer require web-tp3/cal`. 

### As extension from TYPO3 Extension Repository (TER)

Download and install the extension with the extension manager module.

## Minimal setup

1) Include the static TypoScript of the extension. **Optional:** If you are templates are based on Twitter Bootstrap, add the TWB styles as well to get optimized templates.
2) Create some cal records on a sysfolder.
3) Create a plugin on a page and select at least the sysfolder as startingpoint.

## Administration corner

### Versions and support

| Cal           | TYPO3      | PHP       | Support/Development                     |
| ------------- | ---------- | ----------|---------------------------------------- |
| 2.x           | 8.7 - 9.5  | 7.0 - 7.2 | Bugfixes, Security Updates, FeTemplates |
| 1.x           | <= 8.7     | 5.5 - 5.6 | Security Updates                        |

### Release Management

Cal uses **semantic versioning** which basically means for you, that 
- **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes.
- **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes.
- **major updates** (e.g. 1.0.0 => 2.0.0) breaking changes wich can be refactorings, features or bugfixes.

### Contribution


**Forks**, **Pull requests** or **Commits** to support develop are welcome in general! Nevertheless please don't forget to add an issue and connect it to your pull requests. This
is very helpful to understand what kind of issue the **PR** is going to solve.

- Bugfixes: Please describe what kind of bug your fix solve and give us feedback how to reproduce the issue. We're going
to accept only bugfixes if I can reproduce the issue.
- Features: Not every feature is relevant for the bulk of powermail users. In addition: We don't want to make powermail
even more complicated in usability for an edge case feature. Please discuss a new feature before.

[1]: https://getcomposer.org/
