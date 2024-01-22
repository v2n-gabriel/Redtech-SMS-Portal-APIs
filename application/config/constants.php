<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


defined("TABLE_PREFIX") or define("TABLE_PREFIX", "");
defined("TABLE_PREFIX_DEV") or define("TABLE_PREFIX_DEV", "dev_");

defined("ENV_DEV") or define("ENV_DEV", "dev");
defined("ENV_PROD") or define("ENV_PROD", "live");


defined("TABLE_USER_CATEGORY") or define("TABLE_USER_CATEGORY", "user_category");
defined("TABLE_USER") or define("TABLE_USER", "user");
defined("TABLE_SESSION") or define("TABLE_SESSION", "session");
defined("TABLE_SMS") or define("TABLE_SMS", "sms");
defined("TABLE_BATCH") or define("TABLE_BATCH", "batch");
defined("TABLE_EMAIL") or define("TABLE_EMAIL", "email");
defined("TABLE_USER_BILLER") or define("TABLE_USER_BILLER", "user_biller");

defined("USER_CATEGORY_USER") or define("USER_CATEGORY_USER", "User");
defined("USER_CATEGORY_ADMIN") or define("USER_CATEGORY_ADMIN", "Admin");
defined("USER_CATEGORY_PORTALAPI") or define("USER_CATEGORY_PORTALAPI", "PortalApi");
defined("USER_CATEGORY_SUPERADMIN") or define("USER_CATEGORY_SUPERADMIN", "SuperAdmin");

defined("FILTER_API_DRIVEN") or define("FILTER_API_DRIVEN", "API Driven");


defined("STATUS_ACTIVE") or define("STATUS_ACTIVE", "Active");
defined("STATUS_INACTIVE") or define("STATUS_INACTIVE", "Inactive");
defined("STATUS_DISABLED") or define("STATUS_DISABLED", "Disabled");

defined("USER_TYPE_PREPAID") or define("USER_TYPE_PREPAID", "Prepaid");
defined("USER_TYPE_POSTPAID") or define("USER_TYPE_POSTPAID", "Postpaid");

defined("COL_ID") or define("COL_ID", "id");
defined("COL_CATEGORY") or define("COL_CATEGORY", "category");
defined("COL_EMAIL_ADDRESS") or define("COL_EMAIL_ADDRESS", "email_address");
defined("COL_PHONE_NUMBER") or define("COL_PHONE_NUMBER", "phone_number");
defined("COL_API_USERNAME") or define("COL_API_USERNAME", "api_username");
defined("COL_API_PASSWORD") or define("COL_API_PASSWORD", "api_password");
defined("COL_PORTAL_USERNAME") or define("COL_PORTAL_USERNAME", "portal_username");
defined("COL_PORTAL_PASSWORD") or define("COL_PORTAL_PASSWORD", "portal_password");
defined("COL_IP_ADDRESS") or define("COL_IP_ADDRESS", "ip_address");
defined("COL_STATUS") or define("COL_STATUS", "status");
defined("COL_UNIQUE_ID") or define("COL_UNIQUE_ID", "unique_id");
defined("COL_USER_ID") or define("COL_USER_ID", "user_id");
defined("COL_REQUEST_ID") or define("COL_REQUEST_ID", "request_id");
defined("COL_SENDER_ID") or define("COL_SENDER_ID", "sender_id");
defined("COL_RECEIVER") or define("COL_RECEIVER", "receiver");
defined("COL_MESSAGE") or define("COL_MESSAGE", "message");
defined("COL_PAGES") or define("COL_PAGES", "pages");
defined("COL_COMPANY_NAME") or define("COL_COMPANY_NAME", "company_name");
defined("COL_TYPE") or define("COL_TYPE", "type");
defined("COL_COMMISSION") or define("COL_COMMISSION", "commission");
defined("COL_BILLER_CATEGORY") or define("COL_BILLER_CATEGORY", "biller_category");
defined("COL_BILLER_ID") or define("COL_BILLER_ID", "biller_id");
defined("COL_BILLER_DESCRIPTION") or define("COL_BILLER_DESCRIPTION", "biller_description");
defined("COL_WALLET_BALANCE") or define("COL_WALLET_BALANCE", "BALANCE");
defined("COL_ENVIRONMENT") or define("COL_ENVIRONMENT", "environment");

defined("COL_SENDER_NAME") or define("COL_SENDER_NAME", "sender_name");
defined("COL_SENDER_EMAIL") or define("COL_SENDER_EMAIL", "sender_email");
defined("COL_SUBJECT") or define("COL_SUBJECT", "subject");

defined("COL_SESSION_ID") or define("COL_SESSION_ID", "session_id");
defined("COL_DATE_MODIFIED") or define("COL_DATE_MODIFIED", "date_modified");
defined("COL_DATE_CREATED") or define("COL_DATE_CREATED", "date_created");
defined("COL_BATCH_ID") or define("COL_BATCH_ID", "batch_id");
defined("COL_TITLE") or define("COL_TITLE", "title");
defined("COL_SCHEDULED_DATE") or define("COL_SCHEDULED_DATE", "scheduled_date");


defined("CATEGORY_USER") or define("CATEGORY_USER", "User");
defined("CATEGORY_ADMIN") or define("CATEGORY_ADMIN", "Admin");
defined("CATEGORY_SUPERADMIN") or define("CATEGORY_SUPERADMIN", "SuperAdmin");
defined("CATEGORY_PORTALAPI") or define("CATEGORY_PORTALAPI", "PortalApi");

defined("BILLER_CATEGORY_DISCO") or define("BILLER_CATEGORY_DISCO", "disco");
defined("BILLER_CATEGORY_TV") or define("BILLER_CATEGORY_TV", "tv");
defined("BILLER_CATEGORY_EDUCATION") or define("BILLER_CATEGORY_EDUCATION", "education");
defined("BILLER_CATEGORY_VTU") or define("BILLER_CATEGORY_VTU", "vtu");
defined("BILLER_CATEGORY_BANKING") or define("BILLER_CATEGORY_BANKING", "banking");
defined("BILLER_CATEGORY_BET") or define("BILLER_CATEGORY_BET", "bet");
defined("BILLER_CATEGORY_INTERNET") or define("BILLER_CATEGORY_INTERNET", "internet");
defined("BILLER_CATEGORY_TOLL") or define("BILLER_CATEGORY_TOLL", "toll");

defined("WALLET_OP_CREDIT") or define("WALLET_OP_CREDIT", "Credit");
defined("WALLET_OP_DEBIT") or define("WALLET_OP_DEBIT", "Debit");

defined("USER_TYPE_PREPAID") or define("USER_TYPE_PREPAID", "Prepaid");
defined("USER_TYPE_POSTPAID") or define("USER_TYPE_POSTPAID", "Postpaid");

defined("WALLET_OP_TYPE_TRANSACTION_DEBIT") or define("WALLET_OP_TYPE_TRANSACTION_DEBIT", "Transaction Debit");
defined("WALLET_OP_TYPE_REVERSAL_CREDIT") or define("WALLET_OP_TYPE_REVERSAL_CREDIT", "Reversal Credit");
defined("WALLET_OP_TYPE_COMMISSION_CREDIT") or define("WALLET_OP_TYPE_COMMISSION_CREDIT", "Commission Credit");
defined("WALLET_OP_TYPE_WALLET_CREDIT") or define("WALLET_OP_TYPE_WALLET_CREDIT", "Wallet Credit");
defined("WALLET_OP_TYPE_WALLET_DEBIT") or define("WALLET_OP_TYPE_WALLET_DEBIT", "Wallet Debit");