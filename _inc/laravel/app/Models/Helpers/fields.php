<?php

use App\Config\Constants\PermissionsConstants;
use Illuminate\{
    Database\Schema\Blueprint,
    Http\UploadedFile,
    Support\Facades\Log,
    Validation\ValidationException
};
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Configuration Constants
|--------------------------------------------------------------------------
*/

const VOID = ['blank' => true, 'null' => true];
const TINY_BLANK_CHAR = ['max_length' => 63, 'blank' => true];
const SHORT_BLANK_CHAR = ['max_length' => 126, 'blank' => true];

const LANG_CHOICES = [
    'ar'    => 'العربية',
    'zh'    => '中文',
    'da'    => 'Dansk',
    'de'    => 'Deutsch',
    'en'    => 'English',
    'es'    => 'Español',
    'fr'    => 'Français',
    'he'    => 'עברית',
    'it'    => 'Italiano',
    'ja'    => '日本語',
    'nl'    => 'Nederlands',
    'pl'    => 'Polski',
    'ru'    => 'Русский',
    'pt'    => 'Português',
    'tr'    => 'Türkçe',
    'pt-br' => 'Português (Brasil)',
];

const ENGLISH_NAME_DICT = [
    'ar'    => 'Arabic',
    'zh'    => 'Chinese',
    'da'    => 'Danish',
    'de'    => 'German',
    'en'    => 'English',
    'es'    => 'Spanish',
    'fr'    => 'French',
    'he'    => 'Hebrew',
    'it'    => 'Italian',
    'ja'    => 'Japanese',
    'nl'    => 'Dutch',
    'pl'    => 'Polish',
    'ru'    => 'Russian',
    'pt'    => 'Portuguese',
    'tr'    => 'Turkish',
    'pt-br' => 'Portuguese (Brazil)',
];

const PRIORITY_CHOICES = [
    'critical' => 'Critical',
    'high'     => 'High',
    'medium'   => 'Medium',
    'low'      => 'Low',
];

const PRIORITY_COLOR = [
    '#d32f2f' => 'Danger',
    '#ed6c02' => 'Warning',
    '#1976d2' => 'Primary',
    '#0288d1' => 'Info',
    '#0000'   => 'Ignored',
];

const COMPLETION_CHOICES = [
    'pending' => 'Pending',
    'started' => 'Started',
    'done'    => 'Completed',
    'failed'  => 'Failed',
    'canceled' => 'Terminated',
];

const FIN_STATUS_CHOICES = [
    'draft'          => 'Draft',
    'sent'           => 'Sent',
    'unpaid'         => 'Unpaid',
    'partially_paid'  => 'Partially Paid',
    'paid'           => 'Paid',
];

const GENDER_CHOICES = [
    'male'       => 'Male',
    'female'     => 'Female',
    'non_binary' => 'Non-binary',
    'others'     => 'Others',
];

const EVAL_CHOICES = [
    'poor'          => 'Poor',
    'below_average' => 'Below Average',
    'average'       => 'Average',
    'good'          => 'Good',
    'very_good'     => 'Very Good',
    'excellent'     => 'Excellent',
];

const USER_TYPES = [
    'common'   => 'Common',
    PermissionsConstants::SA => 'Super Administrator',
    PermissionsConstants::ADM    => 'Administrator',
    'support'  => 'Support',
    'customer' => 'Customer',
    PermissionsConstants::VD   => 'Vendor',
    'other'    => 'Other',
];

const DEFAULT_USER_TYPE = [
    'max_length' => 63,
    'default'    => 'common',
    'choices'    => USER_TYPES,
];

const PAYMENT_METHODS = [
    'card'           => 'Card',
    'debit'          => 'Debit',
    'pix'            => 'PIX',
    'cash'           => 'Cash',
    'bank_transfer'  => 'Bank Transfer',
    'benefit'        => 'Benefit',
    'online_service' => 'Online Service',
    'other'          => 'Other',
];

const DEFAULTED_PAY_METHODS = [
    'default'    => 'other',
    'choices'    => PAYMENT_METHODS,
    'max_length' => 126,
];

const VALID_FILE_SIZES = [
    'min' => 0,
    'max' => 2147483647,
];

const CC_VALIDATOR_REGEX             = '/^\d{13,19}$/';
const CURRENCY_ACRONYM_VALIDATOR_REGEX = '/^[A-Z]{3}$/';
const UUID_VALIDATOR_REGEX           = '/^[0-9a-fA-F-]{36}$/';
const PHONE_VALIDATOR_REGEX          = '/^\+?(\d[\d\s-]{7,}\d)$/';
const ZIP_VALIDATOR_REGEX            = '/^[A-Za-z0-9-]{2,20}$/';

const COLOR_HEX_PATTERN  = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3}|[A-Fa-f0-9]{8})$/';
const COLOR_RGB_PATTERN  = '/^rgb\(\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*\)$/';
const COLOR_RGBA_PATTERN = '/^rgba\(\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*,\s*(0|1|0?\.\d+)\s*\)$/';
const COLOR_HSL_PATTERN  = '/^hsl\(\s*(\d{1,3})\s*,\s*(\d{1,3}%)\s*,\s*(\d{1,3}%)\s*\)$/';
const COLOR_HSLA_PATTERN = '/^hsla\(\s*(\d{1,3})\s*,\s*(\d{1,3}%)\s*,\s*(\d{1,3}%)\s*,\s*(0|1|0?\.\d+)\s*\)$/';

