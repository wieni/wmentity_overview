# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.8.0] - 2021-06-04
### Added
- Add `buildDateTimeColumn` helper
- Add entity access check to query
- Add abstract base class for actions with batch processing
- Add documentation for hooks in wmentity_overview.api.php

## [1.7.1] - 2021-03-22
### Changed
- Update module name & description
- Improve checkbox filter theming

## [1.7.0] - 2020-12-07
### Added
- Add bulk actions
- Add helper traits for easier column building
- Add a way to show a tooltip while hovering over a column
- Add support for adding classes to header columns, eg. [responsive table classes](https://www.drupal.org/node/1796238).
- Add PHPStan

### Changed
- Added two new public methods to `OverviewBuilderInterface` and `OverviewBuilderBase`: `getRowKeyByEntity` and 
  `getEntityByRowKey`.

## [1.6.0] - 2020-09-01
### Added
- Add default, theme-agnostic styling for filter form

## [1.5.2] - 2020-09-01
### Fixed
- Fix broken query when entity type has no data table

## [1.5.1] - 2020-07-24
### Fixed
- Fix broken query when entity type has no data table

## [1.5.0] - 2020-07-17
### Added
- Add option to specify sort field
- Add column factory method & fluent setters

## [1.4.1] - 2020-05-28
### Fixed
- Fix clearing filters with falsy values

## [1.4.0] - 2020-05-23
### Fixed
- Add a method to process the filter value before storing

## [1.3.1] - 2020-03-13
### Fixed
- Fix issue where site install fails because an entity type referenced in an OverviewBuilder annotation is not yet installed

## [1.3.0] - 2020-02-26
### Changed
- Allow multiple translations of the same entity in the same table

## [1.2.2] - 2020-02-21
### Fixed
- Fix header order of draggable overviews

## [1.2.1] - 2020-02-20
### Fixed
- Make sure routes are only overridden with overviews without filters
- Make sure destination is not used as filter key
- Fix FilterStorageBase::getIterator not returning an iterator

## [1.2.0] - 2020-02-13
### Fixed
- Rename ColumnInterface getSortDirection to getDefaultSortDirection and change the default value to null, so it is actually possible to select a default column for sorting

## [1.1.0] - 2020-02-02
### Added
- Add entity overview alternatives hook & event
- Add getAlternatives method to OverviewBuilderManager

### Fixed
- Fix issue where hook is not called, only event

## [1.0.2] - 2020-02-02
### Fixed
- Remove PHP 7.3 syntax

## [1.0.1] - 2020-01-31
### Fixed
- Fix buildHeader method when used in context of entity queries

## [1.0.0] - 2020-01-30
Initial release
