Suit yourself with the project setup and study all the architecture and workflows, using the attached .yml as your starting guideline.

It must be absolutely clear that we are only working with \_inc/ and the app itself is in \_inc/laravel/ (the remaining folders are for utilities and OS/infrastructures/notes).

Considering that, you will make a full analysis of the state of the system:

1. You will attempt to boot the system fresh, using the composer serve-sh--hard from the start, dynamically adjusting the seeders used by artisan to a mininum amount of a viable production dataset of the crafted entities. WHILE you find errors, you will attempt fixing them and restart fresh;
2. You will run every single suite of testing/linting libraries in the main app and within every subpackage (many are found in the test folder, for content like mock pages, and you can create more with the content you need to simulate). The whole set of libs are: phpunit,phpstan,jest,playwright,eslint,tsc,flake8,mypy. Whenever you find an error, you will fix it and restart the suite of the test;
3. You will run suites of tests to use curl,wget and mysql to verify the results. WHILE you find failure codes with %{http_code}, broken %{speed_download} or %{time_transfer} OR views that render empty html bodies OR fail to deliver critical content (like tables, reports, grids, cards, financial results in numbers, chip data, etc.), you will fix the error, restart the test suite, and if necessary return to the testing library stage AND even to the reseeding stage;

YOU CANNOT:

- Change ANY migration file;
- Change ANY file in .seeders/;

YOU SHOULD:

- Periodically take notes written in file within tmp/copilot/;
- Make rsyncs if necessary to .backup/, but make sure to cleanup after succeding in the task;

---

This is a LONG process, so respect the directives in where-to-update-and-read.yml to keep it stable and seamless as much as possible, and separate in large batches as you deem fit (ask me if necessary about the criteria).
