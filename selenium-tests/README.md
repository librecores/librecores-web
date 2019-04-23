# Selenium Tests for LibreCores

Tests generated using the [Selenium IDE][1] for [LibreCores][2]

Currently tests run against Firefox and Chrome

## Running the tests

### Pre-requisites
1. [NodeJS][3]
2. Firefox or Chrome

### Running the tests
1. Perform an `npm install` to install dependencies (one-time only)
2. `npm test` or `npm run test:staging:chrome` Runs tests against [LibreCores Staging][4] using Chrome
3. `npm run test:staging:firefox` Runs tests against [LibreCores Staging][4] using Firefox
3. `npm run test:local:chrome` Runs tests against [local dev environment][5] using Chrome
3. `npm run test:local:firefox` Runs tests against [local dev environment][5] using Firefox

## Modifying the tests

Install the Selenium IDE plugin in the browser and open `LibreCores.side` in the Selenium IDE to make modifications

[1]: https://www.seleniumhq.org/selenium-ide/
[2]: https://www.librecores.org
[3]: https://nodejs.org
[4]: https://www.stage.librecores.org
[5]: http://www.librecores.devel