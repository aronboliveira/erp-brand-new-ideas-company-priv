TRANSLATION_CONFIG = {
    # Directories to search in.
    "directories": [
        "app",
        "resources",
        "Modules/LandingPage/Http",
        "Modules/LandingPage/Resources",
    ],
    # Directories to exclude from search.
    "excluded_directories": [],
    # File patterns to search for.
    "patterns": [
        "*.php",
        "*.js",
    ],
    # Indicates whether new lines are allowed in translations.
    "allow_newlines": False,
    # Translation function names.
    "functions": [
        "__",
        "_t",
        "@lang",
    ],
    # Whether to sort the translations alphabetically by original strings (keys).
    "sort_keys": True,
    # Whether keys from the persistent-strings file should be also added
    # to translation files automatically on export if they don't yet exist there.
    "add_persistent_strings_to_translations": False,
    # Whether it's necessary to exclude Laravel translation keys
    # from the resulting language file if they have corresponding translations.
    "exclude_translation_keys": False,
    # Whether untranslated strings should be put at the top of a translation file.
    "put_untranslated_strings_at_the_top": False,
}
