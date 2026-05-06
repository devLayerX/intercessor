# Intercessor — Test Suite

## Structure

```
tests/
├── bootstrap-unit.php          Unit test bootstrap (WP stubs + autoloader, no DB)
├── bootstrap-integration.php   Integration test bootstrap (real WP + DB)
├── stubs/
│   └── wordpress-stubs.php     Minimal WP function stubs for unit tests
├── Unit/
│   ├── Admin/Settings/
│   │   ├── RegistryTest.php    Settings schema — tab/section/field access
│   │   ├── RepositoryTest.php  Settings persistence (get/update/delete/replace)
│   │   └── SanitizerTest.php   Field-type sanitization (checkbox, number, email…)
│   ├── Database/Row/
│   │   ├── Prayer_RequestTest.php  Status helpers (is_pending, is_approved…)
│   │   ├── Prayer_NoteTest.php     is_private(), type casting
│   │   ├── Prayer_HistoryTest.php  Audit record structure
│   │   ├── Prayed_CountTest.php    is_from_user(), is_anonymous()
│   │   └── RequesterTest.php       is_linked_to_user(), get_display_name()
│   ├── Http/
│   │   └── RequestTest.php     Input access, typed accessors, nonce, unslashing
│   ├── Tools/
│   │   └── Settings_ExporterTest.php  Boolean normalisation (Yes/No logic)
│   └── Util/
│       ├── AutoloaderTest.php       PSR-4 namespace→file mapping
│       ├── Profanity_FilterTest.php Word boundary matching, cache, build_moderator_note
│       └── RecaptchaTest.php        Configuration helpers (version, threshold, html)
└── Integration/
    ├── Database/
    │   ├── Prayer_Request_QueryTest.php  Full CRUD + update_status + bulk ops
    │   ├── Prayer_Note_QueryTest.php     add_note, get_for_request, delete_all
    │   ├── Prayer_History_QueryTest.php  History written by update_status
    │   ├── Prayed_Count_QueryTest.php    record_prayer, get_total, find_by_actor
    │   └── Requester_QueryTest.php       find_or_create deduplication
    └── Util/
        └── Rate_LimiterTest.php          is_allowed() against real DB rows
```

---

## Running the unit tests

Unit tests have **no external dependencies** — no WordPress, no database.

```bash
# Install PHPUnit
composer install

# Run all unit tests (default)
vendor/bin/phpunit

# Run a specific test class
vendor/bin/phpunit tests/Unit/Util/Profanity_FilterTest.php

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage/
```

---

## Running the integration tests

Integration tests require a WordPress test environment and a dedicated test database.

### 1. Set up the WordPress test library

```bash
# Clone wordpress-develop or use the official test scaffold
svn co https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/ /tmp/wordpress-tests-lib/includes
```

Or use [wp-cli/scaffold](https://developer.wordpress.org/cli/commands/scaffold/plugin-tests/):

```bash
wp scaffold plugin-tests intercessor
bash bin/install-wp-tests.sh intercessor_tests root '' localhost latest
```

### 2. Configure environment variables

```bash
export WP_TESTS_DIR=/tmp/wordpress-tests-lib
export WP_TESTS_DB_NAME=intercessor_tests
export WP_TESTS_DB_USER=root
export WP_TESTS_DB_PASS=
export WP_TESTS_DB_HOST=localhost
```

### 3. Run the integration suite

```bash
vendor/bin/phpunit --testsuite integration
```

---

## Test count summary

| Suite       | Files | Test methods |
|-------------|-------|-------------|
| Unit        | 11    | ~110        |
| Integration | 5     | ~45         |
| **Total**   | **16**| **~155**    |

---

## Adding new tests

- **Pure PHP logic** (no WP functions needed) → `tests/Unit/`
- **Needs `get_option`, database rows, or WP hooks** → `tests/Integration/`
- If a new WP function is called by a unit-tested class, add a stub to `tests/stubs/wordpress-stubs.php`
