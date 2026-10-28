# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 2.12.0 - 2026-10-28
### Fixed
- An amount or operand ending in a line feed is now rejected instead of silently becoming `0`. `"12.34\n"` and `"1234\n"` used to pass validation and then count as `0` in bcmath on PHP 7, or throw `ValueError` on PHP 8. Affected public entry points:
  - `new Money()`, `Money::create()`, `Money::createFromCents()`, `Money::createFromMinorUnits()` and `Money::createFromNoDelimiterAmount()` throw `MoneyException`
  - `Money::mul()` and `Money::div()` validate their operand and throw `MoneyException` (on PHP 7, `div("2\n")` used to return `0` with a division-by-zero warning)
  - `MoneyFactory::create()` and `MoneyFactory::createFromCents()` throw `InvalidAmountException`
  - `MoneyNormalizer::mapToEntity()`, `mapFromAmount()`, `mapFromCents()` and `mapFromMinorUnits()` throw `InvalidDataException('Invalid amount specified')`

  Amounts read line by line (e.g. with `fgets`) must be trimmed before they are passed in.
- `Money::mul()` and `Money::div()` pass a float operand to bcmath in plain decimal notation, with the same 14 significant digits PHP itself prints for the float (its default `precision=14`) and independent of the locale. A float below `1e-4` or of at least `1e14`, which PHP writes in scientific notation (e.g. `3.5E-5`, `1.5E+14`), used to count as `0` on PHP 7 and throw `ValueError` on PHP 8; it now gives the real result. The same applies on PHP 7 under a locale with a decimal comma, where every float was written as e.g. `1,5`. Every other float gives the same result as before with the default `precision=14`. Only `mul()` and `div()` convert floats: `new Money()` still rejects a float PHP writes in scientific notation, e.g. `new Money(1.0E+15)`
- `Money::createFromNoDelimiterAmount()` with a fraction of `0` keeps every digit: `('1234', 'JPY', 0)` used to give `0.1234`
- `Money::createFromNoDelimiterAmount()` accepts a leading `+` and drops it: `"+5"` gives `0.05` (it used to fail with `Amount has invalid chars: 0.+5`), and `"+1234"` gives `12.34` (it used to keep the sign: `getAmount()` returned `+12.34`, the same value)

### Changed
- `Money::createFromNoDelimiterAmount()` validates its raw input first and reports the value it was given (`Invalid amount without delimiter: …`). A sign with no digits (`"-"`, `"+"`) is rejected; `"-"` used to give `-0.00`
- `MoneyFactory::create()` reports an invalid or non-scalar amount as `InvalidAmountException` and an unsupported or non-string currency as `InvalidCurrencyException` (previously `MoneyException`, or a PHP warning or error for an array or an object; the original exception is kept as the previous one). An amount object with `__toString()` is accepted, as by `mul()`/`div()`. **Upgrade note:** these Maba exceptions do not extend `MoneyException`; code that caught `MoneyException` around `MoneyFactory::create()` should catch `Maba\Component\Monetary\Exception\MonetaryException` or `\InvalidArgumentException`
- `MoneyFactory::createFromCents()` accepts integer cents only, like `Money::createFromCents()`: a decimal value such as `12.5` (which used to give `0.125`), a fractional float or a non-scalar value throws `InvalidAmountException` instead of Maba's `InvalidNumberException`. An integral float below `1e14` such as `1050.0` is still accepted, and a leading `+` (`"+500"`) is still rejected, as before
- `Money::mul()` and `Money::div()` reject a string operand bcmath cannot read as a number (a trailing line feed, a decimal comma such as `"2,5"`), an array, and `NAN`/`INF`. Integers, `".5"`, `"5."` and stringable objects still work, and `null`/`false` still count as `0`. `div(null)` now throws `MoneyException('Division by zero')`, like `div(false)` and `div('0')`; it used to throw a plain `\Exception('Right operand is empty/zero!')` from Maba's BcMath
- `Money::createFromNoDelimiterAmount()` rejects a fraction that is not an `int` (e.g. `null`, `'2'`, `2.0`), or is negative, with `MoneyException`. A negative fraction used to give a wrong amount with a warning on PHP 7 (`('1234', 'EUR', -1)` gave `1.234`) and a `ValueError` on PHP 8; `null` was read as `0` (`('5', 'EUR', null)` gave `0.5`), and `'2'` and `2.0` worked like `2`
- `MoneyException` messages thrown by `Money` escape control characters and backslashes in the rejected amount, operand or currency (`"12.34\n"` is reported as `12.34\n`), so a message can no longer split a log line. Messages from `CurrencyIsoNumber` are unchanged

