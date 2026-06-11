# Changelog

## [2.1.1](https://github.com/WebFiori/file/compare/v2.1.0...v2.1.1) (2026-06-11)


### Features

* **upload:** add ResumableUploader class for chunked uploads with resume support ([ac24624](https://github.com/WebFiori/file/commit/ac246240e6dff31903cea2105f1a28f5e3533dba)), closes [#98](https://github.com/WebFiori/file/issues/98)
* **upload:** add setPartialDir() to ResumableUploader ([272aa79](https://github.com/WebFiori/file/commit/272aa794849258246ec81dccae774710fc103b17))


### Bug Fixes

* **upload:** clear stat cache after writing partial file ([0efef26](https://github.com/WebFiori/file/commit/0efef269eca475bbb7bc1509f670e309f8a0529e))


### Miscellaneous Chores

* Merge pull request [#99](https://github.com/WebFiori/file/issues/99) from WebFiori/dev ([76c37fe](https://github.com/WebFiori/file/commit/76c37fe1518f4ce0b7798c6957156d5243372ad2))

## [2.1.0](https://github.com/WebFiori/file/compare/v2.0.4...v2.1.0) (2026-06-10)


### Features

* **examples:** add streaming upload frontend with pause/resume ([c7fb2d5](https://github.com/WebFiori/file/commit/c7fb2d5aa9de6eae4a52d825decb13d085058475))
* extract AbstractUploader and add StreamingUploader ([1f9533b](https://github.com/WebFiori/file/commit/1f9533be362089aa0920576487204e579c47f633))
* **File:** add copy() and moveTo() methods ([01073b8](https://github.com/WebFiori/file/commit/01073b8b8e0152f8e1ef563c9462d01fed175b71)), closes [#49](https://github.com/WebFiori/file/issues/49)
* **File:** decouple from WebFiori framework dependencies ([206e90f](https://github.com/WebFiori/file/commit/206e90f8847a161d66cc42ff2c7011178bee8001))
* **File:** define FileInterface contract ([9093832](https://github.com/WebFiori/file/commit/9093832dbb16e60affcf3a943a892fd6dfd0df3c)), closes [#66](https://github.com/WebFiori/file/issues/66)
* **File:** define ResponseEmitter interface with implementations ([48a429a](https://github.com/WebFiori/file/commit/48a429a1b1d7d3c000364c9970bd81a3943c83e4)), closes [#74](https://github.com/WebFiori/file/issues/74)
* **File:** define StreamableInterface and implement FileStream ([03ba986](https://github.com/WebFiori/file/commit/03ba98648961d74ce5a26019c7b39fcedb8f6de5))
* **File:** File implements FileInterface ([55941dd](https://github.com/WebFiori/file/commit/55941dde8f2505bdb954ae4b3d5490f8bc5d4316)), closes [#67](https://github.com/WebFiori/file/issues/67)
* **FileStream:** add atomic write support (temp + rename) ([4f03c54](https://github.com/WebFiori/file/commit/4f03c5432161725cb916eba567a7e05d99d76788)), closes [#79](https://github.com/WebFiori/file/issues/79)
* **FileUploader:** add upload callback hooks ([c90a19c](https://github.com/WebFiori/file/commit/c90a19c7a2993a3bb59a06b1b581e3cd40fe2c23)), closes [#53](https://github.com/WebFiori/file/issues/53)
* **FileUploader:** streaming upload processing with hash verification ([ba58b3a](https://github.com/WebFiori/file/commit/ba58b3a78223ba0ee45d331ba6cd0091779dbc28)), closes [#84](https://github.com/WebFiori/file/issues/84)
* **FileUploader:** type-hint FileInterface in public API docs ([babde02](https://github.com/WebFiori/file/commit/babde0253da9a519f06ef9bb15a7d1186b631785)), closes [#69](https://github.com/WebFiori/file/issues/69)
* **File:** use FileStream for copy() and moveTo() ([3cb1339](https://github.com/WebFiori/file/commit/3cb13396457d0147b3d4546ba069d30b7de91495)), closes [#83](https://github.com/WebFiori/file/issues/83)
* **File:** view() no longer terminates by default ([422f18f](https://github.com/WebFiori/file/commit/422f18f91b0869ec038e8ad204ec9c446af9355e)), closes [#48](https://github.com/WebFiori/file/issues/48)


### Miscellaneous Chores

* Merge pull request [#96](https://github.com/WebFiori/file/issues/96) from WebFiori/dev ([2aab7d2](https://github.com/WebFiori/file/commit/2aab7d222f352618f842c66ed490d5311aa826c9))

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
