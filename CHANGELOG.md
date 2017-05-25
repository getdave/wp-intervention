# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

For more information about keeping good change logs please refer to [keep a changelog](https://github.com/olivierlacan/keep-a-changelog).

## Changelog

## [1.1.0]

### Added
* Introduced ability to return underlying Intervention.io instance for non-cached requests.

### Fixed
* Fixed major performance issue with caching logic whereby the Intervention lib was being initialised even if file was in cache. Reverted to using raw PHP and significantly improved performance. 

## [1.0.3]

### Changed
* Set composer installs to use `*` as version.

## [1.0.0]

Initial full release of the Plugin.