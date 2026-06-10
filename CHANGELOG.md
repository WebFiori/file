# Changelog

## [2.0.4](https://github.com/WebFiori/file/compare/v2.0.3...v2.0.4) (2026-06-10)


### ⚠ BREAKING CHANGES

* **File:** getSize() return type changed from int to ?int. Callers checking === -1 must switch to === null or use hasKnownSize().

### Features

* **FileUploader:** add custom per-uploader file size limit ([65f382f](https://github.com/WebFiori/file/commit/65f382fc4c77babcca14d0588d45442a5ea5103e)), closes [#51](https://github.com/WebFiori/file/issues/51)
* **File:** use null instead of -1 for unset file size ([e1a1eec](https://github.com/WebFiori/file/commit/e1a1eec2101715ef3383760df2c6b97495745530)), closes [#56](https://github.com/WebFiori/file/issues/56)


### Bug Fixes

* **File:** deduplicate extractPathAndName into File::extractPathAndName() ([e4db32b](https://github.com/WebFiori/file/commit/e4db32b0629f06b81a376e2f712f6ae0d1c268d6)), closes [#42](https://github.com/WebFiori/file/issues/42)
* **File:** make byte-range logic in readHelper explicit ([fee88fe](https://github.com/WebFiori/file/commit/fee88fe72d2de5800ebd788724102d6c84b3773a)), closes [#36](https://github.com/WebFiori/file/issues/36)


### Miscellaneous Chores

* Merge pull request [#93](https://github.com/WebFiori/file/issues/93) from WebFiori/dev ([f986602](https://github.com/WebFiori/file/commit/f986602a44157edc568f7341bacb01e493a374fb))

## [2.0.3](https://github.com/WebFiori/file/compare/v2.0.2...v2.0.3) (2026-06-02)


### Features

* file locking, view terminate param, return types, hasKnownSize, typo fix ([ddc2344](https://github.com/WebFiori/file/commit/ddc2344c1f13a280676e27e41621c8b4cbce2f24))
* phase 2 stability improvements ([8f0ae0d](https://github.com/WebFiori/file/commit/8f0ae0d09f455093926b2a52f3937f4ed871aea2))


### Bug Fixes

* resolve misc bugs in FileUploader, File, and MIME ([1c09298](https://github.com/WebFiori/file/commit/1c0929881316f976db685622b55ed1e331523d52))
* **security:** path traversal, directory permissions, filename sanitization, range validation ([1e7ccfe](https://github.com/WebFiori/file/commit/1e7ccfefc3a962942cd733d55a491b3b46129c48))
* **security:** path traversal, permissions, sanitization, range validation ([8fb802c](https://github.com/WebFiori/file/commit/8fb802caa86565cea558ed70151f04e5bf20356b))
* setUploadDir paths, type mismatch, DS constant, file size math, MIME types, dead code ([c5b16fe](https://github.com/WebFiori/file/commit/c5b16fef2f4455d5d14ed349db1719ef848a650e))


### Miscellaneous Chores

* Merge pull request [#90](https://github.com/WebFiori/file/issues/90) from WebFiori/dev ([b409c4c](https://github.com/WebFiori/file/commit/b409c4c6e42c808e559019272bad8f35f23eea58))

## [2.0.2](https://github.com/WebFiori/file/compare/v2.0.1...v2.0.2) (2026-06-02)


### Miscellaneous Chores

* align CI with ecosystem baseline ([6370e6e](https://github.com/WebFiori/file/commit/6370e6e26473d09c48c6155f8788286b14e25454))
* align CI with ecosystem baseline ([5177c9f](https://github.com/WebFiori/file/commit/5177c9fe6f43ab183ca5c8c6be6566d226193835))
* Fix Config File For Composer ([13de542](https://github.com/WebFiori/file/commit/13de54294bad319f04d80d861eab0f95aace821d))

## [2.0.1](https://github.com/WebFiori/file/compare/v2.0.0...v2.0.1) (2026-04-25)


### Miscellaneous Chores

* Updated PHP Version ([a0502f0](https://github.com/WebFiori/file/commit/a0502f01e3fdc7b850174aa17a75044bcb0a88a7))

## [2.0.0](https://github.com/WebFiori/file/compare/v1.3.8...v2.0.0) (2025-08-06)


### Bug Fixes

* No Die in CLI ([b164fe1](https://github.com/WebFiori/file/commit/b164fe1ba314e436cff00d77be6037bef937b586))


### Miscellaneous Chores

* Few Fixes ([05a5fe6](https://github.com/WebFiori/file/commit/05a5fe61c47b9b10953654c3528c6259e82648c8))
* Move Files Temporarly ([07bb934](https://github.com/WebFiori/file/commit/07bb934e228e0a8e4a12a3a6e76b2d5d6b5640a5))
* Move Test Files ([85e1616](https://github.com/WebFiori/file/commit/85e1616a29e186b7503a0a435293b786fb920788))
* Moved Files / Rename Folder ([0e4a0af](https://github.com/WebFiori/file/commit/0e4a0af2958ff6a56beb8d2a97721c0fa8b8f147))
* release v2.0.0 ([9e12223](https://github.com/WebFiori/file/commit/9e12223fbcd6d00b6683d12c85dc815d264aed26))
* Remove Files ([5b675a8](https://github.com/WebFiori/file/commit/5b675a807d5128242224c14008d6a941fae37315))
* Rename Folder ([f7372b2](https://github.com/WebFiori/file/commit/f7372b222df4cfef82b0fe3942a426576290e3d1))

## [1.3.8](https://github.com/WebFiori/file/compare/v1.3.7...v1.3.8) (2025-01-06)


### Bug Fixes

* Single File Upload ([4126efc](https://github.com/WebFiori/file/commit/4126efc3ff15e030b0bdd58c302eb3aa7e971c0e))

## [1.3.7](https://github.com/WebFiori/file/compare/v1.3.6...v1.3.7) (2024-12-22)


### Bug Fixes

* Casting to Integer ([cc3215d](https://github.com/WebFiori/file/commit/cc3215d23b852f8ed3678bcb25a0998863f928b6))
* Fix New Null Syntax ([3ce5995](https://github.com/WebFiori/file/commit/3ce599576607b07853e0385103c8e7371f30dceb))
* Small Fix to Last Modified ([51ab42a](https://github.com/WebFiori/file/commit/51ab42a4c30dd893577238139feffbd7a6a47425))


### Miscellaneous Chores

* Added Additional Test Case ([ffc2260](https://github.com/WebFiori/file/commit/ffc22603ea10ce5d79c09773fb4822e255c32fda))
* Code Cleanup ([e9e1724](https://github.com/WebFiori/file/commit/e9e1724397187e46c0302a85db0dae8b5d63e5d3))
