# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