const NAMED_COLORS = [
    'indianred', 'lightcoral', 'salmon', 'darksalmon', 'lightsalmon', 'crimson', 'red',
    'firebrick', 'darkred', 'pink', 'lightpink', 'hotpink', 'deeppink', 'mediumvioletred',
    'palevioletred', 'mistyrose', 'orange', 'darkorange', 'coral', 'tomato', 'orangered',
    'sienna', 'sandybrown', 'gold', 'yellow', 'lightyellow', 'lemonchiffon',
    'lightgoldenrodyellow', 'papayawhip', 'moccasin', 'peachpuff', 'palegoldenrod',
    'khaki', 'darkkhaki', 'greenyellow', 'chartreuse', 'lawngreen', 'lime', 'limegreen',
    'palegreen', 'lightgreen', 'mediumspringgreen', 'springgreen', 'mediumseagreen',
    'seagreen', 'forestgreen', 'green', 'darkgreen', 'yellowgreen', 'olivedrab', 'olive',
    'darkolivegreen', 'mediumaquamarine', 'darkseagreen', 'lightseagreen', 'darkcyan',
    'teal', 'aqua', 'cyan', 'lightcyan', 'paleturquoise', 'aquamarine', 'turquoise',
    'mediumturquoise', 'darkturquoise', 'cadetblue', 'steelblue', 'lightsteelblue',
    'powderblue', 'lightblue', 'skyblue', 'lightskyblue', 'deepskyblue', 'dodgerblue',
    'cornflowerblue', 'royalblue', 'blue', 'mediumblue', 'darkblue', 'navy',
    'midnightblue', 'mediumslateblue', 'slateblue', 'darkslateblue', 'lavendor',
    'thistle', 'plum', 'violet', 'orchid', 'fuchsia', 'magenta', 'mediumorchid',
    'mediumpurple', 'blueviolet', 'darkviolet', 'darkorchid', 'darkmagenta',
    'purple', 'rebeccapurple', 'indigo', 'cornsilk', 'blanchedalmond', 'bisque',
    'navajowhite', 'wheat', 'burlywood', 'tan', 'rosybrown', 'peru', 'chocolate',
    'saddlebrown', 'maroon', 'brown', 'white', 'snow', 'honeydew', 'mintcream', 'azure',
    'aliceblue', 'ghostwhite', 'whitesmoke', 'seashell', 'beige', 'oldlace', 'floralwhite',
    'ivory', 'antiquewhite', 'linen', 'lavendorblush', 'gainsboro', 'lightgray',
    'lightgrey', 'silver', 'darkgray', 'darkgrey', 'gray', 'grey', 'dimgray', 'dimgrey',
    'lightslategray', 'lightslategrey', 'slategray', 'slategrey', 'darkslategray',
    'darkslategrey', 'black', 'transparent',
];

/*
|--------------------------------------------------------------------------
| Validation Functions
|--------------------------------------------------------------------------
*/

function worded_name_validator(string $value): bool
{
    // only letters, combining marks, spaces, hyphens
    if (!preg_match('/^[\p{L}\p{M} \-]+$/u', $value)) {
        throw ValidationException::withMessages([
            'value' => "Invalid character in name."
        ]);
    }
    return true;
}

function validate_uuid(string $value): bool
{
    if (!preg_match(UUID_VALIDATOR_REGEX, $value))
        throw ValidationException::withMessages([
            'value' => 'Value must be a valid UUID string'
        ]);
    return true;
}

function validate_phone(string $value): bool
{
    if (!preg_match(PHONE_VALIDATOR_REGEX, $value))
        throw ValidationException::withMessages([
            'value' => 'Enter a valid phone number with at least 9 digits (supports +, spaces, and -).'
        ]);
    return true;
}

function validate_zip(string $value): bool
{
    if (!preg_match(ZIP_VALIDATOR_REGEX, $value))
        throw ValidationException::withMessages([
            'value' => 'Invalid ZIP code'
        ]);
    return true;
}

function validate_credit_card(string $value): bool
{
    if (!preg_match(CC_VALIDATOR_REGEX, $value))
        throw ValidationException::withMessages([
            'value' => 'Credit Card numbers must be between 13 and 19 digits'
        ]);
    return true;
}

function validate_currency_acronym(string $value): bool
{
    if (!preg_match(CURRENCY_ACRONYM_VALIDATOR_REGEX, $value))
        throw ValidationException::withMessages([
            'value' => 'Enter a valid 3-letter currency code (e.g. USD, EUR)'
        ]);
    return true;
}

function validate_month(int $month): bool
{
    if ($month < 1)
        throw ValidationException::withMessages([
            'month' => 'Month must be at least 1'
        ]);
    if ($month > 12)
        throw ValidationException::withMessages([
            'month' => 'Month must be less than 12'
        ]);
    return true;
}

