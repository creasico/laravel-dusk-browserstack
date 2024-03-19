# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

### [0.3.3](https://github.com/creasico/laravel-package/compare/v0.3.2...v0.3.3) (2024-03-19)


### Features

* add `WithBrowserStack` as replacement of `SupportsBrowserStack` ([faa84ec](https://github.com/creasico/laravel-package/commit/faa84eca44eebf8dbfe0e482d4de6e4f717fa862))
* add dedicated class to interacts with BrowserStack ([46a88ef](https://github.com/creasico/laravel-package/commit/46a88ef78bcf3978cd3ee82dc491a3754be055a7))

### [0.3.2](https://github.com/creasico/laravel-package/compare/v0.3.1...v0.3.2) (2024-03-17)


### Features

* add dedicated class to handle local binary ([66a7c9b](https://github.com/creasico/laravel-package/commit/66a7c9bf76468b93daac8b36e444cca5ed72f839))


### Bug Fixes

* fix issue that caused by prematurelly closed bs-local process ([ea43d4a](https://github.com/creasico/laravel-package/commit/ea43d4af3e4155e3a238ed44a09d3e0fb05a872e))
* fix issue that causing orphan process when got an error on bs-local process ([80c507b](https://github.com/creasico/laravel-package/commit/80c507bf45d19356e08191579ad6e6c33c84d863))

### [0.3.1](https://github.com/creasico/laravel-package/compare/v0.3.0...v0.3.1) (2024-03-16)


### Features

* add backward compatibility with phpunit 9 ([d32a44d](https://github.com/creasico/laravel-package/commit/d32a44dc72ecb6569cc375bd810593f3705dda8e))
* init support laravel 11 ([a218124](https://github.com/creasico/laravel-package/commit/a218124cf39ac4e1e03d9df4752288c089646fbf))


### Bug Fixes

* skip test when required env missing ([4876e93](https://github.com/creasico/laravel-package/commit/4876e9387c30f947d1833424c07553b9405d75d9))

## [0.3.0](https://github.com/creasico/laravel-package/compare/v0.2.3...v0.3.0) (2024-02-16)


### Features

* **48:** adds ability to download browserstack-local binary ([08416ba](https://github.com/creasico/laravel-package/commit/08416baf1c9cea14884338bfbc9141a07919dbca))
* **48:** adds ability to start browserstack-local binary ([99e1f38](https://github.com/creasico/laravel-package/commit/99e1f38bdf02845cfd2d948256a247250f54ac78))


### Bug Fixes

* **ci:** fix issue when `BROWSERSTACK_PROJECT_NAME` got value from `github.repository` ([e573974](https://github.com/creasico/laravel-package/commit/e57397473b915cfa71ab66cab5e134c0a43caa6f))

### [0.2.3](https://github.com/creasico/laravel-package/compare/v0.2.2...v0.2.3) (2024-02-06)

### [0.2.2](https://github.com/creasico/laravel-package/compare/v0.2.1...v0.2.2) (2023-10-31)


### Bug Fixes

* fix issue when this package runs in gh action ([7f6c28c](https://github.com/creasico/laravel-package/commit/7f6c28cc58d6be37cdbaa2e7985a49927f6ccd3c))

### [0.2.1](https://github.com/creasico/laravel-package/compare/v0.2.0...v0.2.1) (2023-10-30)


### Features

* add indicator to determine whether browserstack local is running ([f967662](https://github.com/creasico/laravel-package/commit/f9676627c490a3ec65c870f2b2ddb21867756422))


### Bug Fixes

* **tests:** fix false-positive test ([b9a21aa](https://github.com/creasico/laravel-package/commit/b9a21aa7caf1970abd415930cbc3a4bc9afd9081))

## [0.2.0](https://github.com/creasico/laravel-package/compare/v0.1.2...v0.2.0) (2023-07-23)


### ⚠ BREAKING CHANGES

* adds dependency to make it testable and change the namespace

### Features

* **ci:** add `CODEOWNERS` and test workflow ([3dcf17e](https://github.com/creasico/laravel-package/commit/3dcf17e0f86ae7f08d0e3f5543f718c58d9dad0d))
* initialize `lint-staged` ([06c1c22](https://github.com/creasico/laravel-package/commit/06c1c229bc3532242eaf000c5a1fe04ade286231))
* register `laravel-dusk` macro for inertia navigation event ([727235c](https://github.com/creasico/laravel-package/commit/727235cb5bdb89d4718839aa16d67dbadf9e9476))


* adds dependency to make it testable and change the namespace ([5ae124b](https://github.com/creasico/laravel-package/commit/5ae124b90f0b7edc92681037f44cc9872e47a5e1))

### 0.1.2 (2023-06-26)


### Features

* better session name ([41aca04](https://github.com/creasico/laravel-package/commit/41aca0427b7dfda6f8d75c65b8e070b1224e5db8))


### Bug Fixes

* fix session status text ([6dfee58](https://github.com/creasico/laravel-package/commit/6dfee58d938c78455ad404baf14664c8c4f9541d))
* fix stringable object issue ([6b474f4](https://github.com/creasico/laravel-package/commit/6b474f4841b7e37cb376a373ecf570a72579f524))
