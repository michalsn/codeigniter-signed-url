# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]

## [2.1.0](https://github.com/michalsn/codeigniter-signed-url/compare/v1.0.0...v1.1.0) - 2023-09-08

### Enhancements
- Added `token` option to set a randomly generated token of the specified length by @michalsn in #45
- Added `redirectTo` option setting to set a URI path for redirection when filter URL validation fails by @michalsn in #46

## [2.0.0](https://github.com/michalsn/codeigniter-signed-url/compare/v1.1.1...v2.0.0) - 2023-08-26

### Fixes
- Compatibility with CodeIgniter 4.4 by @michalsn in #38

### Enhancements
- Default hashing algorithm has been changed from `sha1` to `sha256` by @michalsn in #38

## [1.1.1](https://github.com/michalsn/codeigniter-signed-url/compare/v1.1.0...v1.1.1) - 2023-04-05

### Bugs
- Take `App::$indexPage` and `App::$baseURL` into consideration during URL verification by @michalsn in #22

## [1.1.0](https://github.com/michalsn/codeigniter-signed-url/compare/v1.0.0...v1.1.0) - 2022-12-31

### Enhancements
- Autoload `signedurl` filter by @datamweb in #2

## [1.0.0] - 2022-12-28
Initial release
