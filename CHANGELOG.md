# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/).

## [Unreleased]
### Added
### Changed
### Removed
### Fixed

## [7.x-1.1](https://www.drupal.org/project/rokka/releases/7.x-1.1)
### Changed
- Updated RokkaClient requirements to v0.6.*
### Fixed
- Fixed FocalPoint handling, ensure non-negative point

## [7.x-1.1-rc3](https://www.drupal.org/project/rokka/releases/7.x-1.1-rc3)
### Added
- [#2819345](https://www.drupal.org/node/2819345) by thePanz: Added support for Blur effect from Rokka.io
- Added Changelog (this file)
### Changed
- Updated Organization display details
- Updated RokkaClient requirements to v0.5.*
### Removed
### Fixed
- Fix warning on Image effects: the "override" action is not set when creating a new stack

## [7.x-1.1-rc2](https://www.drupal.org/project/rokka/releases/7.x-1.1-rc2)
### Added
- Updated readme and install documentation
- Added submodule rokka_effects: Crop with background color
### Changed
- Updated rotation effect, using common ImageStyleHelper functions
- Stack creation: display Rokka API error message if any
- Updated CS, code refactoring
### Fixed
- [#2848739](https://www.drupal.org/node/2848739) by thePanz: Upscale flag not respected for Scale and Resize effects
- Fixed typo in Client::cleanRokkaSeoFilename() function

## [7.x-1.1-rc1](https://www.drupal.org/project/rokka/releases/7.x-1.1-rc1)
### Added
- Added rokka_massmover contrib module
- [#2827204](https://www.drupal.org/node/2827204) by thePanz: Use SEO name when building URI on file_create()
- Added rokka_is_rokka_uri() helper function
