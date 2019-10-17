## TYPHP

`typhp` is a simple tool checks whether type hint for arguments or return type declared.
Unlike static analysis tools, it doesn't point out possible errors and issues
but suggests typehint everything possible.

#### For whom?
- Who works projects using PHP 7.1 and higher. 
- Who doesn't want to point out missing type hint and return type declarations in code review process
by using it as part of CI pipeline.
- Who love strict typing

#### Features

- Respects phpdoc; there are some rare cases mixed or compound types are needed.
If such cases documented in phpdoc, `typhp` doesn't complain. For example: `@return array|bool`, `@param mixed $foo`, etc.
- Analyses based on configuration. Include/exclude files and directories to be analysed.
For optional config file, see the [current project example](https://github.com/seferov/typhp/blob/master/.typhp.yml)
- Does NOT modifies your code

## Installation

Install via composer:

`
composer require seferov/typhp
`

To install globally

`
composer global require seferov/typhp
`

Not to have possible dependency conflict, you can download PHAR file from the [latest Github release](https://github.com/seferov/typhp/releases/latest)

## Usage

`
vendor/bin/typhp analyse path
`

If config file (`.typhp.yml`) is present in project root, it can be run
just by `vendor/bin/typhp`

Example output

<img src="example.png" />

## Todo

- [ ] Analyse closures

- [ ] Check by PHP version. For example, don't suppress `@param object` for >= PHP 7.2

- [ ] Better configuration
 
- [ ] Check `declare(strict_types=1)` by config

- [x] Phar file

- [ ] Github Actions
