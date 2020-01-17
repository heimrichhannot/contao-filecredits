# Changelog
All notable changes to this project will be documented in this file.

## [3.5.1] - 2020-01-17
- fixed an occasionally exception when crawling page

## [3.5.0] - 2019-12-11
- added tl_settings::deactivateFileCreditsCron

## [3.4.1] - 2019-10-01

### Added
- copyright url to template

## [3.4.0] - 2019-09-30

### Added
- copyright url

## [3.3.0] - 2019-04-03

### Fixed
- Contao 3 compatibility `Could not find template "be_filecredits_sync_pageselection_root"`
- Performance issues, do not index twice, just deindex credits on associcated 404,500 pages afterwards

### Changed
- use PageSelecotr picker in backend mode sync view 

## [3.2.5] - 2019-04-03

#### Fixed
- Contao 3 compatibility

## [3.2.4] - 2019-03-13

#### Fixed
- warning in FileCredit class
- namespaces in FileCredits class
- some deprecation warnings in FileCredit class

## [3.2.3] - 2019-03-05

### Fixed
- override `User-Agent` to ignore potential settings in htacces

## [3.2.2] - 2018-12-06

### Fixed
- more indexer time issue

## [3.2.1] - 2018-12-06

### Fixed
- indexer time issue

## [3.2.0] - 2018-08-27

### Changed
- changed backend limit filecredit pages selection

## [3.1.5] - 2018-08-24

### Fixed
- update author if you add file credit page manually

## [3.1.4] - 2018-04-17

### Fixed
- refactoring

## [3.1.3] - 2018-03-08

### Fixed
- empty copyright check before adding credits (array check)
- index credit on pages with alias `index` 

## [3.1.2] - 2018-03-08

### Fixed
- removed unused `fcp` cache

## [3.1.1] - 2018-03-08

### Fixed
- SQL-Error under contao 3 on database update on `tl_filecredit_page` index `pid,page,url`

## [3.1.0] - 2018-03-05

### Fixed
- index files without domain
- contao 4: `app_dev.php` removed from url
- always clean up images without an author before reindex credits again
- remove credits without related pages 
- do not index pages that return an 404, 500 or redirect status code, only index status code 200 pages
- index `/files` images that were not resized

### Changed
- removed `grouping` for credits in back end list view 

### Added
- index background-images inside `/files` directory

## [3.0.4] - 2018-02-27

### Fixed
- missing `heimrichhannot/contao-request` dependency

## [3.0.3] - 2018-02-23

### Fixed
- skip pages correctly if url is no absolute url, try to detect host in addition from `Contao\Environment::get('host')`

## [3.0.2] - 2018-02-19

### Fixed
- restored `2.2.6`, restored author field

## [3.0.1] - 2018-02-19

### Fixed
- cronjob documentation in README.md

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

