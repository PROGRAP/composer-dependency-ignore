# Composer Dependency Ignore

This composer plugin allows for some dependencies to be ignored during
installation and update.

## Usage

Install the composer plugin via composer.

```bash
composer require prograp/composer-dependency-ignore
```

Add dependencies to your ignore list in your `composer.json`.
```JSON
{
    "extra": {
        "ignore": [
            "vendor/package-1",
            "vendor/package-2"
        ]
    }
}
```

run `composer update` to remove ignored dependencies from lock file.