function validate_year(int $year): bool
{
    if ($year < 1000)
        throw ValidationException::withMessages([
            'year' => 'Year must be at least 1000'
        ]);
    if ($year > 9999)
        throw ValidationException::withMessages([
            'year' => 'Year must be 9999 at maximum'
        ]);
    return true;
}

function validate_days_interval(int $days): bool
{
    if ($days < 1)
        throw ValidationException::withMessages([
            'days' => 'Value must be at least 1 day'
        ]);
    if ($days > 365)
        throw ValidationException::withMessages([
            'days' => 'Value must be up to 1 year'
        ]);
    return true;
}

function validate_duration(int $minutes): bool
{
    if ($minutes < 0)
        throw ValidationException::withMessages([
            'minutes' => 'Minutes must be whole numbers'
        ]);
    if ($minutes > 1440)
        throw ValidationException::withMessages([
            'minutes' => 'Minutes must be up to 1440 (1 day)'
        ]);
    return true;
}

function file_size_validator(UploadedFile $file): bool
{
    $size = $file->getSize();
    if ($size < VALID_FILE_SIZES['min'])
        throw ValidationException::withMessages([
            'file' => 'File size below acceptable'
        ]);
    if ($size > VALID_FILE_SIZES['max'])
        throw ValidationException::withMessages([
            'file' => 'File size must be up to 2GB'
        ]);
    return true;
}

function color_name_validator(string $color): bool
{
    $lower = strtolower($color);
    if (
        in_array($lower, NAMED_COLORS, true)
        || preg_match(COLOR_HEX_PATTERN, $color)
        || preg_match(COLOR_RGB_PATTERN, $color)
        || preg_match(COLOR_RGBA_PATTERN, $color)
        || preg_match(COLOR_HSL_PATTERN, $color)
        || preg_match(COLOR_HSLA_PATTERN, $color)
    )
        return true;
    throw ValidationException::withMessages([
        'color' => "'{$color}' is not a valid color. Use HEX (#RRGGBB), RGB/RGBA, HSL/HSLA, or a named color."
    ]);
}

/*
|--------------------------------------------------------------------------
| Miscellaneous Helpers
|--------------------------------------------------------------------------
*/

function max_safe_decimal(): array
{
    return [
        'min' => '0.00',
        'max' => '999999999999999999999999999999999999999999999999999999999999999.999999999999999999999999999999'
    ];
}

function default_decimal_12(
    Blueprint $table,
    string $column,
    int $md = 12,
    int $dp = 2,
    string $df = '0.00',
    array $options = []
) {
    $col = $table->decimal($column, $md, $dp)->default($df);
    foreach ($options as $method => $value) {
        if (method_exists($col, $method)) {
            $col->{$method}($value);
        }
    }
    return $col;
}

function defalt_decimal_10(
    Blueprint $table,
    string $column,
    array $options = []
) {
    // note the original Python had a typo; this matches name
    $col = $table->decimal($column, 10, 2);
    foreach ($options as $method => $value) {
        if (method_exists($col, $method)) {
            $col->{$method}($value);
        }
    }
    return $col;
}

function default_char_field(
    Blueprint $table,
    string $column,
    bool $voidable = false,
    array $options = []
) {
    $col = $table->string($column, 254);
    if ($voidable) {
        $col->nullable();
    }
    foreach ($options as $method => $value) {
        if (method_exists($col, $method)) {
            $col->{$method}($value);
        }
    }
    return $col;
}

function default_text_field(
    Blueprint $table,
    string $column,
    bool $voidable = false,
    string $length = 'small',
    array $options = []
) {
    switch ($length) {
        case 'tiny':
            $col = $table->string($column, 127);
            break;
        case 'medium':
            $col = $table->mediumText($column);
            break;
        case 'long':
            $col = $table->longText($column);
            break;
        default:
            $col = $table->text($column);
    }
    if ($voidable) {
        $col->nullable();
    }
    foreach ($options as $method => $value) {
        if (method_exists($col, $method)) {
            $col->{$method}($value);
        }
    }
    return $col;
}

function uuid_def_primary(
    Blueprint $table,
    string $column = 'id',
    bool $primary = true
) {
    $col = $table->uuid($column);
    if ($primary) {
        $col->primary();
    }
    return $col;
}

function default_user_creation(
    Blueprint $table,
    string $column = 'user_id',
    array $options = []
) {
    $col = $table
        ->foreignId($column)
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    foreach ($options as $method => $value) {
        if (method_exists($col, $method)) {
            $col->{$method}($value);
        }
    }

    return $col;
}

function valid_mysql_min_date($value): bool
{
    $date = Carbon::parse($value);
    if ($date->lt('1000-01-01')) {
        Log::warning("Date lower than valid passed: {$value}");
        throw ValidationException::withMessages([
            'date' => 'Date must not be before 1000-01-01'
        ]);
    }
    if ($date->gt('9999-12-31')) {
        Log::warning("Date higher than valid passed: {$value}");
        throw ValidationException::withMessages([
            'date' => 'Date must not be after 9999-12-31'
        ]);
    }
    return true;
}