## 2.11.0 - 2026-09-30
### Added
- Add Symfony 7.4 support

## 2.10.0 - 2025-04-23
### Added
- South Sudanese pound currency money fraction

## 2.9.0 - 2024-12-16
### Added
- Add Symfony 6 support

## 2.8.0 - 2023-04-20
### Removed
- Drop support for PHP 5.6

### Changed
- Mark `Money::setAmount()` as `@internal`
- Mark `Money::setCurrency()` as `@internal`
- Mark `Money::setAmountInCents()` as `@internal`
- Mark `Money::setAmountInMinorUnits()` as `@internal`
- Update PHPUnit version
- Migrate from Travis CI to GitHub Actions

### Deprecated
- Deprecate `Money::setAmountInCents()` in favor of `Money::setAmountInMinorUnits()`
- Deprecate `Money::getAmountInCents()` in favor of `Money::getAmountInMinorUnits()`

## 2.7.0 - 2023-04-05
### Added
- Add license (#21)
- Add missing soft dependencies (#22)

### Changed
- Switch to PSR-4 autoloader (#23)

## 2.6.0 - 2021-09-13
### Added
- Mauritanian Ouguiya currency iso number
- Mauritanian Ouguiya currency money fraction

## 2.5.0 - 2020-09-25
### Added
- Added Money::createFromMinorUnits to return Money object from minor units
- Added MoneyNormalizer::mapFromMinorUnits to map a Money object from minor units
### Deprecated
- Deprecated Money::createFromCents - more abstract Money::createFromMinorUnits is to be used instead 
- Deprecated MoneyNormalizer::mapFromCents - MoneyNormalizer::mapFromMinorUnits is to be used instead
## 2.4.3 - 2020-09-25
### Changed
- Changed Money::clearAmountValue does not use float anymore for higher precision

## 2.4.2 - 2020-09-23
### Changed
- Changed Money::clearAmountValue clearing capabilities

## 2.4.1 - 2020-08-14
### Changed
- Changed Money::clearAmountValue return from float to string

## 2.4.0 - 2020-07-28
### Changed
- Updated `XAU` fractions

## 2.3.1 - 2020-03-21
### Changed
- Updated `maba/monetary` version

## 2.3.0 - 2020-03-21
### Added
- Added `XAU`, `XAG`, `XPT` as currencies

### Changed
- `\Evp\Component\Money\MoneyException` will extend `\InvalidArgumentException` instead of `\Exception`
- `\Evp\Component\Money\MoneyException` will be thrown instead of `\InvalidArgumentException`

## 2.2.0
### Added
- Venezuelan bolivar currency money fraction

## 2.1.0
### Added
- Venezuelan bolivar currency iso number

## 2.0.0
### Changed
- MoneyNormalizer will now format amount taking into consideration the currency's decimal places.
If Money object was used with numbers beyond the currency's decimal places this will cause truncation. 
See an example below. 

Old behavior:
````
{
  "amount": "42.421234",
  "currency": "EUR"
}
````

New behavior:
````
{
  "amount": "42.42",
  "currency": "EUR"
}
````


## 1.6.2
### Added
- Released to public
