# Changelog
All notable changes to this project will be documented in this file.

## [3.0.2] - 2018-02-19

### Fixed
- cronjob documentation in README.md

## [3.0.1] - 2018-02-19

### Changed
- removed `tl_settings.fileCreditsDisablePoorMansCron` already implemented by contao itself (`tl_settings.disableCron`)

## [3.0.0] - 2018-02-19

### Changed
- detach filecredit indexer from current request by using cron jobs

## [2.2.6] - 2018-01-03

### Added
- `author` field within `tl_filecredit`, credits and pages with `author` wont be maintained, synced. 

## [2.2.5] - 2017-11-04

### Added
- removed `FileCredit::getFileCredit()` that will return an array of file credits for a given file uuid or path
- added `{{copyright::}}` filecredit that will return the file credits for a given uuid or path as delimited string

## [2.2.4] - 2017-10-20

### Changed
- removed `menatwork/contao-multicolumnwizard` dependency

## [2.2.3] - 2017-09-12

### Fixed
- standardize compatibility for contao 3

## [2.2.2] - 2017-08-30

### Fixed
- standardize compatibility for contao 3

## [2.2.1] - 2017-08-28

### Added
- cache filecredits per page for 24 hours, to reduce database queries

## [2.2.0] - 2017-08-04

### Added
- ability to add a copyright field to any DCA which is synced to the linked file field (see README.md)

## [2.1.16] - 2017-05-15

### Fixed
- `tl_files.copyright` tagsinput options for multiple credits (field was changed from varchar to blob recently)

## [2.1.15] - 2017-05-15

### Fixed
- copyright connection

## [2.1.14] - 2017-05-09

### Fixed
- php7 support

## [2.1.13] - 2017-04-12
- created new tag

## [2.1.12] - 2017-04-06

### Changed
- add php7 support. fixed contao-core dependency

## [2.1.11] - 2017-03-10

### Fixed
- check if objCredit is set in addCurrentPage()

