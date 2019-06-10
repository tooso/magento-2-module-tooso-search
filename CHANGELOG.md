# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.0] - 2019-06-10
### Added
- Add redirect improvements to automatically switch store when "___redirect" query param is set to "auto"

## [1.4.0] - 2019-05-28
### Added
- Use store locale code for suggestion init params
- Add exported CSV column consistency
### Fixed
- Fix JSON interpolation for "

## [1.3.8] - 2019-05-14 
### Fixed
- Fix "all stores" indexer configuration
- Fix wrong store locale during indexing process

## [1.3.7] - 2019-04-30 
### Fixed
- Fix cacheable option for SpeechToTextTemplate block
### Added
- Upgrade bitbull/tooso-sdk to 1.3.0

## [1.3.6] - 2019-04-15 
### Fixed
- Fix bad userAgent processing

## [1.3.5] - 2019-04-10 
### Fixed
- Fix search from cli (using dummy data generator)

## [1.3.4] - 2019-04-10 
### Added
- Add cleaning on remote address when multiple proxy servers are present

## [1.3.3] - 2019-04-09 
### Added
- Add logger context tracking for debugging purpose
### Fixed
- Fix error on cart update tracking

## [1.3.2] - 2019-04-09 
### Fixed
- Fix TA inclusion tag

## [1.3.1] - 2019-04-03
### Added
- Add support for different AJAX/no-AJAX paginations 
### Fixed
- Fix clickAfterSearch productSku variable

## [1.3.0] - 2019-04-03
### Added
- Add button to clean log
- Add button to download log
- Add on click event handler with different approach
- Add 'data-search-id' logic to override search id when using AJAX
- Add reindex logging improvement
- Add AJAX response improvements

## [1.2.2] - 2019-03-27
### Fixed
- Fix subtree observer enable flag

## [1.2.1] - 2019-03-27
### Added
- Add subtree obsever to trigger clickAfterSearch events rebuild

## [1.2.0] - 2019-03-27
### Fixed
- Fix TA initialisation before inclusion
### Added
- Add Javascript SDK
- Add SpeechToText functionality
- Add cart product's quantity tracking
- Add AJAX support for click after search tracking
- Add product attributes export functionality

## [1.1.1] - 2019-03-19
### Fixed
- Fix single entity catalog reindex

## [1.1.0] - 2019-03-15
### Added
- Add catalog indexer
- Add tracking system
- Add frontend suggestion

## [1.0.2] - 2019-02-06
### Fixed
- Fix wrong search_request.xml syntax

## [1.0.1] - 2019-02-04
### Added
- Add support for Magento 2.2.5

## [1.0.0] - 2019-02-04
### First Release
